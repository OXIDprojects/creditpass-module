<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       tests
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 */

$aModuleInfo = array(
    'order'   => 'oecreditpass/oecreditpass_order',
    'oxorder' => 'oecreditpass/oecreditpass_oxorder',
    'payment' => 'oecreditpass/oecreditpass_payment',
);


$aConfigIncAdd = array();

$aConfigIncAdd['PE'] = $aConfigIncAdd['CE'] = $aConfigIncAdd['EE'];

$sSetupSql = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "oecreditpass_utf8.sql");

$aDbInstallSql = array(
    'general' => $sSetupSql,
);

