<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class L7P_Shortcodes
{
    public static function init()
    {
        // shortcodes
        $shortcodes = array(
            
        );

        foreach ($shortcodes as $shortcode => $options) {
            add_shortcode($shortcode, $options['callback']);
        }
        
    }
    
}