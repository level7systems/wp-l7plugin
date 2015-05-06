<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class L7P_Frontend
{

    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'styles'));
        add_action('wp_enqueue_scripts', array($this, 'scripts'));
    }

    /**
     * Enqueue styles
     */
    public function styles()
    {
        wp_enqueue_style(
            'level7-forms', plugins_url('/assets/css/frontend/forms.css', L7P_PLUGIN_FILE)
        );

        wp_enqueue_style(
            'level7-jquery-ui-css', plugins_url('/assets/css/jquery-ui.css', L7P_PLUGIN_FILE)
        );
    }

    /**
     * Enqueue scripts
     */
    public function scripts()
    {
        wp_enqueue_script(
            'level7-forms', plugins_url('/assets/js/frontend/forms.js', L7P_PLUGIN_FILE), array('jquery', 'jquery-ui-dialog')
        );
    }

}

return new L7P_Frontend();
