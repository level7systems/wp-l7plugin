(function ($, window, document) {

    function clearErrors(selector) {
        $(selector + '-global-errors').html("").hide();
        $(selector + ' [class*="error-"]').remove();
    }

    $(function () {

        if (!$('#l7p-login-form-global-errors').is(':empty')) {
            $('#l7p-login-form-global-errors').show();
        }

        if (!$('#l7p-login-form-global-success').is(':empty')) {
            $('#l7p-login-form-global-success').show();
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
                    username: $form.find('#username').val(),
                    password: $form.find('#password').val(),
                },
                dataType: 'jsonp',
                success: function (res) {
                    if (!res.success) {

                        if (res.errors.username)
                            $form.find('#username').after('<p class="small error-username">' + res.errors.username + '</p>')
                        if (res.errors.password)
                            $form.find('#password').after('<p class="small error-password">' + res.errors.password + '</p>')
                        if (res.errors.email) {
                            if (res.errors.email.indexOf("unrecognised user name") != -1) {
                                $('#l7p-login-form-global-errors').html(res.errors.email + '<br><a href="/en/recover-password">Have you forgotten your password?</a>').show();
                            } else if (res.errors.email.indexOf("not confirmed") != -1) {
                                $('#l7p-login-form-global-errors').html(res.errors.email + '<br><a href="/en/resend-confirmation-email/' + $form.find('#username').val() + '">Resend confirmation email to ' + $form.find('#username').val() + '</a>').show();
                            } else {
                                $('#l7p-login-form-global-errors').html(res.errors.email).show();
                            }
                        }

                        return false;
                    }

                    if (res.redirect) {
                        // redirect user to their application url
                        window.location.href = res.redirect + '?message=' + res.info;

                        return false;
                    }

                    var redirection = res.info;
                    if ($form.find('#extini').val()) {
                        redirection += '?extini=' + $form.find('#extini').val();
                    }

                    // redirect user to their application url
                    window.location.href = redirection;
                }
            });
        });

        $('select#package_type').on('change', function () {

            if (this.value == "S") {
                $('select#package_route_id').show();
            } else {
                $('select#package_route_id').hide();
            }
        });

        if ($('select#package_type')) {

            var hash = window.location.hash.substring(1);
            if ($.inArray(hash, ['P', 'S', 'A']) !== -1) {
                $('select#package_type').val(hash).change();
            }
        }

        // REGISTER
        $(document).on('submit', 'form#l7p-register-form', function (e) {

            clearErrors('#l7p-register-form');

            var $form = $(this),
                    t = '';
            if ($form.find('#tc').prop('checked'))
                t = true;

            var confirm_pass = $form.find('#password2').val() || $form.find('#password').val();
            var package_type = $form.find('#package_type').val() || "P";

            e.preventDefault();
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: {
                    method: 'register',
                    first_name: $form.find('#firstname').val(),
                    last_name: $form.find('#lastname').val(),
                    email: $form.find('#email').val(),
                    email2: $form.find('#email').val(),
                    password: $form.find('#password').val(),
                    password2: confirm_pass,
                    package_type: package_type,
                    package_route_id: $form.find('#package_route_id').val(),
                    google_client_id: $form.find('#google_client_id').val(),
                    tc: t
                },
                dataType: 'jsonp',
                success: function (res) {

                    if (res.status === 403) {

                        if (res.errors.first_name)
                            $form.find('#firstname').after('<p class="small error-firstname">' + res.errors.first_name + '</p>');
                        if (res.errors.last_name)
                            $form.find('#lastname').after('<p class="small error-lastname">' + res.errors.last_name + '</p>');
                        if (res.errors.email)
                            $form.find('#email').after('<p class="small error-email">' + res.errors.email + '</p>');
                        if (res.errors.email2)
                            $form.find('#email2').after('<p class="small error-email2">' + res.errors.email2 + '</p>');
                        if (res.errors.password)
                            $form.find('#password').after('<p class="small error-password">' + res.errors.password + '</p>');
                        if (res.errors.password2)
                            $form.find('#password2').after('<p class="small error-password2">' + res.errors.password2 + '</p>');
                        if (res.errors.tc)
                            $form.find('#tc').next().after('<p class="small error-ftc">' + res.errors.tc + '</p>');

                        return false;
                    } else {

                        $form.html('<p class="big center text-center">Thank You for registering.</p>'
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
            if ($form.find('#tc').prop('checked'))
                t = true;

            var confirm_pass = $form.find('#password2').val() || $form.find('#password').val(),
                    email = $form.find('#email').val();

            e.preventDefault();
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: {
                    method: 'registeragent',
                    first_name: $form.find('#firstname').val(),
                    last_name: $form.find('#lastname').val(),
                    email: email,
                    email2: $form.find('#email2').val(),
                    password: $form.find('#password').val(),
                    password2: confirm_pass,
                    address: $form.find('#address').val(),
                    country: $form.find('#country').val(),
                    tc: t
                },
                dataType: 'jsonp',
                success: function (res) {

                    if (res.status === 403) {

                        if (res.errors.first_name)
                            $form.find('#firstname').after('<p class="small error-firstname">' + res.errors.first_name + '</p>');
                        if (res.errors.last_name)
                            $form.find('#lastname').after('<p class="small error-lastname">' + res.errors.last_name + '</p>');
                        if (res.errors.email)
                            $form.find('#email').after('<p class="small error-email">' + res.errors.email + '</p>');
                        if (res.errors.email2)
                            $form.find('#email2').after('<p class="small error-email2">' + res.errors.email2 + '</p>');
                        if (res.errors.password)
                            $form.find('#password').after('<p class="small error-password">' + res.errors.password + '</p>');
                        if (res.errors.password2)
                            $form.find('#password2').after('<p class="small error-password2">' + res.errors.password2 + '</p>');
                        if (res.errors.address)
                            $form.find('#address').after('<p class="small error-address">' + res.errors.address + '</p>');
                        if (res.errors.country)
                            $form.find('#country').after('<p class="small error-country">' + res.errors.country + '</p>');
                        if (res.errors.tc)
                            $form.find('#tc').next().after('<p class="small error-ftc">' + res.errors.tc + '</p>');

                        return false;
                    } else {

                        $form.html('<p class="big center text-center">Thank You for registering.</p>'
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
                    email: $form.find('#email').val()
                },
                success: function (res) {

                    if (res.status === 403) {

                        if (res.errors.email)
                            $form.find('#email').after('<p class="small error-email">' + res.errors.email + '</p>');

                        return false;
                    }

                    $form.html('<p class="big center text-center">Your password has been changed. An email has been sent to you with your new login details.</p>');
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
                    reset_token: $form.find('#reset_token').val(),
                    password1: $form.find('#password1').val(),
                    password2: $form.find('#password2').val()
                },
                success: function (res) {

                    if (res.status === 403) {

                        if (res.errors.password1) {
                            $form.find('#password1').after('<p class="small error-email">' + res.errors.password1 + '</p>');
                        }
                        if (res.errors.password2) {
                            $form.find('#password2').after('<p class="small error-email">' + res.errors.password2 + '</p>');
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

        // SUBSCRIPTION
        $(document).on('submit', 'form#l7p-subscription-form', function (e) {

            clearErrors('#l7p-subscription-form');

            var $form = $(this),
                    s = '';
            if ($form.find('#is_subscribed').prop('checked'))
                s = 1;

            e.preventDefault();
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                dataType: 'jsonp',
                data: {
                    method: 'subscribe',
                    is_subscribed: s,
                    conf_link: $form.find('#subscription_token').val()
                },
                success: function (res) {

                    if (res.status === 403) {
                        if (res.errors.tc)
                            $form.find('#is_subscribed').next().after('<p class="small error-ftc">' + res.errors.is_subscribed + '</p>');
                        if (res.errors.subscription_token)
                            $form.find('#is_subscribed').next().after('<p class="small error-ftc">' + res.errors.subscription_token + '</p>');
                        return false;
                    }

                    $form.html('<p class="big center text-center">Your subscription has been updated.</p>');
                }
            });
        });

        // ACTIVATE
        $(document).on('submit', 'form#l7p-activate-form', function (e) {

            clearErrors('#l7p-activate-form');

            var $form = $(this),
                    t = '';
            if ($form.find('#tc').prop('checked'))
                t = true;

            e.preventDefault();
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                dataType: 'jsonp',
                data: {
                    method: 'activate',
                    user_id: $form.find('#activation_token').val(),
                    company: $form.find('#company').val(),
                    address: $form.find('#address').val(),
                    postcode: $form.find('#postcode').val(),
                    city: $form.find('#city').val(),
                    country: $form.find('#country').val(),
                    state: $form.find('#state').val(),
                    tc: t
                },
                success: function (res) {

                    if (res.status === 403) {

                        if (res.errors.tc)
                            $form.find('#tc').next().after('<p class="small error-ftc">' + res.errors.tc + '</p>');

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





