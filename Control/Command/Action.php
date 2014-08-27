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

use phpManufaktur\Basic\Control\kitCommand\Basic;
use Silex\Application;
use phpManufaktur\miniShop\Control\Configuration;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

class Action extends Basic
{
    protected static $config = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\kitCommand\Basic::initParameters()
     */
    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        $ConfigurationData = new Configuration($app);
        self::$config = $ConfigurationData->getConfiguration();
    }

    /**
     * General controller for all miniShop actions
     *
     * @param Application $app
     * @return string
     */
    public function Controller(Application $app)
    {
        $this->initParameters($app);

        // get the kitCommand parameters
        $parameter = $this->getCommandParameters();

        // check wether to use the flexcontent.css or not
        $parameter['load_css'] = (isset($parameter['load_css']) && (($parameter['load_css'] == 0) || (strtolower($parameter['load_css']) == 'false'))) ? false : false;

        if (!isset($parameter['action'])) {
            // there is no 'action' parameter set, so we show the "Welcome" page
            $subRequest = Request::create('/basic/help/minishop/welcome', 'GET');
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        // check the CMS GET parameters
        $GET = $this->getCMSgetParameters();

        if (isset($GET['command']) && (strtolower($GET['command']) == 'minishop') &&
            isset($GET['action']) && (($GET['action'] == $parameter['action']) ||
            (($GET['action'] == 'article') && ($parameter['action'] == 'list')) ||
            (($GET['action'] == 'basket') && ($parameter['action'] == 'list')) ||
            (($GET['action'] == 'order') && ($parameter['action'] == 'list')) ||
            (($GET['action'] == 'guid') && ($parameter['action'] == 'list')) ||
            (($GET['action'] == 'send-guid') && ($parameter['action'] == 'list')))) {
            // the command and parameters are set as GET from the CMS
            foreach ($GET as $key => $value) {
                if (strtolower($key) == 'command') continue;
                $parameter[strtolower($key)] = $value;
            }
            $this->setCommandParameters($parameter);
            // create also a new parameter ID!
            $this->createParameterID($parameter);
        }

        switch (strtolower($parameter['action'])) {
            case 'list':
                $ActionList = new ActionList();
                return $ActionList->Controller($app);
            case 'article':
                $ActionArticle = new ActionArticle();
                return $ActionArticle->Controller($app);
            case 'basket':
                $Basket = new Basket();
                return $Basket->ControllerBasketView($app);
            case 'order':
                $Order = new Order();
                return $Order->ControllerOrder($app);
            case 'guid':
                $Order = new Order();
                return $Order->ControllerGUID($app);
            case 'send-guid':
                $Order = new Order();
                return $Order->ControllerSendGUID($app);
            default:
                $this->setAlert('The parameter <code>%parameter%[%value%]</code> for the kitCommand <code>~~ %command% ~~</code> is unknown, please check the parameter and the given value!',
                    array('%parameter%' => 'action', '%value%' => $parameter['action'], '%command%' => 'miniShop'), self::ALERT_TYPE_DANGER);
                return $this->promptAlert();
        }
    }
}
