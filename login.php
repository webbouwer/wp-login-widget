<?php
/*
 * login functions
 * setup source: http://www.sutanaryan.com/custom-user-login-signin-using-ajax-wordpress/
 */


/*
 * Display Login form
 * @event index.php
 */
function display_ajax_login(){

	echo '<div class="wploginwidget_ajaxlogin">';

	if( is_user_logged_in() ) { // if the user already signed up

		echo '<a class="signoutlink" href="'. wp_logout_url( get_permalink() ) .'">'.__( 'Sign out', 'wploginwidget').'</a>';

	}else{

		$redirectUrl = get_permalink( get_the_ID() );
		if(is_home())
			$redirectUrl = home_url();


        /**
         * Messagebox and Preloader
         */
        echo '<div id="message" class="alert-box"></div>';
        echo '<img align="center" src="'.get_stylesheet_directory_uri().'/images/ajax-loader.gif" id="preloader" alt="Preloader" />';


		echo '<form method="post" id="wploginwidget_loginform">';

		if ( function_exists( 'wp_nonce_field' ) )
			wp_nonce_field( 'wploginwidget_user_login_action', 'wploginwidget_user_login_nonce' ); // set nonce

		echo '<input type="text" name="log" id="log" placeholder="'.__( 'Username', 'wploginwidget').'" />'
			.'<input type="password" name="pwd" id="pwd" placeholder="'.__( 'Password', 'wploginwidget').'" />'
			.'<label><input type="checkbox" name="remember" id="remember" value="true" /> '.__( 'Remember me', 'wploginwidget').'</label>'
			.'<input type="submit" id="submit" class="button" value="'.__( 'Sign in', 'wploginwidget').'" />'
			.'<input type="hidden" name="redirection_url" id="redirection_url" value="'. $redirectUrl .'" />'
			.'<input type="hidden" name="logout_url" id="logout_url" value="'. wp_logout_url( get_permalink() ) .'" />';

		do_action('login_form', 'login');

		echo '</form>';
		}

		echo '</div>';


}


/*
 * Process theme login
 * @callback js/wp_login.js
 */
add_action( 'wp_ajax_nopriv_user_login', 'wploginwidget_user_login_callback' );
add_action( 'wp_ajax_user_login', 'wploginwidget_user_login_callback' );

function wploginwidget_user_login_callback() {

	global $wpdb;

    $json = array();
    $error = '';
    $success = '';
    $nonce = $_POST['nonce'];

    if ( !wp_verify_nonce( $nonce, 'wploginwidget_user_login_action' ) )
        die ( '<p class="error">'.__( 'Security checked, no cheatn please!', 'wploginwidget').'</p>' ); // check nonce

    $username = $wpdb->escape($_POST['log']); // SQL escape avoids sql injection..
    $password = $wpdb->escape($_POST['pwd']);
    $remember = $wpdb->escape($_POST['remember']);
    $redirection_url = $wpdb->escape($_POST['redirection_url']);
    $logout_url = $_POST['logout_url'];

    if( empty( $username ) ) {
        $json[] = __( 'Username required', 'wploginwidget');
    } else if( empty( $password ) ) {
        $json[] = __( 'Password required', 'wploginwidget');
    } else {

        $user_data = array();
        $user_data['user_login'] = $username;
        $user_data['user_password'] = $password;
        $user_data['remember'] = $remember;
        $user = wp_signon( $user_data, false );

        if ( is_wp_error($user) ) {
            $json[] = $user->get_error_message();
        } else {
            wp_set_current_user( $user->ID, $username );
            do_action('set_current_user');

            $json['success'] = 1;
            $json['redirection_url'] = $redirection_url;
            $json['logout_url'] = $logout_url;
        }
    }

    echo json_encode( $json ); // output json

    die(); // return
}


/*
 * Use localized data
 * @require js/wp_login.js, wp-admin/admin-ajax.php, jQuery
 */
add_action('wp_enqueue_scripts', 'wploginwidget_wp_login_js');

function wploginwidget_wp_login_js(){

	wp_enqueue_script( 'wp-login-request-script', plugin_dir_url(__FILE__). 'js/wp_login.js', array( 'jquery' ) );
	wp_localize_script( 'wp-login-request-script', 'wp_login', array(
    'url'        => admin_url( 'admin-ajax.php' ),
    'site_url'     => get_bloginfo('url'),
    'theme_url' => get_bloginfo('template_directory')
	));

}

?>
