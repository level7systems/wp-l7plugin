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
    }
    
    public function uninstall()
    {
        global $wpdb;
        
        // delete created pages or wp_trash_post
        wp_delete_post(l7p_get_option('pricing_page_id'));
        wp_delete_post(l7p_get_option('rates_page_id'));
        wp_delete_post(l7p_get_option('telephone_numbers_page_id'));
        wp_delete_post(l7p_get_option('hardware_page_id'));
        wp_delete_post(l7p_get_option('support_page_id'));
        wp_delete_post(l7p_get_option('login_page_id'));
        wp_delete_post(l7p_get_option('one_time_login_page_id'));
        wp_delete_post(l7p_get_option('recover_page_id'));
        wp_delete_post(l7p_get_option('activation_page_id'));
        wp_delete_post(l7p_get_option('subscription_page_id'));
        wp_delete_post(l7p_get_option('register_page_id'));
        wp_delete_post(l7p_get_option('affiliate_page_id'));
        
        // delete options
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'l7p_%';");
        
        // delete posts + data
        $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type IN ('level7_page', 'l7p_page');" );
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
            'support' => array(
                'slug'      => 'support',
                'title'     => 'Support',
                'content'   => $pages_contents['support'],
                'post_type' => 'page',
            ),
            'login' => array(
                'slug'      => 'login',
                'title'     => 'Login',
                'content'   => $pages_contents['login'],
                'post_type' => 'page',
            ),
            'one_time_login' => array(
                'slug'      => 'one-time-login',
                'title'     => 'One time login',
                'content'   => $pages_contents['one_time_login'],
                'post_type' => 'page',
            ),
            'recover' => array(
                'slug'      => 'recover-password',
                'title'     => 'Recover password',
                'content'   => $pages_contents['recover'],
                'post_type' => 'page',
            ),
            'activation' => array(
                'slug'      => 'activation',
                'title'     => 'Activation',
                'content'   => $pages_contents['activation'],
                'post_type' => 'page',
            ),
            'subscription' => array(
                'slug'      => 'subscription',
                'title'     => 'Email subscription',
                'content'   => $pages_contents['subscription'],
                'post_type' => 'page',
            ),
            'register' => array(
                'slug'      => 'register',
                'title'     => 'Registration',
                'content'   => $pages_contents['register'],
                'post_type' => 'page',
            ),
            'affiliate' => array(
                'slug'      => 'affiliate',
                'title'     => 'Become Our Agent',
                'content'   => $pages_contents['affiliate'],
                'post_type' => 'page',
            ),
            // templates for dynamic pages
            'rates_country' => array(
                'slug'      => 'country-rates',
                'title'     => 'Country call rates',
                'content'   => $pages_contents['rates_country'],
                'post_type' => 'l7p_page',
            ),
            'telephone_numbers_country' => array(
                'slug'      => 'country-telephone-numbers',
                'title'     => 'Country telephone numbers',
                'content'   => $pages_contents['telephone_numbers_country'],
                'post_type' => 'l7p_page',
            ),
            'telephone_numbers_state' => array(
                'slug'      => 'state-telephone-numbers',
                'title'     => 'State telephone numbers',
                'content'   => $pages_contents['telephone_numbers_state'],
                'post_type' => 'l7p_page',
            ),
            'hardware_group' => array(
                'slug'      => 'hardware-group',
                'title'     => 'Hardware group',
                'content'   => $pages_contents['hardware_group'],
                'post_type' => 'l7p_page',
            ),
            'hardware_model' => array(
                'slug'      => 'hardware-model',
                'title'     => 'Hardware phone details',
                'content'   => $pages_contents['hardware_model'],
                'post_type' => 'l7p_page',
            ),
            'manual_chapter' => array(
                'slug'      => 'manual-chapter',
                'title'     => 'Manual chapter',
                'content'   => $pages_contents['manual_chapter'],
                'post_type' => 'l7p_page',
            ),
        );

        // pages that support currency redirects
        $pages_with_currency_redirect_ids = array();
        foreach ($pages as $key => $page_data) {
            
            $page_id = $this->create_page(
                $key,
                $page_data['slug'],
                $page_data['title'],
                $page_data['content'],
                $page_data['post_type']
            );
            
            if (in_array($key, ['pricing', 'rates', 'telephone_numbers', 'hardware'])) {
                $pages_with_currency_redirect_ids[] = $page_id;
            }
        }
        
        l7p_update_option('currency_redirect_ids', $pages_with_currency_redirect_ids);
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

<div>
  <h1>Choose a plan that fits You the most</h1>
    [block currency_form]
</div>

<div>
    <div>
        <h2>Pay As You Go</h2>
        <div>
          <ul>
            <li><span>[USER_CHARGE]</span><br>per user monthly</li>
            <li>Free setup</li>
            <li>24/7 Technical Support</li>
            <li>All <a href="/features">features</a> included</li>
            <li>Free inbound calls</li>
            <li>Free inbound numbers: 0845/0560 and <a href="http://www.inum.net">iNum</a></li>
         </ul>
           <a href="/en/register#P">Free Trial</a>
        </div>
    </div>	
    <div>
        <h2>Unlimited Domestic</h2>
        <div>
          <ul>
            <li><span>[USER_UNLIMITED]</span><br>per user monthly</li>
            <li>Free setup</li>
            <li>24/7 Technical Support</li>
            <li>All <a href="/features">features</a> included</li>
            <li>Free inbound calls</li>
            <li>Free inbound numbers: 0845/0560 and <a href="http://www.inum.net">iNum</a></li>
            <li>Inclusive <a href="/telephone-numbers">geographical number</a> for each user</li>
            <li>Free outbound calls to fixed lines in one 
              <a title="Argentina,
                                        Australia, Austria, Belgium, Brazil São Paulo, Canada, Chile, China, Croatia, Czech Republic, 
                                        Denmark, FranceGermany, Greece, Guam, Hong Kong S.A.R., China, Hungary, Ireland, Israel, 
                                        Italy, Luxembourg, Malaysia, Netherlands, New Zealand, Norway, Peru, Poland, Portugal, Puerto Rico, 
                                        Russia, Singapore, South Korea, Spain, Sweden, Switzerland, Taiwan, Turkey, U.S. Virgin Islands, 
                                        United Kingdom, United States, Vatican, Venezuela">chosen country</a></li>
          </ul>
          <a href="/en/register#S" >Free Trial</a>
        </div>
    </div>		
    <div>
        <h2>Unlimited International</h2>
        <div>
          <ul>
            <li><span>[USER_UNLIMITED_INT]</span><br>per user monthly</li>
            <li>Free setup</li>
            <li>24/7 Technical Support</li>
            <li>All <a href="/features">features</a> included</li>
            <li>Free inbound calls</li>
            <li>Free inbound numbers: 0845/0560 and <a href="http://www.inum.net">iNum</a></li>
            <li>Inclusive <a href="/telephone-numbers">geographical number</a> for each user</li>
            <li>Free outbound calls to fixed lines in all <a title="Argentina,
                                        Australia, Austria, Belgium, Brazil São Paulo, Canada, Chile, China, Croatia, Czech Republic, 
                                        Denmark, FranceGermany, Greece, Guam, Hong Kong S.A.R., China, Hungary, Ireland, Israel, 
                                        Italy, Luxembourg, Malaysia, Netherlands, New Zealand, Norway, Peru, Poland, Portugal, Puerto Rico, 
                                        Russia, Singapore, South Korea, Spain, Sweden, Switzerland, Taiwan, Turkey, U.S. Virgin Islands, 
                                        United Kingdom, United States, Vatican, Venezuela">listed countries </a>
              and to mobile phones in <a title="Canada, China, Hong Kong S.A.R., China, Puerto Rico, United States">these countries</a></li>
          </ul>
          <a href="/register#A">Free Trial</a>
        </div>
    </div>	
</div>
  
<div>
    <h3>Start 30 days free trial</h3>
    <div>
        <div>
          <div><span>How does the 30-days free trial work?</span></div>
          <p>Sign up for a trial account and enjoy 30 days of full access to all of our virtual PBX options and features without any obligations. If you 
            decide that you don't want to continue using our virtual PBX service after the trial period you don't have to do anything. Your trial will end 
            automatically and no monthly fees will be applied.</p>
        </div>
        <div>
          <div><span>Do I need a credit card?</span></div>
          <p>A credit card is not required, you can start using your VoIPstudio IP phone System right away. Sign up now and we will top up your account with 100 free minutes or call credits* which you can use to make calls to any landline or mobile number.* If you need more minutes simply charge your account using a credit card.<br>
            <span>* - Not available in all countries. You may have to provide a valid telephone number to activate your trial</span></p>
        </div>
        <div>
          <div><span>Can i cancel trial at any time?</span></div>
          <p>If for whatever reason you are not completely satisfied with your VoIPstudio hosted phone system you can cancel your subscription at any time. There 
            is no contract or minimum notice period.</p>
        </div>
    </div>
    <a href="/register">Take a Free Trial</a>
</div>
  
CONTENT

            ,'rates' => <<<CONTENT

<div>
    <h1>Business VoIP - Call Rates</h1>
    <p><span>Check</span><span>the costs of calls</span> to landlines and mobiles</p>
    [block currency_form]
</div>
		
<div>
    <div>
        <table>
            <thead>
                <tr>
                    <td colspan="2"><h2>VoIP Calls</h2></td>
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
                    <td>Other VoIP networks: <a href="http://www.sipbroker.com/sipbroker/action/providerWhitePages" target="_blank">list</a></td>
                    <td>Free</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div>
            
        [if term_has_local]
            <table>
                <thead>
                    <tr>
                        <td colspan="3"><h2>Domestic calls ([COUNTRY_NAME])</h2></td>
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
      
  <table>
    <thead>
        <tr>
            <td colspan="4"><h2>International calls</h2>
                <ul>
                    <li><a href="#A">A</a></li><li><a href="#B">B</a></li><li><a href="#C">C</a></li><li><a href="#D">D</a></li><li><a href="#E">E</a></li><li><a href="#F">F</a></li><li><a href="#G">G</a></li><li><a href="#H">H</a></li><li><a href="#I">I</a></li><li><a href="#J">J</a></li><li><a href="#K">K</a></li><li><a href="#L">L</a></li><li><a href="#M">M</a></li><li><a href="#N">N</a></li><li><a href="#O">O</a></li><li><a href="#P">P</a></li><li><a href="#Q">Q</a></li><li><a href="#R">R</a></li><li><a href="#S">S</a></li><li><a href="#T">T</a></li><li><a href="#U">U</a></li><li><a href="#V">V</a></li><li><a href="#W">W</a></li><li><a href="#Y">Y</a></li><li><a href="#Z">Z</a></li>
                </ul>
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
        <tr id="[TERM_LETTER]"><td colspan="4">[TERM_LETTER]</td></tr>
        
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
		
<div>
    <h3>Extra features <span>never end</span></h3>
    <div>
        <div>
            <div><span>Free Internet Call</span></div>
            <p>We will always try to route your call to any telephone number free of charge over the Internet. 
                We use ENUM external link technology to find alternative connection to dialled number. 
                However if we need to deliver your call over traditional telephone network following principles apply:<br>
                Calls are billed per second<br>
                Minimum call charge is [TERM_MIN_CHARGE]<br>
                Prices exclude VAT<br>
                <span>20% VAT applies to Customers based in United Kingdom and EU customers without 
                a VAT number.</span></p>
        </div>
        <div>
            <div><span>SMS - Text Messages</span></div>
            <p>You can send text messages from our website after creating account with us. It cost only [SMS_CHARGE] per message.</p>
        </div>
        <div>
            <div><span>Web Calls</span></div>
            <p>If you have access to the internet, you can use any telephone anywhere to make a call and have the call charged 
                at very low internet rate. VoIPstudio calls the phone you’re making the call from, then makes another call to the 
                person you want to call; then connects both together. So you will be charged for two calls. But because our 
                prices are so low, even with two calls, this is still a more economical way of making a call than, say, from 
                your mobile. If you are travelling abroad you can avoid roaming charges by using web calls.</p>
        </div>
    </div>
</div>
	
CONTENT

            ,'rates_country'   => <<<CONTENT

<div>
    <h1>[TERM_ROUTE_COUNTRY] - Call Rates</h1>
    <p><span>Check</span> 
        <span>the costs of calls</span> to landlines and mobiles</p>
        [block currency_form]
</div>
		
<table>
    <thead>
      <tr>
        <td colspan="3">
          <a href="/rates">Go back to full list</a>
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
		
<div>
    <h3>Extra features <span>never end</span></h3>
    <div>
        <div>
            <div><span>Free Internet Call</span></div>
            <p>We will always try to route your call to any telephone number free of charge over the Internet. 
                We use ENUM external link technology to find alternative connection to dialled number. 
                However if we need to deliver your call over traditional telephone network following principles apply:<br>
                Calls are billed per second<br>
                Minimum call charge is [TERM_MIN_CHARGE]<br>
                Prices exclude VAT<br>
                <span>20% VAT applies to Customers based in United Kingdom and EU customers without 
                a VAT number.</span></p>
        </div>
        <div>
            <div><span>SMS - Text Messages</span></div>
            <p>You can send text messages from our website after creating account with us. It cost only [SMS_CHARGE] per message.</p>
        </div>
        <div>
            <div><span>Web Calls</span></div>
            <p>If you have access to the internet, you can use any telephone anywhere to make a call and have the call charged 
                at very low internet rate. VoIPstudio calls the phone you’re making the call from, then makes another call to the 
                person you want to call; then connects both together. So you will be charged for two calls. But because our 
                prices are so low, even with two calls, this is still a more economical way of making a call than, say, from 
                your mobile. If you are travelling abroad you can avoid roaming charges by using web calls.</p>
        </div>
    </div>
</div>
	
CONTENT
                        
            ,'telephone_numbers'   => <<<CONTENT

<div>
    <h1>Virtual Telephone Numbers</h1>
    <p><span>Check</span> 
    <span>prices for</span> setup and monthly rental</p>
    [block currency_form]
</div>
		
<div>
    <p style="margin-bottom: 60px;"><span>VoIPstudio <span>provides</span> a phone line</span><br> to each of listed countries in <span>a few seconds!</span></p>
</div>

<table>
    <thead>
        <tr>
            <td colspan="4">
            <h2>Virtual Phone Numbers for VoIP PBX</h2>
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
            <td><a href="http://www.inum.net/" target="_blank">iNum</a></td>
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
<table>
    <thead>
        <tr>
          <td colspan="4">
            <p>You can subscribe to incoming VoIP numbers in more than 4000 cities around the World. <br>Check your coutry of choice below.</p><p></p>
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
		
<div>
    <h2>Truly <span>Dependable</span></h2>
    <p>VoIPstudio provides a comprehensive range of virtual numbers for businesses looking to 
        expand their reach. We are a widely trusted call forwarding service provider, enabling our 
        subscribers to forward their calls to any carrier in the world.</p>
    <a href="/features">Read more</a>
</div>
		
<div>
    <h3>Buy a <span>Virtual</span> number</h3>
    <p>and start<span> taking calls!</span></p>
    <p>You can also start our <a href="/register">30-day free trial</a>.<br>In case you need any further information, 
        please feel free to call<br> +1 310 870 9750 (US), +44 203 432 9230 (UK).</p>
</div>
	
CONTENT
            
            ,'telephone_numbers_country'   => <<<CONTENT

<div>
    <h1>Business Telephone Numbers<br><span><span>in</span>
        <span>[DDI_COUNTRY_NAME]</span></span> </h1>
        [block currency_form]
</div>



<div>
    <a href="/telephone-numbers">Go back to full list</a>
    <p>Country phone code: <strong>+[DDI_COUNTRY_TEL_CODE]</strong></p><br><br>
</div>
      
[if ddi_national]

    <table>
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
                <td><a href="[DDI_BUY_URL]" title="">Buy</a></td>
              </tr>
            [/foreach]

        </tbody>
    </table>
  
  [/if]

  [if ddi_ddi_city]
    <table>
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
                    <td><a href="[DDI_BUY_URL]" title="">Buy</a></td>
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
    <table>
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
            <td><a href="[DDI_TOLL_FREE_BUY_URL]" title="">Buy</a></td>
          </tr>
        </tbody>
      [/foreach]
      
    </table>
[/if]
		
<div>
    <h3>Buy a <span>Virtual</span> number</h3>
    <p>and connect with our <span> telephone system </span> instantly!</p>
    <p>You can also start our <a href="/register">30-day free trial</a>.<br>In case you need any further information, 
        please feel free to call<br> +1 310 870 9750 (US), +44 203 432 9230 (UK).</p>
</div>
		
CONTENT

            ,'telephone_numbers_state'     => <<<CONTENT

<div>
    <h1>Virtual Telephone Numbers<br><span><span>in</span>
        <span>United States</span></span> </h1>
        [block currency_form]
</div>
		
<div>
    <a href="/telephone-numbers">Go back to full list</a>
    <p>Select a State</p>

    <ul>
          
        [if ddi_states]
    
            [foreach ddi_states]
                <li><a href="[DDI_STATE_URL]">[DDI_STATE_NAME]</a></li>
            [/foreach]
    
        [/if]
  
    </ul>
</div>
      
[if ddi_ddi_toll_free]

    <table>
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
                <td><a href="[DDI_TOLL_FREE_BUY_URL]">Buy</a></td>
              </tr>
            [/foreach]
        </tbody>
    </table>
[/if]

<div>
    <h3>Buy a <span>Virtual</span> number</h3>
    <p>and start<span> taking calls!</span></p>
    <p>You can also start our <a href="/register">30-day free trial</a>.<br>In case you need any further information, 
        please feel free to call<br> +1 310 870 9750 (US), +44 203 432 9230 (UK).</p>
</div>
	
CONTENT

            ,'hardware'          => <<<CONTENT

<div>
    <h1>The range of available hardware</h1>
    <p><span>that will </span> <span>improve
    the functionality</span> of VoIP Calls</p>
</div>
		
<div>
    <div>
        <figure><img class src="http://static.ssl7.net/images/1/vs/desk-phones.jpg" alt="Desk Phones"></figure>
    </div>
    <div>
        <h2>Desk Phones</h2> <a href="[PHONE_DESK_URL]">Browse All</a>
        <p>A range of specialist, multi-featured Business VoIP desk phones that connect to your LAN
            or Broadband (ADSL) router. Each phone offers a range of innovative options, with prices
            starting from just [PHONE_DESK_MIN_PRICE].</p>
    </div>
</div>
		
<div>
    <div>
        <figure><img class src="http://static.ssl7.net/images/1/vs/dect-phones.jpg" alt="DECT Phones"></figure>
    </div>
    <div>
        <h2>DECT Phones</h2> <a href="[PHONE_DECT_URL]">Browse All</a>
        <p>A selection of high quality, cordless VoIP phones designed to give you complete freedom
            of movement, by enabling multiple VoIP phones to operate from just one base. Prices
            starting from just [PHONE_DECT_MIN_PRICE].</p>
    </div>
</div>
		
<div>
    <div>
        <figure><img class src="http://static.ssl7.net/images/1/vs/conf-phones.jpg" alt="Conference Phones"></figure>
    </div>
    <div>
        <h2>Conference Phones</h2> <a href="[PHONE_CONF_URL]">Browse All</a>
        <p>Highly advanced conference phones, offering crystal clear hosted PBX conversations between
            groups of all sizes, from 4 to 40 people, across all distances.</p>
    </div>
</div>
		
<div>
    <div>
        <figure>
            <img class src="http://static.ssl7.net/images/1/vs/adaptors.jpg" alt="VoIP Adapters">
        </figure>
    </div>
    <div>
        <h2>VoIP Adapters</h2> <a href="[PHONE_ADAPTOR_URL]">Browse All</a>
        <p>VoIP adapters allow you to easily connect an ordinary analogue telephone to a VoIP Phone Adapter.
            This enables anyone to benefit from a VoIP telephone system without the need for a specialist
            VoIP phone or PC.</p>
    </div>
</div>
		
<div>
    <div>
        <figure>
            <img class src="http://static.ssl7.net/images/1/vs/accesories.jpg" alt="Accessories">
        </figure>
    </div>
    <div>
        <h2>Accesories</h2> <a href="[PHONE_ACCESSORY_URL]">Browse All</a>
        <p>A range of accessories designed to improve you VoIP business phone system experience. From
            hands free microphone ear piece sets to expansion modules to specialist Klarvoice handsets.</p>
    </div>
</div>
		
<div>
    <h3>Not sure which VoIPstudio Plan</h3>
    <p><span>is most suitable</span> for Your Company?</p>
        <a href="/pricing">Check Our Pricing</a>
</div>
	
CONTENT
                    
            ,'hardware_group'   => <<<CONTENT

<div>
  <h1>The range of available Desk Phones</h1>
  <p><span>that will </span> <span>improve
  the functionality</span> of VoIP Calls</p>
  [block currency_form]
</div>

<div>
  <ul>
    <li><a href="[PHONE_DESK_URL]">Desk Phones</a></li>
    <li><a href="[PHONE_DECT_URL]">DECT Phones</a></li>
    <li><a href="[PHONE_CONF_URL]">Conference Phones</a></li>
    <li><a href="[PHONE_ADAPTOR_URL]">VoIP Adapters</a></li>
    <li><a href="[PHONE_ACCESSORY_URL]">Accesories</a></li>
  </ul>
    <ul>
      [if phones]

        [foreach phones]

          <li>
            <div>
              <h2><a href="[PHONE_URL]">[PHONE_MANUFACTURER] [PHONE_MODEL]</a></h2>
              <span>[PHONE_PRICE]</span>
              <a href="[PHONE_URL]">
                <figure>
                    <img src="[PHONE_THUMB_IMG]">
                </figure>
              </a>
            </div>
            <div>
            <p>[PHONE_SHORT_DESCRIPTION]... <a href="[PHONE_URL]">Read more.</a></p>
            <p>
              [if phone_in_stock]
                <span>[PHONE_STOCK] in stock</span> <a href="[PHONE_BUY_URL]" title="">Buy</a>
	              [else]
                <span>Out of stock</span>
              [/if]
            </div>
          </li>
        [/foreach]

      [else]
        <li>No phones are available at the moment.</li>
      [/if]
    </ul>
</div>
  
<div>
  <h3>Not sure which VoIPstudio Plan</h3>
  <p><span>is most suitable</span> for Your Company?</p>
    <a href="/pricing">Check Our Pricing</a>
</div>

CONTENT

            ,'hardware_model'   => <<<CONTENT

<div>
    <h1>[PHONE_GROUP_NAME] - [PHONE_MANUFACTURER] [PHONE_MODEL]</h1>
    <p><span>see </span> <span>the detailed specification</span> 
        of this hardware</p>
    [block currency_form]
</div>

<div>
    <ul>
      <li><a href="[PHONE_DESK_URL]">Desk Phones</a></li>
      <li><a href="[PHONE_DECT_URL]">DECT Phones</a></li>
      <li><a href="[PHONE_CONF_URL]">Conference Phones</a></li>
      <li><a href="[PHONE_ADAPTOR_URL]">VoIP Adapters</a></li>
      <li><a href="[PHONE_ACCESSORY_URL]">Accesories</a></li>
    </ul>
    <div>
        <div>
        <h2>[PHONE_MANUFACTURER] [PHONE_MODEL]</h2>
        <span>[PHONE_PRICE]</span>
            <figure>
                    <img src="[PHONE_IMG]">
            </figure>
            [if phone_in_stock]
                <a href="[PHONE_BUY_URL]" title="">Buy This</a>
                <p>[PHONE_STOCK] in stock</p>
            [else]
            <p>Out of stock</p>
            [/if]
        </div>
        <div>
            [PHONE_SHORT_DESCRIPTION]
        </div>
    </div>
</div>
		
<div>
    <h3>Not sure which VoIPstudio Plan</h3>
    <p><span>is most suitable</span> for Your Company?</p>
        <a href="/pricing">Check Our Pricing</a>
</div>
	
CONTENT

            ,'support'                => <<<CONTENT
	
<div>
    <h1>`VoIPstudio <span>Support</span></h1>
    <p>`All <span>information </span> can be found in our user <span>manuals</span></p>
</div>

<div>
    <div>
        <div>
            <h3>`User Manual`</h3>
            <a href="{MANUAL}?chapter=User_Introduction">`Read online`</a>
            <a title="Download PDF" href="http://repo.ssl7.net/downloads/manuals/VoIPStudio-User.pdf"></a>
        </div>
        <div>
            <h3>`Receptionist Manual`</h3>
            <a href="{MANUAL}?chapter=Reception_Introduction">`Read online`</a>
            <a title="Download PDF" href="http://repo.ssl7.net/downloads/manuals/VoIPStudio-Reception.pdf"></a>
        </div>
        <div>
            <h3>`Administrator Manual`</h3>
            <a href="{MANUAL}?chapter=Admin_Introduction">`Read online`</a>
            <a title="Download PDF" href="http://repo.ssl7.net/downloads/manuals/VoIPStudio-Admin.pdf"></a>
        </div>
    </div>
</div>

<div>
    <h3><span>`Support</span> Centre`</h3>
    <p>`Access to the VoIPstudio Support Center is only granted to our customers.<br>
        Please login to Customer Portal to open a new support ticket.`</p>
  <a>`Login`</a>
</div>
	
CONTENT
       
            ,'login'                => <<<CONTENT
<div id="l7p-login">

    [block login_form]

</div>
            
CONTENT
            
            ,'one_time_login'      => <<<CONTENT
<div id="l7p-new-password">

    [block new_password_form]

</div>
        
CONTENT

            ,'recover'             => <<<CONTENT
<div id="l7p-password-recover">

    [block password_recover_form]

</div>
            
CONTENT
            
            ,'activation'                => <<<CONTENT
<div id="l7p-activation">

    [block activation_form]

</div>

CONTENT
            
            ,'subscription'              => <<<CONTENT
<div id="l7p-subscription">

    [block subscription_form]

</div>

CONTENT

            ,'register'                => <<<CONTENT
<div>
            
    [block register_form]
            
</div>

CONTENT
	
            ,'affiliate'                => <<<CONTENT
<div>
            
    [block register_agent_form]
            
</div>
            	
CONTENT
            
            , 'manual_chapter'        => <<<CONTENT
<div>
  <h1>[MANUAL_NAME]<span><span>[MANUAL_CHAPTER]</span></span></h1>
</div>

<div>		
  <div>
    <div>
      <h3>`Choose a chapter`</h3>
      <div>
        [MANUAL_TOC]
      </div>
    </div>
    <div>
      [MANUAL_CONTENT]
    </div>
  </div>
</div>

<div>
  <h3><span>`Support</span> Center`</h3>
  <p>`Access to the VoIP Studio Support Center is only granted to our customers.<br>
    Please login into Customer Portal to open a new Support Ticket.`</p>
  <a href="/login">`Login`</a>
</div>

CONTENT
            
        );
    }
}

return new L7P_Install();