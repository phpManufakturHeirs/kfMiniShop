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

class ActionArticle extends Basic
{
    protected static $config = null;
    protected static $parameter = null;

    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        $Configuration = new Configuration($app);
        self::$config = $Configuration->getConfiguration();


    }

    protected function showList()
    {
        $result = __METHOD__;

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

    public function Controller(Application $app)
    {
        $this->initParameters($app);

        // get the kitCommand parameters
        self::$parameter = $this->getCommandParameters();

        // check the CMS GET parameters
        $GET = $this->getCMSgetParameters();
        if (isset($GET['command']) && ($GET['command'] == 'minishop')
            && isset($GET['action']) && ($GET['action'] == 'article')) {
            // the command and parameters are set as GET from the CMS
            foreach ($GET as $key => $value) {
                if ($key == 'command') {
                    continue;
                }
                self::$parameter[$key] = $value;
            }
            $this->setCommandParameters(self::$parameter);
        }

        // check wether to use the flexcontent.css or not
        self::$parameter['load_css'] = (isset(self::$parameter['load_css']) && ((self::$parameter['load_css'] == 0) || (strtolower(self::$parameter['load_css']) == 'false'))) ? false : true;
        // disable the jquery check?
        self::$parameter['check_jquery'] = (isset(self::$parameter['check_jquery']) && ((self::$parameter['check_jquery'] == 0) || (strtolower(self::$parameter['check_jquery']) == 'false'))) ? false : true;

        return $this->showList();
    }
}
