/*
 * login functions
 * setup source: http://www.sutanaryan.com/custom-user-login-signin-using-ajax-wordpress/
 */

 /*
 * login form AJAX functions
 * @event php/login.php
 */
jQuery(document).ready(function($) {


	$("form#wploginwidget_loginform").submit(function(){ // on loginform submit


		var submit = $("#wploginwidget_loginform #submit"),
			preloader = $("#preloader"),
			message	= $("#message"),
			box = $("#wploginwidget_loginform"),
			contents = {
				action: 		'user_login',
				nonce: 			this.wploginwidget_user_login_nonce.value,
				log:			this.log.value,
				pwd:			this.pwd.value,
				remember:		this.remember.value,
				redirection_url:	this.redirection_url.value,
				logout_url:	this.logout_url.value
			};

			// disable button onsubmit to avoid double
                submit.attr("disabled", "disabled").addClass('disabled');

			// Display our pre-loading
			preloader.slideDown('fast');

                // JSON type so we can check for data success and redirection url.
                $.post( wp_login.url, contents, function( data ){
			submit.removeAttr("disabled").removeClass('disabled');

			// hide pre-loader
			preloader.slideUp('fast');

			// check response data
			if( 1 == data.success ) {

				message.html( '<p class="succes">Logged in, redirecting..</p>' );
				//box.html( '<a href="'+data.logout_url+'">Sign out</a>' );
				window.location = data.redirection_url; // redirect to home page
				/*
				$(".adapptUserLogin").slideUp('fast');
				$("li.user-sign").html('<a href="'+data.logout_url+'">Sign out</a>').addClass('signedin');
				*/
			} else {
				// display return data
				message.html( '<p class="error">' + data + '</p>' );
			}

		}, 'json');

		return false;
	});




});
