# OXID PS creditPass Module

# Version 4.0.0

## Description

 * Standalone release of the creditPass Module
 * Supports 3 payment types debit, invoice, installments
 * Supports handling over the oxid admin backend only

## Installation

Use Composer to add the module to your project
```bash
composer require oxid-professional-services/creditpass
```

 * Activate the module in administration area
 * clear tmp and regenerate views
 * Make sure to take care of all the settings, options and credentials described in the user manual

## Uninstall

 * Deactivate the module in administration area
 * remove "oxid-professional-services/creditpass" from your composer.json

Run Composer again to remove Module from vendor
```bash
composer update
```

## Changelog

### Version 3.0.0

* Initial release of standalone module.

### Version 3.0.1

Fixed bugs:
https://bugs.oxid-esales.com/changelog_page.php?version_id=248

### Version 4.0.0

* Version for OXID6 installable via Composer
