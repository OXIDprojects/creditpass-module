<?php

/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       core
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 */

namespace oe\oecreditpass\Core;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;

/**
 * CreditPass configuration handler
 */
class Config
{

    /**
     * Max request cache in days
     */
    const OECREDITPASS_MAX_CACHE_TTL = 60;

    /**
     * oxconfig::oxmodule field value for creditPass module
     */
    const OECREDITPASS_CONFIG_MODULE = "module:oecreditpass";

    /**
     * Return the list of excluded from check user group list
     *
     * @return oxUserList
     */
    public function getExclUserGroups()
    {
        $oExclUserGroups = oxNew("oxList");
        $oExclUserGroups->init("oxGroups");
        $aConfExclUserGroups = oxRegistry::getConfig()->getConfigParam("aOECreditPassExclUserGroups");

        if (!is_array($aConfExclUserGroups)) {
            $aConfExclUserGroups = array();
        }
        $oExclUserGroups->getList();

        foreach ($oExclUserGroups as $sId => $oExclUserGroup) {
            $blIsExcl = in_array($sId, $aConfExclUserGroups);
            $oExclUserGroups[$sId]->blIsExcl = $blIsExcl;
        }

        return $oExclUserGroups;
    }

    /**
     * Returns module activity status
     *
     * @return bool
     */
    public function isModuleActive()
    {
        return (bool) oxRegistry::getConfig()->getConfigParam("blOECreditPassIsActive");
    }

    /**
     * Sets module active status
     *
     * @param bool $blActive Active flag
     */
    public function setModuleActive($blActive = true)
    {
        oxRegistry::getConfig()->saveShopConfVar(
            "bool",
            "blOECreditPassIsActive",
            $blActive,
            null,
            self::OECREDITPASS_CONFIG_MODULE
        );
    }

    /**
     * Returns configured error message on unauthorized payment
     *
     * @param int $iLang Language id
     *
     * @return string
     */
    public function getUnauthorizedErrorMsg($iLang = null)
    {
        $oCms = oxNew("oxContent");
        if (isset($iLang)) {
            $oCms->setLanguage($iLang);
        }
        $oCms->loadByIdent('oecreditpassunauthorized');

        return $oCms->oxcontents__oxcontent->value;
    }

    /**
     * Saves the error msg
     *
     * @param array  $aFields Fields to be assigned
     *
     * @param string $sNewMsg New error message
     *
     * @throws DatabaseConnectionException
     */
    public function saveUnauthorizedErrorMsg($aFields)
    {
        $sShopId = oxRegistry::getConfig()->getShopId();
        $sCmsId = DatabaseProvider::getDb()->getOne(
            "select oxid from oxcontents where oxshopid='$sShopId' and oxloadid='oecreditpassunauthorized'"
        );

        $oCms = oxNew("oxBase");
        $oCms->init("oxcontents");
        $oCms->load($sCmsId);
        //Robustness: in case the record was not existing let's update it
        $oCms->oxcontents__oxloadid = new oxField("oecreditpassunauthorized");
        $oCms->oxcontents__oxshopid = new oxField($sShopId);
        $oCms->assign($aFields);
        $oCms->save();
    }

    /**
     * Sets request cache TTL in days. Does not allow to set more than defined maximum
     *
     * @param int $iCacheTtl New cache TTL
     */
    public function setCacheTtl($iCacheTtl)
    {
        $iCacheTtl = (float) $iCacheTtl;
        if ($iCacheTtl > oeCreditPassConfig::OECREDITPASS_MAX_CACHE_TTL) {
            $iCacheTtl = oeCreditPassConfig::OECREDITPASS_MAX_CACHE_TTL;
        }

        if ($iCacheTtl < 0) {
            $iCacheTtl = 0;
        }

        oxRegistry::getConfig()->saveShopConfVar(
            "str",
            "iOECreditPassCheckCacheTimeout",
            $iCacheTtl,
            null,
            self::OECREDITPASS_CONFIG_MODULE
        );
    }

    /**
     * Returns request cache TTL in days.
     *
     * @return int
     */
    public function getCacheTtl()
    {
        return oxRegistry::getConfig()->getConfigParam("iOECreditPassCheckCacheTimeout");
    }

    /**
     * Returns max caching days
     *
     * @return int
     */
    public function getMaxCacheTtl()
    {
        return self::OECREDITPASS_MAX_CACHE_TTL;
    }

    /**
     * Returns module URL for admin for given $sFile. This is SSL aware method.
     *
     * @param string $sFile Relative module file name
     *
     * @return string
     */
    public function getModuleAdminUrl($sFile)
    {
        $oConfig = oxRegistry::getConfig();
        /** @var oxModule $oModule */
        $oModule = oxNew("oxViewConfig");
        $sUrl = str_replace(
            rtrim($oConfig->getConfigParam('sShopDir'), '/'),
            rtrim($oConfig->getCurrentShopUrl(true), '/') . "/../",
            $oModule->getModulePath("oecreditpass", $sFile)
        );

        return $sUrl;
    }


}
 