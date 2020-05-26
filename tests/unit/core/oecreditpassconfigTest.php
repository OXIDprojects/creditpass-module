<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       tests
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 */

class Unit_core_oeCreditPassConfigTest extends OxidTestCase
{

    public function testGetExclUserGroups()
    {
        $oConfig = new oeCreditPassConfig();
        modConfig::getInstance()->setConfigParam(
            'aOECreditPassExclUserGroups', array("oxidsmallcust", "oxiddealer", "oxidgoodcust")
        );

        $oExclUserGroups = $oConfig->getExclUserGroups();

        $this->assertFalse($oExclUserGroups["oxidblacklist"]->blIsExcl);
        $this->assertTrue($oExclUserGroups["oxidsmallcust"]->blIsExcl);
        $this->assertFalse($oExclUserGroups["oxidadmin"]->blIsExcl);
        $this->assertTrue($oExclUserGroups["oxiddealer"]->blIsExcl);
        $this->assertTrue($oExclUserGroups["oxidgoodcust"]->blIsExcl);
    }

    public function testSaveGetUnauthorizedErrorMessage()
    {
        $oConfig = new oeCreditPassConfig();


        $sTestVal1 = "TestVal";
        $sTestVal2 = "Das gew�nschte Bezahlverfahren steht derzeit nicht zur Verf�gung. Bitte w�hlen Sie ein anderes.";

        $sTestArr1 = array("oxcontents__oxcontent" => $sTestVal1);
        $sTestArr2 = array("oxcontents__oxcontent" => $sTestVal2);

        $oConfig->saveUnauthorizedErrorMsg($sTestArr1);
        $this->assertEquals($sTestVal1, $oConfig->getUnauthorizedErrorMsg());

        $oConfig->saveUnauthorizedErrorMsg($sTestArr2);
        $this->assertEquals($sTestVal2, $oConfig->getUnauthorizedErrorMsg());
    }

    public function testSetCacheTtl()
    {
        $oConf = new oeCreditPassConfig();
        $oConf->setCacheTtl(1000);
        $this->assertEquals(oeCreditPassConfig::OECREDITPASS_MAX_CACHE_TTL, $oConf->getCacheTtl());

        $oConf->setCacheTtl(30);
        $this->assertEquals(30, $oConf->getCacheTtl());

        $oConf->setCacheTtl(-10);
        $this->assertEquals(0, $oConf->getCacheTtl());

        $oConf->setCacheTtl("abc");
        $this->assertEquals(0, $oConf->getCacheTtl());

        $oConf->setCacheTtl("15.5");
        $this->assertEquals(15.5, $oConf->getCacheTtl());
    }

    public function testGetMaxCacheTtl()
    {
        $oConf = new oeCreditPassConfig();
        $this->assertEquals(60, $oConf->getMaxCacheTtl());
    }

    public function testSetGetModuleActive()
    {
        $oConf = new oeCreditPassConfig();
        $oConf->setModuleActive(false);
        $this->assertFalse($oConf->isModuleActive());

        $oConf->setModuleActive();
        $this->assertTrue($oConf->isModuleActive());
    }

    public function testGetModuleAdminUrl()
    {
        $oConf = new oeCreditPassConfig();
        $sExp = rtrim(oxRegistry::getConfig()->getCurrentShopUrl(true), "/") . "/..//modules/oe/oecreditpass/picture.png";
        $this->assertEquals($sExp, $oConf->getModuleAdminUrl('picture.png'));
    }
}
 