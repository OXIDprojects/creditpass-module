==Title==
creditPass

==Author==
OXID eSales AG

==Prefix==
oecreditpass

==Versionen==
3.0.1
4.0.0

==Link==
http://www.oxid-esales.com

==Mail==
info@oxid-esales.com

==Description==
Module for solvency check with creditPass.

==Extend==
*order
--init
*payment
--getPaymentList
*oxorder
--_sendOrderByEmail
*oxemail

==Installation==
Version 3.0.1
1. Copy the module source files to your shop directory.
2. Activate the module in admin.
3. Enable "Perform creditPass checks" in the module.

Version 4.0.0
1. Install the module via composer. Run the following command in commandline of your shop base directory (where the shop's composer.json file resides).
   composer require oxid-professional-services/creditpass:^4.0.* to install the released version compatible with OXID eShop 6.x.x
2. Activate the module in admin.
3. Enable "Perform creditPass checks" in the module.
