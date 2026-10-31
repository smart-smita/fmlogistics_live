<?php

namespace App\Libraries;

use CodeIgniter\Email\Email as CIEmail;

/**
 * Extended Email library for CodeIgniter 4
 *
 * Provides compatibility fixes for CodeIgniter 4.1.5:
 * 1. Correctly uses ssl:// stream wrapper for Port 465 / SSL connections (SMTPS),
 *    preventing the 'tls://' protocol shutdown crash on PHP 8+.
 * 2. Properly handles modern TLS client negotiation for Port 587 / TLS (STARTTLS).
 * 3. Gracefully handles socket writes and destructor QUIT commands to avoid
 *    fatal ErrorException on remote connection drop or authentication failure.
 */
class Email extends CIEmail
{
    /**
     * Establish SMTP connection with appropriate SSL/TLS stream wrappers
     *
     * @return bool
     */
    protected function SMTPConnect()
    {
        if (is_resource($this->SMTPConnect)) {
            return true;
        }

        $ssl = '';
        $port = (int)$this->SMTPPort;
        $crypto = strtolower($this->SMTPCrypto ?? '');

        // Port 465 is SMTPS (Implicit SSL) and requires the ssl:// stream wrapper
        if ($crypto === 'ssl' || $port === 465) {
            $ssl = 'ssl://';
        }

        $this->SMTPConnect = @fsockopen(
            $ssl . $this->SMTPHost,
            $this->SMTPPort,
            $errno,
            $errstr,
            $this->SMTPTimeout
        );

        if (! is_resource($this->SMTPConnect)) {
            $this->setErrorMessage(lang('Email.SMTPError', [$errno . ' ' . $errstr]));
            return false;
        }

        stream_set_timeout($this->SMTPConnect, $this->SMTPTimeout);
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
                $this->setErrorMessage(lang('Email.SMTPError', [$this->getSMTPData()]));
                return false;
            }
        }

        return $this->sendCommand('hello');
    }

    /**
     * Send data over SMTP with error suppression to avoid fatal ErrorException on broken pipe
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
            $this->setErrorMessage(lang('Email.SMTPDataFailure', [$data]));
            return false;
        }

        return true;
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
