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
            'USER_CHARGE'   => array(
                'callback'      => __CLASS__ . '::user_charge',
                'description'   => ""
            ),
            'USER_UNLIMITED'=> array(
                'callback'      => __CLASS__ . '::user_unlimited',
                'description'   => ""
            ),
            'USER_UNLIMITED_INT'=> array(
                'callback'      => __CLASS__ . '::user_unlimited_int',
                'description'   => ""
            ),
            'APP_URL'       => array(
                'callback'      => __CLASS__ . '::app_url',
                'description'   => "Application URL"
            ),
        );

        foreach ($shortcodes as $shortcode => $options) {
            add_shortcode($shortcode, $options['callback']);
        }
        
    }
    
    public static function user_charge($atts)
    {
		return self::charge('user');
	}
	
	public static function user_unlimited($atts)
	{
	    return self::charge('user-S');
	}
	
	public static function user_unlimited_int($atts)
	{
	    return self::charge('user-A');
	}
	
	private static function charge($service)
	{
	    if (!$currency = l7p_get_session('currency')) {
	      $currency = l7p_get_option('currency', 'USD');	        
	    }
	    
	    $charges = l7p_get_option('charges', array());
	    $charge = array_key_exists($service, $charges) ? $charges[$service][$currency] : 0;
	    
	    return l7p_currency_symbol($charge, $currency);
	}
	
	public static function app_url()
	{
	    // TODO
	    return  "app_url(get_entry_app()).__('/app')";
	}
	
	/*

    ACTIVATE_URL:
    function: "url_for(__('@activate_webproduct').'?user_id='.$sf_request->getParameter('user_id'))"
    description: Activate web product URL
    ACTIVATE_EMAIL:
    function: "(isset($user)) ? $user->getEmail() : '<!-- ACTIVATE_EMAIL not defined -->'"
    description: Activate web product email address
    ACTIVATE_OTHER_WEBSITE:
    function: "(isset($other_product)) ? $other_product->getWebDomain() : '<!-- ACTIVATE_OTHER_WEBSITE not defined -->'"
    description: Activate web product other website address
    ACTIVATE_OTHER_NAME:
    function: "(isset($other_product)) ? $other_product->getName() : '<!-- ACTIVATE_OTHER_NAME not defined -->'"
    description: Activate web product other website address
    DOWNLOAD_WIN:
    function: "url_for(__('@download_win'))"
    description: Download for Windows URL
    DOWNLOAD_LINUX:
    function: "url_for(__('@download_linux'))"
    description: Download for Linux URL
    DOWNLOAD_MAC:
    function: "url_for(__('@download_mac'))"
    description: Download for Mac URL
    HOMEPAGE_URL:
    function: "url_for(__('@homepage'))"
    description: Contact us URL
    CONTACT_US_URL:
    function: "url_for(__('@contact_us'))"
    description: Contact us URL
    REGISTER_AGENT_URL:
    function: "url_for(__('@register_agent'))"
    description: Agent registration page URL
    REGISTER_EMAIL:
    function: "$sf_flash->get('reg_email')"
    description: Registration email
    LOGIN_URL:
    function: "url_for(__('@login'))"
        description: Login page URL
        NEWSLETTER_PROFILE_URL:
        function: "url_for(__('@newsletter_profile').'?conf_link='.$sf_request->getParameter('conf_link'))"
        description: Newsletter subscription profile URL
        USER_PROFILE_URL:
        function: "url_for(__('@profile').'?conf_link='.$sf_request->getParameter('conf_link'))"
        description: User email subscription profile URL
        LOGIN_EMAIL:
        function: "$sf_flash->get('login-email')"
        description: Login email address
        REGISTER_URL:
        function: "url_for(__('@register'))"
        description: Register page URL
        PASSWORD_RECOVER_URL:
        function: "url_for(__('@recover_password'))"
        description: Password recovery page URL
        LOGO:
        function: "get_logo()"
        description: Renders Web Product logo
        CULTURE:
        function: "get_culture()"
        description: Get two letter code for the current language
        CURRENCY:
        function: "get_currency()"
        description: Get three letter currency code
        COUNTRY:
        function: "get_geo()"
        description: Get two letter country code for the current user.
        COUNTRY_NAME:
        function: "format_country(get_geo())"
        description: Get country name for the current user.
        STATE:
        function: "get_geo_state()"
        description: Get two letter state code for the current user.
        REGISTER_ID:
        function: "get_register_id()"
        description: Gets Register Id string.
        SESS_ID:
        function: "get_enc_sess_id()"
        description: Gets Session Id string.
        CSRF_TOKEN:
        function: "get_csrf_token()"
        description: Gets CSRF token.
        PROTOCOL:
        function: "get_protocol()"
        description: Displays current protocol http/https.
        TAX_RATE:
        function: "tax_rate()"
        description: Displays current UK VAT rate.
        SMS_CHARGE:
        function: "currency_symbol(get_charge('sms'),0,true)"
        description: Displays charge for a SMS message.
        USER_CHARGE:
        function: "currency_symbol(get_charge('user'))"
        description: Displays monthly charge for a User account.
        SITEMAP_URL:
        function: "$sitemap_data['url']"
        description: Manual - name
        SITEMAP_TITLE:
        function: "$sitemap_data['title']"
        description: Manual - full title
        SUBSCRIBER_FIRST_NAME:
        function: "(isset($subscriber)) ? $subscriber->getFirstName() : '<!-- SUBSCRIBER_FIRST_NAME not defined -->'"
        description: User/Newsletter subscriber first name
        SUBSCRIBER_LAST_NAME:
        function: "(isset($subscriber)) ? $subscriber->getLastName() : '<!-- SUBSCRIBER_LAST_NAME not defined -->'"
        description: User/Newsletter subscriber last name
        SUBSCRIBER_EMAIL:
        function: "(isset($subscriber)) ? $subscriber->getEmail() : '<!-- SUBSCRIBER_EMAIL not defined -->'"
        description: User/Newsletter subscriber email
        # Manual
        MANUAL_NAME:
        function: "$manual_name"
        description: Manual - name
        MANUAL_TITLE:
        function: "$manual_title"
        description: Manual - full title
        MANUAL_DESCRIPTION:
        function: "$manual_description"
        description: Manual - chapter short description
        MANUAL_TOC:
        function: "$manual_toc"
        description: Manual - Table of Contents
        MANUAL_CHAPTER:
        function: "$manual_chapter"
        description: Manual - chapter title
        MANUAL_CONTENT:
        function: "$manual_content"
        description: Manual - content
        # Blog
        BLOG_PAGE_NO:
        function: "$pager->getPage()"
        description: Blog page number
        BLOG_TAG:
        function: "$tag"
        description: Blog tag
        BLOG_POST_URL:
        function: "url_for(__('@blog_post').'?url='.$post->getUrl())"
        description: Blog post URL
        BLOG_POST_TITLE:
        function: "$post->getTitle()"
        description: Blog post title
        BLOG_POST_DATE:
        function: "$post->getPublishedOn("Y-m-d")"
        description: Blog post date
        BLOG_POST_COMMENTS_COUNT:
        function: "$post->countPublishedComments()"
        description: Blog post comments count
        BLOG_POST_SHORT_TEXT:
        function: "($post->getSyntax() == 'M') ? convert_markdown_text($post->getShortForMarkdown()) : $post->getShortForHTML()"
        description: Blog post short text
        BLOG_PAGER_FIRST_PAGE_URL:
        function: "($tag) ? url_for(__('@blog_tag').'?tag='.$tag) : url_for(__('@blog'))"
        description: Blog pager first pag URL
        BLOG_PAGER_PREV_PAGE_URL:
        function: "blog_prev_page($pager, $tag)"
        description: Blog pager previous page URL
        BLOG_PAGER_PAGE_NO:
        function: "$page_no"
        description: Blog pager page number
        BLOG_PAGER_PAGE_URL:
        function: "blog_page($page_no, $tag)"
        description: Blog pager page URL
        BLOG_PAGER_NEXT_PAGE_URL:
        function: "($tag) ? url_for(__('@blog_tag_page').'?page='.$pager->getNextPage().'&tag='.$tag) : url_for(__('@blog_page').'?page='.$pager->getNextPage())"
        description: Blog pager next page
        BLOG_PAGER_LAST_PAGE_URL:
        function: "($tag) ? url_for(__('@blog_tag_page').'?page='.$pager->getLastPage().'&tag='.$tag) : url_for(__('@blog_page').'?page='.$pager->getLastPage())"
        description: Blog pager last page
        BLOG_TAG_URL:
        function: "url_for(__('@blog_tag').'?tag='.$tag->getTag()->getName())"
        description: Blog tag URL
        BLOG_TAG_NAME:
        function: "$tag->getTag()->getName()"
        description: Blog tag name
        POST_TAG_URL:
        function: "url_for(__('@blog_tag').'?tag='.$post_has_tag->getTag()->getName())"
        description: Blog post tag URL
        POST_TAG_NAME:
        function: "$post_has_tag->getTag()->getName()"
        description: Blog post tag name
        BLOG_URL:
        function: "url_for(__('@blog'))"
        description: Blog index URL
        BLOG_POST_DATE_WORD:
        function: "date("j M Y",$post->getCreatedAt(null))"
        description: Blog post date in words
        BLOG_COMMENT_AUTHOR:
        function: "$comment->getAuthor()"
        description: Blog comment author
        BLOG_COMMENT_DATE:
        function: "$comment->getCreatedAt()"
        description: Blog comment date
        BLOG_COMMENT_TEXT:
        function: "str_replace("\n","<br/>",esc_entities($comment->getContent()))"
        description: Blog comment text
        BLOG_POST_TEXT:
        function: "($post->getSyntax() == 'M') ? convert_markdown_text($post->getContentForMarkdown()) : $post->getContent()"
        description: Blog body
        v:
        UK_NUMBER:
        function: "currency_symbol(get_charge('UK-number'))"
        description: Displays monthly charge for a UK telephone number.
        INT_NUMBER:
        function: "currency_symbol(get_charge('Non-UK-number'))"
        description: Displays monthly charge for a international telephone number.
        USER_UNLIMITED:
        function: "currency_symbol(get_charge('user-S'))"
        description: Displays monthly charge for a Unlimited plan.
        USER_UNLIMITED_INT:
        function: "currency_symbol(get_charge('user-A'))"
        description: Displays monthly charge for a Unlimited International plan.
        # Fax
        FAX_NAME:
        function: "(isset($fax_data) && isset($fax_data['name'])) ? $fax_data['name'] : '<!-- FAX_NAME not defined -->'"
        description: Displays termination name rate.
        FAX_PREFIX:
        function: "(isset($fax_data) && isset($fax_data['prefix'])) ? $fax_data['prefix'] : '<!-- FAX_PREFIX not defined -->'"
        description: Displays termination name rate.
        FAX_PRICE:
        function: "(isset($fax_data) && isset($fax_data['price'])) ? currency_symbol($fax_data['price']) : '<!-- FAX_PRICE not defined -->'"
        description: Displays termination name rate.
        FAX_LETTER:
        function: "(isset($fax_firstletter)) ? $fax_firstletter : '<!-- FAX_LETTER not defined -->'"
        description: Displays termination route first name (if different from previous one).
        # Termination
        TERM_LOCAL_FIXED:
        function: "(isset($domestic) && isset($domestic['fixed'])) ? (($domestic['fixed'] * 100) <= 100) ? currency_symbol($domestic['fixed'],1,true) : currency_symbol($domestic['fixed']) : '<!-- TERM_LOCAL_FIXED not defined -->'"
        description: Displays fixed line termination cost for the current user.
        TERM_LOCAL_MOBILE:
        function: "(isset($domestic) && isset($domestic['mobile'])) ? (($domestic['mobile'] * 100) <= 100) ? currency_symbol($domestic['mobile'],1,true) : currency_symbol($domestic['mobile']) : '<!-- TERM_LOCAL_MOBILE not defined -->'"
        description: Displays fixed line termination cost for the current user.
        TERM_NAME:
        function: "(isset($term_name)) ? __($term_name) : '<!-- TERM_NAME not defined -->'"
        description: Displays termination name rate.
        TERM_PREFIXES:
        function: "(isset($term_data) && isset($term_data['patterns'])) ? implode(", ",$term_data['patterns']) : '<!-- TERM_PREFIXES not defined -->'"
        description: Displays per minute termination rate.
        TERM_MIN_PRICE:
        function: "(isset($term_data) && isset($term_data['rate'])) ? (($term_data['rate'] * 100) <= 100) ? currency_symbol($term_data['rate'],1,true) : currency_symbol($term_data['rate']) : '<!-- TERM_MIN_PRICE not defined -->'"
        description: Displays per minute termination rate.
        TERM_MIN_CHARGE:
        function: "(isset($min_charge)) ? currency_symbol($min_charge,1,true) : '<!-- TERM_MIN_CHARGE not defined -->'"
        description: Displays minimim per minute termination charge.
        TERM_LETTER:
        function: "(isset($firstletter)) ? $firstletter : '<!-- TERM_LETTER not defined -->'"
        description: Displays termination route first name (if different from previous one).
        TERM_MOBILE:
        function: "(($term_data['mobile'] * 100) <= 100) ? currency_symbol($term_data['mobile'],1,true) : currency_symbol($term_data['mobile'])"
        description: Displays mobile termination rate
        TERM_FIXED:
        function: "(($term_data['fixed'] * 100) <= 100) ? currency_symbol($term_data['fixed'],1,true) : currency_symbol($term_data['fixed'])"
        description: Displays fixed termination rate
        TERM_ROUTE_COUNTRY:
        function: "(isset($country_name)) ? __($country_name) : '<!-- TERM_ROUTE_COUNTRY not defined -->'"
        description: Termination route country
        TERM_ROUTE_NAME:
        function: "(isset($term_name)) ? __($term_name) : '<!-- TERM_ROUTE_NAME not defined -->'"
        description: Termination route name
        TERM_ROUTE_RATE:
        function: "(($term_data['rate'] * 100) <= 100) ? currency_symbol($term_data['rate'],1,true) : currency_symbol($term_data['rate'])"
        description: Displays termination route per minut rate
        TERM_ROUTE_CONN_FEE:
        function: "(($term_data['connection'] * 100) <= 100) ? currency_symbol($term_data['connection'],1,true) : currency_symbol($term_data['connection'])"
        description: Displays termination route  connection fee
        TERM_ROUTE_PREFIXES:
        function: "implode(", ",$term_data['patterns'])"
        description: Displays termination route prefixes
        TERM_ROUTE_URL:
        function: "url_for(__('@country_rates').'?country='.$country_name)"
        description: Displays fixed termination rate
        # DDIs
        DDI_COUNTRY_CODE:
        function: "strtolower($ddi_data['country_code'])"
        description: Displays DDI country two letter code
        DDI_COUNTRY_NAME:
        function: "$ddi_data['country_name']"
        description: Displays DDI country name
        DDI_COUNTRY_TEL_CODE:
        function: "$ddi_data['country_tel_code']"
        description: Displays DDI country telephone code
        DDI_AREA_CODE:
        function: "$ddi_data['area_code']"
        description: Displays DDI area code
        DDI_CITY_NAME:
        function: "$ddi_data['city']"
        description: Displays DDI city name
        DDI_COUNTRY_URL:
        function: "url_for(__('@numbers').'?country='.$ddi_data['country_name'])"
        description: Displays DDI country URL
        DDI_SETUP_FEE:
        function: "currency_symbol($ddi_data['NRC'])"
        description: Displays DDI setup fee
        DDI_MONTHLY_FEE:
        function: "currency_symbol($ddi_data['MRC'])"
        description: Displays DDI monthly subscription fee
        DDI_MIN_RATE:
        function: "currency_symbol($ddi_data['min_rate'])"
        description: Displays DDI per minute usage fee
        DDI_BUY_URL:
        function: "(isset($state)) ? url_for(__('@numbers_state').'?state='.$state) : url_for(__('@numbers').'?country='.$ddi_data['country_name'])"
        description: Displays buy DDI url
        DDI_STATE_URL:
        function: "(isset($state_data) && isset($state_data['state_name'])) ? url_for(__('@numbers_state').'?state='.ucwords(strtolower($state_data['state_name']))) : ' '"
        description: Displays buy DDI in selected U.S. state url
        DDI_STATE_NAME:
        function: "(isset($state_data) && isset($state_data['state_name'])) ? $state_data['state_name'] : ' '"
        description: Displays buy DDI in selected U.S. state url
        # Phones
        PHONE_DESK_URL:
        function: "url_for(__('@phones_group').'?group='.__('Desk Phones'))"
        description: Phone - desk category page URL
        PHONE_DESK_MIN_PRICE:
        function: "(isset($min_price)) ? currency_symbol($min_price['Desk Phones']) : '<!-- PHONE_DESK_MIN_PRICE not defined -->'"
        description: Phone - desk min. price
        PHONE_DECT_URL:
        function: "url_for(__('@phones_group').'?group='.__('DECT Phones'))"
        description:Phone - DECT category page URL
        PHONE_DECT_MIN_PRICE:
        function: "(isset($min_price)) ? currency_symbol($min_price['DECT Phones']) : '<!-- PHONE_DECT_MIN_PRICE not defined -->'"
        description: Phone - DECT min. price
        PHONE_CONF_URL:
        function: "url_for(__('@phones_group').'?group='.__('Conference Phones'))"
        description: Phone - conference category page URL
        PHONE_CONF_MIN_PRICE:
        function: "isset($min_price) ? currency_symbol($min_price['Conference Phones']) : '<!-- PHONE_CONF_MIN_PRICE not defined -->'"
        description: Phone - conference min. price
        PHONE_ADAPTOR_URL:
        function: "url_for(__('@phones_group').'?group='.__('VoIP Adaptors'))"
        description: Phone - adaptors category page URL
        PHONE_ADAPTOR_MIN_PRICE:
        function: "(isset($min_price)) ? currency_symbol($min_price['VoIP Adaptors']) : '<!-- PHONE_ADAPTOR_MIN_PRICE not defined -->'"
        description: Phone - adaptors min. price
        PHONE_ACCESSORY_URL:
        function: "url_for(__('@phones_group').'?group='.__('Accessories'))"
        description: Phone - accessories category page URL
        PHONE_ACCESSORY_MIN_PRICE:
        function: "currency_symbol($min_price['Accessories'])"
        description: Phone - accessories min. price
        PHONE_GROUP_NAME:
        function: "$sf_request->getParameter('group')"
        description: Phone - current group name
        PHONE_URL:
        function: "url_for(__('@phone_page').'?group='.$sf_request->getParameter('group').'&model='.$phone_data['name'])"
        description: Phone - URL to the current phone
        PHONE_NAME:
        function: "$phone_data['name']"
        description: Phone - name
        PHONE_MANUFACTURER:
        function: "$phone_data['manufacturer']"
        description: Phone - manufacturer
        PHONE_MODEL:
        function: "$phone_data['model']"
        description: Phone - model
        PHONE_SHORT_DESCRIPTION:
        function: "$phone_data['short_description']"
        description: Phone - short description
        PHONE_STOCK:
        function: "$phone_data['stock']"
        description: Phone - stock
        PHONE_PRICE:
        function: "currency_symbol($phone_data['price'])"
        description: Phone - price
        PHONE_THUMB_IMG:
        function: "$phone_data['thumb_img']"
        description: Phone - thumbnail image
        PHONE_IMG:
        function: "$phone_data['img']"
        description: Phone - image
        PHONE_GROUP_URL:
        function: "url_for(__('@phones_group').'?group='.$sf_request->getParameter('group'))"
        description: Phone - group URL
        PHONE_REVIEWS_COUNT:
        function: "$phone_data['review_count']"
        description: Phone - reviews count
        PHONE_RATING:
        function: "get_rating($phone_data['rating'])"
        description: Phone - ratings stars
        PHONE_REVIEWS_URL:
        function: "url_for(__('@phone_reviews').'?group='.$sf_request->getParameter('group').'&model='.$phone_data['name'])"
        description: Phone - reviews URL
        PHONE_DESCRIPTION:
        function: "str_replace('.pdf">','.pdf" target="_blank">',convert_markdown_text($phone_data['description']))"
        description: Phone - description

  */
}