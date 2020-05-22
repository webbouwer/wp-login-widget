<?php
/*
 * login functions
 */




/*
http://natko.com/wordpress-ajax-login-without-a-plugin-the-right-way/
*/





function frontend_login(){

    // First check the nonce, if it fails the function will break
    check_ajax_referer( 'ajax-login-nonce', 'security' );

    // Nonce is checked, get the POST data and sign user on
    $info = array();
    $info['user_login'] = $_POST['log'];
    $info['user_password'] = $_POST['pwd'];
    $info['remember'] = $_POST['remember'];

    $user_signon = wp_signon( $info, false );
    if ( is_wp_error($user_signon) ){
        echo json_encode(array('loggedin'=>false, 'message'=>__('Wrong username or password.')));
    } else {
        echo json_encode(array('loggedin'=>true, 'message'=>__('Login successful, redirecting...')));
    }

    die();
}

function wploginwidget_ajax() {

    // Register the script(s)
    wp_register_script('ajax-login-script', plugin_dir_url(__FILE__) . 'js/wp_login.js', array('jquery') );
    wp_enqueue_script('ajax-login-script');

    wp_localize_script( 'ajax-login-script', 'ajax_login_object', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'redirecturl' => home_url(),
        'loadingmessage' => __('Sending user info, please wait...')
    ));

}
add_action('wp_enqueue_scripts', 'wploginwidget_ajax');

// Enable the user with no privileges to run ajax_login() in AJAX
add_action( 'wp_ajax_nopriv_frontend_login', 'frontend_login' );
add_action( 'wp_ajax_frontend_login', 'frontend_login' );


/*
// Execute the action only if the user isn't logged in.
if ( ! is_user_logged_in() ) {
	add_action( 'init', 'ajax_login_init' );
}
*/


?>
