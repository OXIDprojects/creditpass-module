<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       views
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id$
 */

/**
 * Defining help texts (English)
 */
$aLang = array(
    'charset'                              => 'UTF-8',
    'HELP_OECREDITPASS_MAIN_ACTIVATE'      => 'Switches creditPass functionality on or off ',
    'HELP_OECREDITPASS_MAIN_CACHING_TTL'   => 'CreditPass check results are cached for the  current user/address combination for the configured number of days. ' .
                                              'While cached, no further creditPass check is performed for that user /address combination. ' .
                                              'If the configured number is "0", no caching will be performed.',
    'HELP_OECREDITPASS_MAIN_MANUAL_REVIEW' => 'Configures  the reaction of the creditPass module in case "Manual Review" is advised by the creditPass service. ' .
                                              'Should "manual review" be selected and the creditPass service advise "manual review" for a specific order,  ' .
                                              'that completed order would be marked as requiring manual review and the shop owner would be notified as to the required review via email to the specified email address.',
    'HELP_OECREDITPASS_MAIN_TESTIN_MODE'   => 'Turns the creditPass service response simulation mode on/off. ' .
                                              'If on, the request will be sent to the creditPass simulation service. ' .
                                              'The response of the simulation will depend on the last digit of the current total basket amount in cents as follows:' .
                                              '<table><thead><tr><th>Last digit</th><th>Service response</th></tr></thead>
                                                    <tr><td>1</td><td>   Not authorised</td></tr>
                                                    <tr><td>2</td><td>   Manual check </td></tr>
                                                    <tr><td>8</td><td>   Error</td></tr>
                                                    <tr><td>0,3,4,5,6,7,9</td><td> Authorised </td></tr></table>' .

                                              'The simulated response is stored in the cache for specified number of days.',

    'HELP_OECREDITPASS_MAIN_DEBUG_MODE'       => 'Saves full response/request technical information in the module\'s xml/ dir. Saves debug information in the module\'s log/session.log file.',
    'HELP_OECREDITPASS_PAYMENT_PURCHASE_TYPE' => 'Identifies the decision matrix agreed with creditPass (example: 1,2,3,4)',
);