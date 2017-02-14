if (!String.prototype.startsWith) {
    String.prototype.startsWith = function(searchString, position){
      position = position || 0;
      return this.substr(position, searchString.length) === searchString;
  };
}

function getCookie(key, defaults) {
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

function setCookie(name, value, options) {

    options = (options === undefined) ? {} : options;

    if (typeof options.expires === 'number') {
        var days = options.expires, t = options.expires = new Date();
        t.setTime(t.getTime() + days * 24*60*60*1000);
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

function isEuCountry(country_code)
{   
    var eu_countries = ["BE", "BG", "CZ", "DK", "DE", "EE", "IE", "GR", "ES", "FR", "IT", "CY", "LV", "LT", "LU", "HU", "MT", "NL", "AT", "PL", "PT", "RO", "SI", "SK", "FI", "SE", "GB", "RU", "UA", "TR", "EG", "GI", "GE", "BY", "MD", "RS", "HR", "BA", "AL", "AZ", "AM", "MC", "AD", "IS", "KZ", "LI", "MK", "ME", "NO", "SM", "CH", "VA", "MA", "DZ", "IR", "SY", "IL", "JO", "IQ", "SA", "AE", "OM", "YE"];
    return eu_countries.indexOf(country_code.toUpperCase()) > -1;
}

(function ($, window, document) {
    
    function setPackageTypeAndCountry(l7_geoip) {
        
        if (typeof package_type_options != 'undefined') { 

            var currencies = { EU: "EUR", US: "USD", JP: "JPY", GB: "GBP", PL: "PLN" },
                currency = getCookie('l7p_currency', false);
        
            if (!currency) {
                l7_geoip.country_code in currencies ? currencies[l7_geoip.country_code] : false;
            }
            
            if (!currency) {
                currency = isEuCountry(l7_geoip.country_code) ? 'EUR' : 'USD';
            }

            $('select[name="package_type"]').html();
            var options = package_type_options[currency];
            for(var value in options) {
                $('select[name="package_type"]').append($('<option>').attr('value', value).text(options[value]));
            }
        }
        
        // select package_country based on geoip
        if (l7_geoip.country_code) {
            
            $('form.l7p-rest-register-form').each(function(index, form) {
                
                var $form = $(form);
                
                if ($form.find('select[name="package_type"]').length > 0) {
                    var exists = $form.find('select[name="package_country"] option[value=' + l7_geoip.country_code + ']').length != 0;
                    if (exists) {
                        $form.find('select[name="package_type"]').val('S');
                        $form.find('select[name="package_type"]').trigger('change');
                        $form.find('select[name="package_country"]').val(l7_geoip.country_code);
                    }
                    
                    return ;
                }

                if (!$form.data('packageCountries')) {
                    return ;
                }
                
                var package_countries = $form.data('packageCountries').split(",");
                
                if ($form.find('input[name="package_type"]') && $form.find('input[name="package_country"]')) {
                    var exists = package_countries.indexOf(l7_geoip.country_code) > -1;
                    if (exists) {
                        $form.find('input[name="package_type"]').val('S');
                        $form.find('input[name="package_type"]').trigger('change');
                        $form.find('input[name="package_country"]').val(l7_geoip.country_code);
                    }
                }
            });
        }
    }

    function getGeoIp() {
        
        if (getCookie('l7_geoip', false)) {
            l7_geoip = getCookie('l7_geoip');
            jQuery(document).trigger("l7p:geoip:loaded",[ l7_geoip ]);
        } else {
            var date = new Date();
            $.getJSON('https://ssl7.net/js/geo-ip.js?_tc' + date.getTime(), function(response) {
                l7_geoip = response;
                // cookie expires in 1 day
                setCookie('l7_geoip', l7_geoip, { expires: 1 });
                jQuery(document).trigger("l7p:geoip:loaded",[ l7_geoip ]);
            }).fail(function() {
                // display global errors
                $('#l7p-global-errors, .l7p-global-errors').html("Your browser does not support C").show();
                
                jQuery(document).trigger("l7p:geoip:error");
            });
        }
    }
    
    $(document).on("l7p:geoip:loaded", function(event, l7p_geoip) {
        setPackageTypeAndCountry(l7_geoip);
    });
    
    function clearErrors($form) {
        $form.find('[class*="-global-success"]').html("").hide();
        $form.find('[class*="-global-errors"]').html("").hide();
        $form.find('[class*="error-"]').remove();
    }

    function validateRequiredFields($form, fields) {
        var errors = [];
        
        for (var i in fields) {
            var field = fields[i];
            if ($form.find('[name="' + field + '"]').length === 0) {
                errors.push(field);
            }
            
            // if form has package_type filed
            if ((field == 'package_type') && ($form.find('select[name="package_type"]').length > 0)) {
                
                if ($form.find('[name="package_country"]').length === 0) {
                    errors.push(field);
                }
            }
        }

        if (errors.length > 0) {
            alert('Your are missing the following fields in your form:\n - ' + errors.join("\n - "));
        }
    }
    
    var onLoginSuccess = function (response) {

        var $form = this;
        
        if ($form.data('appKey') == 'gotrunk') {
            
            if (!response) {
                jQuery(document).trigger("l7p:form:completed");
                $form.find('input[name="username"]').after('<p class="small error-username">Failed to decode API response</p>');
                return false;
            }

            if (!response.user_id || !response.user_token) {
                jQuery(document).trigger("l7p:form:completed");
                $form.find('input[name="username"]').after('<p class="small error-username">API failed to return userId and/or userToken</p>');
                return false;
            }

            setCookie($form.data('appKey') + '.auth', {
                user_id: response.user_id, 
                user_token: response.user_token 
            });

            var url_suffix = '';
            if ($form.find('input[name="extini"]').val()) {
                var match = $form.find('input[name="extini"]').val().match(/SupportSubmitReplyWindow\(([0-9]+)\)/);
                if (match[1]) {
                    url_suffix = '#support,support:' + match[1];
                }
            }
            
            var redirection = '/app/' + url_suffix;
            
        } else {
        
            if (!response.success) {

                jQuery(document).trigger("l7p:form:completed");

                if (response.errors.username) {
                    $form.find('input[name="username"]').after('<p class="small error-username">' + response.errors.username + '</p>');
                }

                if (response.errors.password) {
                    $form.find('input[name="password"]').after('<p class="small error-password">' + response.errors.password + '</p>');
                }

                if (response.errors.email) {
                    if (response.errors.email.indexOf("unrecognised user name") != -1) {

                        var recover_url = '/recover-password';
                        if (document.location.pathname.startsWith('/en')) {
                            recover_url = '/en' + recover_url;
                        }

                        $('#l7p-global-errors, .l7p-global-errors').html(response.errors.email + '<br><a href="' + recover_url + '">Have you forgotten your password?</a>').show();
                    } else if (response.errors.email.indexOf("not confirmed") != -1) {

                        var confirmation_url = '/resend-confirmation-email';
                        if (document.location.pathname.startsWith('/en')) {
                            confirmation_url = '/en' + confirmation_url;
                        }

                        $('#l7p-global-errors, .l7p-global-errors').html(response.errors.email + '<br><a href="' + confirmation_url + '/' + $form.find('input[name="username"]').val() + '">Resend confirmation email to ' + $form.find('input[name="username"]').val() + '</a>').show();
                    } else {
                        $('#l7p-global-errors, .l7p-global-errors').html(response.errors.email).show();
                    }
                }

                if (response.errors.web_product_activation) {
                    jQuery(document).trigger("l7p:web_product:activation", [ response.errors.web_product_activation ]);
                }

                return false;
            }

            if (response.redirect) {
                // redirect user to their application url
                window.location.href = response.redirect + '?message=' + response.info;

                return false;
            }

            var redirection = response.info;
            if ($form.find('input[name="extini"]').val()) {
                redirection += '?extini=' + $form.find('input[name="extini"]').val();
            }
        }

        // redirect user to their application url
        window.location.href = redirection;
    };
            
    var onLoginError = function(jqXhr, status) {

        var $form = this;
        
        jQuery(document).trigger("l7p:form:completed");

        if (jqXhr.status === 400) {

            var response = jqXhr.responseJSON;
            $.each(response.errors, function(i, error) {

                if (error.field == 'email') {

                    if (error.code == 'AU1001') {
                        jQuery(document).trigger("l7p:web_product:activation", [ error.message ]);
                    } else if ($form.hasClass('l7p-activate-form')) {
                        $form.find('input[name="username"]').after('<p class="small error-username">' + error.message + '</p>');
                    } else if (error.message.indexOf("Invalid email and/or password") != -1) {

                        var recover_url = '/recover-password';
                        if (document.location.pathname.startsWith('/en')) {
                            recover_url = '/en' + recover_url;
                        }

                        $('#l7p-global-errors, .l7p-global-errors').html(error.message + '<br><a href="' + recover_url + '">Have you forgotten your password?</a>').show();
                    } else if (error.message.indexOf("not confirmed") != -1) {

                        var confirmation_url = '/resend-confirmation-email';
                        if (document.location.pathname.startsWith('/en')) {
                            confirmation_url = '/en' + confirmation_url;
                        }

                        $('#l7p-global-errors, .l7p-global-errors').html(error.message + '<br><a href="' + confirmation_url + '/' + $form.find('input[name="username"]').val() + '">Resend confirmation email to ' + $form.find('input[name="username"]').val() + '</a>').show();
                    } else {
                        $form.find('input[name="username"]').after('<p class="small error-username">' + error.message + '</p>');
                    }
                }

            });

            return false;
        }
        
        if ($('div#maintenance').length === 0) {
            $form.before('<div id="maintenance" class="f-msg-error error-global" style="display: block">We are sorry, Our website is undergoing maintenance. <br/>We apologise for any inconvenience caused, and thank you for your understanding!</div>');
        }

        jQuery(document).trigger("l7p:login:error");
    };
    
    function loginLegacy($form) {
        
        clearErrors($form);
        var url = $form.attr('action');
            
        if($form.attr('data-api-url')){
            url = $form.attr('data-api-url');
        }
        $.jsonp({
            url: url,
            callbackParameter: "callback",
            type: 'POST',
            data: {
                method: 'login',
                username: $form.find('input[name="username"]').val(),
                password: $form.find('input[name="password"]').val(),
                remember_me: $form.find('#remember').is(':checked')
            },
            beforeSend: function() {
                jQuery(document).trigger("l7p:form:processing");
            },
            success:  onLoginSuccess.bind($form), 
            error: onLoginError.bind($form)
        });
    }
            
    function login($form) {
        
        clearErrors($form);
        var url = $form.attr('action');
            
        if($form.attr('data-api-url')){
            url = $form.attr('data-api-url');
        }
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: JSON.stringify({
                email: $form.find('input[name="username"]').val(),
                password: $form.find('input[name="password"]').val()
            }),
            contentType: 'application/json; charset=utf-8',
            beforeSend: function(){
                jQuery(document).trigger("l7p:form:processing");
            },
            success: onLoginSuccess.bind($form),
            error: onLoginError.bind($form)
        });
    }
    
    $(function () {
        
        $('select[name="package_type"]').on('change', function () {

            var $form = $(this).parents('form:first'),
                $select = $form.find('select[name="package_country"]'),
                $label = $select.prev();
            
            if (this.value == 'S') {
                $select.show();
                $label.show();
            } else {
                $select.hide();
                $label.hide();
            }
        });
        
        getGeoIp();
        
        // set referer cookie
        if (getCookie('xl7ref', false) === false && document.referrer) {
            // cookie for one year
            var expire = new Date(),
                time = expire.getTime() + 1000*60*60*24*365;
            expire.setTime(time);
            setCookie('xl7ref', document.referrer, { expires: expire });
        }
        
        // set cookie on currency change
        $('#currency').change(function() {
            setCookie('l7p_currency', $(this).val());
        });

        if (!$('#l7p-global-errors, .l7p-global-errors').is(':empty')) {
            $('#l7p-global-errors, .l7p-global-errors').show();
        }

        if (!$('#l7p-global-success').is(':empty')) {
            $('#l7p-global-success').show();
        }

        // validate login
        if ($('form.l7p-login-form').length > 0) {
            // validate login form fields
            validateRequiredFields($('form#l7p-login-form, form.l7p-login-form'), [
                'username',
                'password',
                'remember'
            ]);
        }

        // legacy login form
        $(document).on('submit', 'form#l7p-login-form, form.l7p-login-form', function (e) {

            e.preventDefault();
            
            loginLegacy($(this));
        });
        
        // REST login form
        $(document).on('submit', 'form#l7p-rest-login-form, form.l7p-rest-login-form', function (e) {

            e.preventDefault();

            login($(this));
        });

        // REGISTER
        if ($('form.l7p-register-form').length > 0) {
            
            $('form.l7p-register-form').each(function(index, form) {
                
                validateRequiredFields($(form), [
                    'firstname',
                    'lastname',
                    'email',
                    'password',
                    'tc'
                ]);
            });
        }
        
        // REST REGISTER
        if ($('form.l7p-rest-register-form').length > 0) {
            
            $('form.l7p-rest-register-form').each(function(index, form) {
                
                if ($(form).data('appKey') == 'voipstudio') {
                    // voipstudio
                    validateRequiredFields($(form), [
                        'firstname',
                        'lastname',
                        'email',
                        'password',
                        'package_type',
                        'tc'
                    ]);
                } else {
                    // gotrunk
                    validateRequiredFields($(form), [
                        'firstname',
                        'lastname',
                        'email',
                        'password',
                        'tc'
                    ]);
                }
            });
        }
        
        if ($('select[name="package_type"]')) {

            var hash = window.location.hash.substring(1);
            if ($.inArray(hash, ['P', 'S', 'A']) !== -1) {
                $('select[name="package_type"]').val(hash).change();
            }
        }

        $(document).on('submit', 'form#l7p-register-form, form.l7p-register-form', function (e) {

            var $form = $(this),
                t = '';
            
            clearErrors($form);

            if ($form.find('input[name="tc"]').prop('checked')) {
                t = true;
            }

            var confirm_pass = $form.find('input[name="password2"]').val() || $form.find('input[name="password"]').val();
            var package_type = $form.find('select[name="package_type"]').val() || "P";

            var data = {
                method: 'register',
                first_name: $form.find('input[name="firstname"]').val(),
                last_name: $form.find('input[name="lastname"]').val(),
                email: $form.find('input[name="email"]').val(),
                email2: $form.find('input[name="email"]').val(),
                password: $form.find('input[name="password"]').val(),
                password2: confirm_pass,
                package_type: package_type,
                package_route_id: $form.find('select[name="package_route_id"]').val(),
                google_client_id: $form.find('input[name="google_client_id"]').val(),
                tc: t
            };
            
            if (getCookie('xl7ppc', false)) {
                data.xl7ppc = getCookie('xl7ppc');
            }
            if (getCookie('xl7a', false)) {
                data.xl7a = getCookie('xl7a');
            }
            if (getCookie('xl7ref', false)) {
                data.xl7ref = getCookie('xl7ref');
            }
            
            var url = $form.attr('action');
            
            if($form.attr('data-api-url')){
                url = $form.attr('data-api-url');
            }
            e.preventDefault();
            $.jsonp({
                url: url,
                callbackParameter: "callback",
                type: 'POST',
                data: data,
                beforeSend: function(){
                    jQuery(document).trigger("l7p:form:processing");
                },
                success: function (res) {

                    if (res.status === 403) {

                        if (res.errors.web_product_activation) {
                            jQuery(document).trigger("l7p:web_product:activation", [ res.errors.web_product_activation ]);
                            return false;
                        }

                        if (res.errors.first_name)
                            $form.find('input[name="firstname"]').after('<p class="small error-firstname">' + res.errors.first_name + '</p>');
                        if (res.errors.last_name)
                            $form.find('input[name="lastname"]').after('<p class="small error-lastname">' + res.errors.last_name + '</p>');
                        if (res.errors.email)
                            $form.find('input[name="email"]').after('<p class="small error-email">' + res.errors.email + '</p>');
                        if (res.errors.email2)
                            $form.find('input[name="email2"]').after('<p class="small error-email2">' + res.errors.email2 + '</p>');
                        if (res.errors.password)
                            $form.find('input[name="password"]').after('<p class="small error-password">' + res.errors.password + '</p>');
                        if (res.errors.password2)
                            $form.find('input[name="password2"]').after('<p class="small error-password2">' + res.errors.password2 + '</p>');
                        if (res.errors.tc) {
                            if ($form.find('input[name="tc"]').parent().is('label')) {
                                $form.find('input[name="tc"]').parent().after('<p class="small error-ftc">' + res.errors.tc + '</p>');
                            } else if ($form.find('input[name="tc"]').next()) {
                                $form.find('input[name="tc"]').next().after('<p class="small error-ftc">' + res.errors.tc + '</p>');
                            }
                        }

                        return false;
                    } else {

                        var login_url = '/login';
                        if (document.location.pathname.startsWith('/en')) {
                            login_url = '/en' + login_url;
                        }
                        
                        $form.html('<p class="big center text-center">Thank you for registering.</p>' +
                                '<p class="big center text-center text-grey">Check your email for confirmation link and <a href="' + login_url + '">Login</a>.</p>');

                        jQuery(document).trigger("l7p:registration:completed", ['customer', $form.attr('data-l7p-event')]);
                    }
                }, 
                error: function(jqXhr, status) {
                    
                    if ($('div#maintenance').length === 0) {
                        $form.before('<div id="maintenance" class="f-msg-error error-global" style="display: block">We are sorry, Our website is undergoing maintenance. <br/>We apologise for any inconvenience caused, and thank you for your understanding!</div>');
                    }
                    
                    jQuery(document).trigger("l7p:registration:error", ['customer']);
                },
                complete: function(){
                    jQuery(document).trigger("l7p:form:completed");
                }
            });
        });
        
        // REST register form
        $(document).on('submit', 'form#l7p-rest-register-form, form.l7p-rest-register-form', function (e) {

            var $form = $(this);
            
            clearErrors($form);

            var data = {
                first_name: $form.find('input[name="firstname"]').val(),
                last_name: $form.find('input[name="lastname"]').val(),
                email: $form.find('input[name="email"]').val(),
                password: $form.find('input[name="password"]').val(),
                google_client_id: $form.find('input[name="google_client_id"]').val(),
                tc: $form.find('input[name="tc"]').prop('checked')
            };
            
            // business voip
            if ($form.data('appKey') == 'voipstudio') {
                data.package_type = $form.find('select[name="package_type"]').val() || "P";
                
                if (data.package_type == 'S') {
                    data.package_country = $form.find('select[name="package_country"]').val();
                }
            }
            
            if (getCookie('xl7ppc', false)) {
                data.xl7ppc = getCookie('xl7ppc');
            }
            if (getCookie('xl7a', false)) {
                data.xl7a = getCookie('xl7a');
            }
            if (getCookie('xl7ref', false)) {
                data.xl7ref = getCookie('xl7ref');
            }
            
            var url = $form.attr('action');
            if($form.attr('data-api-url')){
                url = $form.attr('data-api-url');
            }

            e.preventDefault();
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: JSON.stringify(data),
                contentType: 'application/json; charset=utf-8',
                beforeSend: function(){
                    jQuery(document).trigger("l7p:form:processing");
                },
                success: function (res) {

                    jQuery(document).trigger("l7p:registration:completed", ['customer', $form.attr('data-l7p-event')]);
                    
                    if ($form.data('appKey') == 'gotrunk') {
                        
                        setCookie($form.data('appKey') + '.register', { email: data.email, first_name: data.first_name, last_name: data.last_name });
                        // redirect user to their application url
                        window.location.href = '/app/';
                    } else {
                        jQuery(document).trigger("l7p:form:completed");

                        $form.html('<p class="big center text-center">Thank you for registering.</p>' +
                                '<p class="big center text-center text-grey">Check your email for confirmation link and <a href="/login">Login</a>.</p>');
                    }
                    
                }, 
                error: function(jqXhr, status) {
                    jQuery(document).trigger("l7p:form:completed");
                    if (jqXhr.status === 400) {

                        var res = jqXhr.responseJSON;
                        
                        $.each(res.errors, function(i, error) {

                            if (error.code == 'CU1001') {
                                jQuery(document).trigger("l7p:web_product:activation", [ error.message ]);
                                return;
                            }
                           
                            if (error.field == 'email') {
                                $form.find('input[name="email"]').after('<p class="small error-email">' + error.message + '</p>');
                            }
                            if (error.field == 'password') {
                                $form.find('input[name="password"]').after('<p class="small error-password">' + error.message + '</p>');
                            }
                            if (error.field == 'first_name') {
                                $form.find('input[name="firstname"]').after('<p class="small error-firstname">' + error.message + '</p>');
                            }
                            if (error.field == 'last_name') {
                                $form.find('input[name="lastname"]').after('<p class="small error-lastname">' + error.message + '</p>');
                            }
                            
                            if (error.field == 'tc') {
                                var $parent = $form.find('input[name="tc"]').parents('label:first');
                                if ($parent.is('label')) {
                                    $parent.after('<p class="small error-ftc">' + error.message + '</p>');
                                } else if ($form.find('input[name="tc"]').next()) {
                                    $form.find('input[name="tc"]').next().after('<p class="small error-ftc">' + error.message + '</p>');
                                }
                            }
                        });
                        
                        return false;
                    } else {
                    
                        if ($('div#maintenance').length === 0) {
                            $form.before('<div id="maintenance" class="f-msg-error error-global" style="display: block">We are sorry, Our website is undergoing maintenance. <br/>We apologise for any inconvenience caused, and thank you for your understanding!</div>');
                        }
                    }
                    
                    jQuery(document).trigger("l7p:registration:error", ['customer']);
                }
            });
        });
        

        // REGISTER AGENT
        if ($('form#l7p-register-agent-form, form.l7p-register-agent-form').length > 0) {
            
            $('form#l7p-register-agent-form, form.l7p-register-agent-form').each(function(index, form) {
            
                validateRequiredFields($(form), [
                    'firstname',
                    'lastname',
                    'email',
                    'password',
                    'address',
                    'city',
                    'postcode',
                    'country',
                    'tc'
                ]);
            });
        }

        $(document).on('submit', 'form#l7p-register-agent-form, form.l7p-register-agent-form', function (e) {

            var $form = $(this),
                    t = '';
                    
            clearErrors($form);
            
            if ($form.find('input[name="tc"]').prop('checked'))
                t = true;

            var confirm_pass = $form.find('input[name="password2"]').val() || $form.find('input[name="password"]').val(),
                email = $form.find('input[name="email"]').val(),
                confirm_email = $form.find('input[name="email2"]').val() || email;

            e.preventDefault();
            $.jsonp({
                url: $form.attr('action'),
                callbackParameter: "callback",
                type: 'POST',
                data: {
                    method: 'registeragent',
                    first_name: $form.find('input[name="firstname"]').val(),
                    last_name: $form.find('input[name="lastname"]').val(),
                    email: email,
                    email2: confirm_email,
                    password: $form.find('input[name="password"]').val(),
                    password2: confirm_pass,
                    address: $form.find('#address').val(),
                    city: $form.find('#city').val(),
                    postcode: $form.find('#postcode').val(),
                    country: $form.find('#country').val(),
                    tc: t
                },
                beforeSend: function(){
                    jQuery(document).trigger("l7p:form:processing");
                },
                success: function (res) {

                    if (res.status === 403) {

                        if (res.errors.first_name)
                            $form.find('input[name="firstname"]').after('<p class="small error-firstname">' + res.errors.first_name + '</p>');
                        if (res.errors.last_name)
                            $form.find('input[name="lastname"]').after('<p class="small error-lastname">' + res.errors.last_name + '</p>');
                        if (res.errors.email)
                            $form.find('input[name="email"]').after('<p class="small error-email">' + res.errors.email + '</p>');
                        if (res.errors.email2)
                            $form.find('input[name="email2"]').after('<p class="small error-email2">' + res.errors.email2 + '</p>');
                        if (res.errors.password)
                            $form.find('input[name="password"]').after('<p class="small error-password">' + res.errors.password + '</p>');
                        if (res.errors.password2)
                            $form.find('input[name="password2"]').after('<p class="small error-password2">' + res.errors.password2 + '</p>');
                        if (res.errors.address)
                            $form.find('#address').after('<p class="small error-address">' + res.errors.address + '</p>');
                        if (res.errors.city)
                            $form.find('#city').after('<p class="small error-city">' + res.errors.city + '</p>');
                        if (res.errors.postcode)
                            $form.find('#postcode').after('<p class="small error-postcode">' + res.errors.postcode + '</p>');
                        if (res.errors.country)
                            $form.find('#country').after('<p class="small error-country">' + res.errors.country + '</p>');
                        if (res.errors.tc)
                            $form.find('input[name="tc"]').next().after('<p class="small error-ftc">' + res.errors.tc + '</p>');

                        return false;
                    } else {

                        $form.html('<p class="big center text-center">Thank you for registering.</p>' +
                                '<p class="big center text-center text-grey">For security purposes, we have sent a confirmation email to <strong>' + email + '</strong>. </p>');
                        
                        jQuery(document).trigger("l7p:registration:completed", ['agent']);
                    }
                }, 
                error: function(jqXhr, status) {
                    
                    if ($('div#maintenance').length === 0) {
                        $form.before('<div id="maintenance" class="f-msg-error error-global" style="display: block">We are sorry, Our website is undergoing maintenance. <br/>We apologise for any inconvenience caused, and thank you for your understanding!</div>');
                    }
                    
                    jQuery(document).trigger("l7p:registration:error", ['agent']);
                },
                complete: function(){
                    jQuery(document).trigger("l7p:form:completed");
                }
            });
        });

        // PASSWORD RECOVERY
        if ($('form#l7p-password-recover-form, form.l7p-password-recover-form').length > 0) {
            // validate login form fields
            validateRequiredFields($('form#l7p-password-recover-form, form.l7p-password-recover-form'), [
                'email'
            ]);
        }

        $(document).on('submit', 'form#l7p-password-recover-form, form.l7p-password-recover-form', function (e) {
            var $form = $(this);
            clearErrors($form);
            
            var url = $form.attr('action');
            if($form.attr('data-api-url')){
                url = $form.attr('data-api-url');
            }
            
            e.preventDefault();
            $.jsonp({
                url: url,
                callbackParameter: "callback",
                type: 'POST',
                data: {
                    method: 'recover',
                    email: $form.find('input[name="email"]').val()
                },
                beforeSend: function(){
                    jQuery(document).trigger("l7p:form:processing");
                },
                success: function (res) {

                    if (res.status === 403) {

                        if (res.errors.email)
                            $form.find('input[name="email"]').after('<p class="small error-email">' + res.errors.email + '</p>');

                        return false;
                    }

                    $form.html('<p class="big center text-center">Your password has been changed. An email has been sent to you with your new login details.</p>');
                    
                    jQuery(document).trigger("l7p:password:requested");
                }, 
                error: function(jqXhr, status) {
                    
                    if ($('div#maintenance').length === 0) {
                        $form.before('<div id="maintenance" class="f-msg-error error-global" style="display: block">We are sorry, Our website is undergoing maintenance. <br/>We apologise for any inconvenience caused, and thank you for your understanding!</div>');
                    }
                    
                    jQuery(document).trigger("l7p:password:error");
                },
                complete: function(){
                    jQuery(document).trigger("l7p:form:completed");
                }
            });
        });

        // NEW PASSWORD
        if ($('form#l7p-new-password-form, form.l7p-new-password-form').length > 0) {
            // validate login form fields
            validateRequiredFields($('form#l7p-new-password-form, form.l7p-new-password-form'), [
                'password1',
                'password2'
            ]);
        }

        $(document).on('submit', 'form#l7p-new-password-form, form.l7p-new-password-form', function (e) {

            var $form = $(this);
            
            clearErrors($form);
            var url = $form.attr('action');
            if($form.attr('data-api-url')){
                url = $form.attr('data-api-url');
            }
            e.preventDefault();
            $.jsonp({
                url: url,
                callbackParameter: "callback",
                type: 'POST',
                data: {
                    method: 'onetimelogin',
                    reset_token: getCookie('reset_token', ''),
                    password1: $form.find('#password1').val(),
                    password2: $form.find('input[name="password2"]').val()
                },
                beforeSend: function(){
                    jQuery(document).trigger("l7p:form:processing");
                },
                success: function (res) {

                    if (res.status === 403) {
                        jQuery(document).trigger("l7p:form:completed");
                        if (res.errors.reset_token) {
                            $('#l7p-global-errors, .l7p-global-errors').html(res.errors.reset_token).show();
                            return false;
                        }
                        if (res.errors.password1) {
                            $form.find('#password1').after('<p class="small error-email">' + res.errors.password1 + '</p>');
                        }
                        if (res.errors.password2) {
                            $form.find('input[name="password2"]').after('<p class="small error-email">' + res.errors.password2 + '</p>');
                        }

                        return false;
                    }
                    
                    jQuery(document).trigger("l7p:password:changed");

                    if (res.redirect) {
                        // redirect user to their application url
                        window.location.href = res.redirect;
                    }
                    
                    if (res.email) {
                        
                        $.ajax({
                            url: $form.data('restApiLoginUrl'),
                            type: 'POST',
                            dataType: 'json',
                            data: JSON.stringify({
                                email:res.email,
                                password: $form.find('#password1').val()
                            }),
                            contentType: 'application/json; charset=utf-8',
                            success: function (res) {

                                if (!res.user_id || !res.user_token) {
                                    jQuery(document).trigger("l7p:form:completed");
                                    $('#l7p-global-errors, .l7p-global-errors').html('API failed to return userId and/or userToken').show();
                                    return false;
                                }

                                setCookie($form.data('appKey') + '.auth', {
                                    user_id: res.user_id, 
                                    user_token: res.user_token 
                                });

                                // redirect user to their application url
                                window.location.href = '/app/';
                            }, 
                            error: function(jqXhr, status) {
                                jQuery(document).trigger("l7p:form:completed");
                                if ($('div#maintenance').length === 0) {
                                    $form.before('<div id="maintenance" class="f-msg-error error-global" style="display: block">We are sorry, Our website is undergoing maintenance. <br/>We apologise for any inconvenience caused, and thank you for your understanding!</div>');
                                }
                            }
                        });
                    }
                    
                    return false;
                }, 
                error: function(jqXhr, status) {
                    jQuery(document).trigger("l7p:form:completed");
                    if ($('div#maintenance').length === 0) {
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

        $(document).on('submit', 'form#l7p-subscription-form, form.l7p-subscription-form', function (e) {

            var $form = $(this),
                    s = '';
                    
            clearErrors($form);

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
                beforeSend: function(){
                    jQuery(document).trigger("l7p:form:processing");
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
                    
                    if ($('div#maintenance').length === 0) {
                        $form.before('<div id="maintenance" class="f-msg-error error-global" style="display: block">We are sorry, Our website is undergoing maintenance. <br/>We apologise for any inconvenience caused, and thank you for your understanding!</div>');
                    }
                    
                    jQuery(document).trigger("l7p:subscription:error");
                },
                complete: function(){
                    jQuery(document).trigger("l7p:form:completed");
                }
            });
        });

        var onActivationSuccess = function (response) {

            var $form = this;
            
            if (!response) {
                jQuery(document).trigger("l7p:form:completed");
                $form.find('input[name="username"]').after('<p class="small error-username">Failed to decode API response</p>');
                return false;
            }

            if (!response.user_id || !response.user_token) {
                jQuery(document).trigger("l7p:form:completed");
                $form.find('input[name="username"]').after('<p class="small error-username">API failed to return userId and/or userToken</p>');
                return false;
            }
            
            var data = {
                web_product_id: $form.find('input[name="web_product_id"]').val(),
                tc: $form.find('input[name="tc"]').prop('checked') ? true : false
            };
            
            // business voip
            if ($form.data('appKey') == 'voipstudio') {
                data.package_type = $form.find('select[name="package_type"]').val() || "P";
                
                if (data.package_type == 'S') {
                    data.package_country = $form.find('select[name="package_country"]').val();
                }
            }

            // web product activation
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                dataType: 'json',
                data: JSON.stringify(data),
                contentType: 'application/json; charset=utf-8',
                beforeSend: function(jqXhr) {
                    jQuery(document).trigger("l7p:form:processing");
                    jqXhr.setRequestHeader("Authorization", "Basic " + btoa(response.user_id + ":" + response.user_token));
                },
                success: function (response) {
                    // set form action to login
                    if ($form.data('appKey') == 'voipstudio') {
                        var login_url = $('form.l7p-login-form').attr('action');
                        $form.attr('action', login_url);
                        // login to new web product
                        loginLegacy($form);
                    } else {
                        var login_url = $form.data('restApiUrl') + '/login';
                        $form.attr('action', login_url);
                        // login to new web product
                        login($form);
                    }
                }, 
                error: function(jqXhr, status) {
                    jQuery(document).trigger("l7p:form:completed");
                    if (jqXhr.status === 400) {

                        var res = jqXhr.responseJSON;

                        $.each(res.errors, function(i, error) {

                            if (error.field == 'email') {
                                $form.find('input[name="email"]').after('<p class="small error-email">' + error.message + '</p>');
                            }
                            if (error.field == 'password') {
                                $form.find('input[name="password"]').after('<p class="small error-password">' + error.message + '</p>');
                            }

                            if (error.field == 'tc') {
                                var $parent = $form.find('input[name="tc"]').parents('label:first');
                                if ($parent.is('label')) {
                                    $parent.after('<p class="small error-ftc">' + error.message + '</p>');
                                } else if ($form.find('input[name="tc"]').next()) {
                                    $form.find('input[name="tc"]').next().after('<p class="small error-ftc">' + error.message + '</p>');
                                }
                            }
                        });

                        return false;
                    } else {

                        if ($('div#maintenance').length === 0) {
                            $form.before('<div id="maintenance" class="f-msg-error error-global" style="display: block">We are sorry, Our website is undergoing maintenance. <br/>We apologise for any inconvenience caused, and thank you for your understanding!</div>');
                        }
                    }

                    jQuery(document).trigger("l7p:registration:error", ['customer']);
                }
            });
        };
        
        var onActivationError = onLoginError;

        $(document).on('submit', 'form#l7p-activate-form, form.l7p-activate-form', function (e) {
            
            e.preventDefault();
            
            var $form = $(this),
                username = $form.find('input[name="username"]').val(),
                password = $form.find('input[name="password"]').val();
                    
            clearErrors($form);
            var url = $form.attr('action');
            if($form.attr('data-api-url')){
                url = $form.attr('data-api-url');
            }
            // login first
            $.ajax({
                url: url.replace('/customerhaswebproducts', '/login'),
                type: 'POST',
                dataType: 'json',
                data: JSON.stringify({
                    email: username,
                    password: password
                }),
                contentType: 'application/json; charset=utf-8',
                beforeSend: function(){
                    jQuery(document).trigger("l7p:form:processing");
                },
                success: onActivationSuccess.bind($form),
                error: onActivationError.bind($form)
            });
            
        });
        $.ui.autocomplete.prototype._renderItem = function (ul, item) {
            item.label = item.label.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + $.ui.autocomplete.escapeRegex(this.term) + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<strong>$1</strong>");
            return $("<li></li>")
                    .data("item.autocomplete", item)
                    .append("<a>" + item.label + "</a>")
                    .appendTo(ul);
        };
        if($('.l7p-manual-search-form').length){
            $( ".l7p-manual-search-form input" ).autocomplete({
                    select: function( event, ui ) {
                        window.location = ui.item.key;
                    },
                    source: function(data, response){
                        $.ajax({
				type: 'POST',
				dataType: 'json',
				url: ajax_options.admin_ajax_url,
				data: {'action': 'search_autocomplete', 'term': data.term},
				success: function(data) {
                                    response(data);
				}
			});
                    },
            });
        }
        
        
    });

}(window.jQuery, window, document));
