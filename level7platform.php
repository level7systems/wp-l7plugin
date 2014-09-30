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
    
    private function __construct() {
        // Auto-load classes on demand
        if (function_exists("__autoload") ) {
            spl_autoload_register("__autoload");
        }
    
        spl_autoload_register(array($this, 'autoload'));
    
        // Include required files
        $this->includes();
        
        // WP hooks
        add_action( 'init', array( $this, 'init' ), 0 );
        add_action( 'init', array( 'Level7_Shortcodes', 'init' ) );
    
        // Loaded action
        do_action( 'level7platform_loaded' );
    }
    
public function autoload( $class ) {
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

		if ( ! defined( 'L7P_ROUNDING_PRECISION' ) ) {
			define( 'L7P_ROUNDING_PRECISION', 4 );
		}
		if ( ! defined( 'L7P_TAX_ROUNDING_MODE' ) ) {
			// 1 = PHP_ROUND_HALF_UP, 2 = PHP_ROUND_HALF_DOWN
			define( 'L7P_TAX_ROUNDING_MODE', get_option( 'woocommerce_prices_include_tax' ) === 'yes' ? 2 : 1 );
		}
		if ( ! defined( 'L7P_DELIMITER' ) ) {
			define( 'L7P_DELIMITER', '|' );
		}
		if ( ! defined( 'L7P_LOG_DIR' ) ) {
			define( 'L7P_LOG_DIR', ABSPATH . 'wc-logs/' );
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	private function includes()
	{
		
		if ( is_admin() ) {
			include_once( 'includes/admin/L7P_Admin.php' );
		} else {
		    include_once( 'includes/L7P_Frontend.php' );
		}

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

function Level7() {
    return Level7Platform::instance();
}

// Global for backwards compatibility.
$GLOBALS['Level7platform'] = Level7();


