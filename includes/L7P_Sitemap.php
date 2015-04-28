<?php
/*
 * This file is part of the Level 7 Systems Ltd. platform.
 *
 * (c) Kamil Adryjanek <kamil@level7systems.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Description of L7P_Sitemap
 *
 * @author kamil
 */
class L7P_Sitemap
{

    public function __construct()
    {
        // build Sitemap
        add_action("sm_buildmap", array($this, "build_sitemap"));
    }

    public function build_sitemap()
    {
        if (class_exists('GoogleSitemapGenerator')) {
            $generatorObject = &GoogleSitemapGenerator::GetInstance();

            if ($generatorObject === null) {
                return;
            }

            $countries = l7p_get_countries($culture);
            
            // rates
            foreach ($countries as $country_code => $country_name) {
                $generatorObject->AddUrl(l7p_url_for('@country_rates', array('country' => $country_name, 'currency' => ''), true), time(), "weekly", 0.5);
            }
            
            // phone numbers
            foreach ($countries as $country_code => $country_name) {
                
                $generatorObject->AddUrl(l7p_url_for('@numbers', array('country' => $country_name, 'currency' => ''), true), time(), "weekly", 0.5);

                if ($country_code == 'US') {
                    $states = l7p_get_states();
                    foreach ($states as $state_code => $state_name) {
                        $generatorObject->AddUrl(l7p_url_for('@numbers_state', array('country' => $country_name, 'state' => $state_name, 'currency' => ''), true), time(), "weekly", 0.5);
                    }
                }
            }

            // hardware
            $phones = l7p_get_phones();
            foreach ($phones as $phone_group => $phones_from_group) {
                $generatorObject->AddUrl(l7p_url_for('@phones_group', array('group' => $phone_group, 'currency' => ''), true), time(), "weekly", 0.5);
                foreach ($phones_from_group as $phone) {
                    $generatorObject->AddUrl(l7p_url_for('@phone_page', array('group' => $phone_group, 'model' => $phone['name'], 'currency' => ''), true), time(), "weekly", 0.5);
                }
            }

            // manual
            $chapters = l7p_get_chapters();
            foreach ($chapters as $manual_type => $chapters) {
                foreach ($chapters as $chapter_name => $chapter) {
                    $generatorObject->AddUrl(l7p_url_for('@manual', array('chapter' => $manual_type . '_' . $chapter_name), true), time(), "weekly", 0.5);
                }
            }
        }
    }
}

return new L7P_Sitemap();
