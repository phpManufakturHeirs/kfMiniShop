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
use phpManufaktur\miniShop\Control\Configuration;

class PermanentLink
{
    protected $app = null;

    protected static $config = null;

    /**
     * Initialize the class
     *
     * @param Application $app
     */
    protected function initialize(Application $app)
    {
        $this->app = $app;

        $Config = new Configuration($app);
        self::$config = $Config->getConfiguration();

    }

    /**
     * Execute cURL to catch the CMS content into the permanent link
     *
     * @param string $url
     * @return mixed
     */
    protected function cURLexec($url, $page_id)
    {
        // init cURL
        $ch = curl_init();

        // set the general cURL options
        $options = array(
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'kitFramework::flexContent',
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        );

        if (is_null($this->app['session']->get('MINISHOP_COOKIE_FILE')) ||
            !$this->app['filesystem']->exists($this->app['session']->get('MINISHOP_COOKIE_FILE'))) {
            // this is the first call of this cURL session, create a cookie file
            $this->app['session']->set('MINISHOP_COOKIE_FILE', FRAMEWORK_TEMP_PATH.'/session/'.uniqid('minishop_'));
            $options[CURLOPT_COOKIEJAR] = $this->app['session']->get('MINISHOP_COOKIE_FILE');
        }
        else {
            // load the existing cookie file
            $options[CURLOPT_COOKIEFILE] = $this->app['session']->get('MINISHOP_COOKIE_FILE');
        }

        // get the visibility of the target page
        $visibility = $this->PageData->getPageVisibilityByPageID($page_id);
        if (in_array($visibility, array('none', 'registered', 'private'))) {
            // page can not be shown!
            $error = 'The visibility of the requested page is "none", can not show the content!';
            $this->app['monolog']->addError($error, array(__METHOD__, __LINE__));
            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'kitcommand/bootstrap/noframe/alert.twig'),
                array(
                    'content' => $this->app['translator']->trans($error),
                    'type' => 'alert-danger'));
        }

        // add the URL to the options
        $options[CURLOPT_URL] = $url;

        curl_setopt_array($ch, $options);

        // set proxy if needed
        $this->app['utils']->setCURLproxy($ch);

        if (false === ($result = curl_exec($ch))) {
            // cURL error
            $error = 'cURL error: '.curl_error($ch);
            $this->app['monolog']->addError($error, array(__METHOD__, __LINE__));
            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'kitcommand/bootstrap/noframe/alert.twig'),
                array(
                    'content' => $error,
                    'type' => 'alert-danger'));
        }

        if (!curl_errno($ch)) {
            $info = curl_getinfo($ch);
            if ($info['http_code'] > 299) {
                // bad request
                $error = 'Error - HTTP Status Code: '.$info['http_code'].' - '.$url;
                $this->app['monolog']->addError($error, array(__METHOD__, __LINE__));
                return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                    '@phpManufaktur/Basic/Template', 'kitcommand/bootstrap/noframe/alert.twig'),
                    array(
                        'content' => $error,
                        'type' => 'alert-danger'));
            }
        }
        curl_close($ch);
        return $result;
    }


    /**
     * Redirect to the target URL to show the category content
     *
     * @return string
     */
    protected function redirectToCategoryID()
    {
        if (false === ($category = $this->CategoryTypeData->select(self::$category_id, self::$language))) {
            // the category ID does not exists!
            $this->app['monolog']->addError('The flexContent category ID '.self::$category_id." does not exists.",
                array(__METHOD__, __LINE__));
            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'kitcommand/bootstrap/noframe/alert.twig'),
                array(
                    'content' => $this->app['translator']->trans('There is no category assigned to this pemanent link!'),
                    'type' => 'alert-danger'));
        }

        // get the CMS page link from the target link
        $link = substr($category['target_url'], strlen($this->PageData->getPageDirectory()), (strlen($this->PageData->getPageExtension()) * -1));

        if (false === ($page_id = $this->PageData->getPageIDbyPageLink($link))) {
            // the page does not exists!
            $this->app['monolog']->addError('The CMS page for the page link '.$link.' does not exists!', array(__METHOD__, __LINE__));
            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'kitcommand/bootstrap/noframe/alert.twig'),
                array(
                    'content' => $this->app['translator']->trans('The target URL assigned to this permanent link does not exists!'),
                    'type' => 'alert-danger'));
        }

        if ((false === ($lang_code = $this->PageData->getPageLanguage($page_id))) || (self::$language != strtolower($lang_code))) {
            // the page does not support the needed language!
            $error = 'The CMS target page does not support the needed language <strong>'.self::$language.'</strong> for this permanent link!';
            $this->app['monolog']->addError(strip_tags($error), array(__METHOD__, __LINE__, self::$content_id));
            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'kitcommand/bootstrap/noframe/alert.twig'),
                array(
                    'content' => $error,
                    'type' => 'alert-danger'));
        }

        if (!$this->PageData->existsCommandAtPageID('flexcontent', $page_id)) {
            // the page exists but does not contain the needed kitCommand
            $this->app['monolog']->addError('The CMS target URL does not contain the needed kitCommand!', array(__METHOD__, __LINE__));
            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'kitcommand/bootstrap/noframe/alert.twig'),
                array(
                    'content' => $this->app['translator']->trans('The CMS target URL does not contain the needed kitCommand!'),
                    'type' => 'alert-danger'));
        }

        // create the parameter array
        $parameter = array(
            'command' => 'flexcontent',
            'action' => 'category',
            'category_id' => self::$category_id,
            'content_id' => self::$content_id,
            'language' => strtolower(self::$language),
            'robots' => self::$config['kitcommand']['permalink']['category']['robots'],
            'canonical' => $this->Tools->getPermalinkBaseURL(self::$language).'/category/'.$category['category_permalink']
        );

        if (self::$config['search']['result']['highlight'] &&
            (null !== ($searchresult = $this->app['request']->query->get('searchresult'))) &&
            (null !== ($sstring = $this->app['request']->query->get('sstring')))) {
            // create a highlight array
            $highlight = array();
            if ($searchresult == 1) {
                if (false !== strpos($sstring, '+')) {
                    $words = explode('+', $sstring);
                    foreach ($words as $word) {
                        $highlight[] = $word;
                    }
                }
                else {
                    $highlight[] = $sstring;
                }
            }
            else {
                $highlight[] = str_replace('_', ' ', $sstring);
            }
            $parameter['highlight'] = $highlight;
        }

        $gets = $this->app['request']->query->all();
        foreach ($gets as $key => $value) {
            if (!key_exists($key, $parameter) && !in_array($key, self::$ignore_parameters)) {
                // pass all other parameters to the target page
                $parameter[$key] = $value;
            }
        }

        // create the target URL and set the needed parameters
        $target_url = CMS_URL.$category['target_url'].'?'.http_build_query($parameter, '', '&');

        return $this->cURLexec($target_url, $page_id);
    }

    /**
     * Redirect to the target URL to show the FAQ content
     *
     * @return string
     */
    protected function redirectToFAQID()
    {
        if (false === ($category = $this->CategoryTypeData->select(self::$category_id, self::$language))) {
            // the category ID does not exists!
            $this->app['monolog']->addError('The flexContent category ID '.self::$category_id." does not exists.",
                array(__METHOD__, __LINE__));
            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'kitcommand/bootstrap/noframe/alert.twig'),
                array(
                    'content' => $this->app['translator']->trans('There is no category assigned to this pemanent link!'),
                    'type' => 'alert-danger'));
        }

        // get the CMS page link from the target link
        $link = substr($category['target_url'], strlen($this->PageData->getPageDirectory()), (strlen($this->PageData->getPageExtension()) * -1));

        if (false === ($page_id = $this->PageData->getPageIDbyPageLink($link))) {
            // the page does not exists!
            $this->app['monolog']->addError('The CMS page for the page link '.$link.' does not exists!', array(__METHOD__, __LINE__));
            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'kitcommand/bootstrap/noframe/alert.twig'),
                array(
                    'content' => $this->app['translator']->trans('The target URL assigned to this permanent link does not exists!'),
                    'type' => 'alert-danger'));
        }

        if ((false === ($lang_code = $this->PageData->getPageLanguage($page_id))) || (self::$language != strtolower($lang_code))) {
            // the page does not support the needed language!
            $error = 'The CMS target page does not support the needed language <strong>'.self::$language.'</strong> for this permanent link!';
            $this->app['monolog']->addError(strip_tags($error), array(__METHOD__, __LINE__, self::$content_id));
            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'kitcommand/bootstrap/noframe/alert.twig'),
                array(
                    'content' => $error,
                    'type' => 'alert-danger'));
        }

        if (!$this->PageData->existsCommandAtPageID('flexcontent', $page_id)) {
            // the page exists but does not contain the needed kitCommand
            $this->app['monolog']->addError('The CMS target URL does not contain the needed kitCommand!', array(__METHOD__, __LINE__));
            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'kitcommand/bootstrap/noframe/alert.twig'),
                array(
                    'content' => $this->app['translator']->trans('The CMS target URL does not contain the needed kitCommand!'),
                    'type' => 'alert-danger'));
        }

        // create the parameter array
        $parameter = array(
            'command' => 'flexcontent',
            'action' => 'faq',
            'category_id' => self::$category_id,
            'language' => strtolower(self::$language),
            'robots' => self::$config['kitcommand']['permalink']['faq']['robots'],
            'canonical' => $this->Tools->getPermalinkBaseURL(self::$language).'/faq/'.$category['category_permalink']
        );

        if (self::$config['search']['result']['highlight'] &&
            (null !== ($searchresult = $this->app['request']->query->get('searchresult'))) &&
            (null !== ($sstring = $this->app['request']->query->get('sstring')))) {
            // create a highlight array
            $highlight = array();
            if ($searchresult == 1) {
                if (false !== strpos($sstring, '+')) {
                    $words = explode('+', $sstring);
                    foreach ($words as $word) {
                        $highlight[] = $word;
                    }
                }
                else {
                    $highlight[] = $sstring;
                }
            }
            else {
                $highlight[] = str_replace('_', ' ', $sstring);
            }
            $parameter['highlight'] = $highlight;
        }

        $gets = $this->app['request']->query->all();
        foreach ($gets as $key => $value) {
            if (!key_exists($key, $parameter) && !in_array($key, self::$ignore_parameters)) {
                // pass all other parameters to the target page
                $parameter[$key] = $value;
            }
        }

        // create the target URL and set the needed parameters
        $target_url = CMS_URL.$category['target_url'].'?'.http_build_query($parameter, '', '&');

        return $this->cURLexec($target_url, $page_id);
    }


    public function ControllerArticle(Application $app)
    {
        return __METHOD__;
    }
}

