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
  'Article'
    => 'Artikel',
  'Article delete checkbox'
    => 'diesen Artikel unwiderruflich löschen',
  'Article group'
    => 'Artikel Gruppe',
  'Article group delete checkbox'
    => 'diese Artikelgruppe und alle verbundenen Artikel unwiderruflich löschen',
  'Article groups'
    => 'Artikelgruppen',
  'Article image'
    => 'Abbildung',
  'Article image folder gallery'
    => 'verwende Ordner der Abbildung für eine Galerie',
  'Article limit'
    => 'max. Bestellmenge',
  'Article list'
    => 'Artikel Übersicht',
  'Article name'
    => 'Artikelname',
  'Article price'
    => 'Preis',
  'Article price type'
    => 'Preisangabe',
  'Article value added tax'
    => 'Umsatzsteuer (Artikel)',
  'Articles'
    => 'Artikel',
  'At least you must specify one payment method!'
    => 'Sie müssen mindestens eine Zahlungsmethode festlegen!',
  'Available date'
    => 'Lieferbar ab',
  'Base configuration'
    => 'Basis Einstellungen',
  'Base configuration delete checkbox'
    => 'diese Basis Einstellung und alle verbundenen Artikelgruppen unwiderruflich löschen',
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
  'Currency iso'
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
  'Order number'
    => 'Bestellnummer',
  'Payment methods'
    => 'Zahlungsmethoden',
  'Paypal'
    => 'PayPal',
  'Permanent link'
    => 'Permanenter Link',
  'Please create a article group to start with your miniShop!'
    => 'Bitte erstellen Sie eine Artikelgruppe um mit Ihrem miniShop zu starten!',
  'Please create a article to start with your miniShop!'
    => 'Bitte legen Sie einen Artikel an um mit Ihrem miniShop zu starten!',
  'Please create a base configuration to start with your miniShop!'
    => 'Bitte erstellen Sie eine Basis Einstellung um mit Ihrem miniShop zu starten!',
  'Please define a permanent link for this article!'
    => 'Bitte definieren Sie einen permanenten Link für diesen Artikel!',
  'Publish date'
    => 'Im Shop ab',
  'Select article image'
    => 'Abbildung auswählen',
  'select the highest shipping cost'
    => 'die höchsten Versandkosten wählen',
  'select the lowest shipping cost'
    => 'die niedrigsten Versandkosten wählen',
  'Seo description'
    => 'SEO: Description',
  'Seo keywords'
    => 'SEO: Keywords',
  'Seo title'
    => 'SEO: Title',
  'Shipping article'
    => 'Versandkostentyp: Artikel',
  'Shipping cost'
    => 'Versandkosten',
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
  'Successful inserted a new article.'
    => 'Der neue Artikel wurde erfolgreich angelegt.',
  'Successful updated the article.'
    => 'Der Artikel wurde erfolgreich aktualisiert.',
  'sum-up the shipping costs'
    => 'alle Versandkosten addieren',
  'The article group has successful updated.'
    => 'Die Artikelgruppe wurde erfolgreich aktualisiert.',
  'The article group with the ID %id% has successfull deleted'
    => 'Die Artikelgruppe mit der ID %id% wurde gelöscht.',
  'The article has not changed.'
    => 'Der Artikel wurde nicht geändert.',
  'The article with the ID %id% has successfull deleted'
    => 'Der Artikel mit der ID %id% wurde erfolgreich gelöscht',
  'The base configuration with the ID %id% has successfull deleted'
    => 'Die Basis Einstellung mit der ID %id% wurde erfolgreich gelöscht.',
  'The miniShop Base has successful updated.'
    => 'Die miniShop Basis Einstellung wurde erfolgreich aktualisiert.',
  'The name <strong>%name%</strong> is already in use, please select another one.'
    => 'Der Bezeichner <strong>%name%</strong> wird bereits verwendet, bitte wählen Sie einen anderen Bezeichner.',
  'The permanent link <strong>/%link%</strong> is already in use by another article, please select an alternate one.'
    => 'Der permanente Link <strong>/%link%</strong> wird bereits von einem anderen Artikel verwendet, bitte legen Sie einen anderen Link fest.',
  'The short description can not be empty!'
    => 'Die Kurzbeschreibung darf nicht leer sein!',
  'VAT'
    => 'Umsatzsteuer',
  
);
