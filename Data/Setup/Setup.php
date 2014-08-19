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
use phpManufaktur\Basic\Control\CMS\InstallAdminTool;
use phpManufaktur\miniShop\Data\Shop\Base;
use phpManufaktur\miniShop\Data\Shop\Group;
use phpManufaktur\miniShop\Data\Shop\Article;

class Setup
{
    protected $app = null;


    /**
     * Execute all steps needed to setup the miniShop
     *
     * @param Application $app
     * @throws \Exception
     * @return string with result
     */
    public function Controller(Application $app)
    {
        try {
            $this->app = $app;

            $baseTable = new Base($app);
            $baseTable->createTable();

            $groupTable = new Group($app);
            $groupTable->createTable();

            $articleTable = new Article($app);
            $articleTable->createTable();

            // setup the miniShop as Add-on in the CMS
            $admin_tool = new InstallAdminTool($app);
            $admin_tool->exec(MANUFAKTUR_PATH.'/miniShop/extension.json', '/minishop/cms');

            return $app['translator']->trans('Successfull installed the extension %extension%.',
                array('%extension%' => 'miniShop'));

        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}
