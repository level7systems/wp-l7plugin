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
    <form method="post" action="" class="currency-form">
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

    <form id="login-form" class="login-form">
        <p class="errors"></p>

        <fieldset>
            <label for="name">E-mail</label>
            <input type="email" name="username" id="username" placeholder="E-mail address" class="text ui-widget-content ui-corner-all" required>
            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Password" class="text ui-widget-content ui-corner-all" required>
            <label for="remember">
                <input id="remember" type="checkbox" name="remember" />
                Remember Me
            </label>
            <button id="login-button">Login</button>
        </fieldset>

    </form>

    <?php
    $content = ob_get_clean();

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

    <form id="register-form" class="register-form">
        <fieldset>
            <?php echo L7P_Form::label(array('id' => 'firstname', 'label' => 'First Name')) ?>
            <?php echo L7P_Form::text_input(array('name' => 'firstname', 'placeholder' => 'First Name')) ?>

            <?php echo L7P_Form::label(array('id' => 'lastname', 'label' => 'Last Name')) ?>
            <?php echo L7P_Form::text_input(array('name' => 'lastname', 'placeholder' => 'Last Name')) ?>

            <?php echo L7P_Form::label(array('id' => 'regpass', 'label' => 'Password')) ?>
            <?php echo L7P_Form::password_input(array('name' => 'regpass', 'placeholder' => 'Password')) ?>

            <?php echo L7P_Form::label(array('id' => 'regpass2', 'label' => 'Confirm Password')) ?>
            <?php echo L7P_Form::password_input(array('name' => 'regpass2', 'placeholder' => 'Confirm Password')) ?>
            
            <?php echo L7P_Form::label(array('id' => 'email', 'label' => 'E-mail')) ?>
            <?php echo L7P_Form::text_input(array('name' => 'email', 'placeholder' => 'E-mail')) ?>
            
            <?php echo L7P_Form::label(array('id' => 'package_type', 'label' => __('Choose prefered Price Plan (you can change it later if needed).'))) ?>
            <?php echo L7P_Form::select(array('name' => 'package_type', 'choices' => $package_types)) ?>

            <?php echo L7P_Form::select(array('name' => 'package_route_id', 'choices' => $routes, 'style' => 'display: none;')) ?>
            
            <label for="tc">
                <input id="tc" type="checkbox" value="1" name="tc">
                I have read and agree to the
                <a href="/voipdito.dev/en/terms-and-conditions" target="_blank">Terms and Conditions</a>
            </label>

            <button id="register-button">Create an account</button>
        </fieldset>
    </form>

    <?php
    $content = ob_get_clean();

    return $content;
}
