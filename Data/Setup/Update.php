<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Data\Setup;

use Silex\Application;

class Update
{
    protected $app = null;
    protected $Configuration = null;

    /**
     * Release 0.12
     */
    protected function release_012()
    {
        $files = array(
            '/miniShop/Template/default/command/include',
            '/miniShop/Template/default/command/contact.order.twig',
            '/miniShop/Template/default/command/list.article.twig',
            '/miniShop/Template/default/command/order.twig',
            '/miniShop/Template/default/command/view.article.twig',
            '/miniShop/Template/default/command/view.basket.twig'
        );
        foreach ($files as $file) {
            // remove no longer needed directories and files
            if ($this->app['filesystem']->exists(MANUFAKTUR_PATH.$file)) {
                $this->app['filesystem']->remove(MANUFAKTUR_PATH.$file);
                $this->app['monolog']->addInfo(sprintf('[miniShop Update] Removed file or directory %s', $file));
            }
        }
    }

    /**
     * Execute the update for the miniShop
     *
     * @param Application $app
     */
    public function Controller(Application $app)
    {
        $this->app = $app;

        // Release 0.12
        $this->release_012();

        return $app['translator']->trans('Successfull updated the extension %extension%.',
            array('%extension%' => 'miniShop'));
    }
}
