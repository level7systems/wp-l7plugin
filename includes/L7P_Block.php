<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function l7p_block_currency_form()
{
    $selected_currency = l7p_get_currency();
    $currencies = l7p_get_currencies();

    ob_start();

    ?>
    <form id="l7p-currency-form" method="post" action="" class="l7p-currency-form">
        <select name="currency" id="currency" onchange="this.form.submit()">
            <?php foreach ($currencies as $currency_iso): ?>
                <option value="<?php echo $currency_iso ?>"<?php if ($selected_currency == $currency_iso): ?>selected="selected"<?php endif; ?>><?php echo l7p_currency_name($currency_iso) ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php
    $content = ob_get_clean();

    return L7P_Content::parse_content($content);
}

function l7p_block_login_form()
{
    ob_start();

    ?>

    <p id="l7p-global-success">[SUCCESS_FLASH_MESSAGE]</p>

    <p id="l7p-global-errors">[ERROR_FLASH_MESSAGE]</p>

    <form id="l7p-login-form" method="post" action="[FORM_LOGIN_ACTION]" class="l7p-login-form">

        [FORM_SECURITY_TOKEN]

        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'username', 'label' => 'E-mail')) ?>
            <?php echo L7P_Form::text_input(array('name' => 'username', 'placeholder' => 'E-mail address', 'required' => true)) ?>
        </div>

        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'password', 'label' => 'Password')) ?>
            <?php echo L7P_Form::password_input(array('name' => 'password', 'placeholder' => 'Password', 'required' => true)) ?>
        </div>

        <label for="remember">
            <input id="remember" type="checkbox" name="remember" />
            <?php echo __('Remember Me', 'level7platform') ?>
        </label>
        <button id="l7p-login-button"><?php echo __('Login', 'level7platform') ?></button>
    </fieldset>

    </form>

    <?php
    $content = ob_get_clean();

    return L7P_Content::parse_content($content);
}

function l7p_block_register_form()
{
    ob_start();

    $currency = l7p_get_currency();
    $register_settings = l7p_get_settings('register');

    $package_types = isset($register_settings['package_types'][$currency]) ? $register_settings['package_types'][$currency] : array();
    $term_routes = isset($register_settings['routes']) ? $register_settings['routes'] : array();

    $routes = array();
    foreach ($term_routes as $id => $country_code) {
        $routes[$id] = l7p_country_name($country_code);
    }

    ?>

    <p id="l7p-global-success">[SUCCESS_FLASH_MESSAGE]</p>

    <p id="l7p-global-errors">[ERROR_FLASH_MESSAGE]</p>

    <form id="l7p-register-form" method="post" action="[FORM_REGISTER_ACTION]" class="l7p-register-form">

        [FORM_SECURITY_TOKEN]

        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'firstname', 'label' => __('First Name', 'level7platform'))) ?>
            <?php echo L7P_Form::text_input(array('name' => 'firstname', 'placeholder' => __('First Name', 'level7platform'), 'required' => true)) ?>
        </div>
        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'lastname', 'label' => __('Last Name', 'level7platform'))) ?>
            <?php echo L7P_Form::text_input(array('name' => 'lastname', 'placeholder' => __('Last Name', 'level7platform'), 'required' => true)) ?>
        </div>
        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'password', 'label' => __('Password', 'level7platform'))) ?>
            <?php echo L7P_Form::password_input(array('name' => 'password', 'placeholder' => __('Password', 'level7platform'), 'required' => true)) ?>
        </div>
        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'password2', 'label' => __('Confirm Password', 'level7platform'))) ?>
            <?php echo L7P_Form::password_input(array('name' => 'password2', 'placeholder' => __('Confirm Password', 'level7platform'), 'required' => true)) ?>
        </div>
        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'email', 'label' => __('E-mail', 'level7platform'))) ?>
            <?php echo L7P_Form::text_input(array('name' => 'email', 'placeholder' => __('E-mail', 'level7platform'), 'required' => true)) ?>
        </div>
        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'package_type', 'label' => __('Choose prefered Price Plan (you can change it later if needed).', 'level7platform'))) ?>
            <?php echo L7P_Form::select(array('name' => 'package_type', 'choices' => $package_types)) ?>
        </div>
        <div class="form-row">
            <?php echo L7P_Form::select(array('name' => 'package_route_id', 'choices' => $routes, 'style' => 'display: none;')) ?>

            <label for="tc">
                <input id="tc" type="checkbox" value="1" name="tc">
                <?php
                echo strtr(__('I have read and agree to the [a]Terms and Conditions[/a]', 'level7platform'), array(
                    '[a]' => '<a href="' . l7p_url_for('@terms') . '" target="_blank">',
                    '[/a]' => '</a>'
                ));

                ?>
            </label>

            <button id="l7p-register-button"><?php echo __('Create an account', 'level7platform') ?></button>
        </div>
    </form>

    <?php
    $content = ob_get_clean();

    return L7P_Content::parse_content($content);
}

function l7p_block_register_agent_form()
{
    ob_start();

    $countries = l7p_get_countries();

    ?>

    <p id="l7p-global-errors">[ERROR_FLASH_MESSAGE]</p>

    <form id="l7p-register-agent-form" method="post" action="[FORM_REGISTER_ACTION]" class="l7p-register-agent-form">

        [FORM_SECURITY_TOKEN]

        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'firstname', 'label' => __('First Name', 'level7platform'))) ?>
            <?php echo L7P_Form::text_input(array('name' => 'firstname', 'placeholder' => __('First Name', 'level7platform'), 'required' => true)) ?>
        </div>
        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'lastname', 'label' => __('Last Name', 'level7platform'))) ?>
            <?php echo L7P_Form::text_input(array('name' => 'lastname', 'placeholder' => __('Last Name', 'level7platform'), 'required' => true)) ?>
        </div>
        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'email', 'label' => __('E-mail', 'level7platform'))) ?>
            <?php echo L7P_Form::text_input(array('name' => 'email', 'placeholder' => __('E-mail', 'level7platform'), 'required' => true)) ?>
        </div>
        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'email2', 'label' => __('Confirm E-mail', 'level7platform'))) ?>
            <?php echo L7P_Form::text_input(array('name' => 'email2', 'placeholder' => __('Confirm E-mail', 'level7platform'), 'required' => true)) ?>
        </div>
        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'password', 'label' => __('Password', 'level7platform'))) ?>
            <?php echo L7P_Form::password_input(array('name' => 'password', 'placeholder' => __('Password', 'level7platform'), 'required' => true)) ?>
        </div>
        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'password2', 'label' => __('Confirm Password', 'level7platform'), 'required' => true)) ?>
            <?php echo L7P_Form::password_input(array('name' => 'password2', 'placeholder' => __('Confirm Password', 'level7platform'), 'required' => true)) ?>
        </div>
        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'address', 'label' => __('Address', 'level7platform'))) ?>
            <?php echo L7P_Form::text_input(array('name' => 'address', 'placeholder' => __('Address', 'level7platform'), 'required' => true)) ?>
        </div>
        <div class="form-row">
    <?php echo L7P_Form::label(array('id' => 'country', 'label' => __('Country', 'level7platform'))) ?>
    <?php echo L7P_Form::select(array('name' => 'country', 'choices' => $countries)) ?>
        </div>
        <div class="form-row">

            <label for="tc">
                <input id="tc" type="checkbox" value="1" name="tc">
                <?php
                echo strtr(__('I have read and agree to the [a]Terms and Conditions[/a]', 'level7platform'), array(
                    '[a]' => '<a href="' . l7p_url_for('@terms') . '" target="_blank">',
                    '[/a]' => '</a>'
                ));

                ?>
            </label>

            <button id="l7p-register-agent-button"><?php echo __('Create an account', 'level7platform') ?></button>
        </div>
    </form>

    <?php
    $content = ob_get_clean();

    return L7P_Content::parse_content($content);
}

function l7p_block_password_recover_form()
{
    ob_start();

    ?>

    <p id="l7p-global-success">[SUCCESS_FLASH_MESSAGE]</p>

    <p id="l7p-global-errors">[ERROR_FLASH_MESSAGE]</p>

    <form id="l7p-password-recover-form" method="post" action="[FORM_PASSWORD_RECOVER_ACTION]" class="l7p-password-recover-form">

        [FORM_SECURITY_TOKEN]

        <div class="form-row">
    <?php echo L7P_Form::label(array('id' => 'email', 'label' => __('E-mail', 'level7platform'))) ?>
    <?php echo L7P_Form::text_input(array('name' => 'email', 'placeholder' => __('E-mail', 'level7platform'), 'required' => true)) ?>

            <button id="l7p-password-recover-button"><?php echo __('Reset password', 'level7platform') ?></button>
        </div>
    </form>

    <?php
    $content = ob_get_clean();

    return L7P_Content::parse_content($content);
}

function l7p_block_new_password_form()
{
    ob_start();

    ?>

    <p id="l7p-global-errors">[ERROR_FLASH_MESSAGE]</p>

    <form id="l7p-new-password-form" method="post" action="[FORM_NEW_PASSWORD_ACTION]" class="l7p-new-password-form">

        [FORM_SECURITY_TOKEN]

        [FORM_RESET_TOKEN]

        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'password1', 'label' => __('New password', 'level7platform'))) ?>
    <?php echo L7P_Form::password_input(array('name' => 'password1', 'placeholder' => __('New password', 'level7platform'), 'required' => true)) ?>
        </div>

        <div class="form-row">
    <?php echo L7P_Form::label(array('id' => 'password2', 'label' => __('Confirm password', 'level7platform'))) ?>
    <?php echo L7P_Form::password_input(array('name' => 'password2', 'placeholder' => __('Confirm password', 'level7platform'), 'required' => true)) ?>
        </div>

        <button id="l7p-new-password-button"><?php echo __('Change password', 'level7platform') ?></button>
    </form>

    <?php
    $content = ob_get_clean();

    return L7P_Content::parse_content($content);
}

function l7p_block_activation_form()
{
    ob_start();

    ?>

    <p id="l7p-global-success">[SUCCESS_FLASH_MESSAGE]</p>

    <p id="l7p-global-errors">[ERROR_FLASH_MESSAGE]</p>

    <form id="l7p-activate-form" method="post" action="[FORM_ACTIVATION_ACTION]" class="l7p-activate-form">

        [FORM_SECURITY_TOKEN]

        [FORM_ACTIVATION_TOKEN]

    <?php echo L7P_Form::hidden_input(array('name' => 'company', 'value' => 'TBC')) ?>
    <?php echo L7P_Form::hidden_input(array('name' => 'address', 'value' => 'TBC')) ?>
    <?php echo L7P_Form::hidden_input(array('name' => 'postcode', 'value' => 'TBC')) ?>
    <?php echo L7P_Form::hidden_input(array('name' => 'city', 'value' => 'TBC')) ?>
                <?php echo L7P_Form::hidden_input(array('name' => 'country', 'value' => l7p_get_geo())) ?>
                <?php echo L7P_Form::hidden_input(array('name' => 'state', 'value' => l7p_get_geo_state())) ?>

        <div class="form-row">

            <label for="tc">
                <input id="tc" type="checkbox" value="1" name="tc">
    <?php
    echo strtr(__('I have read and agree to the [a]Terms and Conditions[/a]', 'level7platform'), array(
        '[a]' => '<a href="' . l7p_url_for('@terms') . '" target="_blank">',
        '[/a]' => '</a>'
    ));

    ?>
            </label>

            <button id="l7p-activate-button"><?php echo __('Activate an account', 'level7platform') ?></button>
        </div>
    </form>

    <?php
    $content = ob_get_clean();

    return L7P_Content::parse_content($content);
}

function l7p_block_subscription_form()
{
    ob_start();

    ?>

    <p id="l7p-global-success">[SUCCESS_FLASH_MESSAGE]</p>

    <p id="l7p-global-errors">[ERROR_FLASH_MESSAGE]</p>

    <form id="l7p-subscription-form" method="post" action="[FORM_SUBSCRIPTION_ACTION]" class="l7p-subscription-form">

        [FORM_SECURITY_TOKEN]

        [FORM_SUBSCRIPTION_TOKEN]

        <div class="form-row">
            <input type="checkbox" name="is_subscribed" value="1" id="is_subscribed" [if is_subscribed]checked="checked"[/if]>
            <label for="is_subscribed"><span></span>Active</label>
            <button type="submit" id="l7p-subscription-button" >Update</button>
        </div>
    </form>

    <?php
    $content = ob_get_clean();

    return L7P_Content::parse_content($content);
}
