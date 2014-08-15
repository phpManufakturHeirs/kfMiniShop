<?php

/**
 * kitFramework::miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 *
 * This file was created by the kitFramework i18nEditor
 */

if ('á' != "\xc3\xa1") {
    // the language files must be saved as UTF-8 (without BOM)
    throw new \Exception('The language file ' . __FILE__ . ' is damaged, it must be saved UTF-8 encoded!');
}

return array(
  'Advance payment'
    => 'Vorkasse',
  'Article group'
    => 'Artikel Gruppe',
  'Article group delete checkbox'
    => 'diese Artikelgruppe und alle verbundenen Artikel löschen',
  'Article groups'
    => 'Artikelgruppen',
  'Article limit'
    => 'max. Bestellmenge',
  'Article price type'
    => 'Preisangabe',
  'Article value added tax'
    => 'Umsatzsteuer (Artikel)',
  'Articles'
    => 'Artikel',
  'At least you must specify one payment method!'
    => 'Sie müssen mindestens eine Zahlungsmethode festlegen!',
  'Base configuration'
    => 'Basis Einstellungen',
  'Base configuration delete checkbox'
    => 'diese Basis Einstellung und alle verbundenen Artikelgruppen löschen',
  'Base configurations'
    => 'Basis Einstellungen',
  'Base id'
    => 'ID',
  'Base name'
    => 'Basis Bezeichnung',
  'Create a new article group'
    => 'Eine neue Artikelgruppe erstellen',
  'Create a new miniShop base'
    => 'Neue Basis Einstellung anlegen',
  'Currency'
    => 'Währung',
  'Define and edit base configurations for the miniShop'
    => 'Anlegen und Bearbeiten von Basis Einstellungen für den miniShop',
  'Define and edit the article groups for the miniShop'
    => 'Erstellen und Bearbeiten von Artikelgruppen für den miniShop',
  'Define and edit the articles for the miniShop'
    => 'Erstellen und Bearbeiten von Artikeln für den miniShop',
  'Determined by each article'
    => 'Festlegung durch jeden Artikel',
  'Flatrate for shipping and handling'
    => 'Versandkostenpauschale',
  'Gross price'
    => 'Bruttopreis',
  'Information about the miniShop extension'
    => 'Informationen über die miniShop Erweiterung',
  'miniShop - About'
    => 'miniShop - Information',
  'miniShop for the kitFramework'
    => 'miniShop für das kitFramework',
  'Net price'
    => 'Nettopreis',
  'No shipping'
    => 'Kein Versand',
  'On account'
    => 'auf Rechnung',
  'Order minimum price'
    => 'Mindestbestellpreis',
  'Payment methods'
    => 'Zahlungsmethoden',
  'Paypal'
    => 'PayPal',
  'Please create a new article group to start with your shop!'
    => 'Bitte erstellen Sie eine neue Artikelgruppe um mit Ihrem Shop starten zu können!',
  'Please create a new miniShop base to start with your shop!'
    => 'Bitte erstellen Sie eine neue miniShop Basis Einstellung um mit Ihrem Shop starten zu können!',
  'select the highest shipping cost'
    => 'die höchsten Versandkosten wählen',
  'select the lowest shipping cost'
    => 'die niedrigsten Versandkosten wählen',
  'Shipping article'
    => 'Versandkostentyp: Artikel',
  'Shipping flatrate'
    => 'Versandkostenpauschale',
  'Shipping type'
    => 'Versandkostentyp',
  'Shipping value added tax'
    => 'Umsatzsteuer (Versandkosten)',
  'Succesful created a new article group'
    => 'Es wurde eine neue Artikelgruppe angelegt.',
  'Succesful created a new miniShop Base'
    => 'Es wurde erfolgreich eine neue miniShop Basis Einstellung angelegt.',
  'sum-up the shipping costs'
    => 'alle Versandkosten addieren',
  'The article group has successful updated.'
    => 'Die Artikelgruppe wurde erfolgreich aktualisiert.',
  'The article group with the ID %id% has successfull deleted'
    => 'Die Artikelgruppe mit der ID %id% wurde gelöscht.',
  'The base configuration with the ID %id% has successfull deleted'
    => 'Die Basis Einstellung mit der ID %id% wurde erfolgreich gelöscht.',
  'The miniShop Base has successful updated.'
    => 'Die miniShop Basis Einstellung wurde erfolgreich aktualisiert.',
  'The name <strong>%name%</strong> is already in use, please select another one.'
    => 'Der Bezeichner <strong>%name%</strong> wird bereits verwendet, bitte wählen Sie einen anderen Bezeichner.',
  'VAT'
    => 'Umsatzsteuer',
  
);
