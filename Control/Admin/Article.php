<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Control\Admin;

use Silex\Application;

class Article extends Admin {

    /**
     * Show the article list of the miniShop
     *
     * @return string rendered dialog
     */
    public function Controller(Application $app)
    {
        $this->initialize($app);


        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/miniShop/Template', 'admin/list.article.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('article'),
            ));
    }

}
