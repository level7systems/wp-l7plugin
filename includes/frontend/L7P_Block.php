<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function l7p_block_currency_form() {
    $currency = l7p_get_session('currency', 'USD');
    
    if (l7p_is_post_request() && array_key_exists('currency', $_POST)) {
        
        // verify allowed currencied
        $currency = $_POST['currency'];
        if (in_array($currency, l7p_get_currencies())) {
            l7p_set_session('currency', $currency);
        } else {
            $currency = l7p_get_session('currency', 'USD');
        }
    }
    
    ob_start();
    ?>
        <form method="post" action="" class="currency-form">
            <select name="currency" id="currency" onchange="this.form.submit()">
                <option value="EUR"<?php if ($currency == 'EUR'): ?>selected="selected"<?php endif; ?>>Euro</option>
                <option value="USD"<?php if ($currency == 'USD'): ?>selected="selected"<?php endif; ?>>US Dollar</option>
                <option value="JPY"<?php if ($currency == 'JPY'): ?>selected="selected"<?php endif; ?>>Japanese Yen</option>
                <option value="GBP"<?php if ($currency == 'GBP'): ?>selected="selected"<?php endif; ?>>British Pound Sterling</option>
                <option value="PLN"<?php if ($currency == 'PLN'): ?>selected="selected"<?php endif; ?>>Polish Zloty</option>
            </select>
        </form>
    <?php 
    
    $content = ob_get_clean();
    
    return $content;
}


