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

