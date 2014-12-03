<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


function l7p_get_option($option, $default = false)
{
    $options = get_option('level7platform_options', array());

    return array_key_exists($option, $options) ? $options[$option] : $default;
}

function l7p_is_auth()
{
    return is_user_logged_in();
}