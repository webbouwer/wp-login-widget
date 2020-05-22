<?php
/*
 * login functions
 */

/*
 * prev src: http://natko.com/wordpress-ajax-login-without-a-plugin-the-right-way/
 * src: https://www.ryansutana.name/2014/07/custom-user-login-signin-using-ajax-wordpress/
 */


function display_ajax_form(){

    global $wp;
    $current_url = home_url( add_query_arg( array(), $wp->request ) );
    $logout_url = wp_logout_url( get_permalink() );
    $ajax_nonce = wp_create_nonce( "rs_user_login_nonce" );

    echo '<form id="loginform" action="login" method="post">';

    if ( function_exists( 'wp_nonce_field' ) )
        wp_nonce_field( 'rs_user_login_action', 'rs_user_login_nonce' );

    echo '<div class="alert-box"></div>';
    echo '<label for="log">Username</label>';
    echo '<input type="text" name="log" id="log" class="input" value="" size="20">';
    echo '<label for="pwd">Password</label>';
    echo '<input type="password" name="pwd" id="pwd" class="input" value="" size="20">';
    echo '<label><input name="remember" type="checkbox" id="remember" value="false"> Remember Me</label>';
    echo '<input class="submit_button" type="submit" value="Login" name="submit">';
    echo '<input type="hidden" name="redirection_url" value="'. $current_url .'">';
    echo '<input type="hidden" name="logout_url" value="'. $logout_url .'">';
    echo '</form>';
    echo '<div class="preloader-box"></div>';

}

function wp_login_register_scripts(){

// localize wp-ajax, notice the path to our theme-ajax.js file
wp_enqueue_script( 'rsclean-request-script', plugin_dir_url(__FILE__) . '/js/wp_ajax_login.js', array( 'jquery' ) );
wp_localize_script( 'rsclean-request-script', 'theme_ajax', array(
    'url'        => admin_url( 'admin-ajax.php' ),
    'site_url'     => get_bloginfo('url'),
    'theme_url' => plugin_dir_url(__FILE__)
) );

add_action( 'wp_ajax_nopriv_user_login', 'rs_user_login_callback' );
add_action( 'wp_ajax_user_login', 'rs_user_login_callback' );

}
add_action('init', 'wp_login_register_scripts');

/*
 *	@desc	Process theme login
 */
function rs_user_login_callback() {

	global $wpdb;

	$json = array();

	$error = '';
	$success = '';$nonce = $_POST['nonce'];

	if ( ! wp_verify_nonce( $nonce, 'rs_user_login_action' ) )
		die ( '<p class="error">Security checked!, Cheatn huh?</p>' );

    // check_ajax_referer( 'rs_user_login_action', 'rs_user_login_nonce' );

	//We shall SQL escape all inputs to avoid sql injection.
	$username = $wpdb->escape($_POST['log']);
	$password = $wpdb->escape($_POST['pwd']);
	$remember = $wpdb->escape($_POST['remember']);
	$redirection_url = $wpdb->escape($_POST['redirection_url']);

	if( empty( $username ) ) {
		$json[] = 'Username field is required.';
	} else if( empty( $password ) ) {
		$json[] = 'Password field is required.';
	} else {

		$user_data = array();
		$user_data['user_login'] = $username;
		$user_data['user_password'] = $password;
		$user_data['remember'] = $remember;
		$user = wp_signon( $user_data, false );

		if ( is_wp_error($user) ) {
			$json[] = $user->get_error_message();
		} else {
			/* not working for admin dashboard..
            wp_set_current_user( $user->ID, $username );
			do_action('set_current_user');
            */
            $curr_user=  new WP_User( $user->ID , $user->user_login );
            wp_set_auth_cookie( $user->ID );
            do_action( 'wp_login', $user->user_login );

			$json['success'] = 1;
			$json['redirection_url'] = $redirection_url;
		}
	}

    header("Content-type: application/json");
	echo json_encode( $json );
	// return proper result
	exit(); //die();
}



?>
