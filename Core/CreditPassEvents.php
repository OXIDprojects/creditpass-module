<?php

namespace OxidProfessionalServices\CreditPassModule\Core;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\Content;

/**
 * CreditPass Events class
 *
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
class CreditPassEvents
{

    //default values
    public const OECREDITPASS_DEFAULT_CACHE_TTL = 0;
    public const OECREDITPASS_DEFAULT_SERVICE_URL = "https://secure.creditpass.de/atgw/authorize.cfm";
    public const OECREDITPASS_CONTENT_URL = "https://secure.creditpass.de/cpgw/index.cfm";
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
        $oDB = DatabaseProvider::getDb();

        $sSql = "UPDATE `oxpayments` SET OXFROMBONI = 0;";
        $oDB->execute($sSql);

        $oLang = Registry::getLang();

        $oContent = oxNew(Content::class);
        if (!$oContent->loadByIdent('oecreditpassunauthorized')) {
            $oContent->oxcontents__oxactive->setValue(1);
            $oContent->oxcontents__oxsnippet->setValue(1);
            $oContent->oxcontents__oxloadid->setValue('oecreditpassunauthorized');
            $oContent->oxcontents__oxshopid->setValue(Registry::getConfig()->getShopId());
            $oContent->save();

            foreach ($oLang->getAllShopLanguageIds() as $iLang => $sLang) {
                $oContent->setLanguage($iLang);
                $oContent->oxcontents__oxtitle->setValue($oLang->translateString('OECREDITPASS_DEFAULT_ERROR_TITLE', $iLang));
                $oContent->oxcontents__oxcontent->setValue($oLang->translateString('OECREDITPASS_DEFAULT_ERROR_MSG', $iLang));
                $oContent->save();
            }
        }
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
