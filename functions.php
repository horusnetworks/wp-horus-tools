<?php
/* WP Horus Tools */

// Disable users' list in Sitemap
add_filter( 'wp_sitemaps_add_provider', function( $provider, $name  ) {
        if ( 'users' === $name ) {
                return false;
        }

        return $provider;
}, 10, 2 );

// Disable users' list in REST API
add_action( 'rest_authentication_errors', function( $access ) {
        if ( is_user_logged_in() ) {
                return $access;
        }

        if ( ( preg_match( '/users/i', $_SERVER['REQUEST_URI'] ) !== 0 )
                || ( isset( $_REQUEST['rest_route'] ) && ( preg_match( '/users/i', $_REQUEST['rest_route'] ) !== 0 ) )
        ) {
                return new \WP_Error(
                        'rest_cannot_access',
                        'Only authenticated users can access the User endpoint REST API.',
                        [
                                'status' => rest_authorization_required_code()
                        ]
                );
        }

        return $access;
} );


// Disable users' enumeration
add_action( 'init', function() {
        if ( isset( $_REQUEST['author'] )
                && preg_match( '/\\d/', $_REQUEST['author'] ) > 0
                && ! is_user_logged_in()
        ) {
                wp_die( 'forbidden - number in author name not allowed = ' . esc_html( $_REQUEST['author'] ) );
                //return home_url();
        }
} );

// Function to display the under construction page
function horus_tools_under_construction_page() {
    $enable_under_construction = get_option('horus_tools_enable', false);
    $password_protected = get_option('horus_tools_password_protected', false);

    if ($enable_under_construction) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            // Check if password protection is enabled
            if ($password_protected) {
                // Check if password cookie is set
                if (!isset($_COOKIE['horus_tools_password']) || $_COOKIE['horus_tools_password'] !== get_option('horus_tools_password')) {
                    // Check if password form submitted
                    if (isset($_POST['horus_tools_password'])) {
                        $password = get_option('horus_tools_password');
                        echo "password = $password";
                        if ($_POST['horus_tools_password'] === $password) {
                            // Password is correct, set cookie and refresh page
                            setcookie('horus_tools_password', $_POST['horus_tools_password'], time() + 3600, '/');
                            wp_redirect(home_url());
                            exit;
                        } else {
                            echo '<p>Mot de passe erroné, veuillez réessayer...</p>';
                        }
                    } 


                    // Display password form
                    echo '<h1>Ce site internet est en cours de développement...</h1>';
                    echo '<h2>Revenez bientôt...</h2>';
                    echo '<p>Ou introduisez le mot de passe pour prévisualiser</p>';
                    echo '<form method="post">';
                    echo '<input type="password" name="horus_tools_password" required>';
                    echo '<input type="submit" value="Valider">';
                    echo '</form>';
                    exit;
                }
            } else {
                // Display under construction message
                echo '<h1>Ce site internet est en construction...</h1>';
                exit;
            }
        }
    }
}
add_action('template_redirect', 'horus_tools_under_construction_page');

// Function to add settings page
function horus_tools_settings_page() {
    add_options_page('Horus Tools Settings', 'Horus Tools', 'manage_options', 'horus-tools-settings', 'horus_tools_settings_form');
}
add_action('admin_menu', 'horus_tools_settings_page');

// Function to display settings form
function horus_tools_settings_form() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['horus_tools_submit'])) {
        update_option('horus_tools_enable', isset($_POST['horus_tools_enable']) ? true : false);
        update_option('horus_tools_password_protected', isset($_POST['horus_tools_password_protected']) ? true : false);
        if (isset($_POST['horus_tools_password'])) {
           update_option('horus_tools_password', $_POST['horus_tools_password']);
        }
    }

    $enable_under_construction = get_option('horus_tools_enable', false);
    $password_protected = get_option('horus_tools_password_protected', false);

    ?>
    <div class="wrap">
        <h1>Horus Tools Settings</h1>
        <form method="post">
            <label for="horus_tools_enable">Enable Under Construction Mode:</label>
            <input type="checkbox" name="horus_tools_enable" id="horus_tools_enable" <?php echo $enable_under_construction ? 'checked' : ''; ?>>
            <br><br>
            <label for="horus_tools_password_protected">Password Protect:</label>
            <input type="checkbox" name="horus_tools_password_protected" id="horus_tools_password_protected" <?php echo $password_protected ? 'checked' : ''; ?>>
            <br><br>
            <?php if ($password_protected): ?>
                <label for="horus_tools_password">Set Password:</label>
                <input type="password" name="horus_tools_password" id="horus_tools_password" required>
            <?php endif; ?>
            <br><br>
            <input type="submit" name="horus_tools_submit" value="Save Settings">
        </form>
    </div>
    <?php
}

