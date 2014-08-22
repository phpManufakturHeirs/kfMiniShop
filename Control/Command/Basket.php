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

use phpManufaktur\Basic\Control\Pattern\Alert;
use Silex\Application;
use phpManufaktur\miniShop\Data\Shop\Basket as DataBasket;

class Basket extends Alert
{
    protected static $basket = array();
    protected static $identifier = null;

    protected $dataBasket = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\Pattern\Alert::initialize()
     */
    public function initialize(Application $app)
    {
        parent::initialize($app);

        self::$identifier = md5($_SERVER['REMOTE_ADDR']);
        $this->dataBasket = new DataBasket($app);
        $this->dataBasket->cleanup();
    }

    /**
     * Get the basket content
     *
     * @return array
     */
    public function getBasket()
    {
        return $this->dataBasket->selectBasket(self::$identifier);
    }

    /**
     * Save the basket to the basket table
     *
     * @param array $basket
     */
    public function setBasket($basket)
    {
        if ($this->dataBasket->existsIdentifier(self::$identifier)) {
            $this->dataBasket->updateBasket(self::$identifier, $basket);
        }
        else {
            $this->dataBasket->insertBasket(self::$identifier, $basket);
        }
    }

    /**
     * Remove the whole basket
     */
    public function removeBasket()
    {
        $this->dataBasket->removeBasket(self::$identifier);
    }

    /**
     * Update the basket with the given article data
     *
     * @param array $data
     */
    public function updateBasket($data)
    {
        // get the basket
        $basket = $this->getBasket();
        // create a checksum for comparison
        $md5 = md5(serialize($data));

        $add_article = true;
        foreach ($basket as $existing_md5 => $article) {
            if (($article['id'] === $data['id']) &&
                ($article['article_variant_values'] === $data['article_variant_values']) &&
                ($article['article_variant_values_2'] === $data['article_variant_values_2'])) {
                $data['quantity'] += $article['quantity'];
                unset($basket[$existing_md5]);
                $add_article = false;
                if ($data['quantity'] > 0) {
                    $basket[$md5] = $data;
                    $this->setAlert('Changed quantity for the article <strong>%article%</strong> to <strong>%quantity%</strong>.',
                        array('%quantity%' => $data['quantity'], '%article%' => $data['article_name']), self::ALERT_TYPE_SUCCESS);
                }
                else {
                    $this->setAlert('Removed the article <strong>%article%</strong> from the basket.',
                        array('%article%' => $data['article_name']), self::ALERT_TYPE_SUCCESS);
                }
                //$this->app['session']->set('minishop_basket', $basket);
                $this->setBasket($basket);
                break;
            }
        }
        if ($add_article) {
            if (!isset($basket[$md5])) {
                if ($data['quantity'] > 0) {
                    $basket[$md5] = $data;
                    $this->setAlert('Added article <strong>%article%</strong> to the basket.',
                        array('%article%' => $data['article_name']), self::ALERT_TYPE_SUCCESS);
                    $this->setBasket($basket);
                }
                else {
                    $this->setAlert('Invalid quantity, ignored article.', array(), self::ALERT_TYPE_WARNING);
                }
            }
            else {
                $this->setAlert('The selected article is already in your basket.', array(), self::ALERT_TYPE_INFO);
            }
        }
    }

    /**
     * Controller to view the current basket
     *
     * @param Application $app
     * @return string
     */
    public function ControllerBasketView(Application $app)
    {
        $this->initialize($app);

        $basket = $this->getBasket();

        echo "<pre>";
        print_r($basket);
        echo "</pre>";
        return __METHOD__;
    }
}
