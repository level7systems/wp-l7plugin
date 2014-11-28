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
    public function __construct()
    {
        add_action('admin_menu', array( $this, 'menu' ), 5);
        
        add_action('admin_head', array( $this, 'menu_head' ));
    }
    
    public function menu()
    {
        add_menu_page("Level7 Platform", "Level7 Platform", 'manage_options', 'l7-settings', null, null, 60);

        add_submenu_page( 'l7-settings', 'Settings', 'Settings', 'manage_options', 'l7-settings', array($this, 'settings_page'));
    }
    
    public function settings_page() {
        
        register_setting('level7platform_settings', 'rate_page_slug');
        
        // Save settings if data has been posted
        if (!empty($_POST)) {
            $this->save();
        }
        
        // Add a section to the permalinks page
        add_settings_section('level7platform_permalinks_section', __( 'Permalinks', 'level7platform' ),
          array($this, 'permalinks_section_callback'), 'level7platform');
        
        $permalinks = get_option('rate_page_slug');
        
        var_dump($permalinks);
        
        // rates page
        add_settings_field(
            'rates_page_slug',  // id
            __( 'Counries rates page', 'level7platform' ), 	      // setting label
            'text_input',                                 // display callback
            'level7platform',                 		      // settings page
            'level7platform_permalinks_section',          // section
            array(
              'name'        => 'rates_page_slug',
              'value'       => $permalinks['rates_page_slug'],
              'placeholder' => 'rates',
              'pre'         => '/',
            )
        );
        
        // rate page
        add_settings_field(
            'rate_page_slug',  // id
            __( 'Single country rate page', 'level7platform' ), 	      // setting label
            'text_input',                                 // display callback
            'level7platform',                 		      // settings page
            'level7platform_permalinks_section',          // section
            array(
                'name'        => 'rate_page_slug',
                'value'       => $permalinks['rate_page_slug'],
                'placeholder' => 'voip-call-rates',
                'pre'         => '/'
            )
        );
        
        // virtual numbers page
        add_settings_field(
            'virtual_numbers_page_slug',  // id
            __( 'Virtual numbers page', 'level7platform' ), 	      // setting label
            'text_input',                                 // display callback
            'level7platform',                 		      // settings page
            'level7platform_permalinks_section',          // section
            array(
                'name'        => 'rates_page_slug',
                'value'       => $permalinks['virtual_numbers_page_slug'],
                'placeholder' => 'telephone-numbers',
                'pre'         => '/',
                'help'        => 'Virtual Telephone Numbers'
            )
        );
        
        // hardware page
        add_settings_field(
            'hardware_page_slug',  // id
            __( 'Hardware page', 'level7platform' ), 	      // setting label
            'text_input',                                 // display callback
            'level7platform',                 		      // settings page
            'level7platform_permalinks_section',          // section
            array(
                'name'        => 'hardware_page_slug',
                'value'       => $permalinks['hardware_page_slug'],
                'placeholder' => 'hardware',
                'pre'         => '/',
            )
        );
        
        ?>
        
        <div class="wrap">
            <p><strong>Settings</p>
            
            <form action='' method='POST' >
            
                <?php settings_fields('level7platform_settings'); ?>
                <?php do_settings_sections('level7platform'); ?>
                <?php submit_button("Save options", 'primary'); ?>
                
            </form>
        </div>
        
        <?php 
    }
    
    	
	public function permalinks_section_callback()
    {
        echo wpautop( __( 'These settings control the permalinks used for pages. These settings only apply when <strong>not using "default" permalinks above</strong>.', 'level7platform' ) );
    }
    
    private function save()
    {
        if (empty($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'level7platform_settings-options')) {
            die( __( 'Action failed. Please refresh the page and retry.', 'level7platform' ) );
        }
        
        // TODO: validate
        
        var_dump($_POST);
        
        // save data
        set_option($permalinks, $permalinks_data);
        
        // TODO: set proper message
        
    }
    	
}

return new L7P_Admin();
