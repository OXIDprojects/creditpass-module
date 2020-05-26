<?php

/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       controllers
 * @copyright (c) anzido GmbH, Andreas Ziethen 2008-2011
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 *
 * @extend        Shop_Config
 */

namespace oe\oecreditpass\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\ShopConfiguration;

class MainController extends ShopConfiguration
{

    /**
     * Template filename.
     *
     * @var string
     */
    protected $_sThisTemplate = 'oecreditpass_main.tpl';

    /**
     * Current shop id.
     *
     * @var int
     */
    protected $_iShopId = 1;

    /**
     * Constructor. Sets up the _iShopId property.
     */
    public function __construct()
    {
        $myConfig = $this->getConfig();
        $this->_iShopId = $myConfig->getShopId();
        if ($this->_iShopId == "oxbaseshop")
            $this->_iShopId = 1;
    }

    /**
     * return theme filter for config variables
     *
     * @return string
     */
    protected function _getModuleForConfigVars()
    {
        return 'module:oecreditpass';
    }

    /**
     * Loads credit pass configuration.
     *
     * @return string template filename
     */
    public function render()
    {
        $sTemplate = parent::render();

        $this->_aViewData['aUserGroups'] = $this->getUserGroups();

        $this->_aViewData['aLangs'] = $this->getLanguageArray();

        return $sTemplate;
    }

    /**
     * Saves credit pass configuration.
     *
     * @return null
     */
    public function save()
    {
        parent::save();

        //saving unauthorized error msg
        oxNew("oeCreditPassConfig")->saveUnauthorizedErrorMsg(
            oxRegistry::getConfig()->getRequestParameter('sUnauthorizedErrorMsg')
        );
        oxNew("oeCreditPassConfig")->setCacheTtl(
            oxRegistry::getConfig()->getRequestParameter('iOECreditPassCheckCacheTimeout')
        );
    }

    /**
     * Returns a list of all available eShop user groups with certain groups marked as excluded from boni check
     *
     * @return oxUserList
     */
    public function getUserGroups()
    {
        return oxNew("oeCreditPassConfig")->getExclUserGroups();
    }

    /**
     * Returns configured unauthorized error msg.
     *
     * @param $iLangId
     *
     * @return string
     */
    public function getUnauthorizedErrorMsg($iLangId = null)
    {
        return oxNew("oeCreditPassConfig")->getUnauthorizedErrorMsg($iLangId);
    }

    /**
     * Return the list of available languages
     *
     * @return mixed
     */
    public function getLanguageArray()
    {
        return oxRegistry::getLang()->getLanguageArray();
    }

    /**
     * Returns db field language prefix depending on lang.
     * Skip prefix on lang 0
     *
     * @param $iLang
     *
     * @return null
     */
    public function getLangPrefix($iLang)
    {
        if ($iLang) {
            return "_" . $iLang;
        }
    }

    /**
     * Returns cache TTL
     *
     * @return int
     */
    public function getCacheTtl()
    {
        return oxNew("oeCreditPassConfig")->getCacheTtl();
    }

    /**
     * Returns max caching days
     *
     * @return int
     */
    public function getMaxCacheTtl()
    {
        return oxNew("oeCreditPassConfig")->getMaxCacheTtl();
    }

    /**
     * Returns module path for admin. SSL aware method.
     *
     * @param string $sFile Relative file name
     *
     * @return mixed
     */
    public function getModuleAdminUrl($sFile)
    {
        return oxNew("oeCreditPassConfig")->getModuleAdminUrl($sFile);
    }

}