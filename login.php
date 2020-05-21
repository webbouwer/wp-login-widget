<?php
/*
 * login functions
 */




/*
http://natko.com/wordpress-ajax-login-without-a-plugin-the-right-way/
*/
function display_ajax_login(){

    //Simple Ajax Login Form
//Source: http://natko.com/wordpress-ajax-login-without-a-plugin-the-right-way/

//html
    if (!is_user_logged_in()) {
    ?>
<form id="login" action="login" method="post">
    <h1>Site Login</h1>
    <p class="status"></p>
    <label for="username">Username</label>
    <input id="username" type="text" name="username">
    <label for="password">Password</label>
    <input id="password" type="password" name="password">
    <p><a class="lost" href="<?php echo wp_lostpassword_url(); ?>">Lost your password?</a></p>
    <input class="submit_button" type="submit" value="Login" name="submit">

    <?php wp_nonce_field( 'ajax-login-nonce', 'security' ); ?>
</form>


<?php

    add_action('init', 'ajax_login_init');

    }else{
        echo '<p class="status">Account</p>';
    }
}

//add this within functions.php
function ajax_login_init(){

    wp_register_script('ajax-login-script', plugin_dir_url(__FILE__) . 'js/wp_login.js', array('jquery') );
    wp_enqueue_script('ajax-login-script');

    wp_localize_script( 'ajax-login-script', 'ajax_login_object', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'redirecturl' => home_url(),
        'loadingmessage' => __('Sending user info, please wait...')
    ));

    // Enable the user with no privileges to run ajax_login() in AJAX
    add_action( 'wp_ajax_nopriv_ajaxlogin', 'ajax_login' );
}

// Execute the action only if the user isn't logged in
//if (!is_user_logged_in()) {
    add_action('init', 'ajax_login_init');
//}


function ajax_login(){

    // First check the nonce, if it fails the function will break
    check_ajax_referer( 'ajax-login-nonce', 'security' );

    // Nonce is checked, get the POST data and sign user on
    $info = array();
    $info['user_login'] = $_POST['username'];
    $info['user_password'] = $_POST['password'];
    $info['remember'] = true;

    $user_signon = wp_signon( $info, false );
    if ( is_wp_error($user_signon) ){
        echo json_encode(array('loggedin'=>false, 'message'=>__('Wrong username or password.')));
    } else {
        echo json_encode(array('loggedin'=>true, 'message'=>__('Login successful, redirecting...')));
    }

    die();
}




?>
