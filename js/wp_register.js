jQuery(document).ready(function($) {

	$("#preloader").hide();

	// for user registration form
	$("form#wploginwidget_registrationform").submit(function(){

		$("input").removeClass('faulty');

		var submit = $(".wploginwidget_registrationform #submit"),
			preloader = $("#preloader"),
			message	= $("#message"),
			check = $(".wploginwidget_registrationform #formcheck"),
			contents = {
				action: 	'user_registration',
				nonce: 		this.wploginwidget_user_registration_nonce.value,
				log:		this.log.value,
				eml:		this.eml.value,
				pwd:		this.pwd.value
			};



		var count = parseInt(this.formcheck.value) + 1;
		if( count > 6 ){
			message.html( 'Enough input for now!' );
			submit.attr("disabled", "disabled").addClass('disabled');
			$(".wploginwidget_userregistration form").hide();
		    return false;
		}
		check.attr("value", count);




		// disable button onsubmit to avoid double submision
		submit.attr("disabled", "disabled").addClass('disabled');

		// Display our pre-loading
		preloader.slideDown('fast');

		$.post( wp_register.url, contents, function( data ){
			submit.removeAttr("disabled").removeClass('disabled');

			// hide pre-loader
			preloader.slideUp('fast');

			// check response data
			if( 1 == data.success ) {
				// redirect to home page
				// window.location = wp_register.site_url;
				message.html( '<p class="succes">Thank you! Your account is ready. A confirmation email is send to given emailaddress.</p>' );
				$(".wploginwidget_userregistration form").hide();
			} else {
				// display return data
				message.html( '<p class="error">' + data + '</p>' );
			}
		}, 'json');

		return false;
	});

});
