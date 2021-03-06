<?php

namespace OxidProfessionalServices\CreditPassModule\Tests\Unit\Core;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassAssessment;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassEvents;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassStorageDbShopAwarePersistence;

/**
 * Test class for CreditPass Events
 *
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
class EventsTest extends UnitTestCase
{


    /**
     * Test if onActivate method setups database tables properly
     */
    public function testOnActivate()
    {
        $this->_prepareDatabase();
        $oDbMetaDataHandler = new DbMetaDataHandler();

        CreditPassEvents::onActivate();
        $this->assertTrue($oDbMetaDataHandler->tableExists('oecreditpasscache'));
        $this->assertTrue($oDbMetaDataHandler->tableExists('oecreditpasspaymentsettings'));
        $this->assertTrue($oDbMetaDataHandler->tableExists('oecreditpasslog'));
        $this->assertTrue($oDbMetaDataHandler->tableExists(CreditPassStorageDbShopAwarePersistence::DATABASE_TABLE));

        $aOrderFolder = Registry::getConfig()->getShopConfVar('aOrderfolder', null, 'module:oecreditpass');
        $this->assertArrayHasKey(CreditPassAssessment::OECREDITPASS_ORDERFOLDER_MANUAL_REVIEW, $aOrderFolder);

        $this->_prepareDatabase();
        CreditPassEvents::onActivate();
    }

    /**
     * OnActivate method should work twice
     */
    public function testOnActivateTwice()
    {
        $this->_prepareDatabase();
        $oDbMetaDataHandler = new DbMetaDataHandler();


        CreditPassEvents::onActivate();
        CreditPassEvents::onActivate();

        $this->assertTrue($oDbMetaDataHandler->tableExists('oecreditpasscache'));
        $this->assertTrue($oDbMetaDataHandler->tableExists('oecreditpasspaymentsettings'));
        $this->assertTrue($oDbMetaDataHandler->tableExists('oecreditpasslog'));
        $this->assertTrue($oDbMetaDataHandler->tableExists(CreditPassStorageDbShopAwarePersistence::DATABASE_TABLE));

        $aOrderFolder = Registry::getConfig()->getShopConfVar('aOrderfolder', null, 'module:oecreditpass');
        $this->assertArrayHasKey(CreditPassAssessment::OECREDITPASS_ORDERFOLDER_MANUAL_REVIEW, $aOrderFolder);

        $this->_prepareDatabase();
        CreditPassEvents::onActivate();
    }

    /**
     * Test if onDeactivate method no longer cleans up database changes made by creditPass activate method properly
     */
    public function testOnDeactivate()
    {
        $this->_prepareDatabase();
        $oDbMetaDataHandler = new DbMetaDataHandler();

        CreditPassEvents::onActivate();

        $aOrderFolder = Registry::getConfig()->getShopConfVar('aOrderfolder', null, 'module:oecreditpass');
        $this->assertArrayHasKey(CreditPassAssessment::OECREDITPASS_ORDERFOLDER_MANUAL_REVIEW, $aOrderFolder);

        CreditPassEvents::onDeactivate();

        $this->assertTrue($oDbMetaDataHandler->tableExists('oecreditpasscache'));
        $this->assertTrue($oDbMetaDataHandler->tableExists('oecreditpasspaymentsettings'));
        $this->assertTrue($oDbMetaDataHandler->tableExists('oecreditpasslog'));
        $this->assertTrue($oDbMetaDataHandler->tableExists(CreditPassStorageDbShopAwarePersistence::DATABASE_TABLE));

        $aOrderFolder = Registry::getConfig()->getShopConfVar('aOrderfolder', null, 'module:oecreditpass');
        $this->assertNull($aOrderFolder);

        $this->_prepareDatabase();
        CreditPassEvents::onActivate();
    }

    public function sqlProvider()
    {
        return array(
            array("1;
                    2;
                    3;", array("1;", "2;", "3;")),
            array(
                "", array()
            ),
            array(
                "1;2;3;
                4", array("1;", "2;", "3;")
            ),
            array(
                ";;;
                ;;", array(";", ";", ";", ";", ";")
            )
        );
    }

    /**
     * Check if SQL is parsed properly
     *
     * @dataProvider sqlProvider
     *
     * @param $sSql
     * @param $aExpected
     */
    public function testParseSql($sSql, $aExpected)
    {
        $this->assertEquals($aExpected, CreditPassEvents::parseSql($sSql));
    }

    /**
     * remove tables created by onActivate, onDeactivate events
     * removes entries added by onActivate, onDeactivate events
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected function _prepareDatabase()
    {
        $sStorageTable = CreditPassStorageDbShopAwarePersistence::DATABASE_TABLE;

        DatabaseProvider::getDb()->execute('DROP TABLE IF EXISTS `oecreditpasscache`');
        DatabaseProvider::getDb()->execute('DROP TABLE IF EXISTS `oecreditpasspaymentsettings`');
        DatabaseProvider::getDb()->execute('DROP TABLE IF EXISTS `oecreditpasslog`');
        DatabaseProvider::getDb()->execute("DROP TABLE IF EXISTS `{$sStorageTable}`");
    }

    public function testDefaultCacheTtlIsZero()
    {
        $this->assertEquals(0, CreditPassEvents::OECREDITPASS_DEFAULT_CACHE_TTL);
    }
}
