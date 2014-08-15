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
use phpManufaktur\miniShop\Data\Shop\Base;
use phpManufaktur\miniShop\Data\Shop\Group;

class Uninstall
{
    protected $app = null;

    /**
     * Execute the update for the miniShop
     *
     * @param Application $app
     */
    public function Controller(Application $app)
    {
        $this->app = $app;

        $baseTable = new Base($app);
        $baseTable->dropTable();

        $groupTable = new Group($app);
        $groupTable->dropTable();

        return $app['translator']->trans('Successfull uninstalled the extension %extension%.',
            array('%extension%' => 'miniShop'));
    }
}
