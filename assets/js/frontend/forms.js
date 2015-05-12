(function ($, window, document) {

    function clearErrors(selector) {
        $(selector + ' [class*="error-"]').remove();
    }

    $(function () {

//        var url = 'https://ssl.l7dev.co.cc/voipstudio.dev/api';
//        var url = 'https://l7dev.co.cc/voipstudio.dev',
        // TODO: sandbox 
        var url = 'https://l7sandbox.net/voipstudio.l7sandbox.net',
                api_url = url + '/api';

        // LOGIN
        $(document).on('click tap', '#block-login-button', function (e) {

            clearErrors('.block-login-form');

            e.preventDefault();
            $.ajax({
                url: api_url,
                data: {
                    method: 'login',
                    username: $('form#block-login-form #username').val(),
                    password: $('form#block-login-form #password').val(),
                },
                dataType: 'jsonp',
                success: function (res) {
                    if (!res.success)
                    {
                        if (res.errors.username)
                            $('form#block-login-form #username').after('<p class="small error-username">' + res.errors.username + '</p>')
                        if (res.errors.password)
                            $('form#block-login-form #password').after('<p class="small error-password">' + res.errors.password + '</p>')
                        if (res.errors.email)
                            $('form#block-login-form #block-login-button').before('<p class="small error-email">' + res.errors.email + '</p>')
                        return false;
                    }

                    var redirection = res.info;
                    if ($('form#block-login-form #extini').val()) {
                        redirection += '?extini=' + $('form#block-login-form #extini').val();
                    }
                    
                    // redirect user to their application url
                    window.location.href = redirection;
                }
            });
        });

        $('form#block-register-form select#package_type').on('change', function() {
            
            if(this.value == "S") {
                $('form#block-register-form #package_route_id').show();
            } else {
                $('form#block-register-form #package_route_id').hide();
            }
        });

        // REGISTER
        $(document).on('click tap', '#block-register-button', function (e) {

            clearErrors('.block-register-form');

            var t = '';
            if ($('#tc').prop('checked'))
                t = true;

            var confirm_pass = $('form#block-register-form #password2').val() || $('form#block-register-form #password').val();
            var package_type = $('form#block-register-form #package_type').val() || "P";

            e.preventDefault();
            $.ajax({
                url: api_url,
                type: 'POST',
                data: {
                    method: 'register',
                    first_name: $('form#block-register-form #firstname').val(),
                    last_name: $('form#block-register-form #lastname').val(),
                    email: $('form#block-register-form #email').val(),
                    email2: $('form#block-register-form #email').val(),
                    password: $('form#block-register-form #password').val(),
                    password2: confirm_pass,
                    package_type: package_type,
                    package_route_id: $('form#block-register-form #package_route_id').val(),
                    google_client_id: $('form#block-register-form #google_client_id').val(),
                    tc: t
                },
                dataType: 'jsonp',
                success: function (res) {

                    if (res.status === 403) {

                        if (res.errors.first_name)
                            $('form#block-register-form #firstname').after('<p class="small error-firstname">' + res.errors.first_name + '</p>');
                        if (res.errors.last_name)
                            $('form#block-register-form #lastname').after('<p class="small error-lastname">' + res.errors.last_name + '</p>');
                        if (res.errors.email)
                            $('form#block-register-form #email').after('<p class="small error-email">' + res.errors.email + '</p>');
                        if (res.errors.email2)
                            $('form#block-register-form #email2').after('<p class="small error-email2">' + res.errors.email2 + '</p>');
                        if (res.errors.password)
                            $('form#block-register-form #password').after('<p class="small error-password">' + res.errors.password + '</p>');
                        if (res.errors.password2)
                            $('form#block-register-form #password2').after('<p class="small error-password2">' + res.errors.password2 + '</p>');
                        if (res.errors.tc)
                            $('form#block-register-form #tc').parents('label:first').after('<p class="small error-ftc">' + res.errors.tc + '</p>');

                        return false;
                    } else {

                        $('#block-register-form').html('<p class="big center text-center">Thank You for registering.</p>'
                                + '<p class="big center text-center text-grey">Check Your email for confirmation link and <a href="/en/login">Login</a>.</p>');
                    }
                }
            });
        });

    });

}(window.jQuery, window, document));





