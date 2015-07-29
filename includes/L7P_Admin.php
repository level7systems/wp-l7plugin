<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class L7P_Admin
{

    private $messages = array(
        'notice' => array(),
        'error' => array()
    );

    public function __construct()
    {
        add_action('admin_menu', array($this, 'menu'), 5);
        add_action('admin_head', array($this, 'head'), 5);

        add_filter('post_row_actions', array($this, 'post_row_actions'), 10, 2);
        add_filter('bulk_actions-edit-l7p_page', array($this, 'bulk_actions'));

        add_action('add_meta_boxes', array($this, 'remove_meta_boxes'), 10);
    }

    public function menu()
    {
        add_menu_page("Level7 Platform", "Level7 Platform", 'manage_options', 'l7-settings', null, null, 57);
        add_submenu_page('l7-settings', 'Settings', 'Settings', 'manage_options', 'l7-settings', array($this, 'settings_page'));
    }

    public function head()
    {
        if (get_post_type() != 'l7p_page') {
            return;
        }

        // hook for not displaying some UI elements
        $style = '<style type="text/css">'
            // hid add new button
            . 'a.add-new-h2 {display: none;}'
            // hide slug metabox
            . 'div#edit-slug-box {display: none;}'
            // hide publishing options
            . 'div#minor-publishing {display: none;}'
            // hide translation options
            . 'div#icl_div_config {display: none;}'
            . '</style>';

        echo $style;
    }

    public function post_row_actions($actions, $post)
    {
        if ($post->post_type != 'l7p_page') {
            return $actions;
        }

        // remove quick edit action
        unset($actions['inline hide-if-no-js']);
        // remove view action
        unset($actions['view']);

        return $actions;
    }

    public function bulk_actions($actions)
    {
        // remove all bulk actions
        return array();
    }

    public function remove_meta_boxes()
    {
        remove_meta_box('slugdiv', 'l7p_page', 'normal');
    }

    public function settings_page()
    {
        register_setting('l7p_settings', 'l7p_config');
        register_setting('l7p_settings', 'l7p_permalinks');

        // Save settings if data has been posted
        if (!empty($_POST)) {
            $this->save();
        }

        // global config
        add_settings_section('l7p_config_section', '', array($this, 'config_section_callback'), 'l7p_general');
        // permalinks section
        add_settings_section('l7p_permalinks_section', __('Permalinks', 'level7platform'), array($this, 'permalinks_section_callback'), 'l7p_general');
        // advanced section
        add_settings_section('l7p_advanced_section', '', array($this, 'advanced_section_callback'), 'l7p_advanced');
        // mappings section
        add_settings_section('l7p_mappings_section', '', array($this, 'mappings_section_callback'), 'l7p_mappings');

        $cultures = l7p_get_cultures();
        $config = l7p_get_config();
        $permalinks = l7p_get_permalinks();
        $section_name = 'l7p_permalinks';

        // WP API key
        add_settings_field(
            'api_key', // id
            __('API key', 'level7platform'), // setting label
            array('L7P_Form', 'text_input'), // display callback
            'l7p_general', // settings page
            'l7p_config_section', // section
            array(
            'name' => 'api_key',
            'section' => 'l7p_config',
            'value' => isset($config['api_key']) ? $config['api_key'] : '',
            'help' => __('This need to be filled with WP API Key from Level7 App to enable communication.', 'level7platform'),
            'placeholder' => 'WP API KEY',
            'style' => 'width: 650px'
            )
        );

        // rate page
        l7p_add_settings_field(
            'rates', // id
            __('Country rates page', 'level7platform'), // setting label
            array('L7P_Form', 'text_input'), // display callback
            'l7p_general', // settings page
            'l7p_permalinks_section', // section
            array(
            'name' => 'rates',
            'section' => $section_name,
            'value' => $permalinks,
            'placeholder' => $this->get_field_default_value('rates'),
            'pre' => '/',
            'post' => '/:country',
            )
        );

        // virtual numbers page
        l7p_add_settings_field(
            'telephone_numbers', // id
            __('Virtual numbers page', 'level7platform'), // setting label
            array('L7P_Form', 'text_input'), // display callback
            'l7p_general', // settings page
            'l7p_permalinks_section', // section
            array(
            'name' => 'telephone_numbers',
            'section' => $section_name,
            'value' => $permalinks,
            'placeholder' => $this->get_field_default_value('telephone_numbers'),
            'pre' => '/',
            'post' => '/:country-or-state',
            'help' => 'Virtual Telephone Numbers'
            )
        );

        // check if has_shop option is enabled for this web_product
        if (l7p_get_web_product_settings('has_shop')) {

            // hardware page
            l7p_add_settings_field(
                'hardware', // id
                __('Hardware page', 'level7platform'), // setting label
                array('L7P_Form', 'text_input'), // display callback
                'l7p_general', // settings page
                'l7p_permalinks_section', // section
                array(
                'name' => 'hardware',
                'section' => $section_name,
                'value' => $permalinks,
                'placeholder' => $this->get_field_default_value('hardware'),
                'pre' => '/',
                'post' => '/:category-or-phone',
                )
            );
        }

        // manual page
        l7p_add_settings_field(
            'manual', // id
            __('Manual page', 'level7platform'), // setting label
            array('L7P_Form', 'text_input'), // display callback
            'l7p_general', // settings page
            'l7p_permalinks_section', // section
            array(
            'name' => 'manual',
            'section' => $section_name,
            'value' => $permalinks,
            'placeholder' => $this->get_field_default_value('manual'),
            'pre' => '/',
            'post' => '/:chapter',
            )
        );

        // terms and  page
        l7p_add_settings_field(
            'manual', // id
            __('Terms and Conditions', 'level7platform'), // setting label
            array('L7P_Form', 'text_input'), // display callback
            'l7p_general', // settings page
            'l7p_permalinks_section', // section
            array(
            'name' => 'terms',
            'section' => $section_name,
            'value' => $permalinks,
            'placeholder' => $this->get_field_default_value('terms'),
            'pre' => '/',
            'help' => __('Links that will be generated for registration form.', 'level7platform'),
            )
        );

        // advanced settings

        $section_name = 'l7p_advanced';
        $value = l7p_get_settings('l7_tld');
        $has_error = empty($value);
        add_settings_field(
            'l7_tld', // id
            'API domain', // setting label
            array('L7P_Form', 'text_input'), // display callback
            'l7p_advanced', // settings page
            'l7p_advanced_section', // section
            array(
            'name' => 'l7_tld',
            'section' => $section_name,
            'value' => $value,
            'help' => __('Required for communication with external Level7 API.', 'level7platform'),
            'style' => $has_error ? 'border: 2px solid red;' : ''
            )
        );

        $value = l7p_get_web_product_settings('domain');
        $has_error = empty($value);
        add_settings_field(
            'web_product_domain', // id
            'Product domain', // setting label
            array('L7P_Form', 'text_input'), // display callback
            'l7p_advanced', // settings page
            'l7p_advanced_section', // section
            array(
            'name' => 'web_product_domain',
            'section' => $section_name,
            'value' => $value,
            'help' => __('Required for communication with external Level7 API.', 'level7platform'),
            'style' => $has_error ? 'border: 2px solid red;' : ''
            )
        );

        // page mappings
        $pages = array(
            'pricing_page_id',
            'rates_page_id',
            'telephone_numbers_page_id',
            'hardware_page_id',
            'support_page_id',
            'activation_page_id',
            'login_page_id',
            'one_time_login_page_id',
            'recover_page_id',
            'activation_page_id',
            'subscription_page_id',
            'register_page_id',
            'affiliate_page_id'
        );
        $section_name = 'l7p_mappings';
        foreach ($pages as $pagename) {
            // pages
            $page_id = l7p_get_option($pagename);
            $page = get_post($page_id);
            $has_error = false;
            if (is_null($page) || $page->post_status != 'publish') {
                $has_error = true;
            }

            add_settings_field(
                $pagename, // id
                __(ucfirst(strtr(rtrim($pagename, '_id'), array('_' => ' '))), 'level7platform'), // setting label
                array('L7P_Form', 'text_input'), // display callback
                'l7p_mappings', // settings page
                'l7p_mappings_section', // section
                array(
                'name' => $pagename,
                'section' => $section_name,
                'value' => $page->ID,
                'help' => !is_null($page) ? '/en/' . $page->post_name : '',
                'style' => $has_error ? 'border: 2px solid red;' : ''
                )
            );
        }

        ?>

        <div>

            <?php echo $this->show_messages() ?>

            <?php if (isset($_GET['tab'])): ?> 
                <?php echo $this->settings_tabs($_GET['tab']) ?> 
            <?php else: ?> 
                <?php $this->settings_tabs('general'); ?>
            <?php endif; ?>

            <form action='' method='POST' >

                <?php
                switch ($_GET['tab']):
                    case 'advanced':

                        ?>

                        <?php settings_fields('l7p_settings'); ?>
                        <?php do_settings_sections('l7p_advanced'); ?>
                        <?php break; ?>

                    <?php case 'mappings': ?>
                        <?php settings_fields('l7p_settings'); ?>
                        <?php do_settings_sections('l7p_mappings'); ?>
                        <?php break; ?>

                    <?php default: ?>

                        <?php settings_fields('l7p_settings'); ?>
                        <?php do_settings_sections('l7p_general'); ?>

                <?php endswitch; ?>

                <?php submit_button("Update settings", 'primary'); ?>

            </form>
        </div>

        <?php
    }

    public function config_section_callback()
    {
        echo wpautop(__('General settings used for Level7 integration plugin.', 'level7platform'));
    }

    public function permalinks_section_callback()
    {
        echo wpautop(__('These settings control the permalinks used for pages. These settings only apply when <strong>not using "default" permalinks below</strong>.', 'level7platform'));
    }

    public function advanced_section_callback()
    {
        echo wpautop(__('These settings control the adnacend configuration of Level7 integration plugin. Do not change these options if it is not required.', 'level7platform'));
    }

    public function mappings_section_callback()
    {
        echo wpautop(__('These mappings are required for proper redirection to pages provided by Level7 inegration plugin.', 'level7platform'));
    }

    private function save()
    {
        if (empty($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'l7p_settings-options')) {
            die(__('Action failed. Please refresh the page and retry.', 'level7platform'));
        }

        $errors = array();
        switch ($_GET['tab']) {
            case 'advanced':

                $advanced_data = $_POST['l7p_advanced'];
                if (empty($advanced_data['l7_tld']) || substr_count($advanced_data['l7_tld'], ".") == 0) {
                    $errors[] = 'The API domain you entered did not appear to be valid. Please enter a valid domain.';
                }
                if (empty($advanced_data['web_product_domain']) || substr_count($advanced_data['web_product_domain'], ".") == 0) {
                    $errors[] = 'The Product domain you entered did not appear to be valid. Please enter a valid domain.';
                }

                if (!count($errors)) {
                    echo "save < < " . $advanced_data['l7_tld'];
                    l7p_update_settings('l7_tld', $advanced_data['l7_tld']);
                    l7p_update_web_product_settings('domain', $advanced_data['web_product_domain']);
                }

                break;

            case 'mappings';

                $mappings_data = $_POST['l7p_mappings'];

                foreach ($mappings_data as $pagename => $page_id) {
                    $field = ucfirst(strtr(rtrim($pagename, '_id'), array('_' => ' ')));
                    if (empty($page_id)) {
                        $errors[] = sprintf("The %s you entered did not appear to be valid. Please enter a valid ID", $field);
                    } else if (!is_numeric($page_id)) {
                        $errors[] = sprintf("The %s you entered did not appear to be a valid ID. Please enter a valid ID.", $field);
                    } else {

                        $page = get_post($page_id);
                        if (is_null($page) || $page->post_status != 'publish') {
                            $errors[] = sprintf("The %s you entered did not appear to be a valid page. Please enter a valid page ID.", $field);
                        }
                    }
                }

                if (!count($errors)) {
                    foreach ($mappings_data as $pagename => $page_id) {
                        l7p_update_option($pagename, $page_id);
                    }
                }

                break;

            default:

                $config_data = $_POST['l7p_config'];
                $permalinks_data = $_POST['l7p_permalinks'];

                // save config data
                l7p_update_option('config', $config_data);

                // validation is not neccessary
                foreach ($permalinks_data as $key => $val) {

                    if (empty($val)) {
                        $val = $this->get_field_default_value($key);
                    }
                    $permalinks_data[$key] = sanitize_title($val);
                }

                // save permalinks data
                l7p_update_option('permalinks', $permalinks_data);

                // rewrite rules
                L7P()->query->add_rewrite_rules();

                // flush rules after install
                flush_rewrite_rules();
        }

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->add_message('error', $error);
            }
        } else {
            $this->add_message('notice', __('Settings saved.', 'level7platform'));
        }
    }

    private function add_message($key, $msg)
    {
        if (!array_key_exists($key, $this->messages)) {
            $this->messages[$key] = array();
        }

        $this->messages[$key][] = $msg;
    }

    private function show_messages()
    {

        ?>

        <?php if (count($this->messages['notice'])): ?>
            <div id="setting-error-settings_updated" class="updated settings-error"> 
                <?php foreach ($this->messages['notice'] as $msg): ?>
                    <p><strong><?php echo $msg ?></strong></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (count($this->messages['error'])): ?>
            <div id="setting-error-invalid_siteurl" class="error settings-error"> 
                <?php foreach ($this->messages['error'] as $msg): ?>
                    <p><strong><?php echo $msg ?></strong></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php
    }

    private function get_field_default_value($id)
    {
        $defaults = array(
            'rates' => 'voip-call-rates',
            'telephone_numbers' => 'telephone-numbers',
            'hardware' => 'hardware',
            'manual' => 'manual'
        );

        return isset($defaults[$id]) ? $defaults[$id] : "";
    }

    private function settings_tabs($current = 'general')
    {
        $tabs = array('general' => 'General', 'advanced' => 'Advanced', 'mappings' => 'Page mappings');
        echo '<div id="icon-themes" class="icon32"><br></div>';
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($tabs as $tab => $name) {
            $class = ( $tab == $current ) ? ' nav-tab-active' : '';
            echo "<a class='nav-tab$class' href='?page=l7-settings&tab=$tab'>$name</a>";
        }
        echo '</h2>';
    }
}

return new L7P_Admin();
