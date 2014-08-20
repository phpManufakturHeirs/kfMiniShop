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
use phpManufaktur\miniShop\Control\Configuration;

class Setup
{
    protected $app = null;
    protected static $configuration = null;


    /**
     * Create the routes needed for the permanentlinks and write bootstrap.include.inc
     *
     * @param Application $app
     * @param array $config load config only if needed!
     * @throws \Exception
     */
    public function createPermalinkRoutes(Application $app, $config=null)
    {
        if (is_null($config)) {
            $Configuration = new  Configuration($app);
            $config = $Configuration->getConfiguration();
        }

        $subdirectory = parse_url(CMS_URL, PHP_URL_PATH);

        // always remove an existing include
        $app['filesystem']->remove(MANUFAKTUR_PATH.'/miniShop/bootstrap.include.inc');

        if (false === ($include = file_get_contents(MANUFAKTUR_PATH.'/miniShop/Data/Setup/PermanentLink/bootstrap.include.inc'))) {
            throw new \Exception('Missing /miniShop/Data/Setup/PermanentLink/bootstrap.include.inc!');
        }

        $permanentlink = $config['permanentlink']['directory'];

        $search = array('%subdirectory%', '%permanentlink%');
        $replace = array($subdirectory, $permanentlink);

        $include = str_replace($search, $replace, $include);

        if (false === (file_put_contents(MANUFAKTUR_PATH.'/miniShop/bootstrap.include.inc', $include))) {
            throw new \Exception("Can't create '/miniShop/bootstrap.include.inc!");
        }
        $app['monolog']->addDebug('Create /miniShop/bootstrap.include.inc');
    }

    /**
     * Create the physical directories and the needed .htaccess files for the permanent links
     *
     * @param Application $app
     * @param array $config load config only if needed!
     * @throws \Exception
     */
    public function createPermalinkDirectories(Application $app, $config=null)
    {
        if (is_null($config)) {
            $Configuration = new Configuration($app);
            $config = $Configuration->getConfiguration();
        }

        $subdirectory = parse_url(CMS_URL, PHP_URL_PATH);

        $path = $config['permanentlink']['directory'];
        $app['filesystem']->mkdir(CMS_PATH.$path);
        if (false === ($include = file_get_contents(MANUFAKTUR_PATH.'/miniShop/Data/Setup/PermanentLink/.htaccess'))) {
            throw new \Exception('Missing /miniShop/Data/Setup/PermanentLink/.htaccess!');
        }
        $include = str_replace(array('%subdirectory%'), array($subdirectory), $include);

        if (false === (file_put_contents(CMS_PATH.$path.'/.htaccess', $include))) {
            throw new \Exception("Can't create $path/.htaccess!");
        }
        $app['monolog']->addDebug('Create '.'/'.$config['permanentlink']['directory'].'/.htaccess');
    }

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

            $Configuration = new Configuration($app);
            self::$configuration = $Configuration->getConfiguration();

            $baseTable = new Base($app);
            $baseTable->createTable();

            $groupTable = new Group($app);
            $groupTable->createTable();

            $articleTable = new Article($app);
            $articleTable->createTable();

            // create the permanent link routes
            $this->createPermalinkRoutes($app, self::$configuration);
            $this->createPermalinkDirectories($app, self::$configuration);

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
