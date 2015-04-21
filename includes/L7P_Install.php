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
        add_filter( 'plugin_action_links_' . L7P_PLUGIN_BASENAME, array( $this, 'add_action_links' ) );
        
        register_activation_hook(L7P_PLUGIN_FILE, array($this, 'install'));
        register_deactivation_hook(L7P_PLUGIN_FILE, array($this, 'uninstall'));
    }
    
    // action links
    public function add_action_links($links)
    {
        $action_links = array(
            'settings'	=>	sprintf('<a href="%s" title="%s">%s</a>',
                admin_url('admin.php?page=l7-settings'),
                esc_attr(__( 'View Level7 platform settings', 'level7platform')),
                __('Settings', 'level7platform')
            )
        );
        
        return array_merge($action_links, $links);
    }
    
    public function install()
    {
        // enable XmlRpc
        update_option('enable_xmlrpc', '1');

        // create roles and capabilities
        $this->create_roles();
        
        // create pages
        $this->create_pages();
        
        // rewrite rules
        L7P()->query->add_rewrite_rules();
        
        // flush rules after install
        flush_rewrite_rules();
        
        // other settup
        
    }
    
    public function uninstall()
    {
        global $wpdb;
        
        // delete created pages or wp_trash_post
        wp_delete_post(l7p_get_option('pricing_page_id'));
        wp_delete_post(l7p_get_option('rates_page_id'));
        wp_delete_post(l7p_get_option('telephone_numbers_page_id'));
        wp_delete_post(l7p_get_option('hardware_page_id'));
        wp_delete_post(l7p_get_option('manual_page_id'));
        
        // delete options
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'l7p_%';");
        
        // delete posts + data
        $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type IN ('level7_page', 'level7platform_page');" );
        $wpdb->query( "DELETE FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE wp.ID IS NULL;" );
    }
    
    // add roles and capabilities
    private function create_roles()
    {
        global $wp_roles;
        
        // TODO: add capabilities: 
    }
    
    private function create_pages()
    {
        global $wpdb;
        
        $pages_contents = $this->get_pages_contents();
        
        // 5 standard pages
        $pages = array(
            'pricing' => array(
                'slug'      => 'pricing',
                'title'     => 'Plans',
                'content'   => $pages_contents['pricing'],
                'post_type' => 'page',
            ),
            'rates' => array(
                'slug'      => 'rates',
                'title'     => 'Call Rates',
                'content'   => $pages_contents['rates'],
                'post_type' => 'page',
            ),
            'telephone_numbers' => array(
                'slug'      => 'telephone-numbers',
                'title'     => 'Telephone Numbers',
                'content'   => $pages_contents['telephone_numbers'],
                'post_type' => 'page',
            ),
            'hardware' => array(
                'slug'      => 'hardware',
                'title'     => 'Hardware',
                'content'   => $pages_contents['hardware'],
                'post_type' => 'page',
            ),
            'manual' => array(
                'slug'      => 'manual',
                'title'     => 'Manuals',
                'content'   => $pages_contents['manual'],
                'post_type' => 'page',
            ),
            // templates for dynamic pages
            'rates_country' => array(
                'slug'      => 'country-rates',
                'title'     => 'Country call rates',
                'content'   => $pages_contents['rates_country'],
                'post_type' => 'level7platform_page',
            ),
            'telephone_numbers_country' => array(
                'slug'      => 'country-telephone-numbers',
                'title'     => 'Country telephone numbers',
                'content'   => $pages_contents['telephone_numbers_country'],
                'post_type' => 'level7platform_page',
            ),
            'telephone_numbers_state' => array(
                'slug'      => 'state-telephone-numbers',
                'title'     => 'State telephone numbers',
                'content'   => $pages_contents['telephone_numbers_state'],
                'post_type' => 'level7platform_page',
            ),
            'hardware_group' => array(
                'slug'      => 'hardware-group',
                'title'     => 'Hardware group',
                'content'   => $pages_contents['hardware_group'],
                'post_type' => 'level7platform_page',
            ),
            'hardware_model' => array(
                'slug'      => 'hardware-model',
                'title'     => 'Hardware phone details',
                'content'   => $pages_contents['hardware_model'],
                'post_type' => 'level7platform_page',
            ),
            'manual_chapter' => array(
                'slug'      => 'manual-chapter',
                'title'     => 'Manual chapter',
                'content'   => $pages_contents['manual_chapter'],
                'post_type' => 'level7platform_page',
            ),
        );

        foreach ($pages as $key => $page_data) {
            
            $this->create_page(
                $key,
                $page_data['slug'],
                $page_data['title'],
                $page_data['content'],
                $page_data['post_type']
            );
        }
    }
    
    private function create_page($page_name, $page_slug, $page_title, $page_content, $post_type = 'page')
    {
        global $wpdb;
        
        $page_data = array(
            'post_status'       => 'publish',
            'post_type'         => $post_type,
            'post_author'       => 1,
            // slug
            'post_name'         => sanitize_title($page_slug),
            'post_title'        => $page_title,
            'post_content'      => $page_content,
            'post_parent'       => 0,
            'comment_status'    => 'closed'
        );
        
        $page_id = null;
        if (strlen( $page_content ) > 0) {
            // search for an existing page with the specified page content (typically a shortcode)
            $page_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%"));
        } else {
            // search for an existing page with the specified page name
            $page_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_name = %s LIMIT 1;", sanitize_title($page_name)));
        }
        
        if (!$page_id) {
            $page_id =  wp_insert_post($page_data);
        }
        
        l7p_update_option(sprintf("%s_page_id", $page_name), $page_id);
        
        return $page_id;
    }
    
    private function get_pages_contents()
    {
        return array(
            'pricing' => <<<CONTENT
<article class="main plans-page">
  <div class="main-image pricing-image"></div>
  <header class="page-header dark-bg">
    <div class="inner">
      <h1>Choose a plan</h1>
      <p class="byline"><span class="text-grey">that</span> 
        <span class="text-orange">fits You</span> the most</p>
        [block currency_form]
    </div>
  </header>
  <section class="tr-w plans-wrap">
    <div class="grid-row plans">
      <div class="plan">
        <h2 class="h3">Pay As You Go</h2>
        <div class="inner">
          <ul>
            <li><span class="price">[USER_CHARGE]</span><br>per user monthly</li>
            <li>Free setup</li>
            <li>24/7 Technical Support</li>
            <li>All <a href="/features">features</a> included</li>
            <li>Free inbound calls</li>
            <li>Free inbound numbers: 0845/0560 and <a href="http://www.inum.net">iNum</a></li>
         </ul>
          [if is_auth]
           <a href="https://ssl7.net/voipstudio.com/u/app" class="button b-dborder b-freetrial">Buy Plan Now</a>
          [else]
           <a href="/register#P" class="button b-dborder b-freetrial">Free Trial</a>
          [/if]
        
        </div>
      </div>	
      <div class="plan">
        <h2 class="h3">Unlimited Domestic</h2>
        <div class="inner">
          <ul>
            <li><span class="price">[USER_UNLIMITED]</span><br>per user monthly</li>
            <li>Free setup</li>
            <li>24/7 Technical Support</li>
            <li>All <a href="/features">features</a> included</li>
            <li>Free inbound calls</li>
            <li>Free inbound numbers: 0845/0560 and <a href="http://www.inum.net">iNum</a></li>
            <li>Inclusive <a href="/telephone-numbers">geographical number</a> for each user</li>
            <li>Free outbound calls to fixed lines in one 
              <a class="tooltip" title="Argentina,
                                        Australia, Austria, Belgium, Brazil São Paulo, Canada, Chile, China, Croatia, Czech Republic, 
                                        Denmark, FranceGermany, Greece, Guam, Hong Kong S.A.R., China, Hungary, Ireland, Israel, 
                                        Italy, Luxembourg, Malaysia, Netherlands, New Zealand, Norway, Peru, Poland, Portugal, Puerto Rico, 
                                        Russia, Singapore, South Korea, Spain, Sweden, Switzerland, Taiwan, Turkey, U.S. Virgin Islands, 
                                        United Kingdom, United States, Vatican, Venezuela">chosen country</a></li>
          </ul>
          [if is_auth]
          <a href="https://ssl7.net/voipstudio.com/u/app" class="button b-dborder b-freetrial">Buy Plan Now</a>
          [else]
          <a href="/register#S" class="button b-dborder b-freetrial">Free Trial</a>
          [/if]
        </div>
      </div>		
      <div class="plan">
        <h2 class="h3">Unlimited International</h2>
        <div class="inner">
          <ul>
            <li><span class="price">[USER_UNLIMITED_INT]</span><br>per user monthly</li>
            <li>Free setup</li>
            <li>24/7 Technical Support</li>
            <li>All <a href="/features">features</a> included</li>
            <li>Free inbound calls</li>
            <li>Free inbound numbers: 0845/0560 and <a href="http://www.inum.net">iNum</a></li>
            <li>Inclusive <a href="/telephone-numbers">geographical number</a> for each user</li>
            <li>Free outbound calls to fixed lines in all <a class="tooltip" title="Argentina,
                                        Australia, Austria, Belgium, Brazil São Paulo, Canada, Chile, China, Croatia, Czech Republic, 
                                        Denmark, FranceGermany, Greece, Guam, Hong Kong S.A.R., China, Hungary, Ireland, Israel, 
                                        Italy, Luxembourg, Malaysia, Netherlands, New Zealand, Norway, Peru, Poland, Portugal, Puerto Rico, 
                                        Russia, Singapore, South Korea, Spain, Sweden, Switzerland, Taiwan, Turkey, U.S. Virgin Islands, 
                                        United Kingdom, United States, Vatican, Venezuela">listed countries </a>
              and to mobile phones in <a class="tooltip" title="Canada, China, Hong Kong S.A.R., China, Puerto Rico, United States">these countries</a></li>
          </ul>
          [if is_auth]
          <a href="https://ssl7.net/voipstudio.com/u/app" class="button b-dborder b-freetrial">Buy Plan Now</a>
          [else]
          <a href="/register#A" class="button b-dborder b-freetrial">Free Trial</a>
          [/if]
        </div>
      </div>	
    </div>
    <div class="tp_-_box" align="center" style="height:300px; margin-top:40px;" data-tp-settings="domainId:7923938,linkColor:00c0f2,fontSize:14,fontColor:2d4454,bgBarColor:2d4454,borderRadius:0,borderColor:2d4454,showRatingText:False,showUserImage:False,width:700,numOfReviews:1,useDarkLogo:False,fontBarColor:2d4454"> 
    <a href="https://www.trustpilot.com/review/voipstudio.com" rel="nofollow" hidden>VoIPstudio Reviews</a>
</div>
<script type="text/javascript">
    (function () { var a = "https:" == document.location.protocol ? "https://ssl.trustpilot.com" : "http://s.trustpilot.com", b = document.createElement("script"); b.type = "text/javascript"; b.async = true; b.src = a + "/tpelements/tp_elements_all.js"; var c = document.getElementsByTagName("script")[0]; c.parentNode.insertBefore(b, c) })();
</script>
  </section>
  <section class="dark-bg text-center">
    <div class="inner">
      <h3 class="h2">Start <span class="text-orange">30 days free trial</span></h3>
      <div class="grid-row icons">
        <div class="col-1-3">
          <div class="icon-how-trial"><span>How does the 30-days free trial work?</span></div>
          <p>Sign up for a trial account and enjoy 30 days of full access to all of our virtual PBX options and features without any obligations. If you 
            decide that you don't want to continue using our virtual PBX service after the trial period you don't have to do anything. Your trial will end 
            automatically and no monthly fees will be applied.</p>
        </div>
        <div class="col-1-3">
          <div class="icon-credit-card"><span>Do I need a credit card?</span></div>
          <p>A credit card is not required, you can start using your VoIPstudio IP phone System right away. Sign up now and we will top up your account with 100 free minutes or call credits* which you can use to make calls to any landline or mobile number.* If you need more minutes simply charge your account using a credit card.<br>
            <span class="super-small text-grey">* - Not available in all countries. You may have to provide a valid telephone number to activate your trial</span></p>
        </div>
        <div class="col-1-3">
          <div class="icon-cancel-trial"><span>Can i cancel trial at any time?</span></div>
          <p>If for whatever reason you are not completely satisfied with your VoIPstudio hosted phone system you can cancel your subscription at any time. There 
            is no contract or minimum notice period.</p>
        </div>
      </div>
      <a href="/register" class="button-b button-b-white">Take a Free Trial</a>
    </div>
  </section>
</article>
CONTENT

            ,'rates' => <<<CONTENT
<article class="main">
		<div class="main-image pricing-image"></div>
		<header class="page-header dark-bg tr-d">
			<div class="inner">
				<h1>Business VoIP - Call Rates</h1>
				<p class="byline"><span class="text-grey">Check</span> 
        <span class="text-orange">the costs of calls</span> to landlines and mobiles</p>
				[block currency_form]
			</div>
		</header>
		<section class="tr-w">
			<div class="grid-row pricing-columns">
				<div class="col-1-2">
					<table class="table voip-calls">
						<thead>
							<tr>
								<td colspan="2"><h2 class="h3">VoIP Calls</h2></td>
							</tr>
							<tr>
								<td><strong>Destination</strong></td>
								<td><strong>Per minute rate</strong></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>VoIPstudio network</td>
								<td>Free</td>
							</tr>
						</tbody>
						<tbody>
							<tr>
								<td>Other VoIP networks: <a href="http://www.sipbroker.com/sipbroker/action/providerWhitePages" class="outer" target="_blank">list</a></td>
								<td>Free</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="col-1-2">
          [if term_has_local]
					<table class="table dom-calls">
						<thead>
							<tr>
								<td colspan="3"><h2 class="h3">Domestic calls ([COUNTRY_NAME])</h2></td>
							</tr>
							<tr>
								<td><strong>Destination</strong></td>
                              [if term_unlimited_local]
                                <td><strong>Unlimited Plan</strong><br>(Per minute rate)</td>
                                <td><strong>Pay As You Go</strong><br>(Per minute rate)</td>
                              [else]
                                <td><strong>Per minute rate</strong></td>
                              [/if]
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>Calls to Landlines</td>
                              [if term_unlimited_local]
                                <td>[if term_local_fixed_free]Free[else][TERM_LOCAL_FIXED][/if]</td>
                                <td>[TERM_LOCAL_FIXED]</td>
                              [else]
                                <td>[TERM_LOCAL_FIXED]</td>
                              [/if]
							</tr>
						</tbody>
						<tbody>
							<tr>
								<td>Calls to Mobiles</td>
                              [if term_unlimited_local]
                                <td>[if term_local_mobile_free]Free[else][TERM_LOCAL_MOBILE][/if]</td>
                                <td>[TERM_LOCAL_MOBILE]</td>
                              [else]
                                <td>[TERM_LOCAL_MOBILE]</td>
                              [/if]
							</tr>
						</tbody>
					</table>
          
         [/if]
				</div>
			</div>
      
  <table class="table int-calls">
    <thead>
      <tr>
        <td colspan="4"><h2 class="h3">International calls</h2>
          <ul class="alphabet">
            
            <li><a href="#A">A</a></li><li><a href="#B">B</a></li><li><a href="#C">C</a></li><li><a href="#D">D</a></li><li><a href="#E">E</a></li><li><a href="#F">F</a></li><li><a href="#G">G</a></li><li><a href="#H">H</a></li><li><a href="#I">I</a></li><li><a href="#J">J</a></li><li><a href="#K">K</a></li><li><a href="#L">L</a></li><li><a href="#M">M</a></li><li><a href="#N">N</a></li><li><a href="#O">O</a></li><li><a href="#P">P</a></li><li><a href="#Q">Q</a></li><li><a href="#R">R</a></li><li><a href="#S">S</a></li><li><a href="#T">T</a></li><li><a href="#U">U</a></li><li><a href="#V">V</a></li><li><a href="#W">W</a></li><li><a href="#Y">Y</a></li><li><a href="#Z">Z</a></li>          </ul>
        </td>
      </tr>
      <tr>
        <td><strong>Destination</strong><br>(Click for details)</td>
        <td><strong>Unlimited Plan</strong></td>
        <td><strong>Landline</strong><br>(Per minute rate)</td>
        <td><strong>Mobile</strong><br>(Per minute rate)</td>
      </tr>
    </thead>

    [foreach term_letters]
      <tbody>
        <tr id="[TERM_LETTER]" class="letter"><td colspan="4">[TERM_LETTER]</td></tr>
        
        [foreach term_countries]
					<tr>
						<td><a href="[TERM_ROUTE_URL]">[TERM_ROUTE_COUNTRY]</a></td>
						<td>[if term_is_unlimited]Free[/if]</td>
						<td>[TERM_FIXED]</td>
						<td>[TERM_MOBILE]</td>
					</tr>
        [/foreach]
        
      </tbody>
    [/foreach]
  </table>
    
		</section>
		<section class="dark-bg text-center">
			<div class="inner">
				<h3 class="h2">Extra features <span class="text-orange">never end</span></h3>
				<div class="grid-row icons">
					<div class="col-1-3">
						<div class="icon-free-calls-light"><span>Free Internet Call</span></div>
						<p>We will always try to route your call to any telephone number free of charge over the Internet. 
							We use ENUM external link technology to find alternative connection to dialled number. 
							However if we need to deliver your call over traditional telephone network following principles apply:<br>
							Calls are billed per second<br>
							Minimum call charge is [TERM_MIN_CHARGE]<br>
							Prices exclude VAT<br>
							<span class="super-small text-grey">20% VAT applies to Customers based in United Kingdom and EU customers without 
							a VAT number.</span></p>
					</div>
					<div class="col-1-3">
						<div class="icon-sms"><span>SMS - Text Messages</span></div>
						<p>You can send text messages from our website after creating account with us. It cost only [SMS_CHARGE] per message.</p>
					</div>
					<div class="col-1-3">
						<div class="icon-web-calls"><span>Web Calls</span></div>
						<p>If you have access to the internet, you can use any telephone anywhere to make a call and have the call charged 
							at very low internet rate. VoIPstudio calls the phone you’re making the call from, then makes another call to the 
							person you want to call; then connects both together. So you will be charged for two calls. But because our 
							prices are so low, even with two calls, this is still a more economical way of making a call than, say, from 
							your mobile. If you are travelling abroad you can avoid roaming charges by using web calls.</p>
					</div>
				</div>
			</div>
		</section>
	</article>
CONTENT

            ,'rates_country'   => <<<CONTENT
<article class="main">
		<div class="main-image pricing-image m-hidden"></div>
		<header class="page-header dark-bg tr-d">
			<div class="inner">
				<h1>[TERM_ROUTE_COUNTRY] - Call Rates</h1>
				<p class="byline"><span class="text-grey">Check</span> 
					<span class="text-orange">the costs of calls</span> to landlines and mobiles</p>
					[block currency_form]
			</div>
		</header>
		<section class="tr-w">
          <table class="table int-calls-single">
            <thead>
              <tr>
                <td class="float-h-b clearfix" colspan="3">
                  <a href="/rates" class="button-b">Go back to full list</a>
                </td>
              </tr>
              <tr>
                <td><strong>Type of band</strong><br></td>
                <td><strong>Dialing codes</strong></td>
                <td><strong>Per minute rate</strong></td>
              </tr>
            </thead>
        [foreach term_routes]
          <tbody>
            <tr>
              <td>[TERM_ROUTE_NAME]</td>
              <td>[TERM_ROUTE_PREFIXES]</td>
              <td>[TERM_ROUTE_RATE]
              [if term_route_unlimited]
                (Free*)</td>
              [/if]
              [if term_route_conn_fee]
                Connection fee: [TERM_ROUTE_CONN_FEE]
              [/if]
            </tr>
          </tbody>
        [/foreach]
            <tfoot>
              <tr>
                <td colspan="3">*-Free with Unlimited Plan</td>
              </tr>
            </tfoot>
          </table>
		</section>
		<section class="dark-bg text-center">
			<div class="inner">
				<h3 class="h2">Extra features <span class="text-orange">never end</span></h3>
				<div class="grid-row icons">
					<div class="col-1-3">
						<div class="icon-free-calls-light"><span>Free Internet Call</span></div>
						<p>We will always try to route your call to any telephone number free of charge over the Internet. 
							We use ENUM external link technology to find alternative connection to dialled number. 
							However if we need to deliver your call over traditional telephone network following principles apply:<br>
							Calls are billed per second<br>
							Minimum call charge is [TERM_MIN_CHARGE]<br>
							Prices exclude VAT<br>
							<span class="super-small text-grey">20% VAT applies to Customers based in United Kingdom and EU customers without 
							a VAT number.</span></p>
					</div>
					<div class="col-1-3">
						<div class="icon-sms"><span>SMS - Text Messages</span></div>
						<p>You can send text messages from our website after creating account with us. It cost only [SMS_CHARGE] per message.</p>
					</div>
					<div class="col-1-3">
						<div class="icon-web-calls"><span>Web Calls</span></div>
						<p>If you have access to the internet, you can use any telephone anywhere to make a call and have the call charged 
							at very low internet rate. VoIPstudio calls the phone you’re making the call from, then makes another call to the 
							person you want to call; then connects both together. So you will be charged for two calls. But because our 
							prices are so low, even with two calls, this is still a more economical way of making a call than, say, from 
							your mobile. If you are travelling abroad you can avoid roaming charges by using web calls.</p>
					</div>
				</div>
			</div>
		</section>
	</article>
CONTENT
                        
            ,'telephone_numbers'   => <<<CONTENT
<article class="main">
		<div class="main-image pricing-image"></div>
		<header class="page-header dark-bg tr-d">
			<div class="inner">
				<h1>Virtual Telephone Numbers</h1>
				<p class="byline"><span class="text-grey">Check</span> 
        <span class="text-orange">prices for</span> setup and monthly rental</p>
				[block currency_form]
			</div>
		</header>
		<section>
			<div class="inner ">
				<p class="big fat text-center center full-width" style="margin-bottom: 60px;"><span class="m-h1">VoIPstudio <span class="text-orange">provides</span> a phone line</span><br class="m-hidden"> to each of listed countries in <span class="text-orange">a few seconds!</span></p>
			</div>

			<table class="table v-numbers">
				<thead>
					<tr>
						<td colspan="4">
						<h2 class="h3">Virtual Phone Numbers for VoIP PBX</h2>
						<p>Signup with VoIPstudio and get a real telephone number for free.</p><p></p>
					</td>
					</tr>
					<tr>
						<td><strong>Destination</strong></td>
						<td><strong>Number</strong></td>
						<td><strong>Setup Fee</strong></td>
						<td><strong>Monthly Rental</strong></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>United Kingdom</td>
						<td>+44 845 National</td>
						<td>Free</td>
						<td>Free</td>
					</tr>
				</tbody>
				<tbody>
					<tr>
						<td>United Kingdom</td>
						<td>+44 560 National</td>
						<td>Free</td>
						<td>Free</td>
					</tr>
				</tbody>
				<tbody>
					<tr>
						<td><a href="http://www.inum.net/" class="outer" target="_blank">iNum</a></td>
						<td>+883 5100 iNum</td>
						<td>Free</td>
						<td>Free</td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="4"></td>
					</tr>
				</tfoot>
			</table>
			<table class="table v-countries">
				<thead>
					<tr>
                      <td colspan="4">
						<p>You can subscribe to incoming VoIP numbers in more than 4000 cities around the World. <br class="m-hidden">Check your coutry of choice below.</p><p></p>
                      </td>
                    </tr>
					<tr>
						<td><strong>Destination</strong><br>(Click for details)</td>
						<td><strong>Unlimited Plan</strong></td>
						<td><strong>Setup Fee</strong></td>
						<td><strong>Monthly Rental</strong></td>
					</tr>
				</thead>

      [foreach ddi_paid]
				<tbody>
					<tr>
						<td><a href="[DDI_COUNTRY_URL]">[DDI_COUNTRY_NAME]</a></td>
						<td>[if ddi_is_unlimited]Free[/if]</td>
						<td>[DDI_SETUP_FEE]</td>
						<td>[DDI_MONTHLY_FEE]</td>
					</tr>
				</tbody>
      
      [/foreach]

				<tfoot>
					<tr>
						<td colspan="4"></td>
					</tr>
				</tfoot>
			</table>
		</section>
  <div class="tp_-_box" align="center" style="height:300px;" data-tp-settings="domainId:7923938,linkColor:00c0f2,fontSize:14,fontColor:2d4454,bgBarColor:2d4454,borderRadius:0,borderColor:2d4454,showRatingText:False,showUserImage:False,width:700,numOfReviews:1,useDarkLogo:False,fontBarColor:2d4454"> 
    <a href="https://www.trustpilot.com/review/voipstudio.com" rel="nofollow" hidden>VoIPstudio Reviews</a>
</div>
<script type="text/javascript">
    (function () { var a = "https:" == document.location.protocol ? "https://ssl.trustpilot.com" : "http://s.trustpilot.com", b = document.createElement("script"); b.type = "text/javascript"; b.async = true; b.src = a + "/tpelements/tp_elements_all.js"; var c = document.getElementsByTagName("script")[0]; c.parentNode.insertBefore(b, c) })();
</script>
		<section class="text-center tr-w">
			<div class="inner">
				<h2>Truly <span class="text-orange">Dependable</span></h2>
				<p>VoIPstudio provides a comprehensive range of virtual numbers for businesses looking to 
					expand their reach. We are a widely trusted call forwarding service provider, enabling our 
					subscribers to forward their calls to any carrier in the world.</p>
				<a href="/features" class="button-b b-set">Read more</a>
			</div>
		</section>
		<section class="dark-bg text-center">
			<div class="inner">
				<h3 class="h1">Buy a <span class="text-orange">Virtual</span> number</h3>
				<p class="byline">and start<span class="text-orange"> taking calls!</span></p>
				<p>You can also start our <a href="/register">30-day free trial</a>.<br>In case you need any further information, 
					please feel free to call<br class="m-hidden"> +1 310 870 9750 (US), +44 203 432 9230 (UK).</p>
			</div>
		</section>
	</article>
CONTENT
            
            ,'telephone_numbers_country'   => <<<CONTENT
<article class="main">
		<div class="main-image pricing-image m-hidden"></div>
		<header class="page-header dark-bg tr-d">
			<div class="inner">
				<h1>Business Telephone Numbers<br><span class="byline"><span class="text-grey">in</span>
					<span class="text-orange">[DDI_COUNTRY_NAME]</span></span> </h1>
					[block currency_form]
			</div>
		</header>
		<section class="tr-w">

			<div class="inner">
				<a href="/telephone-numbers" class="button-b no-margin">Go back to full list</a>
				<p>Country phone code: <strong>+[DDI_COUNTRY_TEL_CODE]</strong></p><br><br>
			</div>
      
      
  [if ddi_national]

			<table class="table v-numbers-single">
				<thead>
					<tr>
						<td><strong>Available National Numbers</strong></td>
						<td><strong>Area Code</strong></td>
						<td><strong>Setup Fee</strong></td>
						<td><strong>Monthly Rental</strong></td>
						<td></td>
					</tr>
				</thead>
				<tbody>
      [foreach ddi_national]
        <tr>
          <td>National</td>
          <td>([DDI_AREA_CODE])</td>
          <td>[DDI_SETUP_FEE]</td>
          <td>[DDI_MONTHLY_FEE]</td>
          <td><button class="button-b" onclick="buy('[DDI_BUY_URL]');">Buy</button></td>
        </tr>
      [/foreach]

				</tbody>
			</table>
  
  [/if]

  [if ddi_ddi_city]
			<table class="table v-numbers-single">
				<thead>
					<tr>
                      <td colspan="5"></td>
					</tr>
					<tr>
						<td><strong>Available City</strong></td>
						<td><strong>Area Code</strong></td>
						<td><strong>Setup Fee</strong></td>
						<td><strong>Monthly Rental</strong></td>
						<td></td>
					</tr>
				</thead>
    [foreach ddi_city]
				<tbody>
					<tr>
						<td>[DDI_CITY_NAME]</td>
						<td>
            [if ddi_has_area_code]
              [DDI_AREA_CODE]
            [/if]
            </td>
						<td>[DDI_SETUP_FEE]</td>
						<td>[DDI_MONTHLY_FEE]</td>
						<td><button class="button-b" onclick="buy('[DDI_BUY_URL]');">Buy</button></td>
					</tr>
				</tbody>
    [/foreach]
				<tfoot>
					<tr>
						<td colspan="5"></td>
					</tr>
				</tfoot>
			</table>
  [/if]

  [if ddi_ddi_toll_free]
    <table class="table v-toll-free">
      <thead>
        <tr>
          <td colspan="6"></td>
        </tr>
        <tr>
          <td><strong>Toll Free</strong></td>
          <td><strong>Area Code</strong></td>
          <td><strong>Setup Fee</strong></td>
          <td><strong>Monthly Rental</strong></td>
          <td><strong>Usage Fee</strong></td>
          <td></td>
        </tr>
      </thead>
      
      [foreach ddi_toll_free]
        <tbody>
          <tr>
            <td>Toll Free</td>
            <td>
            [if ddi_has_area_code]
              [DDI_AREA_CODE]
            [/if]
            </td>
            <td>[DDI_SETUP_FEE]</td>
            <td>[DDI_MONTHLY_FEE]</td>
            <td>[DDI_MIN_RATE]</td>
            <td><button class="button-b" onclick="buy('[DDI_BUY_URL]');">Buy</button></td>
          </tr>
        </tbody>
      [/foreach]
      
    </table>
  [/if]

		</section>
		<section class="dark-bg text-center">
			<div class="inner">
				<h3 class="h1">Buy a <span class="text-orange">Virtual</span> number</h3>
				<p class="byline">and connect with our <span class="text-orange"> telephone system </span> instantly!</p>
				<p>You can also start our <a href="/register">30-day free trial</a>.<br>In case you need any further information, 
					please feel free to call<br class="m-hidden"> +1 310 870 9750 (US), +44 203 432 9230 (UK).</p>
			</div>
		</section>
	</article>
CONTENT

            ,'telephone_numbers_state'     => <<<CONTENT
<article class="main">
		<div class="main-image pricing-image m-hidden"></div>
		<header class="page-header dark-bg tr-d">
			<div class="inner">
				<h1>Virtual Telephone Numbers<br><span class="byline"><span class="text-grey">in</span>
					<span class="text-orange">United States</span></span> </h1>
					[block currency_form]
			</div>
		</header>
		<section class="tr-w">
			<div class="inner">
				<a href="/telephone-numbers" class="button-b no-margin">Go back to full list</a>
				<p class="big fat">Select a State</p>
        
				<ul class="state-list">
          
  [if ddi_states]
    
    [foreach ddi_states]
      
      <li><a href="[DDI_STATE_URL]">[DDI_STATE_NAME]</a></li>
      
    [/foreach]
    
  [/if]
  
				</ul>
        
        
			</div>
      
[if ddi_ddi_toll_free]

			<table class="table v-toll-free">
				<thead>
					<tr>
					</tr>
					<tr>
						<td><strong>Toll Free</strong></td>
						<td><strong>Area Code</strong></td>
						<td><strong>Setup Fee</strong></td>
						<td><strong>Monthly Rental</strong></td>
						<td><strong>Usage Fee</strong></td>
						<td></td>
					</tr>
				</thead>
				<tbody>
  
  [foreach ddi_toll_free]
      <tr>
        <td>Toll Free</td>
        <td>([DDI_AREA_CODE])</td>
        <td>[DDI_SETUP_FEE]</td>
        <td>[DDI_MONTHLY_FEE]</td>
        <td>[DDI_MIN_RATE]</td>
        <td><button class="button-b" onclick="buy('[DDI_BUY_URL]','Toll+Free');">Buy</button></td>
      </tr>
    [/foreach]
				</tbody>
			</table>
[/if]

		</section>
		<section class="dark-bg text-center">
			<div class="inner">
				<h3 class="h1">Buy a <span class="text-orange">Virtual</span> number</h3>
				<p class="byline">and start<span class="text-orange"> taking calls!</span></p>
				<p>You can also start our <a href="/register">30-day free trial</a>.<br>In case you need any further information, 
					please feel free to call<br class="m-hidden"> +1 310 870 9750 (US), +44 203 432 9230 (UK).</p>
			</div>
		</section>
	</article>
CONTENT

            ,'hardware'          => <<<CONTENT
<article class="main hardware-page">
		<div class="main-image hardware-image"></div>
		<header class="page-header dark-bg tr-d">
			<div class="inner">
				<h1>The range of available hardware</h1>
				<p class="byline"><span class="text-grey">that will </span> <span class="text-orange">improve
				the functionality</span> of VoIP Calls</p>
			</div>
		</header>
		<section>
			<div class="inner">
				<div class="grid-row">
					<div class="col-1-2 m-hidden l">
						<figure class="img-hw"><img class src="http://static.ssl7.net/images/1/vs/desk-phones.jpg" alt="Desk Phones"></figure>
					</div>
					<div class="col-1-2 float-h-b r">
						<h2 class="h3">Desk Phones</h2> <a href="[PHONE_DESK_URL]" class="button-b">Browse All</a>
						<p>A range of specialist, multi-featured Business VoIP desk phones that connect to your LAN
							or Broadband (ADSL) router. Each phone offers a range of innovative options, with prices
							starting from just [PHONE_DESK_MIN_PRICE].</p>
					</div>
					</div>
			</div>
		</section>
		<section class="light-bg">
			<div class="inner">
				<div class="grid-row">
					<div class="col-1-2 m-hidden r">
						<figure class="img-hw"><img class src="http://static.ssl7.net/images/1/vs/dect-phones.jpg" alt="DECT Phones"></figure>
					</div>
					<div class="col-1-2 float-h-b l">
						<h2 class="h3">DECT Phones</h2> <a href="[PHONE_DECT_URL]" class="button-b">Browse All</a>
						<p>A selection of high quality, cordless VoIP phones designed to give you complete freedom
							of movement, by enabling multiple VoIP phones to operate from just one base. Prices
							starting from just [PHONE_DECT_MIN_PRICE].</p>
					</div>
				</div>
			</div>
		</section>
		<section>
			<div class="inner">
				<div class="grid-row">
					<div class="col-1-2 m-hidden l">
						<figure class="img-hw"><img class src="http://static.ssl7.net/images/1/vs/conf-phones.jpg" alt="Conference Phones"></figure>
					</div>
					<div class="col-1-2 float-h-b r">
						<h2 class="h3">Conference Phones</h2> <a href="[PHONE_CONF_URL]" class="button-b">Browse All</a>
						<p>Highly advanced conference phones, offering crystal clear hosted PBX conversations between
							groups of all sizes, from 4 to 40 people, across all distances.</p>
					</div>
					</div>
			</div>
		</section>
		<section class="light-bg">
			<div class="inner">
				<div class="grid-row">
					<div class="col-1-2 m-hidden r">
						<figure class="img-hw">
							<img class src="http://static.ssl7.net/images/1/vs/adaptors.jpg" alt="VoIP Adapters">
						</figure>
					</div>
					<div class="col-1-2 float-h-b l">
						<h2 class="h3">VoIP Adapters</h2> <a href="[PHONE_ADAPTOR_URL]" class="button-b">Browse All</a>
						<p>VoIP adapters allow you to easily connect an ordinary analogue telephone to a VoIP Phone Adapter.
							This enables anyone to benefit from a VoIP telephone system without the need for a specialist
							VoIP phone or PC.</p>
					</div>
				</div>
			</div>
		</section>
		<section class="tr-w">
			<div class="inner">
				<div class="grid-row">
					<div class="col-1-2 m-hidden l">
						<figure class="img-hw">
							<img class src="http://static.ssl7.net/images/1/vs/accesories.jpg" alt="Accessories">
						</figure>
					</div>
					<div class="col-1-2 float-h-b r">
						<h2 class="h3">Accesories</h2> <a href="[PHONE_ACCESSORY_URL]" class="button-b">Browse All</a>
						<p>A range of accessories designed to improve you VoIP business phone system experience. From
							hands free microphone ear piece sets to expansion modules to specialist Klarvoice handsets.</p>
					</div>
					</div>
			</div>
		</section>
		<section class="dark-bg text-center">
			<div class="inner">
				<h3 class="h1">Not sure which VoIPstudio Plan</h3>
				<p class="byline"><span class="text-orange">is most suitable</span> for Your Company?</p>
					<a href="/pricing" class="button-b button-b-white">Check Our Pricing</a>
			</div>
		</section>
	</article>
CONTENT
                    
            ,'hardware_group'   => <<<CONTENT
<article class="main hardware-product-page">
<div class="main-image hardware-image  m-hidden"></div>
<header class="page-header dark-bg tr-d">
  <div class="inner">
    <h1>The range of available Desk Phones</h1>
    <p class="byline"><span class="text-grey">that will </span> <span class="text-orange">improve
    the functionality</span> of VoIP Calls</p>
    [block currency_form]
  </div>
</header>
<section class="tr-w">
  <div class="inner">
    <ul class="product-nav">
      <li><a href="[PHONE_DESK_URL]">Desk Phones</a></li>
      <li><a href="[PHONE_DECT_URL]">DECT Phones</a></li>
      <li><a href="[PHONE_CONF_URL]">Conference Phones</a></li>
      <li><a href="[PHONE_ADAPTOR_URL]">VoIP Adapters</a></li>
      <li><a href="[PHONE_ACCESSORY_URL]">Accesories</a></li>
    </ul>
      <ul class="product-list clearfix">
        [if phones]
          
          [foreach phones]
            
            <li class="product clearfix">
              <div class="head">
                <h2 class="h3"><a href="[PHONE_URL]">[PHONE_MANUFACTURER] [PHONE_MODEL]</a></h2>
                <span class="price">[PHONE_PRICE]</span>
                <a href="[PHONE_URL]">
                  <figure>
                      <img src="[PHONE_THUMB_IMG]">
                  </figure>
                </a>
              </div>
              <div class="desc">
              <p class="small">[PHONE_SHORT_DESCRIPTION]... <a href="[PHONE_URL]">Read more.</a></p>
              <p class="small text-right">
                [if phone_in_stock]
                  <span class="text-grey">[PHONE_STOCK] in stock</span> <a href="#" class="button-b" onclick="buy('[PHONE_URL]');">Buy</a>	
                [else]
                  <span class="text-grey">Out of stock</span>
                [/if]
              </div>
            </li>
          [/foreach]
          
        [else]
          <li>No phones are available at the moment.</li>
        [/if]
      </ul>
    </div>
  </section>
  <section class="dark-bg text-center">
    <div class="inner">
      <h3 class="h1">Not sure which VoIPstudio Plan</h3>
      <p class="byline"><span class="text-orange">is most suitable</span> for Your Company?</p>
        <a href="/pricing" class="button-b button-b-white">Check Our Pricing</a>
    </div>
  </section>
</article>
CONTENT

            ,'hardware_model'   => <<<CONTENT
<article class="main hardware-product-single-page">
		<div class="main-image hardware-image m-hidden"></div>
		<header class="page-header dark-bg tr-d">
			<div class="inner">
				<h1>[PHONE_GROUP_NAME] - [PHONE_MANUFACTURER] [PHONE_MODEL]</h1>
				<p class="byline"><span class="text-grey">see </span> <span class="text-orange">the detailed specification</span> 
					of this hardware</p>
				[block currency_form]
			</div>
		</header>
		<section class="tr-w">
			<div class="inner">
        <ul class="product-nav">
          <li><a href="[PHONE_DESK_URL]">Desk Phones</a></li>
          <li><a href="[PHONE_DECT_URL]">DECT Phones</a></li>
          <li><a href="[PHONE_CONF_URL]">Conference Phones</a></li>
          <li><a href="[PHONE_ADAPTOR_URL]">VoIP Adapters</a></li>
          <li><a href="[PHONE_ACCESSORY_URL]">Accesories</a></li>
        </ul>
				<div class="grid-row product">
					<div class="col-1-3 product-head">
						<h2 class="h3">[PHONE_MANUFACTURER] [PHONE_MODEL]</h2>
						<span class="price">[PHONE_PRICE]</span>
							<figure>
									<img src="[PHONE_IMG]">
							</figure>
            [if phone_in_stock]
						<button class="button b-dborder center" onclick="buy('[PHONE_URL]');">Buy This</button>
						<p class="small text-center text-grey">[PHONE_STOCK] in stock</p>
            [else]
            <p class="small text-center text-grey">Out of stock</p>
            [/if]
					</div>
					<div class="col-2-3 product-content">
						[PHONE_SHORT_DESCRIPTION]
					</div>
				</div>
			</div>
		</section>
		<section class="dark-bg text-center">
			<div class="inner">
				<h3 class="h1">Not sure which VoIPstudio Plan</h3>
				<p class="byline"><span class="text-orange">is most suitable</span> for Your Company?</p>
					<a href="/pricing" class="button-b button-b-white">Check Our Pricing</a>
			</div>
		</section>
	</article>
CONTENT

            , 'manual'        => <<<CONTENT
<article class="main support-page">
  <div class="main-image support-image"></div>
  <header class="page-header dark-bg tr-d">
    <div class="inner">
      <h1>%MANUAL_NAME%<span class="byline"><span class="text-orange">%MANUAL_CHAPTER%</span></span></h1>
    </div>
  </header>
  <section>
    <div class="inner">		
      <div class="grid-row">
        <div class="col-1-3">
          <h3 class="text-grey">`Choose a chapter`</h3>
          <div class="chapters">
            %MANUAL_TOC%
          </div>
        </div>
        <div class="col-2-3 content-overwrite">
          %MANUAL_CONTENT%
        </div>
      </div>
    </div>
  </section>
  <section class="dark-bg home-trial text-center">
    <div class="inner">
      <h3 class="h1"><span class="text-orange">`Support</span> Centre`</h3>
      <p>`Access to the VoIP Studio Support Center is only granted to our customers.<br class="m-hidden">
        Please login into Customer Portal to open a new Support Ticket.`</p>
      <a href="%LOGIN_URL%" class="button-b b-set button-b-white">`Login`</a>
    </div>
  </section>
</article>
CONTENT
            
            , 'manual_chaoter'        => <<<CONTENT
<h1>%MANUAL_NAME%</h1>

%MANUAL_CONTENT%
CONTENT
            
        );
    }
}

return new L7P_Install();