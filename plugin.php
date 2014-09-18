<?php
/*
Plugin Name: Level 7 Systems integration plugin
Plugin URI: http://level7systems.co.uk
Description: This is a Level 7 Systems integration plugin which will help you to manage your white label communication service.
Author: Level 7 Systems Ltd.
Author URI: http://level7systems.co.uk
Version: 0.1
*/

if(defined('LEVEL7_VERSION')) return;

define('LEVEL7_VERSION', '0.1');
define('LEVEL7_PATH', dirname(__FILE__));

//require LEVEL7_PATH . '/inc/core.php';
//$level7 = new Level7Plugin();

function level7_init_scripts() {

    if (is_admin()) {
        // only backend
        
    } else {
        /// non-admin section
        wp_enqueue_script('level7-login', plugins_url('/js/level7-login.js', __FILE__), array('jquery'));
    }
}


function level7_menu() {
    
    add_menu_page("Level7", "Level7", 'manage_options', 'level7-menu', 'level7_options');
}

function level7_options() {
    echo '<div class="wrap">';
    echo '<p><strong>TO-DO: Add some config options...</p>';
    echo '</div>';
}

add_action('wp_enqueue_scripts', 'level7_init_scripts');

// admin menu
add_action('admin_menu', 'level7_menu');