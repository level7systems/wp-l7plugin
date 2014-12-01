<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class L7P_Install
{
    public function __construct()
    {
        register_activation_hook(L7P_PLUGIN_FILE, array($this, 'install'));
    }
    
    public function install()
    {
        
        // enable XmlRpc
        update_option('enable_xmlrpc', '1');
        
        // create pages
        $this->create_pages();
        
        // rewrite endpoints
        $this->rewrite_endpoints();
        
        // admin
        add_filter('plugin_action_links', array('settings'	=>	'<a href="' . admin_url( 'admin.php?page=l7-settings' ) . '" title="' . esc_attr( __( 'View WooCommerce Settings', 'woocommerce' ) ) . '">' . __( 'Settings', 'level7platform' ) . '</a>'));
        
        // other settup
        
        
        // flush rules after install
        flush_rewrite_rules();
    }
    
    private function create_pages()
    {
        global $wpdb;
        
        $pages_contents = $this->get_pages_contents();
        
        // 3 standard pages
        // TODO: add more pages to installer
        
        $pages = array(
//            'plans' => array(
//                'slug'    => _x('pricing', 'Page slug', 'level7platform' ),
//                'title'   => _x('Plans', 'Page title', 'level7platform' ),
//                'content' => $pages_contents['pricing']
 //           ),
            'rates' => array(
                'slug'      => 'rates',
                'title'     => 'Call rates',
                'content'   => $pages_contents['rates'],
                'post_type' => 'page',
            ),
            'virtual_numbers' => array(
                'slug'      => 'telephone-numbers',
                'title'     => 'Virtual numbers',
                'content'   => $pages_contents['virtual_numbers'],
                'post_type' => 'page',
            ),
            'hardware' => array(
                'slug'      => 'hardware',
                'title'     => 'Hardware',
                'content'   => $pages_contents['hardware'],
                'post_type' => 'page',
            ),
            // templates for dynamic pages
            
            'rates_country' => array(
                'slug'      => 'rates-country',
                'title'     => 'Country call rates',
                'content'   => $pages_contents['rates_country'],
                'post_type' => 'level7platform_page',
            ),
            'virtual_numbers_country' => array(
                'slug'      => 'virtual-numbers-country',
                'title'     => 'Country telephone numbers',
                'content'   => $pages_contents['virtual_numbers_country'],
                'post_type' => 'level7platform_page',
            ),
            'virtual_numbers_state' => array(
                'slug'      => 'virtual-numbers-state',
                'title'     => 'State telephone numbers',
                'content'   => $pages_contents['virtual_numbers_state'],
                'post_type' => 'level7platform_page',
            ),
            'hardware_category' => array(
                'slug'      => 'hardware-category',
                'title'     => 'Hardware category',
                'content'   => $pages_contents['hardware_category'],
                'post_type' => 'level7platform_page',
            ),
            'hardware_phone' => array(
                'slug'      => 'hardware-phone',
                'title'     => 'hardware phone details',
                'content'   => $pages_contents['hardware_phone'],
                'post_type' => 'level7platform_page',
            ),
        );

        foreach ($pages as $key => $page_data) {
            
            // set parent if neccesarry
            if (isset($page_data['parent'])) {
                $page_data['parent'] = $pages[$page_data['parent']]['page_id'];
            }
            
            $this->create_page(
                $page_data['title'],
                $page_data['slug'],
                $page_data['content'],
                isset($page_data['parent']) ? $page_data['parent'] : false  
            );
            
            $pages[$key]['page_id'] = $page_id;
        }
    }
    
    private function create_page($page_title, $slug, $page_content, $post_parent = false)
    {
        $page_data = array(
            'post_status'       => 'publish',
            'post_type'         => 'page',
            'post_author'       => 1,
            'post_name'         => $slug,
            'post_title'        => $page_title,
            'post_content'      => $page_content,
            'post_parent'       => $post_parent,
            'comment_status'    => 'closed'
        );
        
        $page_found = null;
        // TODO: fix
        if (strlen( $page_content ) > 0) {
            // Search for an existing page with the specified page content (typically a shortcode)
            $page_found = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%"));
        } else {
            // Search for an existing page with the specified page slug
            $page_found = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_name = %s LIMIT 1;", $slug));
        }
        
        // set parent if neccesarry
        if (isset($page_data['parent'])) {
            $page_data['post_parent'] = $pages[$page_data['parent']]['page_id'];
            unset($page_data['parent']);
        }
        
        return  wp_insert_post($page_data);
    }
    
    private function get_pages_contents()
    {
        return array(
            'pricing' => <<<CONTENT
<article class="main plans-page">
  <div class="main-image pricing-image"></div>
  <header class="page-header dark-bg">
    <div class="inner">
      <h1>`Choose a plan</h1>
      <p class="byline"><span class="text-grey">that</span>
        <span class="text-orange">fits You</span> the most`</p>
        <cms id="block" name="Currency form" />
    </div>
  </header>
  <section class="tr-w plans-wrap">
    <div class="grid-row plans">
      <div class="plan">
        <h2 class="h3">`Pay As You Go`</h2>
        <div class="inner">
          <ul>
            <li><span class="price">%USER_CHARGE%</span><br>`per user monthly`</li>
            <li>`Free setup`</li>
            <li>`24/7 Technical Support`</li>
            <li>`All <a href="/en/features">features</a> included`</li>
            <li>`Free inbound calls`</li>
            <li>`Free inbound numbers: 0845/0560 and <a href="http://www.inum.net">iNum</a>`</li>
          </ul>
          |if:auth|
          <a href="https://ssl7.net/voipstudio.com/u/app" class="button b-dborder">`Buy Plan Now`</a>
          |else|
          <a href="`/en/register#P`" class="button b-dborder">`Free Trial`</a>
          |endif|
        </div>
      </div>
      <div class="plan">
        <h2 class="h3">`Unlimited Domestic`</h2>
        <div class="inner">
          <ul>
            <li><span class="price">%USER_UNLIMITED%</span><br>`per user monthly`</li>
            <li>`Free setup`</li>
            <li>`24/7 Technical Support`</li>
            <li>`All <a href="/en/features">features</a> included`</li>
            <li>`Free inbound calls`</li>
            <li>`Free inbound numbers: 0845/0560 and <a href="http://www.inum.net">iNum</a>`</li>
            <li>`Inclusive <a href="/en/telephone-numbers">geographical number</a> for each user`</li>
            <li>`Free outbound calls to fixed lines in one
              <a class="tooltip" title="Argentina,
                                        Australia, Austria, Belgium, Brazil São Paulo, Canada, Chile, China, Croatia, Czech Republic,
                                        Denmark, FranceGermany, Greece, Guam, Hong Kong S.A.R., China, Hungary, Ireland, Israel,
                                        Italy, Luxembourg, Malaysia, Netherlands, New Zealand, Norway, Peru, Poland, Portugal, Puerto Rico,
                                        Russia, Singapore, South Korea, Spain, Sweden, Switzerland, Taiwan, Turkey, U.S. Virgin Islands,
                                        United Kingdom, United States, Vatican, Venezuela">chosen country`</a></li>
          </ul>
          |if:auth|
          <a href="https://ssl7.net/voipstudio.com/u/app" class="button b-dborder">`Buy Plan Now`</a>
          |else|
          <a href="`/en/register#S`" class="button b-dborder">`Free Trial`</a>
          |endif|
        </div>
      </div>
      <div class="plan">
        <h2 class="h3">`Unlimited International`</h2>
        <div class="inner">
          <ul>
            <li><span class="price">%USER_UNLIMITED_INT%</span><br>`per user monthly`</li>
            <li>`Free setup`</li>
            <li>`24/7 Technical Support`</li>
            <li>`All <a href="/en/features">features</a> included`</li>
            <li>`Free inbound calls`</li>
            <li>`Free inbound numbers: 0845/0560 and <a href="http://www.inum.net">iNum</a>`</li>
            <li>`Inclusive <a href="/en/telephone-numbers">geographical number</a> for each user`</li>
            <li>`Free outbound calls to fixed lines in all <a class="tooltip" title="Argentina,
                                        Australia, Austria, Belgium, Brazil São Paulo, Canada, Chile, China, Croatia, Czech Republic,
                                        Denmark, FranceGermany, Greece, Guam, Hong Kong S.A.R., China, Hungary, Ireland, Israel,
                                        Italy, Luxembourg, Malaysia, Netherlands, New Zealand, Norway, Peru, Poland, Portugal, Puerto Rico,
                                        Russia, Singapore, South Korea, Spain, Sweden, Switzerland, Taiwan, Turkey, U.S. Virgin Islands,
                                        United Kingdom, United States, Vatican, Venezuela">listed countries </a>
              and to mobile phones in <a class="tooltip" title="Canada, China, Hong Kong S.A.R., China, Puerto Rico, United States">these countries`</a></li>
          </ul>
          |if:auth|
          <a href="https://ssl7.net/voipstudio.com/u/app" class="button b-dborder">`Buy Plan Now`</a>
          |else|
          <a href="`/en/register#A`" class="button b-dborder">`Free Trial`</a>
          |endif|
        </div>
      </div>
    </div>
  </section>
  <section class="dark-bg text-center">
    <div class="inner">
      <h3 class="h2">`Start <span class="text-orange">30 days free trial`</span></h3>
      <div class="grid-row icons">
        <div class="col-1-3">
          <div class="icon-how-trial"><span>`How does the 30-days free trial work?</span></div>
          <p>Sign up for a trial account and enjoy 30 days of full access to all of our virtual PBX options and features without any obligations. If you
            decide that you don't want to continue using our virtual PBX service after the trial period you don't have to do anything. Your trial will end
            automatically and no monthly fees will be applied.</p>`
        </div>
        <div class="col-1-3">
          <div class="icon-credit-card"><span>`Do I need a credit card?</span></div>
          <p>A credit card is not required, you can start using your VoIPstudio IP phone System right away. Sign up now and we will top up your account with 100 free minutes or call credits* which you can use to make calls to any landline or mobile number.* If you need more minutes simply charge your account using a credit card.`<br>
            <span class="super-small text-grey">`* - Not available in all countries. You may have to provide a valid telephone number to activate your trial`</span></p>
        </div>
        <div class="col-1-3">
          <div class="icon-cancel-trial"><span>`Can i cancel trial at any time?</span></div>
          <p>If for whatever reason you are not completely satisfied with your VoIPstudio hosted phone system you can cancel your subscription at any time. There
            is no contract or minimum notice period.`</p>
        </div>
      </div>
      <a href="`/en/register`" class="button-b button-b-white">`Take a Free Trial`</a>
    </div>
  </section>
</article>
CONTENT
            ,
            'rates' => <<<CONTENT
       <article class="main">
		<div class="main-image pricing-image"></div>
		<header class="page-header dark-bg tr-d">
			<div class="inner">
				<h1>`Business VoIP - Call Rates</h1>
				<p class="byline"><span class="text-grey">Check</span>
        <span class="text-orange">the costs of calls</span> to landlines and mobiles`</p>
				<cms id="block" name="Currency form" />
			</div>
		</header>
		<section class="tr-w">
			<div class="grid-row pricing-columns">
				<div class="col-1-2">
					<table class="table voip-calls">
						<thead>
							<tr>
								<td colspan="2"><h2 class="h3">`VoIP Calls`</h2></td>
							</tr>
							<tr>
								<td><strong>`Destination`</strong></td>
								<td><strong>`Per minute rate`</strong></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>`VoIPstudio network`</td>
								<td>`Free`</td>
							</tr>
						</tbody>
						<tbody>
							<tr>
								<td>`Other VoIP networks:` <a href="http://www.sipbroker.com/sipbroker/action/providerWhitePages" class="outer" target="_blank">list</a></td>
								<td>`Free`</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="col-1-2">
          |if:term_has_local|
					<table class="table dom-calls">
						<thead>
							<tr>
								<td colspan="3"><h2 class="h3">`Domestic calls` (%COUNTRY_NAME%)</h2></td>
							</tr>
							<tr>
								<td><strong>`Destination`</strong></td>
                              |if:term_unlimited_local|
                                <td><strong>`Unlimited Plan`</strong><br>`(Per minute rate)`</td>
                                <td><strong>`Pay As You Go`</strong><br>`(Per minute rate)`</td>
                              |else|
                                <td><strong>`Per minute rate`</strong></td>
                              |endif|
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>`Calls to Landlines`</td>
                              |if:term_unlimited_local|
                                <td>|if:term_local_fixed_free|Free|else|%TERM_LOCAL_FIXED%|endif|</td>
                                <td>%TERM_LOCAL_FIXED%</td>
                              |else|
                                <td>%TERM_LOCAL_FIXED%</td>
                              |endif|
							</tr>
						</tbody>
						<tbody>
							<tr>
								<td>`Calls to Mobiles`</td>
                              |if:term_unlimited_local|
                                <td>|if:term_local_mobile_free|Free|else|%TERM_LOCAL_MOBILE%|endif|</td>
                                <td>%TERM_LOCAL_MOBILE%</td>
                              |else|
                                <td>%TERM_LOCAL_MOBILE%</td>
                              |endif|
							</tr>
						</tbody>
					</table>
                        
         |endif|
				</div>
			</div>
                        
  <table class="table int-calls">
    <thead>
      <tr>
        <td colspan="4"><h2 class="h3">`International calls`</h2>
          <ul class="alphabet">
                        
            <li><a href="#A">A</a></li><li><a href="#B">B</a></li><li><a href="#C">C</a></li><li><a href="#D">D</a></li><li><a href="#E">E</a></li><li><a href="#F">F</a></li><li><a href="#G">G</a></li><li><a href="#H">H</a></li><li><a href="#I">I</a></li><li><a href="#J">J</a></li><li><a href="#K">K</a></li><li><a href="#L">L</a></li><li><a href="#M">M</a></li><li><a href="#N">N</a></li><li><a href="#O">O</a></li><li><a href="#P">P</a></li><li><a href="#Q">Q</a></li><li><a href="#R">R</a></li><li><a href="#S">S</a></li><li><a href="#T">T</a></li><li><a href="#U">U</a></li><li><a href="#V">V</a></li><li><a href="#W">W</a></li><li><a href="#Y">Y</a></li><li><a href="#Z">Z</a></li>          </ul>
        </td>
      </tr>
      <tr>
        <td><strong>`Destination`</strong><br>`(Click for details`)</td>
        <td><strong>`Unlimited Plan`</strong></td>
        <td><strong>`Landline`</strong><br>`(Per minute rate`)</td>
        <td><strong>`Mobile`</strong><br>`(Per minute rate)`</td>
      </tr>
    </thead>
                        
    |foreach:term_letters|
      <tbody>
        <tr id="%TERM_LETTER%" class="letter"><td colspan="4">%TERM_LETTER%</td></tr>
                        
        |foreach:term_countries|
					<tr>
						<td><a href="%TERM_ROUTE_URL%">%TERM_ROUTE_COUNTRY%</a></td>
						<td>|if:term_is_unlimited|Free|endif|</td>
						<td>%TERM_FIXED%</td>
						<td>%TERM_MOBILE%</td>
					</tr>
        |endforeach|
                        
      </tbody>
    |endforeach|
  </table>
                        
		</section>
		<section class="dark-bg text-center">
			<div class="inner">
				<h3 class="h2">`Extra features <span class="text-orange">never end`</span></h3>
				<div class="grid-row icons">
					<div class="col-1-3">
						<div class="icon-free-calls-light"><span>`Free Internet Call`</span></div>
						<p>We will always try to route your call to any telephone number free of charge over the Internet.
							We use ENUM external link technology to find alternative connection to dialled number.
							However if we need to deliver your call over traditional telephone network following principles apply:<br>
							Calls are billed per second<br>
							Minimum call charge is %TERM_MIN_CHARGE%<br>
							Prices exclude VAT<br>
							<span class="super-small text-grey">20% VAT applies to Customers based in United Kingdom and EU customers without
							a VAT number.`</span></p>
					</div>
					<div class="col-1-3">
						<div class="icon-sms"><span>`SMS - Text Messages</span></div>
						<p>You can send text messages from our website after creating account with us. It cost only %SMS_CHARGE% per message.`</p>
					</div>
					<div class="col-1-3">
						<div class="icon-web-calls"><span>`Web Calls</span></div>
						<p>If you have access to the internet, you can use any telephone anywhere to make a call and have the call charged
							at very low internet rate. VoIPstudio calls the phone you’re making the call from, then makes another call to the
							person you want to call; then connects both together. So you will be charged for two calls. But because our
							prices are so low, even with two calls, this is still a more economical way of making a call than, say, from
							your mobile. If you are travelling abroad you can avoid roaming charges by using web calls.`</p>
					</div>
				</div>
			</div>
		</section>
	</article>
CONTENT
            ,
            
        );
    }
    
    private function rewrite_endpoints()
    {
        $permalinks = get_option(L7P_Admin::OPTION_PERMALINKS);
        
        $this->query_vars = array(
            // rates action
            'rates'              => $permalinks['rate_page_slug'] ? $permalinks['rate_page_slug'] : 'voip-call-rates',
            // telephone numbers
            'telephone-numbers'  => $permalinks['virtual_numbers_page_sluga'] ? $permalinks['rates_page_slug'] : 'pricing',
            // hardware
            'hardware'           => $permalinks['rate_page_slug'] ? $permalinks['rate_page_slug'] : 'hardware',
        );
        
        foreach ( $this->query_vars as $key => $var ) {
            add_rewrite_endpoint( $var, EP_ROOT | EP_PAGES );
        }
        
    }
}

return new L7P_Install();