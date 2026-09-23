<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Retrieve the request body
$payload = file_get_contents('php://input');
// Log the received payload for debugging
$logFilePath = 'webhook.log';
$logMessage = "[" . date('Y-m-d H:i:s') . "] Received Payload: " . $payload . "\n";
file_put_contents($logFilePath, $logMessage, FILE_APPEND | LOCK_EX);

// Log the request headers for debugging
$logMessage = "[" . date('Y-m-d H:i:s') . "] Request Headers: " . var_export(getallheaders(), true) . "\n";
file_put_contents($logFilePath, $logMessage, FILE_APPEND | LOCK_EX);

// ... (Rest of the code)
// Respond with a success message
echo 'Webhook received successfully.';
