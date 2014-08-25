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
use phpManufaktur\Contact\Data\Contact\CategoryType;
use phpManufaktur\miniShop\Control\Payment\AdvancePayment;
use phpManufaktur\miniShop\Control\Payment\OnAccount;
use phpManufaktur\miniShop\Control\Payment\PayPal;

class Order extends CommandBasic
{
    protected $Basket = null;

    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        $this->Basket = new Basket($app);
    }

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
                'data' => isset(self::$config['contact']['field']['default_value']['contact_type']) ? self::$config['contact']['field']['default_value']['contact_type'] : 'PERSON'
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
    protected function CheckAddressType($data=array())
    {

        // get submitted form data
        $query = $this->getCMSgetParameters();

        // get the current order from the basket
        $order = $this->Basket->CreateOrderDataFromBasket();

        // create a contact form
        $ContactForm = new ContactForm($this->app);

        // set default values for the contact form
        foreach (self::$config['contact']['field']['default_value'] as $key => $value) {
            if (!isset($data[$key])) {
                $data[$key] = $value;
            }
        }
        if (isset($query['order_for'])) {
            $data['contact_type'] = $query['order_for'];
        }

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
        $payments = array();
        foreach (explode(',', $order['base']['payment_methods']) as $payment) {
            if (!empty($payment)) {
                $payments[$payment] = $this->app['utils']->humanize($payment);
            }
        }
        $special[] = array(
            'enabled' => true,
            'name' => 'payment_method',
            'type' => 'choice',
            'choices' => $payments,
            'required' => true,
            'empty_value' => '- please select -',
            'read_only' => false,
            'expanded' => false,
            'multiple' => false,
            'preferred_choices' => array()
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
        $validation_errors = array();
        if (false === ($contact = $ContactForm->checkData($query['order'], self::$config['contact']['field'], true, $validation_errors))) {
            // the contact pattern will set an alert - go back to the first dialog
            return $this->selectAddressType();
        }

        $contact_id = $contact['contact']['contact_id'];

        if (!empty($validation_errors)) {
            // one or more values have not passed the validation
            return $this->CheckAddressType($query['order']);
        }

        if (($contact_id > 0) && ($contact['contact']['contact_status'] === 'LOCKED')) {
            // this contact record is LOCKED!
            $this->setAlert('Sorry, but we have a problem. Please contact the webmaster and tell him to check the status of the email address %email%.',
                array('%email%' => $query['order']['communication_email']), self::ALERT_TYPE_DANGER);
            return $this->selectAddressType();
        }

        // check if the tag #minishop exists
        if (!$this->app['contact']->existsTagName('MINISHOP')) {
            $this->app['contact']->createTagName('MINISHOP', 'Indicate that the contact has used the miniShop');
        }

        if (!isset($contact['category'][0]['category_name']) || ($contact['category'][0]['category_name'] === 'UNCHECKED') ||
            ($contact['category'][0]['category_name'] === 'NO_CATEGORY')) {
            // set the category CUSTOMER for this contact
            $dataCategoryType = new CategoryType($this->app);
            if (false !== ($category_type = $dataCategoryType->selectByName('CUSTOMER'))) {
                $contact['category'][0] = array(
                    'category_id' => -1,
                    'contact_id' => $contact_id,
                    'category_type_id' => $category_type['category_type_id'],
                    'category_type_name' => $category_type['category_type_name']
                );
            }
        }

        if ($contact_id > 0) {
            // update an existing contact record
            if (false === ($this->app['contact']->update($contact, $contact_id))) {
                return $this->CheckAddressType($query['order']);
            }

        }
        else {
            // insert a new contact record
            if (!$this->app['contact']->insert($contact, $contact_id)) {
                // problem insert the record - go back to the first dialog
                return $this->checkAddressType($query['order']);
            }
        }

        if (!$this->app['contact']->issetContactTag('MINISHOP', $contact_id)) {
            // set the tag #miniShop for this contact
            $this->app['contact']->setContactTag('MINISHOP', $contact_id);
        }

        if (!isset($query['order']['payment_method'])) {
            throw new \Exception('Missing the payment method!');
        }

        switch ($query['order']['payment_method']) {
            case 'ADVANCE_PAYMENT':
                $Payment = new AdvancePayment($this->app);
                return $Payment->startPayment($contact_id);
            case 'ON_ACCOUNT':
                $Payment = new OnAccount($this->app);
                return $Payment->startPayment($contact_id);
            case 'PAYPAL':
                $Payment = new PayPal($this->app);
                return $Payment->startPayment($contact_id);
            default:
                throw new \Exception('Unknown payment method '.$query['order']['payment']);
        }

        echo $query['order']['payment_method'];
        //print_r($query);

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

    protected function selectAddressType()
    {
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

        // start the order by selecting the address type
        return $this->selectAddressType();
    }
}
