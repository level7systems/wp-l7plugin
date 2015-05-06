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

    ?>

    <form id="register-form" class="register-form">
        <fieldset>
            <label for="firstname">First Name</label>
            <input id="firstname" type="text" required="" placeholder="First Name" name="firstname" />

            <label for="lastname">Last Name</label>
            <input id="lastname" type="text" required="" placeholder="Last Name" name="lastname" />

            <label for="regpass">Password</label>
            <input id="regpass" type="password" required="" placeholder="Password" name="regpass" />

            <label for="regpass2">Confirm Password</label>
            <input id="regpass2" type="password" required="" placeholder="Password" name="regpass2" />

            <label for="email">E-mail</label>
            <input id="email" type="text" required="" placeholder="E-mail" name="email" />

            <label for="package_type">Package</label>
            <select id="package_type" name="package_type">
                <option selected="selected" value="P">Pay As You Go</option>
                <option value="S">Unlimited Domestic</option>
                <option value="A">Unlimited International</option>
            </select>

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
