<?php
/*
Plugin Name: WP Login widget
Plugin URI:  https://webdesigndenhaag.net/pluginlab
Description: Wordpress plugin to place a login widget on your website frontend
Version:     1.0
Author:      Oddsized Webdesign Den Haag
Author URI:  https://webdesigndenhaag.net/
License:     Oddsized Copyright 2020
License URI: https://www.oddsized.com
Text Domain: wploginwidget
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'FEEDBOARD_VERSION', '1.0.0' );


load_plugin_textdomain('wploginwidget', false, basename( dirname( __FILE__ ) ) . '/languages' );

require_once plugin_dir_path( __FILE__ ) . '/login.php'; // login ajax functions
require_once plugin_dir_path( __FILE__ ) . '/register.php'; // login ajax functions

/* Login Frontend Widget */
function wp_login_widget_load() {
    register_widget( 'wp_login_widget' );
}
add_action( 'widgets_init', 'wp_login_widget_load' );



class wp_login_widget extends WP_Widget {


	function __construct() {
		parent::__construct(
			'wp_login_widget', // Base ID
			__('WP login widget', 'wploginwidget'), // Widget name and description in UI
			array( 'description' => __( 'Place a login widget on your website frontend', 'wploginwidget' ), )
		);
	}

	// Creating widget front-end
	public function widget( $args, $instance ) {

		$paneltype = 0;
		//$currentid = get_queried_object_id();

		if(isset($instance['boxtype']) && $instance['boxtype'] !='' )
			$paneltype = $instance['boxtype'];


		$title = apply_filters( 'widget_title', $instance['title'] );

		// before and after widget arguments are defined by themes
		echo $args['before_widget'];

		if ( ! empty( $title ) )
		echo $args['before_title'] . $title . $args['after_title'];

        // define box by $paneltype
        switch ($paneltype) {
            case 1:
                $this->display_basic_panel();
                break;
            case 2:
                echo "Here comes more";
                /**
                 * Login & Register
                 */
                display_ajax_login();
                display_ajax_register();

                break;
            default:
                $this->display_basic_panel();
                break;
        }


		//wp_reset_query();
        // before and after widget arguments are defined by themes
		echo $args['after_widget'];
	}


    /** Custom Login/Register/Password
     * source https://digwp.com/2010/12/login-register-password-code/
     */

    public function display_basic_panel(){

        echo '<div id="userpanel">';

        global $user_ID, $user_identity; wp_get_current_user(); //get_currentuserinfo();
        // MU switch_to_blog( 1 );
        $regallowed = get_option( 'users_can_register' );


        if (!$user_ID) { // is not logged in


                // sign-in link or button
                echo '<ul class="tabmenu"><li class="signintab"><span >'.__( 'Sign in', 'wploginwidget' ).'</span></li>';

                // sign-up link or button
                if ( $regallowed ) {

                    echo '<li class="registertab"><span >'.__( 'Register', 'wploginwidget' ).'</span></li>';
                }
                echo '</ul>';

                echo '<ul class="tabcontainer"><li class="tab1 tab" style="display:none">';

                global $user_login;
                global $user_email;
                global $register;
                global $reset;
                if ($regallowed && isset(  $_GET['register'] )) { $register = $_GET['register']; }

                if (isset(  $_GET['reset'] )) { $ $reset = $_GET['reset'];}


                if ($register == true && $regallowed) {

                    // registered with succes
                    echo '<h3>'.__( 'Success!', 'wploginwidget' ).'</h3>';
                    echo '<p>'.__( 'Check your email for the password and use it to sign in', 'wploginwidget').'</p>';

                }else if($reset == true) {

                    //  request reset mail send
                    echo '<h3>Success!</h3><p>Check your email to reset your password.</p>';

                }else{

                    // show login elements
                    echo '<h3>'.__( 'Sign in', 'wploginwidget' ).'</h3>';
                }

                // display login form
                wp_login_form();

                echo '<div class="resetlogin"><span>'.__( 'Forgot password?', 'wploginwidget' ).'</span></div>';

                do_action('login_form', 'login');

                //echo do_shortcode( '' );

                echo '</li>';





                if ( $regallowed ) {
                echo '<li class="tab2 tab" style="display:none">';
                echo '<h3>'.__( 'Register', 'wploginwidget' ).'</h3>';
                echo '<p>'.__( 'Sign up', 'wploginwidget' ).'</p>';
                ?>
                    <form method="post" action="<?php echo site_url('wp-login.php?action=register', 'login_post') ?>" class="wp-user-form">
                    <div class="username">
                    <label for="user_login"><?php __('Username', 'wploginwidget' ); ?>: </label>
                    <input type="text" name="user_login" value="<?php echo esc_attr(stripslashes($user_login)); ?>" size="20" id="user_login" tabindex="101" />
                    </div>
                    <div class="password">
                    <label for="user_email"><?php _e('Your Email', 'wploginwidget' ); ?>: </label>
                    <input type="text" name="user_email" value="<?php echo esc_attr(stripslashes($user_email)); ?>" size="25" id="user_email" tabindex="102" />
                    </div>
                    <div class="login_fields">
                    <input type="submit" name="user-submit" value="<?php _e('Sign up', 'wploginwidget' ); ?>" class="user-submit" tabindex="103" />
                    <?php do_action('register_form'); ?>
                    <?php if (isset(  $_GET['register'] )) { $register = $_GET['register']; } if($register == true) { echo '<p>'.__( 'Check your email for the password!', 'wploginwidget' ).'</p>'; } ?>
                    <input type="hidden" name="redirect_to" value="<?php echo $_SERVER['REQUEST_URI']; ?>?register=true" />
                    <input type="hidden" name="user-cookie" value="1" />
                    </div>
                    </form>
                    </li>
                <?php } ?>





                <li class="tab3 tab" style="display:none">

                    <?php
                    echo '<h3>'.__( 'Reset password', 'wploginwidget' ).'</h3>';
                    echo '<p>'.__( 'Reset your password. You\'ll receive an email with link to the reset form.', 'wploginwidget').'</p>';
                    ?>

                    <form method="post" action="<?php echo site_url('wp-login.php?action=lostpassword', 'login_post') ?>" class="wp-user-form">
                    <div class="username">
                    <label for="user_login" class="hide"><?php _e('Username or Email', 'wploginwidget' ); ?>: </label>
                    <input type="text" name="user_login" value="" size="20" id="user_login" tabindex="1001" />
                    </div>
                    <div class="login_fields">
                    <input type="submit" name="user-submit" value="<?php _e('Reset my password', 'wploginwidget' ); ?>" class="user-submit" tabindex="1002" />
                    <?php do_action('login_form', 'resetpass'); ?>
                    <?php if (isset(  $_GET['reset'] )) { $reset = $_GET['reset']; } if($reset == true) { echo '<p>'.__( 'Check your mailbox for a link to the password reset form.', 'wploginwidget' ).'</p>'; } ?>
                    <input type="hidden" name="redirect_to" value="<?php echo $_SERVER['REQUEST_URI']; ?>?reset=true" />
                    <input type="hidden" name="user-cookie" value="1" />
                    </div>
                    </form>
                </li>
                </ul>




        <?php } else { // is logged in


                    global $userdata;
                    wp_get_current_user();

                    echo '<div class="infocontainer">';

                    echo '<div class="userinfo">';

                    echo '<div class="loggedtext"><span>'.$userdata->roles[0].' <strong>'. $user_identity .'</strong></span></div>';

                    echo '<div class="loginmenubar"><ul class="menu">';

                    /*
                    $page1 = get_page_by_name('user-info');
                    $page2 = get_page_by_name('user-profile');

                    if (!empty($page1) && current_user_can('manage_options') ) {
                    // link to profile
                    echo '<li class="menu-item"><a href="'.get_bloginfo('siteurl').'/user-info">' . __('Info', 'fndtn' ) . '</a></li>';
                    }
                    if (!empty($page2)) {
                    // link to profile
                    echo '<li class="menu-item"><a href="'.get_bloginfo('siteurl').'/user-profile">' . __('Profile', 'fndtn' ) . '</a></li>';
                    }

                    if (current_user_can('manage_options')) {
                    echo '<li class="menu-item"><a href="' . admin_url() . '">' . __('Admin', 'fndtn' ) . '</a></li>';
                    }
                    */

                    echo '<li class="menu-item"><a class="logout-link" href="'.wp_logout_url( 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'] ).'" title="Sign off"><span>'.__('Sign off', 'wploginwidget').'</span></a></li>';

                    echo '</ul></div>';



                    if ( has_nav_menu( 'usermenu' ) ) {
                    echo '<div class="usermenubar">';
                    wp_nav_menu( array( 'theme_location' => 'usermenu' ) );
                    echo '<div class="clr"></div></div>';
                    }

                echo '</div></div>';
        }

        echo '</div>';

    } // end userpanel





	// Widget Backend
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
		$title = $instance[ 'title' ];
		}else{
		$title = __( 'New title', 'wploginwidget' );
		}


		/*
	 	 * Widget admin form
		 */

		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php __( 'Title:', 'wploginwidget' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>



		<?php
		$boxtype = 0;
		if ( isset( $instance[ 'boxtype' ] ) ) {
		$boxtype = $instance[ 'boxtype' ];
		}
		?>
		<p><label for="<?php echo $this->get_field_id( 'boxtype' ); ?>">Box type:</label>
		<select name="<?php echo $this->get_field_name( 'boxtype' ); ?>" id="<?php echo $this->get_field_id( 'boxtype' ); ?>">
		<option value="0" <?php selected( $boxtype, 0 ); ?>>Default</option>
		<option value="1" <?php selected( $boxtype, 1 ); ?>>Basic</option>
		<option value="2" <?php selected( $boxtype, 2 ); ?>>Smooth</option>
		</select>
		</p>


		<?php

	}






	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['boxtype'] = ( ! empty( $new_instance['boxtype'] ) ) ? $new_instance['boxtype'] : 0;
		return $instance;
	}





} // Class ends here












// http://wordpress.stackexchange.com/questions/57386/how-do-i-force-wp-enqueue-scripts-to-load-at-the-end-of-head
// > https://wpshout.com/quick-guides/use-wp_enqueue_script-include-javascript-wordpress-site/
function wploginwidget_js() {

    // Register the script(s)
    //wp_register_script( 'wploginwidgettabs', basename( dirname( __FILE__ ) ) . '/js/logintabs.js', 99, '1.0', false);
    //wp_register_script( 'wploginwidgettabs', plugins_url('/js/logintabs.js',__FILE__ ));

    wp_enqueue_script( 'wploginwidgettabs', plugin_dir_url(__FILE__) . 'js/logintabs.js', array(), '1.0.0', true );
}

add_action('wp_enqueue_scripts', 'wploginwidget_js');





/* DIY
 * https://codex.wordpress.org/Writing_a_Plugin
 * https://developer.wordpress.org/plugins/plugin-basics/header-requirements/
 */
?>
