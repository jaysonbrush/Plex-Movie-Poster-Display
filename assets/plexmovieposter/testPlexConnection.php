<?php
/**
 * PMPD - Test Plex Server Connection
 * AJAX endpoint to verify Plex server connectivity
 */
header('Content-Type: application/json');

// Require login
session_start();
if (!isset($_SESSION['username']) || $_SESSION['username'] === null) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

include_once '../config.php';

// Get parameters (use POST values if provided, otherwise use config)
$server = $_POST['server'] ?? $plexServer;
$token = $_POST['token'] ?? $plexToken;
$useSSL = isset($_POST['ssl']) ? $_POST['ssl'] === '1' : (bool)$plexServerSSL;
$serverDirect = $_POST['serverDirect'] ?? $plexServerDirect;

// Determine URL scheme and server address
if ($useSSL && !empty($serverDirect)) {
    $scheme = 'https';
    $serverAddr = $serverDirect;
} else {
    $scheme = 'http';
    $serverAddr = $server;
}

$testUrl = "{$scheme}://{$serverAddr}:32400/?X-Plex-Token={$token}";

$result = [
    'success' => false,
    'server' => $serverAddr,
    'scheme' => $scheme,
    'error' => null,
    'serverName' => null,
    'version' => null,
    'platform' => null,
    'libraries' => []
];

// Test connection
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $testUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_FOLLOWLOCATION => true
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    $result['error'] = "Connection failed: {$curlError}";
    echo json_encode($result);
    exit();
}

if ($httpCode === 401) {
    $result['error'] = "Authentication failed - invalid Plex token";
    echo json_encode($result);
    exit();
}

if ($httpCode !== 200) {
    $result['error'] = "Server returned HTTP {$httpCode}";
    echo json_encode($result);
    exit();
}

// Parse server info
$xml = @simplexml_load_string($response);
if ($xml === false) {
    $result['error'] = "Invalid response from server";
    echo json_encode($result);
    exit();
}

$result['success'] = true;
$result['serverName'] = (string)$xml['friendlyName'];
$result['version'] = (string)$xml['version'];
$result['platform'] = (string)$xml['platform'];

// Also test library sections
$libraryUrl = "{$scheme}://{$serverAddr}:32400/library/sections?X-Plex-Token={$token}";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $libraryUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);
$libResponse = curl_exec($ch);
curl_close($ch);

if ($libResponse) {
    $libXml = @simplexml_load_string($libResponse);
    if ($libXml) {
        foreach ($libXml->Directory as $dir) {
            $result['libraries'][] = [
                'id' => (string)$dir['key'],
                'title' => (string)$dir['title'],
                'type' => (string)$dir['type']
            ];
        }
    }
}

echo json_encode($result);
