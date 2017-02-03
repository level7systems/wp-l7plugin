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
    <form id="l7p-currency-form" method="post" action="" class="l7p l7p-currency-form">
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

    <p id="l7p-global-success" class="alert alert-success">[SUCCESS_FLASH_MESSAGE]</p>

    <p id="l7p-global-errors">[ERROR_FLASH_MESSAGE]</p>

    <form id="l7p-login-form" method="post" action="[FORM_LOGIN_ACTION]" class="l7p l7p-login-form">

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

    </form>

    <?php
    $content = ob_get_clean();

    return L7P_Content::parse_content($content);
}

function l7p_block_rest_login_form()
{
    ob_start();

    ?>

    <p id="l7p-global-success" class="alert alert-success">[SUCCESS_FLASH_MESSAGE]</p>

    <p id="l7p-global-errors" class="alert alert-danger">[ERROR_FLASH_MESSAGE]</p>

    <form id="l7p-rest-login-form" method="post" action="[REST_API_LOGIN_URL]" class="l7p l7p-rest-login-form" data-app-key="[APP_KEY]">

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

    $countries = array();
    foreach ($term_routes as $id => $country_code) {
        $countries[$country_code] = l7p_country_name($country_code);
    }

    ?>

    <p id="l7p-global-success" class="alert alert-success">[SUCCESS_FLASH_MESSAGE]</p>

    <p id="l7p-global-errors" class="alert alert-danger">[ERROR_FLASH_MESSAGE]</p>

    <form id="l7p-register-form" method="post" action="[FORM_REGISTER_ACTION]" class="l7p l7p-register-form">

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
            <?php echo L7P_Form::select(array('name' => 'package_country', 'choices' => $countries, 'style' => 'display: none;')) ?>

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

    <p id="l7p-global-errors" class="alert alert-danger">[ERROR_FLASH_MESSAGE]</p>

    <form id="l7p-register-agent-form" method="post" action="[FORM_REGISTER_ACTION]" class="l7p l7p-register-agent-form">

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

    <p id="l7p-global-success" class="alert alert-success">[SUCCESS_FLASH_MESSAGE]</p>

    <p id="l7p-global-errors" class="alert alert-danger">[ERROR_FLASH_MESSAGE]</p>

    <form id="l7p-password-recover-form" method="post" action="[FORM_PASSWORD_RECOVER_ACTION]" class="l7p l7p-password-recover-form">

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

    <p id="l7p-global-errors" class="alert alert-danger">[ERROR_FLASH_MESSAGE]</p>

    <form id="l7p-new-password-form" method="post" action="[FORM_NEW_PASSWORD_ACTION]" class="l7p l7p-new-password-form">

        [FORM_SECURITY_TOKEN]

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

function l7p_block_rest_new_password_form()
{
    ob_start();

    ?>

    <form id="l7p-new-password-form" method="post" action="[FORM_NEW_PASSWORD_ACTION]" class="l7p l7p-new-password-form" data-rest-api-login-url="[REST_API_LOGIN_URL]" data-app-key="[APP_KEY]">

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

function l7p_block_subscription_form()
{
    ob_start();

    ?>

    <p id="l7p-global-success" class="alert alert-success">[SUCCESS_FLASH_MESSAGE]</p>

    <p id="l7p-global-errors" class="alert alert-danger">[ERROR_FLASH_MESSAGE]</p>

    <form id="l7p-subscription-form" method="post" action="[FORM_SUBSCRIPTION_ACTION]" class="l7p l7p-subscription-form">

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

function l7p_block_javascript_package_type_select()
{
    $register_settings = l7p_get_settings('register');
    $package_types = isset($register_settings['package_types']) ? $register_settings['package_types'] : array();
    
    $options = array();
    foreach ($package_types as $currency => $types) {
        $currency_options = array(); 
        foreach ($types as $type => $label) {
            $currency_options[] = sprintf("%s: '%s'", $type, $label);
        }
        $options[] = sprintf("%s: {%s}", $currency, implode(", ", $currency_options));
    }
    
    ob_start();

    ?>
    <?php echo L7P_Form::select(array('name' => 'package_type', 'choices' => array(), 'value' => 'P')) ?>
    
    <script type="text/javascript">var package_type_options = {<?php echo implode(", ", $options) ?>};</script>
    
    <?php
    $content = ob_get_clean();

    return L7P_Content::parse_content($content);
}

function l7p_block_manual_search_form()
{
    ob_start();

    ?>
    <form action="[FORM_SEARCH_ACTION]" class="l7p l7p-manual-search-form" method="GET">
        <div class="row">
        <div class="form-row col-xs-8">
            <?php echo L7P_Form::text_input(array('name' => 'search', 'placeholder' => 'search...', 'required' => true)) ?>
        </div>
        <div class="col-xs-3">
            <button class="l7p-search-button "><span class="fa fa-search fa-6"></span></button>
        </div>
        </div>
    </form>
    <script type="text/javascript" >
        var availableTags = [MANUAL_KEYWORDS];
    </script>
    <?php
    $content = ob_get_clean();

    return L7P_Content::parse_content($content);
}

function l7p_block_manual_search_results()
{
    $search = sanitize_text_field(get_query_var('search'));
    if(strlen($search) > 3){
        $chapters = l7p_get_chapters();
        $results = array();
        foreach($chapters as $key => $chapter){
            unset($chapter['index']);
            foreach($chapter as $subchapter){
                $search_in = str_replace(array("\r\n", "\n", "\r"), ' ', strip_tags($subchapter['content']));
                $position = strpos(strtolower($search_in), strtolower($search));
                if($position !== false){
                    $start = 0;
                    if($position > 150){
                        $start = $position - 150;
                    }
                    $result = array();
                    $result['manual'] = $key;
                    $result['chapter'] = $subchapter['chapter'];
                    $result['name'] = $subchapter['name'];
                    $searchCaseInsensitive = substr($search_in, $position, strlen($search));
                    $result['content'] = str_replace($searchCaseInsensitive,'<strong>' . $searchCaseInsensitive .'</strong>',substr($search_in, $start, 300)); 
                    $result['url'] = '/manual/' . $key . '_' . str_replace(' ', '-', $result['chapter']); // @todo pobieranie ładne linku
                    $results[] = $result; 
                }
            }
        }
    }
    ob_start();
    
    ?>
    
    <?php if($results): ?>
    <div class="resultsfor">Search results for: <strong><?php echo $search; ?></strong></div>
    <div  class="results">
        <?php foreach($results as $result): ?>
        <div class="result">
            <h3><a href="<?php echo $result['url']; ?>" class="title" title=""><?php echo $result['name'] . ' - ' . $result['chapter']; ?></a></h3>
            [...] <?php echo $result['content']; ?> [...]
            <br />
            <a href="<?php echo $result['url']; ?>" class="btn read-more" title="">Read more</a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else : ?>
        <h3>No results found!</h3>
    <?php endif; ?>

    <?php
    $content = ob_get_clean();
    return L7P_Content::parse_content($content);
}
// deprecated
function l7p_block_javascript_package_type_select_class()
{
    return l7p_block_javascript_package_type_select();
}

function l7p_block_package_country_select()
{
    $packageCountries = array('' => "Please select one...");
    $packageCountries += l7p_get_package_country_options();
    
    ob_start();

    ?>
    
    <?php echo L7P_Form::select(array('name' => 'package_country', 'id' => false, 'choices' => $packageCountries, 'class' => 'combo', 'style' => 'display: none;')) ?>
     
    <?php
    $content = ob_get_clean();

    return L7P_Content::parse_content($content);
}

