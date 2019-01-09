<?php
/*
  Plugin Name: Level 7 Systems integration plugin
  Plugin URI: http://level7systems.co.uk
  Description: This is a Level 7 Systems integration plugin which will help you to manage your white label communication service.
  Author: Level 7 Systems Ltd.
  Author URI: http://level7systems.co.uk
  Version: 1.0.6
 */

if (defined('LEVEL7PLATFORM_VERSION'))
    return;

define('LEVEL7_PATH', dirname(__FILE__));

class Level7Platform
{

    const VERSION = '1.0.6';

    /**
     * @var L7P_Query $query
     */
    public $query = null;

    /**
     * @var WooCommerce The single instance of the class
     * @since 2.1
     */
    protected static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct()
    {
        // Auto-load classes on demand
        if (function_exists("__autoload")) {
            spl_autoload_register("__autoload");
        }

        spl_autoload_register(array($this, 'autoload'));

        // constants
        $this->define_constants();

        // session initialization
        $this->init_session();

        // include required files
        $this->includes();

        // integration with other plugins
        add_action('plugins_loaded', array('L7P_PluginIntegration', 'setup'), 10);

        add_action('plugins_loaded', array($this, 'init'), 0);

        // Loaded action
        do_action('level7platform_loaded');
    }

    public function autoload($class)
    {
        $path = null;
        $file = $class . '.php';

        if (strpos($class, 'L7P_Admin') === 0) {
            $path = $this->plugin_path() . '/includes/admin/';
        }

        if ($path && is_readable($path . $file)) {
            include_once( $path . $file );
            return;
        }

        // Fallback
        if (strpos($class, 'L7P_') === 0) {
            $path = $this->plugin_path() . '/includes/';
        }

        if ($path && is_readable($path . $file)) {
            include_once( $path . $file );
            return;
        }
    }

    public function init()
    {
        // Set up localisation
        $this->load_plugin_textdomain();
    }

    private function define_constants()
    {
        define('L7P_PLUGIN_FILE', __FILE__);
        define('L7P_PLUGIN_BASENAME', plugin_basename(__FILE__));
        define('L7P_VERSION', self::VERSION);

        if (!defined('L7P_LOG_DIR')) {
            define('L7P_LOG_DIR', ABSPATH . 'l7p-logs/');
        }
    }

    /**
     * Include required core files used in admin and on the frontend.
     */
    private function includes()
    {
        // common functions
        include_once('includes/L7P_Exceptions.php');
        include_once('includes/L7P_Functions.php');
        include_once('includes/L7P_Form.php');
        include_once('includes/L7P_Block.php');

        // installer
        include_once('includes/L7P_Install.php');
        // integrations
        include_once('includes/L7P_PluginIntegration.php');

        if (is_admin()) {
            // ajax hooks needs to be loaded here
            include_once('includes/L7P_Ajax.php');
            include_once('includes/admin/L7P_Functions.php');
            include_once('includes/L7P_Admin.php');
        } else { // Frontend
            include_once('includes/frontend/L7P_Content.php');
            include_once('includes/frontend/L7P_Inline.php');
            include_once('includes/L7P_Frontend.php');
        }

        // Query class
        $this->query = include( 'includes/L7P_Query.php' );

        // Post Types
        include_once('includes/L7P_Post_Types.php');
        // XmlRpc Api
        include_once('includes/L7P_XmlRpc_Api.php');
    }

    public function load_plugin_textdomain()
    {
        load_plugin_textdomain('level7platform', false, dirname(plugin_basename(__FILE__)) . '/i18n');
    }

    public function plugin_url()
    {
        return untrailingslashit(plugins_url('/', __FILE__));
    }

    public function plugin_path()
    {
        return untrailingslashit(plugin_dir_path(__FILE__));
    }

    private function init_session()
    {
        // do not start session for level7 theme
        if (defined('L7_CONFIG_PATH')) {
            return;
        }

        if (!session_id()) {
            session_start();
        }
    }
}

function L7P()
{
    return Level7Platform::instance();
}
// Global for backwards compatibility.
$GLOBALS['level7platform'] = L7P();

