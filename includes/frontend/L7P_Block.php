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
    
    $selected_currency = l7p_get_currency();
    $currencies = l7p_get_currencies();
    
//    print_r(l7p_get_pricelist_letters());
    
    ob_start();
    ?>
        <form method="post" action="" class="currency-form">
            <select name="currency" id="currency" onchange="this.form.submit()">
                <?php foreach ($currencies as $currency_iso): ?>
                    <option value="<?php echo $currency_iso ?>"<?php if ($selected_currency == $currency_iso): ?>selected="selected"<?php endif; ?>><?php echo l7p_currency_name($currency_iso) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    <?php 
    
    $content = ob_get_clean();
    
    return $content;
}


