<?php
/**
 * PHP QR Code encoder
 *
 * This is a placeholder for the actual phpqrcode library.
 * The real library should be placed here (e.g., from http://phpqrcode.sourceforge.net/).
 * This placeholder creates a text file instead of a PNG.
 */

if (!class_exists('QRcode')) {
    class QRcode {
        public static function png($text, $outfile = false, $level = 'L', $size = 3, $margin = 4, $saveandprint = false) {
            if ($outfile !== false) {
                $dirname = dirname($outfile);
                if (!is_dir($dirname)) {
                    @mkdir($dirname, 0755, true); // 0755 is standard for PHP's mkdir
                }
                $dummy_content = "=== PHP QR Code Placeholder ===\n" .
                                 "This is a dummy QR Code file for: " . htmlspecialchars($text) . "\n" .
                                 "Intended path: " . htmlspecialchars($outfile) . "\n" .
                                 "Error Correction Level: " . htmlspecialchars($level) . "\n" .
                                 "Pixel Size: " . htmlspecialchars($size) . "\n" .
                                 "Margin: " . htmlspecialchars($margin) . "\n" .
                                 "Timestamp: " . date('Y-m-d H:i:s') . "\n" .
                                 "=============================";
                if (@file_put_contents($outfile, $dummy_content) === false) {
                    error_log("PSLocationLocator - Placeholder QRcode::png: Failed to write dummy file to " . $outfile);
                }
                // This placeholder does not support $saveandprint = true for direct output
            } else {
                error_log("PSLocationLocator - Placeholder QRcode::png: outfile=false mode (direct output) is not supported by this module's usage of the library.");
            }
        }
    }
}
if (!defined('QR_ECLEVEL_L')) define('QR_ECLEVEL_L', 'L');
if (!defined('QR_ECLEVEL_M')) define('QR_ECLEVEL_M', 'M');
if (!defined('QR_ECLEVEL_Q')) define('QR_ECLEVEL_Q', 'Q');
if (!defined('QR_ECLEVEL_H')) define('QR_ECLEVEL_H', 'H');
