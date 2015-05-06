(function ($, window, document) {

    function clearErrors(selector) {
        $(selector + ' [class*="error-"]').remove();
    }

    $(function () {

        console.log("Dom is ready!");

//        var url = 'https://ssl.l7dev.co.cc/voipstudio.dev/api';
        var url = 'https://l7dev.co.cc/voipstudio.dev',
                api_url = url + '/api';

        // LOGIN
        $(document).on('click tap', '#login-button', function (e) {

            clearErrors('.login-form');

            e.preventDefault();
            $.ajax({
                url: api_url,
                data: {
                    method: 'login',
                    username: $('#username').val(),
                    password: $('#password').val(),
                },
                dataType: 'jsonp',
                success: function (res) {
                    if (!res.success)
                    {
                        if (res.errors.username)
                            $('#username').after('<p class="small error-username">' + res.errors.username + '</p>')
                        if (res.errors.password)
                            $('#password').after('<p class="small error-password">' + res.errors.password + '</p>')
                        if (res.errors.email)
                            $('#login-button').before('<p class="small error-email">' + res.errors.email + '</p>')
                        return false;
                    }

                    // redirect user to their application url
                    window.location.href = url + '/login?username=' + $('#username').val() + '&password=' + $('#password').val();
                }
            });
        });


        // REGISTER
        $(document).on('click tap', '#register-button', function (e) {

            clearErrors('.register-form');
            clearErrors('.register-form-sidebar');

            var t = '';
            if ($('#tc').prop('checked'))
                t = true;

            var confirm_pass = $('#regpass2').val() || $('#regpass').val();
            var package_type = $('#package_type').val() || "P";

            e.preventDefault();
            $.ajax({
                url: api_url,
                type: 'POST',
                data: {
                    method: 'register',
                    first_name: $('#firstname').val(),
                    last_name: $('#lastname').val(),
                    email: $('#email').val(),
                    email2: $('#email').val(),
                    password: $('#regpass').val(),
                    password2: confirm_pass,
                    package_type: package_type,
                    package_route_id: $('#package_route_id').val(),
                    google_client_id: $('#google_client_id').val(),
                    tc: t
                },
                dataType: 'jsonp',
                success: function (res) {

                    if (res.status === 403) {

                        if (res.errors.first_name)
                            $('#firstname').after('<p class="small error-firstname">' + res.errors.first_name + '</p>')
                        if (res.errors.last_name)
                            $('#lastname').after('<p class="small error-lastname">' + res.errors.last_name + '</p>')
                        if (res.errors.email)
                            $('#email').after('<p class="small error-email">' + res.errors.email + '</p>')
                        if (res.errors.email2)
                            $('#email2').after('<p class="small error-email2">' + res.errors.email2 + '</p>')
                        if (res.errors.password)
                            $('#regpass').after('<p class="small error-regpass">' + res.errors.password + '</p>')
                        if (res.errors.password2)
                            $('#regpass2').after('<p class="small error-regpass2">' + res.errors.password2 + '</p>')
                        if (res.errors.tc)
                            $('#tc').parents('label:first').after('<p class="small error-ftc">' + res.errors.tc + '</p>')

                        return false;
                    } else {

                        // google conversion
                        var google_conversion_id = 1006351132;
                        var google_conversion_language = "en";
                        var google_conversion_format = "3";
                        var google_conversion_color = "ffffff";
                        var google_conversion_label = "b4RZCLyz3gcQnObu3wM";
                        var google_remarketing_only = false;
                        $.getScript('https://www.googleadservices.com/pagead/conversion.js');

                        $('.register-form, .register-form-sidebar').html('<p class="big center text-center">Thank You for registering.</p>'
                                + '<p class="big center text-center text-grey">Check Your email for confirmation link and <a class="login-form-button">Login</a>.</p>')
                        $('.login-form-button').bind('click tap', function () {
                            showModal('login');
                        })
                    }
                }
            });
        });


        // old version with 
        var $login_link = jQuery('a').filter(function (index) {
            return jQuery(this).text() == "Login";
        });

        $login_link.click(function () {

            $.get(
                "/wp-admin/admin-ajax.php",
                { action: 'login_form' },
                function (data) {
                    
                    dialog = jQuery(data).dialog({
                        title: 'Login',
                        autoOpen: true,
                        resize: "auto",
                        modal: true
                    });
                }
            );
            
            return false;
        });
        
        // old version with 
        var $register_link = jQuery('a').filter(function (index) {
            return $(this).text() == "Register" || $(this).text() == "Free Trial";
        });

        $register_link.click(function () {

            $.get(
                "/wp-admin/admin-ajax.php",
                { action: 'register_form' },
                function (data) {
                    
                    dialog = jQuery(data).dialog({
                        title: 'Registration',
                        autoOpen: true,
                        resize: "auto",
                        modal: true
                    });
                }
            );
            
            return false;
        });

    });

}(window.jQuery, window, document));





