<?php
/*
 * register functions
 * http://www.sutanaryan.com/custom-user-registration-signup-using-ajax-wordpress/
 * __ http://www.sutanaryan.com/wordpress-custom-registration-without-using-a-plugin/#comment-12346
 */

function display_ajax_register(){

if( !is_user_logged_in() && get_option('users_can_register') ) {

echo '<div class="wploginwidget_userregistration">';
echo '<form method="post" id="wploginwidget_registrationform">';
// to make our script safe, it's a best practice to use nonce on our form to check things out
if ( function_exists( 'wp_nonce_field' ) )
wp_nonce_field( 'wploginwidget_user_registration_action', 'wploginwidget_user_registration_nonce' );

echo '<input type="text" name="log" id="log" placeholder="'.__( 'Username', 'wploginwidget').'" />';
echo '<input type="text" name="eml" id="eml" placeholder="'.__( 'Emailaddress', 'wploginwidget').'" />';
echo '<input type="password" name="pwd" id="pwd" placeholder="'.__( 'Password', 'wploginwidget').'" />';
echo '<input type="hidden" name="formcheck" id="formcheck" value="1" />';
echo '<input type="submit" id="submit" class="button" value="'.__( 'Sign up', 'wploginwidget').'" />';

do_action('register_form');

echo '</form></div>';

}

}






add_action( 'wp_ajax_nopriv_user_registration', 'wploginwidget_user_registration_callback' );
add_action( 'wp_ajax_user_registration', 'wploginwidget_user_registration_callback' );

/*
 *	@desc	Register user
 */
function wploginwidget_user_registration_callback() {
	global $wpdb;

	$json = array();

	$error = '';
	$success = '';
	$nonce = $_POST['nonce'];

	if ( !wp_verify_nonce( $nonce, 'wploginwidget_user_registration_action' ) )
        die ( '<p class="error">'.__( 'Security checked, no cheatn please!', 'wploginwidget').'</p>' );

	$log = sanitize_user( $wpdb->escape( $_POST['log'] ) );
	$pwd = sanitize_user( $wpdb->escape( $_POST['pwd'] ) );
	$eml = sanitize_user( $wpdb->escape( $_POST['eml'] ) );

	if( empty( $log ) ) {
		$json[] = __( 'Username required', 'wploginwidget');
	} else if( empty( $pwd ) ) {
		$json[] = __( 'Password required', 'wploginwidget');
	} else if (empty( $eml )){
		$json[] = __( 'Emailaddress required', 'wploginwidget');
	} else if( email_exists( $eml )) { // check if email is known
		$json[] = __( 'Emailaddress exists allready', 'wploginwidget');
	} else if( check_email_address($eml) === false ){
 		$json[] = __( 'Emailaddress not valid', 'wploginwidget');
	} else {

		// define default_role to register
		$default_role = get_option( 'default_role' );
		if( !$default_role ){
			$default_role = 'subscriber';
		}

		// bundle user params
		$user_params = array (
			'user_login' 	=> apply_filters( 'pre_user_user_login', $log ),
			'user_pass' 	=> apply_filters( 'pre_user_user_pass', $pwd ),
			'user_email' 	=> apply_filters( 'pre_user_user_email', $eml ),
			'role' 			=> $default_role
		);
        $user_id = wp_insert_user( $user_params );

		// check user_id registered
        if( is_wp_error( $user_id ) ) {

			$json[] = $user_id->get_error_message();

        }else{

			// register user
            do_action( 'user_register', $user_id );

			// send email(s)
			wp_new_user_notification( $user_id, null, 'both' );

			 $json['success'] = 1;
  		}


    }


	echo json_encode( $json );

	// return proper result
	die();
}

/**
 * Disable toolbar display frontend for new users
 */
add_action("user_register", "wploginwidget_user_admin_bar_hidden", 10, 1);
function wploginwidget_user_admin_bar_hidden($user_id) {
    update_user_meta( $user_id, 'show_admin_bar_front', 'false' );
    update_user_meta( $user_id, 'show_admin_bar_admin', 'false' );
}


add_action('wp_enqueue_scripts', 'wploginwidget_wp_register_js');
function wploginwidget_wp_register_js(){

/**
 * Localize wp_register data
 */
wp_enqueue_script( 'wp-register-request-script', plugin_dir_url(__FILE__) . 'js/wp_register.js', array( 'jquery' ) );
wp_localize_script( 'wp-register-request-script', 'wp_register', array(
	'url'       => admin_url( 'admin-ajax.php' ),
	'site_url' 	=> get_bloginfo('url'),
	'theme_url' => get_bloginfo('template_directory')
) );

}

/**
 * Basic email check
 */
function check_email_address($email) {

		// default WP check
		if ( is_email( $email ) != $email ){
			return false;
		}

		// Most recommended filter_var
		if( filter_var($email, FILTER_VALIDATE_EMAIL) === false ){
			return false;
		}

		// Last check with Regex by Michael Rushton.
		// copyright notice: Feel free to use and redistribute this code. But please keep this copyright notice.
		$pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';

		if ( preg_match($pattern, $email) === 1) {
    		// emailaddress is valid accoording to php basics
			return true;
		}

}

?>
