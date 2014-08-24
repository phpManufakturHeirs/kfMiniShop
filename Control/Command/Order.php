<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Control\Command;

use Silex\Application;
use phpManufaktur\Basic\Control\kitCommand\Basic;
use phpManufaktur\miniShop\Control\Configuration;
use phpManufaktur\miniShop\Control\Command\Basket;
use phpManufaktur\miniShop\Data\Shop\Article as DataArticle;
use phpManufaktur\miniShop\Data\Shop\Base as DataBase;
use phpManufaktur\Contact\Control\Pattern\Form\Contact as ContactForm;

class Order extends Basic
{
    protected static $parameter = null;
    protected static $config = null;

    protected $Basket = null;
    protected $dataArticle = null;
    protected $dataBase = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\kitCommand\Basic::initParameters()
     */
    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        $Configuration = new Configuration($app);
        self::$config = $Configuration->getConfiguration();

        $this->Basket = new Basket($app);
        $this->dataArticle = new DataArticle($app);
        $this->dataBase = new DataBase($app);
    }


    protected function getAddressTypeForm()
    {
        return $this->app['form.factory']->createBuilder('form', null, array('csrf_protection' => false))
        ->add('form_action', 'hidden', array(
            'data' => 'address_type'
        ))
        ->add('order_for', 'choice', array(
            'choices' => array(
                'PERSON' => $this->app['translator']->trans('a private person'),
                'COMPANY' => $this->app['translator']->trans('a company or a organization')
            ),
            'expanded' => true,
            'data' => 'PERSON'
        ))
        ->getForm();
    }

    protected function CheckAddressType()
    {
        self::$parameter = $this->getCommandParameters();

        // check wether to use the minishop.css or not
        self::$parameter['load_css'] = (isset(self::$parameter['load_css']) && ((self::$parameter['load_css'] == 0) || (strtolower(self::$parameter['load_css']) == 'false'))) ? false : true;
        // disable the jquery check?
        self::$parameter['check_jquery'] = (isset(self::$parameter['check_jquery']) && ((self::$parameter['check_jquery'] == 0) || (strtolower(self::$parameter['check_jquery']) == 'false'))) ? false : true;

        // get submitted form data
        $query = $this->getCMSgetParameters();

        // get the current order from the basket
        $order = $this->Basket->CreateOrderDataFromBasket();

        $ContactForm = new ContactForm($this->app);
        $data = array(
            'contact_type' => isset($query['order']['order_for']) ? $query['order']['order_for'] : 'PERSON',
            'address_country_code' => $order['base']['locale']
        );

        $field = self::$config['contact']['field'];

        // important: switch off the CSRF protection!
        $field['csrf_protection'] = false;

        // get predefined special fields
        $special = isset($field['special']) ? $field['special'] : array();

        $special[] = array(
            'enabled' => true,
            'name' => 'form_action',
            'type' => 'hidden',
            'data' => 'check_contact'
        );
        $field['special'] = $special;

        if (false === ($form = $ContactForm->getFormContact($data, $field))) {
            throw new \Exception($this->getMessage());
        }

        $result = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'command/start.order.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory'],
                'form' => $form->createView(),
                'order' => $order,
                'shop_url' => CMS_URL.$order['base']['target_page_link']
            ));

        // set the parameters for jQuery and CSS
        $params = array();
        $params['library'] = null;
        if (self::$parameter['check_jquery']) {
            if (self::$config['libraries']['enabled'] &&
                !empty(self::$config['libraries']['jquery'])) {
                // load all predefined jQuery files for the miniShop
                foreach (self::$config['libraries']['jquery'] as $library) {
                    if (!empty($params['library'])) {
                        $params['library'] .= ',';
                    }
                    $params['library'] .= $library;
                }
            }
        }
        if (self::$parameter['load_css']) {
            if (self::$config['libraries']['enabled'] &&
            !empty(self::$config['libraries']['css'])) {
                // load all predefined CSS files for the miniShop
                foreach (self::$config['libraries']['css'] as $library) {
                    if (!empty($params['library'])) {
                        $params['library'] .= ',';
                    }
                    // attach to 'library' not to 'css' !!!
                    $params['library'] .= $library;
                }
            }

            // set the CSS parameter
            $params['css'] = 'miniShop,css/minishop.min.css,'.$this->getPreferredTemplateStyle();
        }

        return $this->app->json(array(
            'parameter' => $params,
            'response' => $result
        ));
    }

    public function ControllerOrder(Application $app)
    {
        $this->initParameters($app);

        // get submitted form data
        $query = $this->getCMSgetParameters();
        if (isset($query['order']['form_action'])) {
            // a form was submitted, call the desired function
            switch ($query['order']['form_action']) {
                case 'address_type':
                    return $this->CheckAddressType();
                default:
                    $this->setAlert('Ooops, unknown form action: %action%',
                        array('%action%' => $query['order']['form_action']), self::ALERT_TYPE_DANGER,
                        array(__METHOD__, __LINE__));
            }
        }

        self::$parameter = $this->getCommandParameters();

        // check wether to use the minishop.css or not
        self::$parameter['load_css'] = (isset(self::$parameter['load_css']) && ((self::$parameter['load_css'] == 0) || (strtolower(self::$parameter['load_css']) == 'false'))) ? false : true;
        // disable the jquery check?
        self::$parameter['check_jquery'] = (isset(self::$parameter['check_jquery']) && ((self::$parameter['check_jquery'] == 0) || (strtolower(self::$parameter['check_jquery']) == 'false'))) ? false : true;


        // get the current order from the basket
        $order = $this->Basket->CreateOrderDataFromBasket();

        $form = $this->getAddressTypeForm();

        $result = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'command/start.order.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory'],
                'form' => $form->createView(),
                'order' => $order,
                'shop_url' => CMS_URL.$order['base']['target_page_link']
            ));

        // set the parameters for jQuery and CSS
        $params = array();
        $params['library'] = null;
        if (self::$parameter['check_jquery']) {
            if (self::$config['libraries']['enabled'] &&
                !empty(self::$config['libraries']['jquery'])) {
                // load all predefined jQuery files for the miniShop
                foreach (self::$config['libraries']['jquery'] as $library) {
                    if (!empty($params['library'])) {
                        $params['library'] .= ',';
                    }
                    $params['library'] .= $library;
                }
            }
        }
        if (self::$parameter['load_css']) {
            if (self::$config['libraries']['enabled'] &&
            !empty(self::$config['libraries']['css'])) {
                // load all predefined CSS files for the miniShop
                foreach (self::$config['libraries']['css'] as $library) {
                    if (!empty($params['library'])) {
                        $params['library'] .= ',';
                    }
                    // attach to 'library' not to 'css' !!!
                    $params['library'] .= $library;
                }
            }

            // set the CSS parameter
            $params['css'] = 'miniShop,css/minishop.min.css,'.$this->getPreferredTemplateStyle();
        }

        return $this->app->json(array(
            'parameter' => $params,
            'response' => $result
        ));
    }
}
