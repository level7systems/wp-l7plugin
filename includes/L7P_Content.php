<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class L7P_Content
{
    public function __construct()
    {
        add_filter('the_content', array($this, 'parse_content'), 20);
    }
    
    public function parse_content($content)
    {
        // TODO: 
        if (is_single()) {
            // parse for extra syntax
            $content = sprintf(
                "PARSED: %s",
                $content
            );
        }
        
        return $content;
    }
}

return new L7P_Content();