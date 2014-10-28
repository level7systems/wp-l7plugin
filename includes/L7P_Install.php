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
        
        // other settup
    }
    
    public function create_pages()
    {
        $pages = apply_filters( 'level7platform_create_pages', array(
            'pricing' => array(
                'name'    => _x('pricing', 'Page slug', 'level7platform' ),
                'title'   => _x('Pricing', 'Page title', 'level7platform' ),
                // TODO
                'content' => <<<CONTENT
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
            ),
            'rates' => array(
                'name'    => _x('rates', 'Page slug', 'level7platform' ),
                'title'   => _x('Rates', 'Page title', 'level7platform' ),
                // TODO:
                'content' => <<<CONTENT
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
            ),
        ));
    
        foreach ( $pages as $key => $page ) {
            l7_create_page(esc_sql($page['name']), 'level7platform_' . $key . '_page_id', $page['title'], $page['content']);
        }
    }
}

return new L7P_Install();