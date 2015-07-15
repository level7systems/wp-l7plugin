(function ($, window, document) {

    function clearErrors(selector) {
        $(selector + '-global-errors').html("").hide();
        $(selector + ' [class*="error-"]').remove();
    }

    $(function () {

        if (!$('#l7p-login-form-global-errors').is(':empty')) {
            $('#l7p-login-form-global-errors').show();
        }

        // LOGIN
        $(document).on('submit', 'form#l7p-login-form', function (e) {

            clearErrors('#l7p-login-form');

            var $form = $(this);

            e.preventDefault();
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: {
                    method: 'login',
                    username: $('form#l7p-login-form #username').val(),
                    password: $('form#l7p-login-form #password').val(),
                },
                dataType: 'jsonp',
                success: function (res) {
                    if (!res.success)
                    {
                        if (res.errors.username)
                            $('form#l7p-login-form #username').after('<p class="small error-username">' + res.errors.username + '</p>')
                        if (res.errors.password)
                            $('form#l7p-login-form #password').after('<p class="small error-password">' + res.errors.password + '</p>')
                        if (res.errors.email)
                            $('#l7p-login-form-global-errors').html(res.errors.email + '<br><a href="/en/recover-password">Have you forgotten your password?</a>').show();

                        return false;
                    }

                    // TODO: to be continued
                    if (res.redirect) {

                        if ($('#activation_url')) {

                            var redirection = $('#activation_url').val() + '/' + res.activation_token;
                            if (res.redirect) {
                                redirection += '?message=' + res.info;
                            }

                            // redirect user to their application url
                            window.location.href = redirection;
                        }
                        return false;
                    }

                    var redirection = res.info;
                    if ($('form#l7p-login-form #extini').val()) {
                        redirection += '?extini=' + $('form#l7p-login-form #extini').val();
                    }

                    // redirect user to their application url
                    window.location.href = redirection;
                }
            });
        });

        $('form#l7p-register-form select#package_type').on('change', function () {

            if (this.value == "S") {
                $('form#l7p-register-form #package_route_id').show();
            } else {
                $('form#l7p-register-form #package_route_id').hide();
            }
        });

        if ($('form#l7p-register-form select#package_type')) {

            var hash = window.location.hash.substring(1);
            if ($.inArray(hash, ['P', 'S', 'A']) !== -1) {
                $('form#l7p-register-form select#package_type').val(hash).change();
            }
        }

        // REGISTER
        $(document).on('submit', 'form#l7p-register-form', function (e) {

            clearErrors('#l7p-register-form');

            var $form = $(this),
                    t = '';
            if ($('#tc').prop('checked'))
                t = true;

            var confirm_pass = $('form#l7p-register-form #password2').val() || $('form#l7p-register-form #password').val();
            var package_type = $('form#l7p-register-form #package_type').val() || "P";

            e.preventDefault();
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: {
                    method: 'register',
                    first_name: $('form#l7p-register-form #firstname').val(),
                    last_name: $('form#l7p-register-form #lastname').val(),
                    email: $('form#l7p-register-form #email').val(),
                    email2: $('form#l7p-register-form #email').val(),
                    password: $('form#l7p-register-form #password').val(),
                    password2: confirm_pass,
                    package_type: package_type,
                    package_route_id: $('form#l7p-register-form #package_route_id').val(),
                    google_client_id: $('form#l7p-register-form #google_client_id').val(),
                    tc: t
                },
                dataType: 'jsonp',
                success: function (res) {

                    if (res.status === 403) {

                        if (res.errors.first_name)
                            $('form#l7p-register-form #firstname').after('<p class="small error-firstname">' + res.errors.first_name + '</p>');
                        if (res.errors.last_name)
                            $('form#l7p-register-form #lastname').after('<p class="small error-lastname">' + res.errors.last_name + '</p>');
                        if (res.errors.email)
                            $('form#l7p-register-form #email').after('<p class="small error-email">' + res.errors.email + '</p>');
                        if (res.errors.email2)
                            $('form#l7p-register-form #email2').after('<p class="small error-email2">' + res.errors.email2 + '</p>');
                        if (res.errors.password)
                            $('form#l7p-register-form #password').after('<p class="small error-password">' + res.errors.password + '</p>');
                        if (res.errors.password2)
                            $('form#l7p-register-form #password2').after('<p class="small error-password2">' + res.errors.password2 + '</p>');
                        if (res.errors.tc)
                            $('form#l7p-register-form #tc').next().after('<p class="small error-ftc">' + res.errors.tc + '</p>');

                        return false;
                    } else {

                        $('#l7p-register-form').html('<p class="big center text-center">Thank You for registering.</p>'
                                + '<p class="big center text-center text-grey">Check Your email for confirmation link and <a href="/en/login">Login</a>.</p>');
                    }
                }
            });
        });

        // REGISTER AGENT
        $(document).on('submit', 'form#l7p-register-agent-form', function (e) {

            clearErrors('#l7p-register-agent-form');

            var $form = $(this),
                    t = '';
            if ($('#tc').prop('checked'))
                t = true;

            var confirm_pass = $('form#l7p-register-agent-form #password2').val() || $('form#l7p-register-agent-form #password').val(),
                    email = $('form#l7p-register-agent-form #email').val();

            e.preventDefault();
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: {
                    method: 'registeragent',
                    first_name: $('form#l7p-register-agent-form #firstname').val(),
                    last_name: $('form#l7p-register-agent-form #lastname').val(),
                    email: email,
                    email2: $('form#l7p-register-agent-form #email2').val(),
                    password: $('form#l7p-register-agent-form #password').val(),
                    password2: confirm_pass,
                    address: $('form#l7p-register-agent-form #address').val(),
                    country: $('form#l7p-register-agent-form #country').val(),
                    tc: t
                },
                dataType: 'jsonp',
                success: function (res) {

                    if (res.status === 403) {

                        if (res.errors.first_name)
                            $('form#l7p-register-agent-form #firstname').after('<p class="small error-firstname">' + res.errors.first_name + '</p>');
                        if (res.errors.last_name)
                            $('form#l7p-register-agent-form #lastname').after('<p class="small error-lastname">' + res.errors.last_name + '</p>');
                        if (res.errors.email)
                            $('form#l7p-register-agent-form #email').after('<p class="small error-email">' + res.errors.email + '</p>');
                        if (res.errors.email2)
                            $('form#l7p-register-agent-form #email2').after('<p class="small error-email2">' + res.errors.email2 + '</p>');
                        if (res.errors.password)
                            $('form#l7p-register-agent-form #password').after('<p class="small error-password">' + res.errors.password + '</p>');
                        if (res.errors.password2)
                            $('form#l7p-register-agent-form #password2').after('<p class="small error-password2">' + res.errors.password2 + '</p>');
                        if (res.errors.address)
                            $('form#l7p-register-agent-form #address').after('<p class="small error-address">' + res.errors.address + '</p>');
                        if (res.errors.country)
                            $('form#l7p-register-agent-form #country').after('<p class="small error-country">' + res.errors.country + '</p>');
                        if (res.errors.tc)
                            $('form#l7p-register-agent-form #tc').next().after('<p class="small error-ftc">' + res.errors.tc + '</p>');

                        return false;
                    } else {

                        $('#l7p-register-agent-form').html('<p class="big center text-center">Thank You for registering.</p>'
                                + '<p class="big center text-center text-grey">For security purposes, we have sent a confirmation email to <strong>' + email + '</strong>. </p>');
                    }
                }
            });
        });

        // PASSWORD RECOVERY
        $(document).on('submit', 'form#l7p-password-recover-form', function (e) {

            clearErrors('#l7p-password-recover-form');

            var $form = $(this);

            e.preventDefault();
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                dataType: 'jsonp',
                data: {
                    method: 'recover',
                    email: $('form#l7p-password-recover-form #email').val()
                },
                success: function (res) {

                    if (res.status === 403) {

                        if (res.errors.email)
                            $('form#l7p-password-recover-form #email').after('<p class="small error-email">' + res.errors.email + '</p>');

                        return false;
                    }

                    $('#l7p-password-recover-form').html('<p class="big center text-center">Your password has been changed. An email has been sent to you with your new login details.</p>');
                }
            });
        });

        // NEW PASSWORD
        $(document).on('submit', 'form#l7p-new-password-form', function (e) {

            clearErrors('#l7p-new-password-form');

            var $form = $(this);

            e.preventDefault();
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                dataType: 'jsonp',
                data: {
                    method: 'onetimelogin',
                    password1: $('form#l7p-new-password-form #password1').val(),
                    password2: $('form#l7p-new-password-form #password2').val()
                },
                success: function (res) {

                    if (!res.success) {
                        if (res.errors.password1) {
                            $('form#l7p-new-password-form #password1').after('<p class="small error-username">' + res.errors.password1 + '</p>')
                        }

                        return false;
                    }

                    if (res.redirect) {
                        // redirect user to their application url
                        window.location.href = res.redirect;
                    }

                    return false;
                }
            });
        });

        // ACTIVATE
        $(document).on('submit', 'form#l7p-activate-form', function (e) {

            clearErrors('#l7p-activate-form');

            var $form = $(this),
                    t = '';
            if ($('#tc').prop('checked'))
                t = true;

            e.preventDefault();
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                dataType: 'jsonp',
                data: {
                    method: 'activate',
                    user_id: $('form#l7p-activate-form #activation_token').val(),
                    company: $('form#l7p-activate-form #company').val(),
                    address: $('form#l7p-activate-form #address').val(),
                    postcode: $('form#l7p-activate-form #postcode').val(),
                    city: $('form#l7p-activate-form #city').val(),
                    country: $('form#l7p-activate-form #country').val(),
                    state: $('form#l7p-activate-form #state').val(),
                    tc: t
                },
                success: function (res) {

                    if (res.status === 403) {

                        if (res.errors.tc)
                            $('form#l7p-activate-form #tc').next().after('<p class="small error-ftc">' + res.errors.tc + '</p>');

                        return false;
                    }

                    var redirection = res.info;
                    if ($('form#l7p-login-form #extini').val()) {
                        redirection += '?extini=' + $('form#l7p-login-form #extini').val();
                    }

                    // redirect user to their application url
                    window.location.href = redirection;
                }
            });
        });
    });

}(window.jQuery, window, document));





