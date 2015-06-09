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

    return $content;
}

function l7p_block_login_form()
{
    ob_start();

    ?>

    <p id="l7p-login-form-global-errors">[FLASH_MESSAGE]</p>
    
    <form id="l7p-login-form" method="post" class="l7p-login-form">

        [FORM_SECURITY_TOKEN]
        
        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'username', 'label' => 'E-mail')) ?>
            <?php echo L7P_Form::text_input(array('name' => 'username', 'placeholder' => 'E-mail address')) ?>
        </div>
        
        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'password', 'label' => 'Password')) ?>
            <?php echo L7P_Form::password_input(array('name' => 'password', 'placeholder' => 'Password')) ?>
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

    // clear ext ini?
//    l7p_update_session('extini', '');

    return $content;
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
    
    <p id="l7p-login-form-global-errors">[FLASH_MESSAGE]</p>

    <form id="l7p-register-form" method="post" class="l7p-register-form">

        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'firstname', 'label' => __('First Name', 'level7platform'))) ?>
            <?php echo L7P_Form::text_input(array('name' => 'firstname', 'placeholder' => __('First Name', 'level7platform'))) ?>
        </div>
        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'lastname', 'label' => __('Last Name', 'level7platform'))) ?>
            <?php echo L7P_Form::text_input(array('name' => 'lastname', 'placeholder' => __('Last Name', 'level7platform'))) ?>
        </div>
        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'password', 'label' => __('Password', 'level7platform'))) ?>
            <?php echo L7P_Form::password_input(array('name' => 'password', 'placeholder' => __('Password', 'level7platform'))) ?>
        </div>
        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'password2', 'label' => __('Confirm Password', 'level7platform'))) ?>
            <?php echo L7P_Form::password_input(array('name' => 'password2', 'placeholder' => __('Confirm Password', 'level7platform'))) ?>
        </div>
        <div class="form-row">
            <?php echo L7P_Form::label(array('id' => 'email', 'label' => __('E-mail', 'level7platform'))) ?>
            <?php echo L7P_Form::text_input(array('name' => 'email', 'placeholder' => __('E-mail', 'level7platform'))) ?>
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

    return $content;
}
