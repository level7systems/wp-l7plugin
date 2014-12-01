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
    /**
     * Name of the option key that store permalinks settings
     * @var string
     */
    const OPTION_PERMALINKS = 'level7platform_permalinks';
    
    private $messages = array(
    	'notice'   => array(),
        'errors'   => array()
    );
    
    public function __construct()
    {
        
        add_action('admin_menu', array( $this, 'menu' ), 5);
        
        // add_action('admin_head', array( $this, 'menu_head' ));
    }
    
    public function menu()
    {
        add_menu_page("Level7 Platform", "Level7 Platform", 'manage_options', 'l7-settings', null, null, 70);

        add_submenu_page( 'l7-settings', 'Settings', 'Settings', 'manage_options', 'l7-settings', array($this, 'settings_page'));
    }
    
    public function settings_page() {
        
        register_setting('level7platform_settings', self::OPTION_PERMALINKS);
        
        // Save settings if data has been posted
        if (!empty($_POST)) {
            $this->save();
        }
        
        // Add a section to the permalinks page
        add_settings_section('level7platform_permalinks_section', __( 'Permalinks', 'level7platform' ),
          array($this, self::OPTION_PERMALINKS . '_callback'), 'level7platform');
        
        $permalinks = get_option(self::OPTION_PERMALINKS);
        
        // rate page
        add_settings_field(
            'rates_page_slug',  // id
            __( 'Country rates page', 'level7platform' ), // setting label
            'text_input',                                 // display callback
            'level7platform',                 		      // settings page
            'level7platform_permalinks_section',          // section
            array(
                'name'        => 'rates_page_slug',
                'section'     => self::OPTION_PERMALINKS,
                'value'       => $permalinks['rates_page_slug'],
                'placeholder' => 'voip-call-rates',
                'pre'         => '/'
            )
        );
        
        // virtual numbers page
        add_settings_field(
            'virtual_numbers_page_slug',  // id
            __( 'Virtual numbers page', 'level7platform' ), // setting label
            'text_input',                                 // display callback
            'level7platform',                 		      // settings page
            'level7platform_permalinks_section',          // section
            array(
                'name'        => 'virtual_numbers_page_slug',
                'section'     => self::OPTION_PERMALINKS,
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
                'section'     => self::OPTION_PERMALINKS,
                'value'       => $permalinks['hardware_page_slug'],
                'placeholder' => 'hardware',
                'pre'         => '/',
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
        echo wpautop( __( 'These settings control the permalinks used for pages. These settings only apply when <strong>not using "default" permalinks above</strong>.', 'level7platform' ) );
    }
    
    private function save()
    {
        if (empty($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'level7platform_settings-options')) {
            die( __( 'Action failed. Please refresh the page and retry.', 'level7platform' ) );
        }
        
        $permalinks_data = $_POST[self::OPTION_PERMALINKS];
        
        // validation is not neccessary
        foreach ($permalinks_data as $key => $val) {
            $permalinks_data[$key] = sanitize_title($val);
        }
        
        // save data
        update_option(self::OPTION_PERMALINKS, $permalinks_data);
        
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
}

return new L7P_Admin();
