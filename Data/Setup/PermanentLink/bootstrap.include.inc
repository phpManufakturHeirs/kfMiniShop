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
$app->get('%subdirectory%%permanentlink%/{name}',
    'phpManufaktur\miniShop\Control\PermanentLink::ControllerArticle');
