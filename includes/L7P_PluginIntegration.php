<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Description of L7P_PluginIntegration
 *
 * @author kamil
 */
class L7P_PluginIntegration
{
    public static function setup()
    {
        
        // if Google XML Sitemap plugin exists
        if (defined('SM_SUPPORTFEED_URL')) {
            include_once('plugins/L7P_Google_Xml_Sitemap.php');
        }
    }
}
