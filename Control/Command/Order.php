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
use phpManufaktur\Contact\Control\Pattern\Form\Contact as ContactForm;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;

class Order extends CommandBasic
{

    /**
     * Get the form to select the address type for the order
     *
     * @return FormFactory
     */
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

    /**
     * Controller to check the Address type and show the contact form for the
     * next step in order
     *
     * @throws \Exception
     * @return JsonResponse
     */
    protected function CheckAddressType()
    {

        // get submitted form data
        $query = $this->getCMSgetParameters();

        // get the current order from the basket
        $order = $this->Basket->CreateOrderDataFromBasket();

        // create a contact form
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

        // get the params to autoload jQuery and CSS
        $params = $this->getResponseParameter();

        return $this->app->json(array(
            'parameter' => $params,
            'response' => $result
        ));
    }

    /**
     * Controller to check the submitted contact. Insert or update the contact
     * record and got to the next step: select the payment method
     *
     * @return JsonResponse
     */
    protected function CheckContact()
    {
        // get submitted form data
        $query = $this->getCMSgetParameters();

        // get the current order from the basket
        $order = $this->Basket->CreateOrderDataFromBasket();

        $ContactForm = new ContactForm($this->app);
        if (false === ($contact = $ContactForm->checkData($query['order'], self::$config['contact']['field']))) {
            $this->setAlert('Something went terribly wrong ...');
            return $this->CheckAddressType();
        }



        $result = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'command/start.order.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'alert' => $this->getAlert(),
                'config' => self::$config,
                'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory'],
                //'form' => $form->createView(),
                'order' => $order,
                'shop_url' => CMS_URL.$order['base']['target_page_link']
            ));

        // get the params to autoload jQuery and CSS
        $params = $this->getResponseParameter();

        return $this->app->json(array(
            'parameter' => $params,
            'response' => $result
        ));
    }

    /**
     * General Controller for the order form - check the submitted form actions
     * and return the desired control
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
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
                case 'check_contact':
                    return $this->CheckContact();
                default:
                    $this->setAlert('Ooops, unknown form action: %action%',
                        array('%action%' => $query['order']['form_action']), self::ALERT_TYPE_DANGER,
                        array(__METHOD__, __LINE__));
            }
        }

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

        // get the params to autoload jQuery and CSS
        $params = $this->getResponseParameter();

        return $this->app->json(array(
            'parameter' => $params,
            'response' => $result
        ));
    }
}
