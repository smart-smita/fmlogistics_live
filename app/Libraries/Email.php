<?php

namespace App\Libraries;

use CodeIgniter\Email\Email as CIEmail;

/**
 * Extended Email library for CodeIgniter 4 in ALERT Application
 *
 * Diagnostic & Compatibility enhancements:
 * 1. Accurately maps Port 465 / SSL connections to the ssl:// stream wrapper.
 * 2. Implements modern TLS client negotiation for Port 587 / TLS.
 * 3. Enforces a single connection attempt per send request without repetitive loops on timeout.
 * 4. Captures socket-level diagnostic codes ($errno, $errstr) for clear administrator reporting.
 * 5. Sanitizes internal error buffers to ensure credentials are never leaked to logs or UI.
 * 6. Gracefully handles broken-pipe teardowns during PHP shutdown.
 */
class Email extends CIEmail
{
    /**
     * Flag indicating whether a connection attempt has already failed in this request
     *
     * @var bool
     */
    protected $connectionAttemptFailed = false;

    /**
     * Establish SMTP connection with appropriate SSL/TLS stream wrappers and fail-fast timeout
     *
     * @return bool
     */
    protected function SMTPConnect()
    {
        if (is_resource($this->SMTPConnect)) {
            return true;
        }

        // Do not repeatedly retry a connection that has already timed out or failed in this instance
        if ($this->connectionAttemptFailed) {
            return false;
        }

        $ssl = '';
        $port = (int)$this->SMTPPort;
        $crypto = strtolower($this->SMTPCrypto ?? '');

        // Port 465 is SMTPS (Implicit SSL) and requires the ssl:// stream wrapper
        if ($crypto === 'ssl' || $port === 465) {
            $ssl = 'ssl://';
        }

        // Enforce a strict connection timeout (default 10s) to prevent hanging UI
        $connectTimeout = (!empty($this->SMTPTimeout) && (int)$this->SMTPTimeout > 0) 
            ? min((int)$this->SMTPTimeout, 10) 
            : 10;

        $errno = 0;
        $errstr = '';

        $this->SMTPConnect = @fsockopen(
            $ssl . $this->SMTPHost,
            $this->SMTPPort,
            $errno,
            $errstr,
            $connectTimeout
        );

        if (! is_resource($this->SMTPConnect)) {
            $this->connectionAttemptFailed = true;
            $errDetail = trim($errstr);
            if (empty($errDetail) && $errno === 0) {
                $errDetail = 'Connection timed out or network blocked by firewall';
            }
            $this->setErrorMessage(lang('Email.SMTPError', ["SOCKET_ERROR: [{$errno}] {$errDetail}"]));
            return false;
        }

        stream_set_timeout($this->SMTPConnect, $connectTimeout);
        $this->setErrorMessage($this->getSMTPData());

        if ($crypto === 'tls' || ($port === 587 && empty($crypto))) {
            $this->sendCommand('hello');
            $this->sendCommand('starttls');

            $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            }
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
                $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
            }

            $cryptoResult = @stream_socket_enable_crypto(
                $this->SMTPConnect,
                true,
                $cryptoMethod
            );

            if ($cryptoResult !== true) {
                $this->setErrorMessage(lang('Email.SMTPError', ['TLS_HANDSHAKE_ERROR: ' . $this->getSMTPData()]));
                return false;
            }
        }

        return $this->sendCommand('hello');
    }

    /**
     * Send data over SMTP with credential sanitization and error suppression
     *
     * @param string $data
     * @return bool
     */
    protected function sendData($data)
    {
        $data .= $this->newline;

        $result = null;

        for ($written = $timestamp = 0, $length = static::strlen($data); $written < $length; $written += $result) {
            if (!is_resource($this->SMTPConnect) || ($result = @fwrite($this->SMTPConnect, static::substr($data, $written))) === false) {
                break;
            }

            if ($result === 0) {
                if ($timestamp === 0) {
                    $timestamp = time();
                } elseif ($timestamp < (time() - $this->SMTPTimeout)) {
                    $result = false;
                    break;
                }

                usleep(250000);
                continue;
            }
        }

        if ($result === false) {
            // Never expose data contents (which may include credentials) in error buffers
            $this->setErrorMessage(lang('Email.SMTPDataFailure', ['Data transmission interrupted']));
            return false;
        }

        return true;
    }

    /**
     * Clear email instance and reset connection attempt state
     *
     * @param bool $clearAttachments
     * @return Email
     */
    public function clear($clearAttachments = false)
    {
        $this->connectionAttemptFailed = false;
        return parent::clear($clearAttachments);
    }

    /**
     * Graceful destruction to prevent unhandled exceptions during script shutdown
     */
    public function __destruct()
    {
        try {
            if (is_resource($this->SMTPConnect)) {
                @$this->sendCommand('quit');
            }
        } catch (\Throwable $e) {
            // Suppress disconnect errors during script teardown
        }
    }
}
