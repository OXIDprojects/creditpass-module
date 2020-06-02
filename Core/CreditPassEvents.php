<?php

namespace OxidProfessionalServices\CreditPassModule\Core;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;

/**
 * CreditPass Events class
 *
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
class CreditPassEvents
{

    //default values
    public const OECREDITPASS_DEFAULT_ERROR_TITLE_DE = "creditPass - abgelehnte Zahlungsart";
    public const OECREDITPASS_DEFAULT_ERROR_TITLE_EN = "creditPass - unauthorized payment method";
    public const OECREDITPASS_DEFAULT_ERROR_MSG_DE = "Die gewünschte Zahlungsart steht derzeit nicht zur Verfügung. Bitte wählen Sie eine andere!";
    public const OECREDITPASS_DEFAULT_ERROR_MSG_EN = "The chosen payment method is currently not available. Please select another one!";
    public const OECREDITPASS_MANUAL_REVIEW_EMAIL_ORDER_DE = 'Die unten aufgelisteten Artikel wurden soeben unter [{ $shop->oxshops__oxname->value }] bestellt. Das Ergebnis der creditPass-Prüfung lautet \'Manuelle Prüfung\'. Bitte prüfen Sie daher diese Bestellung!';
    public const OECREDITPASS_MANUAL_REVIEW_EMAIL_ORDER_EN = 'The products listed below have been ordered in [{ $shop->oxshops__oxname->value }] right now. The result of the creditPass check is \'manual review\'. Therefore please check this order!';
    public const OECREDITPASS_DEFAULT_CACHE_TTL = 0;
    public const OECREDITPASS_DEFAULT_SERVICE_URL = "https://secure.creditpass.de/atgw/authorize.cfm";
    public const OECREDITPASS_CONTENT_URL = "https://secures.creditpass.de/cpgw/index.cfm";
    public const OECREDITPASS_DEFAULT_MANUAL_WORKFLOW = 1; // see values in oecreditpass_main.tpl


    /**
     * Parse sql file to split sql file into one line queries and execute them.
     * Reused from oxSetup::ParseSql
     *
     * @param string $sSQL
     *
     * @return array
     */
    public static function parseSql($sSQL)
    {
        $aRet = array();
        $blComment = false;
        $blQuote = false;
        $sThisSQL = "";

        $aLines = explode("\n", $sSQL);

        // parse it
        foreach ($aLines as $sLine) {
            $iLen = strlen($sLine);
            for ($i = 0; $i < $iLen; $i++) {
                if (!$blQuote && ($sLine[$i] == '#' || ($sLine[0] == '-' && $sLine[1] == '-'))) {
                    $blComment = true;
                }

                // add this char to current command
                if (!$blComment) {
                    $sThisSQL .= $sLine[$i];
                }

                // test if quote on
                if (($sLine[$i] == '\'' && $sLine[$i - 1] != '\\')) {
                    $blQuote = !$blQuote; // toggle
                }

                // now test if command end is reached
                if (!$blQuote && $sLine[$i] == ';') {
                    // add this
                    $sThisSQL = trim($sThisSQL);
                    if ($sThisSQL) {
                        $sThisSQL = str_replace("\r", "", $sThisSQL);
                        $aRet[] = $sThisSQL;
                    }
                    $sThisSQL = "";
                }
            }
            // comments and quotes can't run over newlines
            $blComment = false;
            $blQuote = false;
        }

        return $aRet;
    }


    /**
     * Execute actions on module Activation
     */
    public static function onActivate()
    {
        self::_checkSystem();

        self::_addTables();

        self::_modifyExistingTables();

        self::_setConfigDefaults();

        self::_restoreConfigs();

        self::_modifyExistingConfigs();
    }

    /**
     * Execute actions on module Deactivation
     */
    public static function onDeactivate()
    {
        return true;
        self::_removeModifiedExistingConfigs();

        self::_backupConfigs();
    }

    /**
     * check the system requirements
     *
     * @throws SystemErrorException
     */
    protected static function _checkSystem()
    {
        $bResult = true;

        $oLang = Registry::getLang();
        $oUtilsView = Registry::getUtilsView();

        $blHttps = false;
        if ($blHttps = in_array('https', stream_get_wrappers())) {
            $blHttps = true;
        } else {
            $oUtilsView->addErrorToDisplay(
                $oLang->translateString('OECREDITPASS_ERROR_HTTPSWRAPPER')
            );
        }
        if ($blHttps) {
            try {
                $bResult = (false !== file_get_contents(self::OECREDITPASS_CONTENT_URL, 'r'));
            } catch (Exception $exception) {
                $bResult = false;
            }
            if (!$bResult) {
                $oUtilsView->addErrorToDisplay(
                    $oLang->translateString('OECREDITPASS_ERROR_CALL_IP')
                );
            }
        }
        return $bResult;
    }

    /**
     * Adds tables to database
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected static function _addTables()
    {
        //the data are supplied from this file and currently it is not UTF, therfore we set connection to ISO (latin1).
        $sSql = "SET NAMES latin1";
        DatabaseProvider::getDb()->execute($sSql);

        $sSql = "CREATE TABLE IF NOT EXISTS `oecreditpasscache` (
                  `ID` varchar(32) character set latin1 collate latin1_general_ci NOT NULL default '',
                  `USERID` varchar(32) character set latin1 collate latin1_general_ci NOT NULL default '' COMMENT 'User id, oxuser.OXID',
                  `ASSESSMENTRESULT` blob NOT NULL,
                  `TIMESTAMP` timestamp NOT NULL default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Timestamp',
                  `USERIDENT` varchar(32) NOT NULL default '' COMMENT 'User address hash',
                  `PAYMENTID` varchar(32) NOT NULL default '' COMMENT 'Payment id, reference to oxpayments.OXID',
                  `PAYMENTDATA` varchar(32) NOT NULL default '' COMMENT 'Payment data hash',
                  `ANSWERCODE` varchar(2) NOT NULL default '' COMMENT 'Answer code',
                  PRIMARY KEY  (`ID`),
                  KEY `USERID` (`USERID`),
                  KEY `TIMESTAMP` (`TIMESTAMP`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;";

        DatabaseProvider::getDb()->execute($sSql);

        $sSql = "CREATE TABLE IF NOT EXISTS `oecreditpasspaymentsettings` (
                  `ID` varchar(32) character set latin1 collate latin1_general_ci NOT NULL,
                  `PAYMENTID` varchar(32) character set latin1 collate latin1_general_ci NOT NULL COMMENT 'Payment id, reference to oxpayments.OXID',
                  `SHOPID` int(11) NOT NULL DEFAULT '0' COMMENT 'Shop id, reference to oxshops.OXID',
                  `ACTIVE` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Perform check: 0 - no, 1 - yes.',
                  `FALLBACK` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Use Payment Method as Fallback: 0 - no, 1 - yes.',
                  `PURCHASETYPE` int(11) NULL DEFAULT NULL COMMENT 'Purchase type.',
                  `ALLOWONERROR` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Allow Payment Method on error or if service unavailable: 0 - no, 1 - yes.',
                  `TIMESTAMP` timestamp NOT NULL default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Timestamp',
                  PRIMARY KEY (`ID`),
                  KEY `SHOPID` (`SHOPID`),
                  UNIQUE KEY `PAYMENTID_SHOPID` (`PAYMENTID`,`SHOPID`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;";

        DatabaseProvider::getDb()->execute($sSql);

        $sSql = "CREATE TABLE IF NOT EXISTS `oecreditpasslog` (
                  `ID` varchar(32) character set latin1 collate latin1_general_ci NOT NULL,
                  `SHOPID` int(11) NOT NULL DEFAULT '0' COMMENT 'Shop id, reference to oxshops.OXID',
                  `USERID` varchar(32) character set latin1 collate latin1_general_ci NOT NULL default '' COMMENT 'User id, reference to oxuser.OXID',
                  `ORDERID` varchar(32) character set latin1 collate latin1_general_ci NOT NULL default '' COMMENT 'Order id, reference to oxorder.OXID',
                  `TIMESTAMP` timestamp NOT NULL default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Timestamp',
                  `ANSWERCODE` varchar(2) NOT NULL default '' COMMENT 'ANSWER CODE',
                  `ANSWERTEXT` varchar(128) NOT NULL DEFAULT '' COMMENT 'ANSWER TEXT',
                  `ANSWERDETAILS` text NOT NULL DEFAULT '' COMMENT 'ANSWER DETAILS',
                  `TRANSACTIONID` varchar(32) NOT NULL DEFAULT '' COMMENT 'TRANSACTION ID',
                  `CACHED` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Log is taken from cache: 0 - no, 1 - yes.',
                  `CUSTOMERTRANSACTIONID` varchar(32) NOT NULL DEFAULT '' COMMENT 'CUSTOMER TRANSACTION ID',
                  PRIMARY KEY (`ID`),
                  KEY `USERID` (`USERID`),
                  KEY `ORDERID` (`ORDERID`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        DatabaseProvider::getDb()->execute($sSql);

        $sTable = CreditPassStorageDbShopAwarePersistence::DATABASE_TABLE;
        $sSql = "CREATE TABLE IF NOT EXISTS `{$sTable}` (
                  `SHOPID` int(11) NOT NULL DEFAULT '0' COMMENT 'Shop id, reference to oxshops.OXID',
                  `KEY` varchar(64) character set latin1 collate latin1_general_ci NOT NULL,
                  `VALUE` text NOT NULL DEFAULT '' COMMENT 'STORED VALUE',
                  PRIMARY KEY (`SHOPID`, `KEY`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        DatabaseProvider::getDb()->execute($sSql);
    }

    /**
     * Modify existing database not related to the module.
     * This is set for installation only
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected static function _modifyExistingTables()
    {
        $sSql = "UPDATE `oxpayments` SET OXFROMBONI = 0;";

        $sErrorMsgDe = self::OECREDITPASS_DEFAULT_ERROR_MSG_DE;
        $sErrorMsgEn = self::OECREDITPASS_DEFAULT_ERROR_MSG_EN;
        $sErrorTitleDe = self::OECREDITPASS_DEFAULT_ERROR_TITLE_DE;
        $sErrorTitleEn = self::OECREDITPASS_DEFAULT_ERROR_TITLE_EN;

        $sShopId = Registry::getConfig()->getShopId();

        //add CMS page to active subshop
        $sSql .= "INSERT IGNORE INTO oxcontents (OXID, OXLOADID, OXSHOPID, OXSNIPPET, OXACTIVE, OXACTIVE_1, OXTITLE, OXCONTENT, OXTITLE_1, OXCONTENT_1)
                         VALUES ( '9be49ff479009$sShopId', 'oecreditpassunauthorized', '$sShopId', 1, 1, 1, '$sErrorTitleDe', '$sErrorMsgDe', '$sErrorTitleEn', '$sErrorMsgEn');";

        //add CMS page to active subshop
        $sSql .= "INSERT IGNORE INTO oxcontents (OXID, OXLOADID, OXSHOPID, OXSNIPPET, OXACTIVE, OXACTIVE_1, OXTITLE, OXCONTENT, OXTITLE_1, OXCONTENT_1)
                         VALUES ( '9be49ff479009$sShopId', 'oecreditpassunauthorized', '$sShopId', 1, 1, 1, '$sErrorTitleDe', '$sErrorMsgDe', '$sErrorTitleEn', '$sErrorMsgEn');";

        $aSqlStrings = self::parseSql($sSql);

        $aSqlStrings[] = self::_getManualReviewEmailOrderContents();

        foreach ($aSqlStrings as $sSqlEntry) {
            DatabaseProvider::getDb()->execute($sSqlEntry);
        }
    }

    /**
     * Get some contents for manual review email for insertion
     *
     * @return string
     */
    protected static function _getManualReviewEmailOrderContents()
    {
        $sMsgDE = self::OECREDITPASS_MANUAL_REVIEW_EMAIL_ORDER_DE;
        $sMsgEN = self::OECREDITPASS_MANUAL_REVIEW_EMAIL_ORDER_EN;

        $sShopId = Registry::getConfig()->getShopId();

        $sSql = "INSERT IGNORE INTO `oxcontents` (`OXID`, `OXLOADID`, `OXSHOPID`, `OXSNIPPET`, `OXTYPE`, `OXACTIVE`, `OXACTIVE_1`, `OXPOSITION`, `OXTITLE`, `OXCONTENT`, `OXTITLE_1`, `OXCONTENT_1`, `OXACTIVE_2`, `OXTITLE_2`, `OXCONTENT_2`, `OXACTIVE_3`, `OXTITLE_3`, `OXCONTENT_3`, `OXCATID`, `OXFOLDER`, `OXTERMVERSION`) VALUES
(\"ad542e49bff479009oe.cp64538090$sShopId\", \"oecreditpassorderemail\", \"$sShopId\", 1, 0, 1, 1, \"\", \"Ihre Bestellung Admin\", \"$sMsgDE<br>\r\n<br>\", \"your order admin\", \"$sMsgEN<br>\r\n<br>\", 1, \"\", \"\", 1, \"\", \"\", \"30e44ab83fdee7564.23264141\", \"CMSFOLDER_EMAILS\", \"\"),
(\"c8d45408c4998f421oe.cp15746968$sShopId\", \"oecreditpassordernpemail\", \"$sShopId\", 1, 0, 1, 1, \"\", \"Ihre Bestellung Admin (Fremdländer)\", \"<div>\r\n<p> <span style='color: #ff0000;'><strong>Hinweis:</strong></span> Derzeit ist keine Liefermethode für dieses Land bekannt. Bitte Liefermöglichkeiten suchen und den Besteller unter Angabe der <strong>Lieferkosten</strong> informieren!\r\n&nbsp;</p> </div>\r\n<div>$sMsgDE<br>\r\n<br>\r\n</div>\", \"your order admin (other country)\", \"<p> <span style='color: #ff0000'><strong>Information:</strong></span> Currently, there is no shipping method defined for this country. Please find a delivery option and inform the customer about the <strong>shipping costs</strong>.</p>\r\n<p>$sMsgEN<br />\r\n<br /></p>\", 1, \"\", \"\", 1, \"\", \"\", \"30e44ab83fdee7564.23264141\", \"CMSFOLDER_EMAILS\", \"\"),
(\"c8d45408c718782f3oe.cp21298666$sShopId\", \"oecreditpassordernpplainemail\", \"$sShopId\", 1, 0, 1, 1, \"\", \"Ihre Bestellung Admin (Fremdländer) Plain\", \"Hinweis: Derzeit ist keine Liefermethode für dieses Land bekannt. Bitte Liefermöglichkeiten suchen und den Besteller informieren!\r\n\r\n$sMsgDE\", \"your order admin plain (other country)\", \"<p>Information: Currently, there is no shipping method defined for this country. Please find a delivery option and inform the customer about the shipping costs.\r\n\r\n$sMsgEN</p>\", 1, \"\", \"\", 1, \"\", \"\", \"30e44ab83fdee7564.23264141\", \"CMSFOLDER_EMAILS\", \"\"),
(\"ad542e49c19109ad6oe.cp04198712$sShopId\", \"oecreditpassorderplainemail\", \"$sShopId\", 1, 0, 1, 1, \"\", \"Ihre Bestellung Admin Plain\", \"<p>$sMsgDE</p>\", \"your order admin plain\", \"$sMsgEN\", 1, \"\", \"\", 1, \"\", \"\", \"30e44ab83fdee7564.23264141\", \"CMSFOLDER_EMAILS\", \"\");";

        return $sSql;
    }

    /**
     * Sets the default config options in case they are not existing
     */
    protected static function _setConfigDefaults()
    {
        $oConfig = Registry::getConfig();

        $iCacheTtl = $oConfig->getConfigParam("iOECreditPassCheckCacheTimeout");
        if (empty($iCacheTtl)) {
            $oConfig->saveShopConfVar(
                "str",
                "iOECreditPassCheckCacheTimeout",
                self::OECREDITPASS_DEFAULT_CACHE_TTL,
                null,
                'module:oecreditpass'
            );
        }

        $sServiceUrl = $oConfig->getConfigParam("sOECreditPassUrl");
        if (!$sServiceUrl) {
            $oConfig->saveShopConfVar(
                "str",
                "sOECreditPassUrl",
                self::OECREDITPASS_DEFAULT_SERVICE_URL,
                null,
                'module:oecreditpass'
            );
        }

        $iManualWorkflow = $oConfig->getConfigParam("iOECreditPassManualWorkflow");
        if (empty($iManualWorkflow)) {
            $oConfig->saveShopConfVar(
                "str",
                "iOECreditPassManualWorkflow",
                self::OECREDITPASS_DEFAULT_MANUAL_WORKFLOW,
                null,
                'module:oecreditpass'
            );
        }
    }

    /**
     * Modifies existing config options in case they are not modified
     */
    protected static function _modifyExistingConfigs()
    {
        $oConfig = Registry::getConfig();

        $aOrderFolder = $oConfig->getConfigParam("aOrderfolder");
        if (!isset($aOrderFolder[CreditPassAssessment::OECREDITPASS_ORDERFOLDER_MANUAL_REVIEW])) {
            $aOrderFolder[CreditPassAssessment::OECREDITPASS_ORDERFOLDER_MANUAL_REVIEW] = '#FFA500';
            $oConfig->saveShopConfVar("aarr", "aOrderfolder", $aOrderFolder, null, 'module:oecreditpass');
        }
    }

    /**
     * Stores creditPass modules settings to backup storage
     */
    protected static function _backupConfigs()
    {
        $aBackup = self::_getModuleSettingsFromDb();
        //$aBackup = array();

        $oBackupStorage = CreditPassStorage::createInstance();
        $oBackupStorage->setValue("oecreditPass.settings", $aBackup);
    }

    /**
     * Restores creditPass module settings from backup storage
     */
    protected static function _restoreConfigs()
    {
        $oBackupStorage = CreditPassStorage::createInstance();
        $aBackup = $oBackupStorage->getValue("oecreditPass.settings");

        if ($aBackup) {
            self::_setModuleSettingsToDb($aBackup);
        }
    }

    /**
     * Drop tables created by creditPass
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected static function _removeTables()
    {
        $sSql = "DROP TABLE IF EXISTS `oecreditpasscache`,`oecreditpasspaymentsettings`,`oecreditpasslog`;";
        DatabaseProvider::getDb()->execute($sSql);
    }

    /**
     * Remove Modified existing config options
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected static function _removeModifiedExistingConfigs()
    {
        $sShopIdQuoted = DatabaseProvider::getDb()->quote(Registry::getConfig()->getShopId());
        $sModuleQuoted = DatabaseProvider::getDb()->quote('module:oecreditpass');
        $sVarNameQuoted = DatabaseProvider::getDb()->quote('aOrderfolder');

        $sQ = "DELETE FROM oxconfig WHERE oxshopid = $sShopIdQuoted AND oxvarname = $sVarNameQuoted AND oxmodule = $sModuleQuoted";
        DatabaseProvider::getDb()->execute($sQ);
    }

    /**
     * Returns all modules settings (raw) stored in oxconfig table
     *
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected static function _getModuleSettingsFromDb()
    {
        $oDb = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
        $iShopId = Registry::getConfig()->getShopId();
        $sQ = "SELECT * FROM oxconfig WHERE oxmodule = 'module:oecreditpass' and oxshopid = '$iShopId' ";

        $aBackup = array();
        $oRows = $oDb->select($sQ);
        if ($oRows && $oRows->count() > 0) {
            while ($aRow = $oRows->fetchRow()) {
                $sOxid = $aRow["OXID"];
                $aBackup[$sOxid] = $aRow;
            }
        }

        return $aBackup;
    }

    /**
     * Stores module settings in DB
     *
     * @param array $aBackup Raw module settings
     *
     * @return void
     */
    protected static function _setModuleSettingsToDb($aBackup)
    {
        if (!$aBackup || !is_array($aBackup)) {
            return;
        }

        $iShopId = Registry::getConfig()->getShopId();
        $oDb = DatabaseProvider::getDb();
        foreach ($aBackup as $aRow) {
            $sOxid = $oDb->quote($aRow["OXID"]);
            $sVarName = $oDb->quote($aRow["OXVARNAME"]);
            $sVarType = $oDb->quote($aRow["OXVARTYPE"]);
            $sVarValue = $oDb->quote($aRow["OXVARVALUE"]);
            $sQ = "REPLACE INTO oxconfig (oxid, oxshopid, oxmodule, oxvarname, oxvartype, oxvarvalue)
                          VALUES ($sOxid, '$iShopId', 'module:oecreditpass', $sVarName, $sVarType, $sVarValue )";

            $oDb->Execute($sQ);
        }
    }
}
