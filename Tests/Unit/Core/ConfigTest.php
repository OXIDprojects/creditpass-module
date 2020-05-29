<?php

namespace OxidProfessionalServices\CreditPassModule\Tests\Unit\Core;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassConfig;

class ConfigTest extends UnitTestCase
{

    public function testGetExclUserGroups()
    {
        $oConfig = new CreditPassConfig();
        modConfig::getInstance()->setConfigParam(
            'aOECreditPassExclUserGroups',
            array("oxidsmallcust", "oxiddealer", "oxidgoodcust")
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
        $oConfig = new CreditPassConfig();


        $sTestVal1 = "TestVal";
        $sTestVal2 = "Das gewünschte Bezahlverfahren steht derzeit nicht zur Verfügung. Bitte wählen Sie ein anderes.";

        $sTestArr1 = array("oxcontents__oxcontent" => $sTestVal1);
        $sTestArr2 = array("oxcontents__oxcontent" => $sTestVal2);

        $oConfig->saveUnauthorizedErrorMsg($sTestArr1);
        $this->assertEquals($sTestVal1, $oConfig->getUnauthorizedErrorMsg());

        $oConfig->saveUnauthorizedErrorMsg($sTestArr2);
        $this->assertEquals($sTestVal2, $oConfig->getUnauthorizedErrorMsg());
    }

    public function testSetCacheTtl()
    {
        $oConf = new CreditPassConfig();
        $oConf->setCacheTtl(1000);
        $this->assertEquals(CreditPassConfig::OECREDITPASS_MAX_CACHE_TTL, $oConf->getCacheTtl());

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
        $oConf = new CreditPassConfig();
        $this->assertEquals(60, $oConf->getMaxCacheTtl());
    }

    public function testSetGetModuleActive()
    {
        $oConf = new CreditPassConfig();
        $oConf->setModuleActive(false);
        $this->assertFalse($oConf->isModuleActive());

        $oConf->setModuleActive();
        $this->assertTrue($oConf->isModuleActive());
    }

    public function testGetModuleAdminUrl()
    {
        $oConf = new CreditPassConfig();
        $sExp = rtrim(Registry::getConfig()->getCurrentShopUrl(true), "/") . "/..//modules/oe/oecreditpass/picture.png";
        $this->assertEquals($sExp, $oConf->getModuleAdminUrl('picture.png'));
    }
}
