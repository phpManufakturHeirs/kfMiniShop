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
use phpManufaktur\miniShop\Data\Shop\Article as DataArticle;
use phpManufaktur\miniShop\Data\Shop\Base as DataBase;
use phpManufaktur\miniShop\Data\Shop\Group as DataGroup;

class ActionList extends Basic
{
    protected static $config = null;
    protected static $parameter = null;
    protected $dataArticle = null;
    protected $dataBase = null;
    protected $dataGroup = null;
    protected $Basket = null;

    protected function initParameters(Application $app, $parameter_id=-1)
    {
        parent::initParameters($app, $parameter_id);

        $Configuration = new Configuration($app);
        self::$config = $Configuration->getConfiguration();

        $this->dataArticle = new DataArticle($app);
        $this->dataBase = new DataBase($app);
        $this->dataGroup = new DataGroup($app);
        $this->Basket = new Basket($app);
    }

    protected function showList()
    {
        $result = null;

        $base = null;
        $groups = null;

        if (!is_null(self::$parameter['groups'])) {
            // show the list for a article group
            $checks = strpos(self::$parameter['groups'], ',') ? explode(',', self::$parameter['groups']) : array(self::$parameter['groups']);
            $groups = array();
            foreach ($checks as $check) {
                $check = trim($check);
                if (!$this->dataGroup->existsName($check)) {
                    $this->setAlert('The article group <strong>%group%</strong> does not exists, please check the kitCommand!',
                        array('%group%' => $check), self::ALERT_TYPE_DANGER);
                    continue;
                }
                else {
                    $groups[] = $check;
                }
            }
        }

        if (!is_null(self::$parameter['base'])) {
            if (!$this->dataBase->existsName(self::$parameter)) {
                $this->setAlert('The base configuration <strong>%base%</strong> does not exists, please check the kitCommand!',
                    array('%base%', self::$parameter['base']));
            }
            else {
                $base = self::$parameter['base'];
            }
        }

        if (is_null($groups) && is_null($base)) {
            if ($this->dataBase->countActive() > 1) {
                $this->setAlert('There exists more than one base configurations, so you must set a base or a group as parameter!',
                    array(), self::ALERT_TYPE_DANGER);
            }
            elseif (false !== ($base_config = $this->dataBase->selectAllActive())) {
                $base = $base_config[0]['name'];
                if (false !== ($active_groups = $this->dataGroup->selectAllActiveByBase($base))) {
                    $groups = array();
                    foreach ($active_groups as $group) {
                        $groups[] = $group['name'];
                    }
                }
            }
        }

        $articles = null;

        if (!is_null($groups)) {
            $articles = $this->dataArticle->selectByGroup($groups);
        }

        $result = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'command/list.article.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'config' => self::$config,
                'parameter' => self::$parameter,
                'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory'],
                'articles' => $articles,
                'basket' => $this->Basket->getBasket()
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

    public function Controller(Application $app)
    {
        $this->initParameters($app);

        // get the kitCommand parameters
        self::$parameter = $this->getCommandParameters();

        // check the CMS GET parameters
        $GET = $this->getCMSgetParameters();
        if (isset($GET['command']) && ($GET['command'] == 'minishop')
            && isset($GET['action']) && ($GET['action'] == 'list')) {
            // the command and parameters are set as GET from the CMS
            foreach ($GET as $key => $value) {
                if ($key == 'command') {
                    continue;
                }
                self::$parameter[$key] = $value;
            }
            $this->setCommandParameters(self::$parameter);
        }

        // check wether to use the minishop.css or not
        self::$parameter['load_css'] = (isset(self::$parameter['load_css']) && ((self::$parameter['load_css'] == 0) || (strtolower(self::$parameter['load_css']) == 'false'))) ? false : true;
        // disable the jquery check?
        self::$parameter['check_jquery'] = (isset(self::$parameter['check_jquery']) && ((self::$parameter['check_jquery'] == 0) || (strtolower(self::$parameter['check_jquery']) == 'false'))) ? false : true;

        self::$parameter['groups'] = (isset(self::$parameter['groups']) && !empty(self::$parameter['groups'])) ? self::$parameter['groups'] : null;
        self::$parameter['base'] = (isset(self::$parameter['base']) && !empty(self::$parameter['base'])) ? self::$parameter['base'] : null;

        self::$parameter['limit'] = (isset(self::$parameter['limit']) && is_numeric(self::$parameter['limit'])) ? intval(self::$parameter['limit']) : -1;
        self::$parameter['order_by'] = (isset(self::$parameter['order_by'])) ? strtolower(self::$parameter['order_by']) : 'article_name';
        self::$parameter['order_direction'] = isset(self::$parameter['order_direction']) ? strtoupper(self::$parameter['order_direction']) : 'ASC';

        if (isset(self::$parameter['status']) && !empty(self::$parameter['status'])) {
            if (strpos(self::$parameter['status'], ',')) {
                $status = array();
                $stats = explode(',', self::$parameter['status']);
                foreach ($stats as $stat) {
                    $stat = trim($stat);
                    if (!empty($stat)) {
                        $status[] = $stat;
                    }
                }
            }
            else {
                $status = array(trim(self::$parameter['status']));
            }
            self::$parameter['status'] = $status;
        }

        return $this->showList();
    }
}
