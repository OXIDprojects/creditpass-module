<?php

namespace OxidProfessionalServices\CreditPassModule\Core;

use OxidEsales\Eshop\Application\Model\Content;
use OxidEsales\Eshop\Application\Model\UserList;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Model\ListModel;
use OxidEsales\Eshop\Core\Module\Module;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\ViewConfig;

/**
 * CreditPass configuration handler
 */
class CreditPassConfig
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
     * @return UserList
     */
    public function getExclUserGroups()
    {
        $oExclUserGroups = oxNew(ListModel::class);
        $oExclUserGroups->init("oxGroups");
        $aConfExclUserGroups = Registry::getConfig()->getConfigParam("aOECreditPassExclUserGroups");

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
        return (bool) Registry::getConfig()->getConfigParam("blOECreditPassIsActive");
    }

    /**
     * Sets module active status
     *
     * @param bool $blActive Active flag
     */
    public function setModuleActive($blActive = true)
    {
        Registry::getConfig()->saveShopConfVar(
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
        $oCms = oxNew(Content::class);
        if (isset($iLang)) {
            $oCms->setLanguage($iLang);
        }
        $oCms->loadByIdent('oecreditpassunauthorized');

        return $oCms->oxcontents__oxcontent->value;
    }

    /**
     * Saves the error msg
     *
     * @param array $aFields Fields to be assigned
     */
    public function saveUnauthorizedErrorMsg($aFields)
    {
        $sShopId = Registry::getConfig()->getShopId();
        $sCmsId = DatabaseProvider::getDb()->getOne(
            "select oxid from oxcontents where oxshopid='$sShopId' and oxloadid='oecreditpassunauthorized'"
        );

        $oCms = oxNew(BaseModel::class);
        $oCms->init("oxcontents");
        $oCms->load($sCmsId);
        //Robustness: in case the record was not existing let's update it
        $oCms->oxcontents__oxloadid = new Field("oecreditpassunauthorized");
        $oCms->oxcontents__oxshopid = new Field($sShopId);
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
        $iCacheTtl = (float)$iCacheTtl;
        if ($iCacheTtl > CreditPassConfig::OECREDITPASS_MAX_CACHE_TTL) {
            $iCacheTtl = CreditPassConfig::OECREDITPASS_MAX_CACHE_TTL;
        }

        if ($iCacheTtl < 0) {
            $iCacheTtl = 0;
        }

        Registry::getConfig()->saveShopConfVar(
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
        return Registry::getConfig()->getConfigParam("iOECreditPassCheckCacheTimeout");
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
        $oConfig = Registry::getConfig();
        /**
         * @var Module $oModule
         */
        $oModule = oxNew(ViewConfig::class);
        $sUrl = str_replace(
            rtrim($oConfig->getConfigParam('sShopDir'), '/'),
            rtrim($oConfig->getCurrentShopUrl(true), '/') . "/../",
            $oModule->getModulePath("oecreditpass", $sFile)
        );

        return $sUrl;
    }
}
