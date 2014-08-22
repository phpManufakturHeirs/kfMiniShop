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
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class ActionArticle extends Basic
{
    protected static $config = null;
    protected static $parameter = null;

    protected $dataArticle = null;
    protected $dataGroup = null;
    protected $dataBase = null;
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

        if (isset(self::$parameter['alert'])) {
            $this->setAlertUnformatted(base64_decode(self::$parameter['alert']));
        }

        // check wether to use the flexcontent.css or not
        self::$parameter['load_css'] = (isset(self::$parameter['load_css']) && ((self::$parameter['load_css'] == 0) || (strtolower(self::$parameter['load_css']) == 'false'))) ? false : true;
        // disable the jquery check?
        self::$parameter['check_jquery'] = (isset(self::$parameter['check_jquery']) && ((self::$parameter['check_jquery'] == 0) || (strtolower(self::$parameter['check_jquery']) == 'false'))) ? false : true;

        self::$parameter['id'] = (isset(self::$parameter['id']) && is_numeric(self::$parameter['id'])) ? intval(self::$parameter['id']) : null;

        self::$parameter['groups'] = (isset(self::$parameter['groups']) && !empty(self::$parameter['groups'])) ? self::$parameter['groups'] : null;
        self::$parameter['base'] = (isset(self::$parameter['base']) && !empty(self::$parameter['base'])) ? self::$parameter['base'] : null;

        self::$parameter['image_max_width'] = (isset(self::$parameter['image_max_width']) && is_numeric(self::$parameter['image_max_width'])) ? intval(self::$parameter['image_max_width']) : 200;
        self::$parameter['image_max_height'] = (isset(self::$parameter['image_max_height']) && is_numeric(self::$parameter['image_max_height'])) ? intval(self::$parameter['image_max_height']) : 200;

    }

    protected function getOrderForm($data=array())
    {

        $title_quantity = $this->app['translator']->trans('Quantity to order');

        $form = $this->app['form.factory']->createBuilder('form', null, array('csrf_protection' => false))
        ->add('id', 'hidden', array(
            'data' => isset($data['id']) ? $data['id'] : -1
        ))
        ->add('article_name', 'hidden', array(
            'data' => isset($data['article_name']) ? $data['article_name'] : ''
        ))
        ->add('permanent_link', 'hidden', array(
            'data' => isset($data['permanent_link']) ? $data['permanent_link'] : ''
        ))
        ->add('quantity', 'number', array(
            'data' => isset($data['quantity']) ? $data['quantity'] : 1,
            'label' => 'Quantity',
            'attr' => array(
                'class' => 'form-control input-sm',
                'title' => $title_quantity
            )
        ));

        if (isset($data['article_variant_values']) && !empty($data['article_variant_values']) &&
            isset($data['article_variant_name']) && !empty($data['article_variant_name'])) {

            $variant_values = array();
            $items = explode("\r\n", $data['article_variant_values']);
            foreach ($items as $item) {
                if (!empty(trim($item))) {
                    $variant_values[] = trim($item);
                }
            }
            if (!empty($variant_values)) {
                $empty_value = $this->app['translator']->trans($data['article_variant_name']);
                $empty_value = sprintf('- %s -', $empty_value);
                $form->add('article_variant_name', 'hidden', array(
                    'data' => $data['article_variant_name']
                ))
                ->add('article_variant_values', 'choice', array(
                    'choices' => $variant_values,
                    'empty_value' => $empty_value,
                    'attr' => array(
                        'class' => 'form-control input-sm'
                    )
                ));
            }
        }
        else {
            $form->add('article_variant_name', 'hidden')
            ->add('article_variant_values', 'hidden');
        }

        if (isset($data['article_variant_values_2']) && !empty($data['article_variant_values_2']) &&
            isset($data['article_variant_name_2']) && !empty($data['article_variant_name_2'])) {
            $variant_values = array();
            $items = explode("\r\n", $data['article_variant_values_2']);
            foreach ($items as $item) {
                if (!empty(trim($item))) {
                    $variant_values[] = trim($item);
                }
            }
            if (!empty($variant_values)) {
                $empty_value = $this->app['translator']->trans($data['article_variant_name_2']);
                $empty_value = sprintf('- %s -', $empty_value);
                $form->add('article_variant_name_2', 'hidden', array(
                    'data' => $data['article_variant_name_2']
                ))
                ->add('article_variant_values_2', 'choice', array(
                    'choices' => $variant_values,
                    'empty_value' => $empty_value,
                    'attr' => array(
                        'class' => 'form-control input-sm'
                    )
                ));
            }
        }
        else {
            $form->add('article_variant_name_2', 'hidden')
            ->add('article_variant_values_2', 'hidden');
        }

        return $form->getForm();
    }

    protected function showArticle()
    {

        $article = null;
        $base = null;
        $groups = null;
        $shop_url = null;

        if (is_null(self::$parameter['id'])) {
            $this->setAlert('Please submit a article ID!', array(), self::ALERT_TYPE_DANGER);
        }
        elseif (false === ($article = $this->dataArticle->select(self::$parameter['id']))) {
            $this->setAlert('The record with the ID %id% does not exists!',
                array('%id%' => self::$parameter['id']), self::ALERT_TYPE_DANGER);
        }

        if (is_array($article)) {
            $article['folder_images'] = null;
            if ($article['article_image_folder_gallery'] == 1) {
                // loop through the folder and gather the images
                $folder_images = array();
                $main_image = pathinfo($article['article_image'], PATHINFO_BASENAME);
                $directory = pathinfo(FRAMEWORK_PATH.$article['article_image'], PATHINFO_DIRNAME);
                $images = new Finder();
                $images->files()->in($directory)->sortByName();
                foreach (self::$config['images']['extension'] as $extension) {
                    $images->name($extension);
                }
                foreach ($images as $image) {
                    if ($image->getFilename() !== $main_image) {
                        $realpath = $image->getRealPath();
                        if (strpos($realpath, realpath(CMS_MEDIA_PATH)) === 0) {
                            $img = CMS_URL.substr($realpath, strlen(realpath(CMS_PATH)));
                        }
                        else {
                            $img = FRAMEWORK_URL.substr($image->getRealPath(), strlen(realpath(FRAMEWORK_PATH)));
                        }
                        $folder_images[] = str_replace('\\','/', $img);
                    }
                }
                $article['folder_images'] = $folder_images;
            }

            if (false !== ($base = $this->dataBase->select($article['base_id']))) {
                $shop_url = CMS_URL.$base['target_page_link'];
            }
        }

        $form = $this->getOrderForm($article);

        $result = $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'command/view.article.twig',
            $this->getPreferredTemplateStyle()),
            array(
                'basic' => $this->getBasicSettings(),
                'config' => self::$config,
                'parameter' => self::$parameter,
                'permalink_base_url' => CMS_URL.self::$config['permanentlink']['directory'],
                'article' => $article,
                'groups' => $groups,
                'base' => $base,
                'shop_url' => $shop_url,
                'form' => $form->createView(),
                'basket' => $this->Basket->getBasket()
            ));

        // set the parameters for jQuery and CSS
        $params = array();
        if (isset($article['id'])) {
            // set the page header and the canonical link
            $params['set_header'] = $article['id'];
            $params['canonical'] = $article['id'];
        }
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

        $response = new Response();
        $response->headers->setCookie(new Cookie('minishop', 'test'));
        return $this->app->json(array(
            'parameter' => $params,
            'response' => $result
        ));
    }

    public function Controller(Application $app)
    {
        $this->initParameters($app);

        return $this->showArticle();
    }

    /**
     * add/remove articles to the shopping basket
     *
     * @param Application $app
     * @throws \Exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ControllerBasketAdd(Application $app)
    {
        $this->initParameters($app);

        // we need the route to the article
        $request_form = $this->app['request']->request->get('form');
        if (!isset($request_form['permanent_link']) || empty($request_form['permanent_link'])) {
            throw new \Exception('Invalid form submission, stopped script.');
        }
        $subdirectory = parse_url(CMS_URL, PHP_URL_PATH);
        $article_route = $subdirectory.self::$config['permanentlink']['directory'].'/article/'.$request_form['permanent_link'];

        // now we handle the order form
        $form = $this->getOrderForm();
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();
            // update the basket with the article data
            $this->Basket->updateBasket($data);
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
        }

        $subRequest = Request::create($article_route, 'GET', array(
            'command' => 'minishop',
            'action' => 'article',
            'id' => $request_form['id'],
            'robots' => 'noindex,follow',
            'canonical' => CMS_URL.$article_route,
            'alert' => base64_encode($this->getAlert())
        ));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
