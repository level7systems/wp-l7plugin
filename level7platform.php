<?php
/*
Plugin Name: Level 7 Systems integration plugin
Plugin URI: http://level7systems.co.uk
Description: This is a Level 7 Systems integration plugin which will help you to manage your white label communication service.
Author: Level 7 Systems Ltd.
Author URI: http://level7systems.co.uk
Version: 0.1
*/

if(defined('LEVEL7PLATFORM_VERSION')) return;

define('LEVEL7_PATH', dirname(__FILE__));

class Level7Platform
{
    const VERSION = '0.1';
    
    /**
     * @var WooCommerce The single instance of the class
     * @since 2.1
     */
    protected static $_instance = null;
    
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function __construct() {
        // Auto-load classes on demand
        if (function_exists("__autoload") ) {
            spl_autoload_register("__autoload");
        }
    
        spl_autoload_register(array($this, 'autoload'));
    
        // constants
        $this->define_constants();
        
        // include required files
        $this->includes();
        
        // WP hooks
        add_action('init', array($this, 'init'), 0 );
        add_action('init', array('L7P_Shortcodes','init'));
    
        // Loaded action
        do_action( 'level7platform_loaded' );
    }
    
    public function autoload( $class )
    {
		$path  = null;
		$file = $class . '.php';

		if ( strpos( $class, 'L7P_Admin' ) === 0 ) {
			$path = $this->plugin_path() . '/includes/admin/';
		}

		if ( $path && is_readable( $path . $file ) ) {
			include_once( $path . $file );
			return;
		}

		// Fallback
		if ( strpos( $class, 'L7P_' ) === 0 ) {
			$path = $this->plugin_path() . '/includes/';
		}

		if ( $path && is_readable( $path . $file ) ) {
			include_once( $path . $file );
			return;
		}
	}

	private function define_constants()
	{
		define( 'L7P_PLUGIN_FILE', __FILE__ );
		define( 'L7P_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'L7P_VERSION', $this->version );

		if (!defined('L7P_LOG_DIR')) {
			define( 'L7P_LOG_DIR', ABSPATH . 'l7p-logs/' );
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	private function includes()
	{
	    // installer
	    include_once('includes/L7P_Install.php');
	    
		if (is_admin()) {
		    include_once('includes/admin/L7P_Admin_Functions.php');
		    include_once('includes/admin/L7P_Admin.php');
		    include_once('includes/L7P_Form.php');
		} else { // Frontend
		    include_once('includes/L7P_Frontend.php');
		    include_once('includes/L7P_Shortcodes.php');
		    include_once('includes/L7P_Content.php');
		}

		// Post Types
		include_once('includes/L7P_Post_Types.php');
		// XmlRpc Api
		include_once('includes/L7P_XmlRpc_Api.php');
	}
	
	public function plugin_url()
	{
	    return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}
	
	public function plugin_path()
	{
	    return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}
}

function L7P() {
    return Level7Platform::instance();
}

// Global for backwards compatibility.
$GLOBALS['level7platform'] = L7P();

