<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// permanent link route to a article
$app->get('%subdirectory%%permanentlink%/article/{name}',
    'phpManufaktur\miniShop\Control\PermanentLink::ControllerArticle');

// routes for the basket
$app->match('%subdirectory%%permanentlink%/basket',
    'phpManufaktur\miniShop\Control\PermanentLink::ControllerBasket')
    ->method('GET|POST');
$app->post('%subdirectory%%permanentlink%/basket/add',
    'phpManufaktur\miniShop\Control\Command\ActionArticle::ControllerBasketAdd');

// routes for the order
$app->match('%subdirectory%%permanentlink%/order',
    'phpManufaktur\miniShop\Control\PermanentLink::ControllerOrder')
    ->method('GET|POST');
$app->get('%subdirectory%%permanentlink%/order/send/{id}',
    'phpManufaktur\miniShop\Control\PermanentLink::ControllerSendActivation')
    ->assert('id', '\d+');
$app->get('%subdirectory%%permanentlink%/order/confirm/{guid}',
    'phpManufaktur\miniShop\Control\PermanentLink::ControllerDoubleOptIn');
