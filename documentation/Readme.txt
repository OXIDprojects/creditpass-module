==Title==
creditPass

==Author==
OXID eSales AG

==Prefix==
oecreditpass

==Version==
3.0.1

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
1. Copy the module source files to your shop directory.
2. Activate the module in admin.
3. Enable "Perform creditPass checks" in the module.