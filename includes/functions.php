<?php

/**
 * Format price in GBP
 */
function format_price($price)
{
    return '£' . number_format((float) $price, 2);
}

/**
 * Calculate shipping cost
 */
function calculate_shipping($subtotal)
{
    if ($subtotal >= 100) {
        return 0.00; // Free shipping over £100
    }
    return 10.00; // Standard shipping
}

/**
 * Sanitize user input for HTML output
 */
function h($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Flash messages
 */
function set_flash_message($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function display_flash_message()
{
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $message = $_SESSION['flash']['message'];

        $icon = '';
        if ($type === 'success') {
            $icon = '✓';
        } elseif ($type === 'error') {
            $icon = '⚠';
        }

        echo "<div class=\"alert alert-{$type}\"><strong>{$icon}</strong> " . h($message) . "</div>";
        unset($_SESSION['flash']);
    }
}
?>