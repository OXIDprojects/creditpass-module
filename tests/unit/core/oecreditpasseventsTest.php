<?php

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;

/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       tests
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 */
class Unit_core_oeCreditPassEventsTest extends OxidTestCase
{


    /**
     * Test if onActivate method setups database tables properly
     */
    public function testOnActivate()
    {
        $this->_prepareDatabase();
        $oDbMetaDataHandler = new oxDbMetaDataHandler();

        oeCreditPassEvents::onActivate();
        $this->assertTrue($oDbMetaDataHandler->tableExists('oecreditpasscache'));
        $this->assertTrue($oDbMetaDataHandler->tableExists('oecreditpasspaymentsettings'));
        $this->assertTrue($oDbMetaDataHandler->tableExists('oecreditpasslog'));
        $this->assertTrue($oDbMetaDataHandler->tableExists(oeCreditPassStorageDbShopAwarePersistence::DATABASE_TABLE));

        $aOrderFolder = oxRegistry::getConfig()->getShopConfVar('aOrderfolder', null, 'module:oecreditpass');
        $this->assertArrayHasKey(oeCreditPassAssessment::OECREDITPASS_ORDERFOLDER_MANUAL_REVIEW, $aOrderFolder);

        $this->_prepareDatabase();
        oeCreditPassEvents::onActivate();
    }

    /**
     * OnActivate method should work twice
     */
    public function testOnActivateTwice()
    {
        $this->_prepareDatabase();
        $oDbMetaDataHandler = new oxDbMetaDataHandler();


        oeCreditPassEvents::onActivate();
        oeCreditPassEvents::onActivate();

        $this->assertTrue($oDbMetaDataHandler->tableExists('oecreditpasscache'));
        $this->assertTrue($oDbMetaDataHandler->tableExists('oecreditpasspaymentsettings'));
        $this->assertTrue($oDbMetaDataHandler->tableExists('oecreditpasslog'));
        $this->assertTrue($oDbMetaDataHandler->tableExists(oeCreditPassStorageDbShopAwarePersistence::DATABASE_TABLE));

        $aOrderFolder = oxRegistry::getConfig()->getShopConfVar('aOrderfolder', null, 'module:oecreditpass');
        $this->assertArrayHasKey(oeCreditPassAssessment::OECREDITPASS_ORDERFOLDER_MANUAL_REVIEW, $aOrderFolder);

        $this->_prepareDatabase();
        oeCreditPassEvents::onActivate();
    }

    /**
     * Test if onDeactivate method no longer cleans up database changes made by creditPass activate method properly
     */
    public function testOnDeactivate()
    {
        $this->_prepareDatabase();
        $oDbMetaDataHandler = new oxDbMetaDataHandler();

        oeCreditPassEvents::onActivate();

        $aOrderFolder = oxRegistry::getConfig()->getShopConfVar('aOrderfolder', null, 'module:oecreditpass');
        $this->assertArrayHasKey(oeCreditPassAssessment::OECREDITPASS_ORDERFOLDER_MANUAL_REVIEW, $aOrderFolder);

        oeCreditPassEvents::onDeactivate();

        $this->assertTrue($oDbMetaDataHandler->tableExists('oecreditpasscache'));
        $this->assertTrue($oDbMetaDataHandler->tableExists('oecreditpasspaymentsettings'));
        $this->assertTrue($oDbMetaDataHandler->tableExists('oecreditpasslog'));
        $this->assertTrue($oDbMetaDataHandler->tableExists(oeCreditPassStorageDbShopAwarePersistence::DATABASE_TABLE));

        $aOrderFolder = oxRegistry::getConfig()->getShopConfVar('aOrderfolder', null, 'module:oecreditpass');
        $this->assertNull($aOrderFolder);

        $this->_prepareDatabase();
        oeCreditPassEvents::onActivate();
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
     */
    public function testParseSql($sSql, $aExpected)
    {
        $this->assertEquals($aExpected, oeCreditPassEvents::parseSql($sSql));
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
        $sStorageTable = oeCreditPassStorageDbShopAwarePersistence::DATABASE_TABLE;

        DatabaseProvider::getDb()->execute('DROP TABLE IF EXISTS `oecreditpasscache`');
        DatabaseProvider::getDb()->execute('DROP TABLE IF EXISTS `oecreditpasspaymentsettings`');
        DatabaseProvider::getDb()->execute('DROP TABLE IF EXISTS `oecreditpasslog`');
        DatabaseProvider::getDb()->execute("DROP TABLE IF EXISTS `{$sStorageTable}`");
    }

    public function testDefaultCacheTtlIsZero()
    {
        $this->assertEquals(0, oeCreditPassEvents::OECREDITPASS_DEFAULT_CACHE_TTL);
    }

}