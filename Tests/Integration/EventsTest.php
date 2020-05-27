<?php

use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\CreditPassModule\Core\Events;
use OxidProfessionalServices\CreditPassModule\Core\StorageDbShopAwarePersistence;

/**
 * Test for oeCreditPassStorage
 */
class EventsTest extends UnitTestCase
{

    /**
     * Current shop id.
     *
     * @var int
     */
    protected $_iShopId;

    /** {@inheritdoc} */
    public function setUp()
    {
        parent::setUp();
        $this->_clearTestData();

        $this->_iShopId = $this->getConfig()->getShopId();
        if ('oxbaseshop' === $this->_iShopId) {
            $this->_iShopId = 1;
        }
    }

    /** {@inheritdoc} */
    public function tearDown()
    {
        $this->_clearTestData();
        parent::tearDown();
    }

    /**
     * Tests backup configs onDeactivate.
     */
    public function testBackupConfigsOnDeactivate()
    {
        $iShopId = $this->_iShopId;
        $sConfigName = 'testConfig1';
        $sConfigValue = 'testValue';
        $sStorageKey = 'oecreditPass.settings';

        $oConfig = Registry::getConfig();

        $oConfig->saveShopConfVar("str", $sConfigName, $sConfigValue, null, 'module:oecreditpass');

        Events::onDeactivate();

        $aBackup = $this->_getData($iShopId, $sStorageKey);

        $sActualConfigValue = $this->_getConfigValueFromArray($aBackup, $sConfigName);

        $this->assertSame($sConfigValue, $sActualConfigValue);

        Events::onActivate();
    }

    /**
     * Tests restore configs onActivate.
     */
    public function testRestoreConfigsOnActivate()
    {
        $iShopId = $this->_iShopId;
        $sConfigName = 'testConfig1';
        $sConfigValue = 'testValue';
        $sStorageKey = 'oecreditPass.settings';

        $aBackup = array(
            array(
                'OXID'        => 'test_config_id',
                'OXSHOPID'    => $iShopId,
                'OXMODULE'    => 'module:oecreditpass',
                'OXVARNAME'   => $sConfigName,
                'OXVARTYPE'   => 'str',
                'OXVARVALUE'  => $this->_dbEncode($sConfigValue),
                'OXTIMESTAMP' => '2014-10-15 10:34:07',
            ),
        );

        $this->_setData($iShopId, $sStorageKey, $aBackup);

        Events::onActivate();

        // Instance of oxConfig was already used internally, it loaded configs and cached them.
        // When new configs are added to database within the same session, oxConfig does not reload them.
        // We are using a new instance of oxConfig to load new configs for assertion.
        /** @var Config $oConfig */
        $oConfig = oxNew(Config::class);
        $sActualConfigValue = $oConfig->getConfigParam($sConfigName);

        $this->assertSame($sConfigValue, $sActualConfigValue);
    }

    /**
     * Sets data into database for testing.
     *
     * @param integer $iShopId Shop Id.
     * @param string  $sKey    Key.
     * @param mixed   $mValue  Value.
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function _setData($iShopId, $sKey, $mValue)
    {
        $sTable = StorageDbShopAwarePersistence::DATABASE_TABLE;

        $sSql = "replace into `{$sTable}` (`SHOPID`, `KEY`, `VALUE`) values (?, ?, ?)";
        $aSqlParameters = array($iShopId, $sKey, $this->_encode($mValue));

        DatabaseProvider::getDb()->execute($sSql, $aSqlParameters);
    }

    /**
     * Gets data from database for testing.
     *
     * @param integer $iShopId Shop Id.
     * @param string  $sKey    Key.
     *
     * @return mixed
     * @throws DatabaseConnectionException
     */
    private function _getData($iShopId, $sKey)
    {
        $sTable = StorageDbShopAwarePersistence::DATABASE_TABLE;

        $sSql = "select `VALUE` from `{$sTable}` where `SHOPID` = ? and `KEY` = ?";
        $aSqlParameters = array($iShopId, $sKey);

        $sEncodedValue = DatabaseProvider::getDb()->getOne($sSql, $aSqlParameters);

        $sValue = $this->_decode($sEncodedValue);

        return $sValue;
    }

    /**
     * Encodes value.
     *
     * @param mixed $mValue Value to be encoded.
     *
     * @return string
     */
    private function _encode($mValue)
    {
        return serialize($mValue);
    }

    /**
     * Decodes value.
     *
     * @param string $sValue Encoded value.
     *
     * @return mixed
     */
    private function _decode($sValue)
    {
        return unserialize($sValue);
    }

    /**
     * Gets config value from config array.
     *
     * @param array  $aConfigs    Config array.
     * @param string $sConfigName Config name.
     *
     * @return string
     */
    private function _getConfigValueFromArray($aConfigs, $sConfigName)
    {
        if (function_exists('array_column')) {
            $aBackupIndexes = array_column($aConfigs, 'OXVARNAME');
        } else {
            $aBackupIndexes = array_map(
                function ($element) {
                    return $element['OXVARNAME'];
                },
                $aConfigs
            );
        }
        $sBackupIdx = array_search($sConfigName, $aBackupIndexes);

        $sEncodedConfigValue = $aConfigs[$sBackupIdx]['OXVARVALUE'];

        $sConfigValue = $this->_dbDecode($sEncodedConfigValue);

        return $sConfigValue;
    }

    /**
     * Encodes value using SQL ENCODE.
     *
     * @param string $sValue Value to encode.
     *
     * @return string
     * @throws DatabaseConnectionException
     */
    private function _dbEncode($sValue)
    {
        $oConfig = Registry::getConfig();
        $oDb = DatabaseProvider::getDb();

        $sConfigKey = $oConfig->getConfigParam('sConfigKey');

        $sSql = "select ENCODE(?, ?)";
        $aSqlParameters = array($sValue, $sConfigKey);

        $sEncodedValue = $oDb->getOne($sSql, $aSqlParameters);

        return $sEncodedValue;
    }

    /**
     * Decodes value using SQL DECODE.
     *
     * @param string $sEncodedValue Encoded value.
     *
     * @return string
     * @throws DatabaseConnectionException
     */
    private function _dbDecode($sEncodedValue)
    {
        $oConfig = Registry::getConfig();
        $oDb = DatabaseProvider::getDb();

        $sConfigKey = $oConfig->getConfigParam('sConfigKey');

        $sSql = "select DECODE(?, ?)";
        $aSqlParameters = array($sEncodedValue, $sConfigKey);

        $sValue = $oDb->getOne($sSql, $aSqlParameters);

        return $sValue;
    }

    /**
     * Clears test data from db tables.
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function _clearTestData()
    {
        DatabaseProvider::getDb()->execute(
            "DELETE FROM `oxconfig` WHERE `OXVARNAME` LIKE ? AND `OXMODULE` = ?",
            array(
                'test%',
                'module:oecreditpass'
            )
        );

        $sStorageTable = StorageDbShopAwarePersistence::DATABASE_TABLE;
        DatabaseProvider::getDb()->execute("DELETE FROM `$sStorageTable`");
    }
}
