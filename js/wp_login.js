/*
 * login functions
 */
jQuery(document).ready(function($) {

 $("form#loginform").submit(function(e){ // on loginform submit

     e.preventDefault();
     var submit = $("#loginform #submit"),

			message	= $(".status").text('loading'),
			box = $("#userpanel"),
			contents = {
				action: 		'ajax_login',
				nonce: 			this.security.value,
				log:			this.log.value,
				pwd:			this.pwd.value,
				remember:		this.remember.value,
				redirection_url:	this.redirection_url.value,
				logout_url:	this.logout_url.value
			};

			// disable button onsubmit to avoid double
            submit.attr("disabled", "disabled").addClass('disabled');

			// Display our pre-loading
			message.show();

            // JSON type so we can check for data success and redirection url.
            $.post( ajax_login_object.ajaxurl, contents, function( data ){
			submit.removeAttr("disabled").removeClass('disabled');

			// hide pre-loader
			message.hide();

			// check response data
			if( 1 == data.success ) {

				message.html( '<p class="succes">Logged in, redirecting..</p>' ).show();
				//box.html( '<a href="'+data.logout_url+'">Sign out</a>' );
				window.location = data.redirection_url; // redirect to home page
				/*
				$(".adapptUserLogin").slideUp('fast');
				$("li.user-sign").html('<a href="'+data.logout_url+'">Sign out</a>').addClass('signedin');
				*/
			} else {
				// display return data
				message.html( '<p class="error">' + data + '</p>' ).show();
			}

		}, 'json');

		return false;
	});




/*
    // Perform AJAX login on form submit
    $('form#loginform').on('submit', function(e){

        //alert('check');
        $('form#loginform p.status').show().text('test');


        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajax_login_object.ajaxurl,
            data: {
                'action': 'ajaxlogin', //calls wp_ajax_nopriv_ajaxlogin
                'username': $('form#login #user_login').val(),
                'password': $('form#login #user_pass').val(),
                'security': $('form#login #security').val() },
            success: function(data){
                $('form#loginform p.status').text(data.message);
                if (data.loggedin == true){
                    document.location.href = ajax_login_object.redirecturl;
                }
            }
        });

        e.preventDefault();
    });
*/

});
