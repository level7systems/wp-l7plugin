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
        
        add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 10 );
    }

    public function menu()
    {
        add_menu_page("Level7 Platform", "Level7 Platform", 'manage_options', 'l7-settings', null, null, 57);
        add_submenu_page('l7-settings', 'Settings', 'Settings', 'manage_options', 'l7-settings', array($this, 'settings_page'));
    }

    public function head()
    {
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
        remove_meta_box( 'slugdiv', 'l7p_page' , 'normal' );
    }

    public function settings_page()
    {
        register_setting('level7platform_settings', 'l7p_permalinks');

        // Save settings if data has been posted
        if (!empty($_POST)) {
            $this->save();
        }

        // Add a section to the permalinks page
        add_settings_section('level7platform_permalinks_section', __('Permalinks', 'level7platform'), array($this, 'permalinks_section_callback'), 'level7platform');

        $cultures = l7p_get_cultures();
        $permalinks = l7p_get_permalinks();
        $section_name = 'l7p_permalinks';

        // TODO: add support for defaults values from placeholders
        // rate page
        l7p_add_settings_field(
            'rates', // id
            __('Country rates page', 'level7platform'), // setting label
            'text_input', // display callback
            'level7platform', // settings page
            'level7platform_permalinks_section', // section
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
            'text_input', // display callback
            'level7platform', // settings page
            'level7platform_permalinks_section', // section
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

        // TODO: check if has_shop option is enabled
        // hardware page
        l7p_add_settings_field(
            'hardware', // id
            __('Hardware page', 'level7platform'), // setting label
            'text_input', // display callback
            'level7platform', // settings page
            'level7platform_permalinks_section', // section
            array(
            'name' => 'hardware',
            'section' => $section_name,
            'value' => $permalinks,
            'placeholder' => $this->get_field_default_value('hardware'),
            'pre' => '/',
            'post' => '/:category-or-phone',
            )
        );

        // manual page
        l7p_add_settings_field(
            'manual', // id
            __('Manual page', 'level7platform'), // setting label
            'text_input', // display callback
            'level7platform', // settings page
            'level7platform_permalinks_section', // section
            array(
            'name' => 'manual',
            'section' => $section_name,
            'value' => $permalinks,
            'placeholder' => $this->get_field_default_value('manual'),
            'pre' => '/',
            'post' => '/:chapter',
            )
        );

        ?>

        <div class="wrap">

            <?php echo $this->show_messages() ?>

            <h2>Settings</h2>

            <form action='' method='POST' >

                <?php settings_fields('level7platform_settings'); ?>
                <?php do_settings_sections('level7platform'); ?>
                <?php submit_button("Save", 'primary'); ?>

            </form>
        </div>

        <?php
    }

    public function permalinks_section_callback()
    {
        echo wpautop(__('These settings control the permalinks used for pages. These settings only apply when <strong>not using "default" permalinks below</strong>.', 'level7platform'));
    }

    private function save()
    {
        if (empty($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'level7platform_settings-options')) {
            die(__('Action failed. Please refresh the page and retry.', 'level7platform'));
        }

        $permalinks_data = $_POST['l7p_permalinks'];

        // validation is not neccessary
        foreach ($permalinks_data as $key => $val) {

            if (empty($val)) {
                $val = $this->get_field_default_value($key);
            }
            $permalinks_data[$key] = sanitize_title($val);
        }

        // save data
        l7p_update_option('permalinks', $permalinks_data);

        // rewrite rules
        L7P()->query->add_rewrite_rules();

        // flush rules after install
        flush_rewrite_rules();

        $this->add_message('notice', __('Settings saved.', 'level7platform'));
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
}

return new L7P_Admin();
