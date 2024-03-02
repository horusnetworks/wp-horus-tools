<?php
/* WP Horus Tools */

// Prevent authors enumeration
function prevent_author_enumeration($redirect_url, $requested_url) {
    if (is_admin()) {
        // Don't run this redirect in the admin area
        return $redirect_url;
    }

    // Check if this is an author query
    if (preg_match('/author=([0-9]*)/i', $requested_url) || is_author()) {
        // Redirect to homepage, or you could choose to redirect to a 404 page by using home_url('/404')
        return home_url();
    }

    // Return the original redirect URL if not an author query
    return $redirect_url;
}

// Hook the function to 'redirect_canonical' filter
add_filter('redirect_canonical', 'prevent_author_enumeration', 10, 2);
