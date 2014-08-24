<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Control;

use Silex\Application;
use phpManufaktur\Basic\Data\CMS\Settings;

class Configuration
{
    protected $app = null;
    protected static $config = null;
    protected static $config_path = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$config_path = MANUFAKTUR_PATH.'/miniShop/config.minishop.json';
        $this->readConfiguration();
    }


    /**
     * Return the default configuration array for the miniShop
     *
     * @return array
     */
    public function getDefaultConfigArray()
    {
        $cmsSettings = new Settings($this->app);
        $default_language = $cmsSettings->getSetting('default_language');

        return array(
            'nav_tabs' => array(
                'order' => array(
                    'article',
                    'group',
                    'base',
                    'about'
                ),
                'default' => 'article'
            ),
            'locale' => array(
                'EN',
                'DE'
            ),
            'currency' => array(
                'EUR' => array(
                    'name' => 'Euro',
                    'iso' => 'EUR',
                    'symbol' => '€'
                ),
                'USD' => array(
                    'name' => 'US-Dollar',
                    'iso' => 'USD',
                    'symbol' => '$'
                ),
                'CHF' => array(
                    'name' => 'Schweizer Franken',
                    'iso' => 'CHF',
                    'symbol' => 'SFr.'
                ),
                'GBP' => array(
                    'name' => 'Pound Sterling',
                    'iso' => 'GBP',
                    'symbol' => '£'
                )
            ),
            'images' => array(
                'directory' => array(
                    'start' => '/media/public',
                    'select' => '/media/public/shop'
                 ),
                'extension' => array(
                    '*.jpg',
                    '*.jpeg',
                    '*.png',
                    '*.JPG',
                    '*.JPEG',
                    '*.PNG'
                )
            ),
            'permanentlink' => array(
                'directory' => '/shop'
            ),
            'libraries' => array(
                'enabled' => true,
                'jquery' => array(
                    'jquery/jquery/latest/jquery.min.js',
                    'bootstrap/latest/js/bootstrap.min.js',
                    'jquery/lightbox/latest/js/lightbox.min.js'
                ),
                'css' => array(
                    'bootstrap/latest/css/bootstrap.min.css',
                    'font-awesome/latest/css/font-awesome.min.css',
                    'jquery/lightbox/latest/css/lightbox.css'
                )
            ),
            'contact' => array(
                    'field' => array(
                        'predefined' => array(
                            'contact_type'
                        ),
                        'visible' => array(
                            'person_gender',
                            'person_first_name',
                            'person_last_name',
                            'company_name',
                            'company_department',
                            'communication_email',
                            'communication_phone',
                            'address_street',
                            'address_zip',
                            'address_city',
                            'address_country_code',
                            'extra_fields',
                            'special_fields'
                        ),
                        'required' => array(
                            'person_gender',
                            'person_last_name',
                            'company_name',
                            'address_street',
                            'address_zip',
                            'address_city',
                            'address_country_code',
                        ),
                        'hidden' => array(
                            'contact_id',
                            'contact_type',
                            'category_id',
                            'category_type_id',
                            'person_id',
                            'company_id',
                            'address_id'
                        ),
                        'readonly' => array(
                            'contact_status',
                            'category_name'
                        ),
                        'tags' => array(
                        ),
                        'default_value' => array(
                            'contact_type' => 'PERSON',
                            'person_gender' => 'MALE',
                            'address_country_code' => 'DE'
                        )
                    )
                )
        );
    }

    /**
     * Read the configuration file
     */
    protected function readConfiguration()
    {
        if (!file_exists(self::$config_path)) {
            self::$config = $this->getDefaultConfigArray();
            $this->saveConfiguration();
        }
        self::$config = $this->app['utils']->readConfiguration(self::$config_path);
    }

    /**
     * Save the configuration file
     */
    public function saveConfiguration()
    {
        // write the formatted config file to the path
        file_put_contents(self::$config_path, $this->app['utils']->JSONFormat(self::$config));
        $this->app['monolog']->addDebug('Save configuration to '.basename(self::$config_path));
    }

    /**
     * Get the configuration array
     *
     * @return array
     */
    public function getConfiguration()
    {
        return self::$config;
    }

    /**
     * Set the configuration array
     *
     * @param array $config
     */
    public function setConfiguration($config)
    {
        self::$config = $config;
    }

}
