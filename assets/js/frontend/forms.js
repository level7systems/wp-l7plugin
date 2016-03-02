(function ($, window, document) {

    function clearErrors(selector) {
        $(selector + '-global-errors').html("").hide();
        $(selector + ' [class*="error-"]').remove();
    }

    function validateRequiredFields($form, fields) {
        var errors = [];

        for (var i in fields) {
            var field = fields[i];
            // if fields depend on other fields
            if ($.isArray(field)) {
                // if first exists - other are required to exist
                if ($form.find('#' + field[0]).length > 0) {
                    // check each field
                    for (var j in field) {
                        if (j > 0) {
                            if ($form.find('#' + field[j]).length == 0) {
                                errors.push(field[j]);
                            }
                        }
                    }
                }
            } else if ($form.find('#' + field).length == 0) {
                errors.push(field);
            }
        }

        if (errors.length > 0) {
            alert('Your are missing the following fields in your form:\n - ' + errors.join("\n - "));
        }
    }
    
    function get_cookie(key, defaults) {
        var cookies = document.cookie ? document.cookie.split('; ') : [];

        for (var i = 0, l = cookies.length; i < l; i++) {
            var parts = cookies[i].split('=');
            var name = decodeURIComponent(parts.shift());
            var cookie = parts.join('=');

            if (key && key === name) {
                try {
                   return JSON.parse(cookie);
                } catch (error) {
                   return cookie;
                }
            }
        }

        return defaults !== undefined ? defaults : undefined;
    }

    function set_cookie(name, value, options) {
        
        var options = (options === undefined) ? {} : options;
        
        if (typeof options.expires === 'number') {
            var days = options.expires, t = options.expires = new Date();
            t.setTime(+t + days * 864e+5);
        }
            
        document.cookie = [
            // storage of JOSN objects - serialize
            name, '=', JSON.stringify(value),
            options.expires ? '; expires=' + options.expires.toGMTString() : '', // use expires attribute, max-age is not supported by IE
            '; path=' + (options.path ? options.path : '/'),
            options.domain  ? '; domain=' + options.domain : '',
            options.secure || window.location.protocol == "https:" ? '; secure' : ''
        ].join('');
    }

    $(function () {
        
        // set referer cookie
        if (get_cookie('xl7ref', false) === false && document.referrer) {
            // cookie for one year
            var expire = new Date(),
                time = expire.getTime() + 1000*60*60*24*365;
            expire.setTime(time);
            set_cookie('xl7ref', document.referrer, { expires: expire });
        }
        
        // set cookie on currency change
        $('#currency').change(function() {
            set_cookie('l7p_currency', $(this).val());
        });

        if (!$('#l7p-global-errors').is(':empty')) {
            $('#l7p-global-errors').show();
        }

        if (!$('#l7p-global-success').is(':empty')) {
            $('#l7p-global-success').show();
        }

        // LOGIN
        if ($('form#l7p-login-form').length > 0) {
            // validate login form fields
            validateRequiredFields($('form#l7p-login-form'), [
                'username',
                'password',
                'remember'
            ]);
        }

        $(document).on('submit', 'form#l7p-login-form', function (e) {

            clearErrors('#l7p-login-form');

            var $form = $(this);

            e.preventDefault();
            $.jsonp({
                url: $form.attr('action'),
                callbackParameter: "callback",
                type: 'POST',
                data: {
                    method: 'login',
                    username: $form.find('#username').val(),
                    password: $form.find('#password').val(),
                    remember_me: $form.find('#remember').is(':checked'),
                },
                success: function (res) {
                    if (!res.success) {

                        if (res.errors.username)
                            $form.find('#username').after('<p class="small error-username">' + res.errors.username + '</p>')
                        if (res.errors.password)
                            $form.find('#password').after('<p class="small error-password">' + res.errors.password + '</p>')
                        if (res.errors.email) {
                            if (res.errors.email.indexOf("unrecognised user name") != -1) {
                                $('#l7p-global-errors').html(res.errors.email + '<br><a href="/en/recover-password">Have you forgotten your password?</a>').show();
                            } else if (res.errors.email.indexOf("not confirmed") != -1) {
                                $('#l7p-global-errors').html(res.errors.email + '<br><a href="/en/resend-confirmation-email/' + $form.find('#username').val() + '">Resend confirmation email to ' + $form.find('#username').val() + '</a>').show();
                            } else {
                                $('#l7p-global-errors').html(res.errors.email).show();
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
                }, 
                error: function(jqXhr, status) {
                    
                    if ($('div#maintenance').length == 0) {
                        $form.before('<div id="maintenance" class="f-msg-error error-global" style="display: block">We are sorry, Our website is undergoing maintenance. <br/>We apologise for any inconvenience caused, and thank you for your understanding!</div>');
                    }
                    
                    jQuery(document).trigger("l7p:login:error");
                }
            });
        });

        // REGISTER
        if ($('form#l7p-register-form').length > 0) {
            // validate login form fields
            validateRequiredFields($('form#l7p-register-form'), [
                'firstname',
                'lastname',
                'email',
                'password',
                ['package_type', 'package_route_id'],
                'tc'
            ]);
        }
        
        if (typeof package_type_options != 'undefined') {
            
            var currency = 'USD';
            if (typeof l7_geoip != 'undefined') {
                var currencies = { EU: "EUR", US: "USD", JP: "JPY", GB: "GBP", PL: "PLN" };
                currency = l7_geoip.country_code in currencies ? currencies[l7_geoip.country_code] : currency;
            }
            if (get_cookie('l7p_currency', false) !== false) {
                currency = get_cookie('l7p_currency');
            }
            
            $('select#package_type').html();
            var options = package_type_options[currency];
            for(var value in options) {
                $('select#package_type').append($('<option>').attr('value', value).text(options[value]));
            };
        }
        
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

        $(document).on('submit', 'form#l7p-register-form', function (e) {

            clearErrors('#l7p-register-form');

            var $form = $(this),
                    t = '';
            if ($form.find('#tc').prop('checked'))
                t = true;

            var confirm_pass = $form.find('#password2').val() || $form.find('#password').val();
            var package_type = $form.find('#package_type').val() || "P";

            var data = {
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
            };
            
            if (get_cookie('xl7ppc', false)) {
                data.xl7ppc = get_cookie('xl7ppc');
            }
            if (get_cookie('xl7a', false)) {
                data.xl7a = get_cookie('xl7a');
            }
            if (get_cookie('xl7ref', false)) {
                data.xl7ref = get_cookie('xl7ref');
            }

            e.preventDefault();
            $.jsonp({
                url: $form.attr('action'),
                callbackParameter: "callback",
                type: 'POST',
                data: data,
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

                        jQuery(document).trigger("l7p:registration:completed", ['customer']);
                    }
                }, 
                error: function(jqXhr, status) {
                    
                    if ($('div#maintenance').length == 0) {
                        $form.before('<div id="maintenance" class="f-msg-error error-global" style="display: block">We are sorry, Our website is undergoing maintenance. <br/>We apologise for any inconvenience caused, and thank you for your understanding!</div>');
                    }
                    
                    jQuery(document).trigger("l7p:registration:error", ['customer']);
                }
            });
        });

        // REGISTER AGENT
        if ($('form#l7p-register-agent-form').length > 0) {
            // validate login form fields
            validateRequiredFields($('form#l7p-register-agent-form'), [
                'firstname',
                'lastname',
                'email',
                'password',
                'address',
                'country',
                'tc'
            ]);
        }

        $(document).on('submit', 'form#l7p-register-agent-form', function (e) {

            clearErrors('#l7p-register-agent-form');

            var $form = $(this),
                    t = '';
            if ($form.find('#tc').prop('checked'))
                t = true;

            var confirm_pass = $form.find('#password2').val() || $form.find('#password').val(),
                    email = $form.find('#email').val();

            e.preventDefault();
            $.jsonp({
                url: $form.attr('action'),
                callbackParameter: "callback",
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
                        
                        jQuery(document).trigger("l7p:registration:completed", ['agent']);
                    }
                }, 
                error: function(jqXhr, status) {
                    
                    if ($('div#maintenance').length == 0) {
                        $form.before('<div id="maintenance" class="f-msg-error error-global" style="display: block">We are sorry, Our website is undergoing maintenance. <br/>We apologise for any inconvenience caused, and thank you for your understanding!</div>');
                    }
                    
                    jQuery(document).trigger("l7p:registration:error", ['agent']);
                }
            });
        });

        // PASSWORD RECOVERY
        if ($('form#l7p-password-recover-form').length > 0) {
            // validate login form fields
            validateRequiredFields($('form#l7p-password-recover-form'), [
                'email'
            ]);
        }

        $(document).on('submit', 'form#l7p-password-recover-form', function (e) {

            clearErrors('#l7p-password-recover-form');

            var $form = $(this);

            e.preventDefault();
            $.jsonp({
                url: $form.attr('action'),
                callbackParameter: "callback",
                type: 'POST',
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
                    
                    jQuery(document).trigger("l7p:password:requested");
                }, 
                error: function(jqXhr, status) {
                    
                    if ($('div#maintenance').length == 0) {
                        $form.before('<div id="maintenance" class="f-msg-error error-global" style="display: block">We are sorry, Our website is undergoing maintenance. <br/>We apologise for any inconvenience caused, and thank you for your understanding!</div>');
                    }
                    
                    jQuery(document).trigger("l7p:password:error");
                }
            });
        });

        // NEW PASSWORD
        if ($('form#l7p-new-password-form').length > 0) {
            // validate login form fields
            validateRequiredFields($('form#l7p-new-password-form'), [
                'reset_token',
                'password1',
                'password2',
            ]);
        }

        $(document).on('submit', 'form#l7p-new-password-form', function (e) {

            clearErrors('#l7p-new-password-form');

            var $form = $(this);

            e.preventDefault();
            $.jsonp({
                url: $form.attr('action'),
                callbackParameter: "callback",
                type: 'POST',
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
                    
                    jQuery(document).trigger("l7p:password:changed");

                    if (res.redirect) {
                        // redirect user to their application url
                        window.location.href = res.redirect;
                    }

                    return false;
                }, 
                error: function(jqXhr, status) {
                    
                    if ($('div#maintenance').length == 0) {
                        $form.before('<div id="maintenance" class="f-msg-error error-global" style="display: block">We are sorry, Our website is undergoing maintenance. <br/>We apologise for any inconvenience caused, and thank you for your understanding!</div>');
                    }
                    
                    jQuery(document).trigger("l7p:password:error");
                }
            });
        });

        // SUBSCRIPTION
        if ($('form#l7p-subscription-form').length > 0) {
            // validate login form fields
            validateRequiredFields($('form#l7p-subscription-form'), [
                'is_subscribed',
                'subscription_token'
            ]);
        }

        $(document).on('submit', 'form#l7p-subscription-form', function (e) {

            clearErrors('#l7p-subscription-form');

            var $form = $(this),
                    s = '';
            if ($form.find('#is_subscribed').prop('checked'))
                s = 1;

            e.preventDefault();
            $.jsonp({
                url: $form.attr('action'),
                callbackParameter: "callback",
                type: 'POST',
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
                    
                    jQuery(document).trigger("l7p:subscription:completed", [s]);
                }, 
                error: function(jqXhr, status) {
                    
                    if ($('div#maintenance').length == 0) {
                        $form.before('<div id="maintenance" class="f-msg-error error-global" style="display: block">We are sorry, Our website is undergoing maintenance. <br/>We apologise for any inconvenience caused, and thank you for your understanding!</div>');
                    }
                    
                    jQuery(document).trigger("l7p:subscription:error");
                }
            });
        });

        // ACTIVATE
        if ($('form#l7p-activate-form').length > 0) {
            // validate login form fields
            validateRequiredFields($('form#l7p-activate-form'), [
                'activation_token',
                'company',
                'address',
                'postcode',
                'city',
                'country',
                'state',
                'tc'
            ]);
        }

        $(document).on('submit', 'form#l7p-activate-form', function (e) {

            clearErrors('#l7p-activate-form');

            var $form = $(this),
                    t = '';
            if ($form.find('#tc').prop('checked'))
                t = true;

            e.preventDefault();
            $.jsonp({
                url: $form.attr('action'),
                callbackParameter: "callback",
                type: 'POST',
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
                    
                    jQuery(document).trigger("l7p:activation:completed");

                    var redirection = res.info;
                    if ($('form#l7p-login-form #extini').val()) {
                        redirection += '?extini=' + $('form#l7p-login-form #extini').val();
                    }

                    // redirect user to their application url
                    window.location.href = redirection;
                }, 
                error: function(jqXhr, status) {
                    
                    if ($('div#maintenance').length == 0) {
                        $form.before('<div id="maintenance" class="f-msg-error error-global" style="display: block">We are sorry, Our website is undergoing maintenance. <br/>We apologise for any inconvenience caused, and thank you for your understanding!</div>');
                    }
                    
                    jQuery(document).trigger("l7p:activation:error");
                }
            });
        });
    });

}(window.jQuery, window, document));





