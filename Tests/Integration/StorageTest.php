<?php

namespace OxidProfessionalServices\CreditPassModule\Tests\Integration;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\CreditPassModule\Core\Interfaces\ICreditPassStorageShopAwarePersistence;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassStorage;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassStorageDbShopAwarePersistence;

/**
 * Test for CreditPassStorage
 *
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
class StorageTest extends UnitTestCase
{

    /**
     * Current shop id.
     *
     * @var int
     */
    protected $_iShopId;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->_iShopId = $this->getConfig()->getShopId();
        if ('oxbaseshop' === $this->_iShopId) {
            $this->_iShopId = 1;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $this->_clearTestData();
        parent::tearDown();
    }

    /**
     * Clears test data from db tables.
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function _clearTestData()
    {
        $sTable = CreditPassStorageDbShopAwarePersistence::DATABASE_TABLE;
        DatabaseProvider::getDb()->execute("DELETE FROM `{$sTable}`");
    }

    /**
     * Data provider for testSetGetValue
     *
     * @return array
     */
    public function _dpTestSetGetValue()
    {
        return array(
            // same key different value
            array('test_same_key', 'test_value_1'),
            array('test_same_key', 'test_value_2'),
            // different key same value
            array('test_key_1', 'test_same_value'),
            array('test_key_2', 'test_same_value'),
            // different value types
            array('test_key', 'string'),
            array('test_key', 'string with spaces'),
            array('test_key', 123),
            array('test_key', 123.45),
            array('test_key', '123'),
            array('test_key', '123.45'),
            array('test_key', array('mixed', 3, 'value' => 'array')),
            array('test_key', 'a:1:{s:10:"serialized";s:5:"array";}'),
            array('test_key', true),
            array('test_key', false),
            array('test_key', null),
            array('test_key', 'true'),
            array('test_key', 'false'),
            array('test_key', 'null'),
            // special characters
            array('test_key', '£$%^&*()}{@\'#~?><>,@|-=-_+-¬✷☹❱@ᴧ⋁⋀⊿⊽⊼∃∄∆∉∋ꟽⓢⓈŭŬ℟ℛṁ℞ⱤM₲þð⌦'),
            array('test_key', '૱꠸┯┰┱❗┲►◄Ăă0123456789ǕǖꞀ¤Ð¢℥Ω℧Kℶℷℸⅇ⅊⚌⚍⚎⚏⚭⚮⌀⏑⏒⏓⏔⏕⏖⏗⏘⏙⏠⏡⏦ᶁᶀᶂᶃᶄᶆᶇᶈᶊᶉᶋᶌᶍᶎᶏ'),
            array('test_key', 'ᶖᶗᶘᶙᶚᶸᵯᵰᵴᵶᵹᵼᵽᵾᵿ   ⁁⁊ ⸜⸝¶¥£⅕⅙⅛⅔⅖⅗⅘⅜↛↚↙↘↗↖↕↔↓→↑←⅞⅒⅑⅓↉⅝⅐⅚↜↝↞↟↠↡↢↣↤↥↦↧↨↩⤍⤎⤏⤐'),
            array('test_key', 'ᶕ↪↫↬↭↮↯↰⇄⇃⇂⇁⇀↿↾↽↼↻↺↹↸↷↶↵↴↳↲↱⇅⇆⇇⇉⇈⇊⇋⇌⇍⇎⇏⇐⇑⇒⇓⇔⇕⇖⇗⇘⇬⇫⇧⇪⇩⇨⇦⇥⇤⇣⇢⇠⇡⇟⇞⇝⇜⇛⇚⇙ᶒ⤋⤌ᶑ'),
            array('test_key', 'ᶓᶔ⇭⇮⇯⇰⇱⇲⇳⇴⇵⇶⇷⇸⇹⇺⇻⇼⇽⇾⇿⟰⟾⟽⟼⟻⟺⟹⟸⟷⟶⟵⟴⟳⟲⟱⟿⤀⤁⤂⤃⤄⤅⤆⤇⤈⤉⤊⤑⤒ᶐ'),
            array('test_key', 'ᾹὙΚⒶϾΗΔΓΒΐΏΌΉᾥὀᾡ╇╅◎ț₨☹℈₲₭⁂ẟ@®©Ὡ⧴❷☹☹⦝ƴṧṥȘẔŹ⧑ẄqℚĒⒸⓒ⒞ḀÂÃǍǎẤὤὙὛΖᾮᾭᾬᾫ⊛⊘⦿⍂✜'),
        );
    }

    /**
     * Tests set value.
     *
     * @param string $sKey   Key.
     * @param string $mValue Value.
     *
     * @dataProvider _dpTestSetGetValue
     */
    public function testSetValue($sKey, $mValue)
    {
        $iShopId = $this->_iShopId;

        $oCreditPassStorage = CreditPassStorage::createInstance();

        $oCreditPassStorage->setValue($sKey, $mValue);

        $this->assertSame($mValue, $this->_getData($iShopId, $sKey));
    }

    /**
     * Tests get value.
     *
     * @param string $sKey   Key.
     * @param string $mValue Value.
     *
     * @dataProvider _dpTestSetGetValue
     */
    public function testGetValue($sKey, $mValue)
    {
        $iShopId = $this->_iShopId;

        $oCreditPassStorage = CreditPassStorage::createInstance();

        $this->_setData($iShopId, $sKey, $mValue);

        $this->assertSame($mValue, $oCreditPassStorage->getValue($sKey));
    }

    /**
     * Tests set value with other shop.
     */
    public function testSetValueWithOtherShop()
    {
        if ($this->getConfig()->getEdition() != 'EE') {
            $this->markTestSkipped('This shop edition does not support multiple shops.');
        }

        $oCreditPassStorage = CreditPassStorage::createInstance();

        $sKey = 'test_key';
        $iShopId1 = 1;
        $iShopId2 = 2;
        $sValueShop1 = 'test_value_shop_1';
        $sValueShop2 = 'test_value_shop_2';

        $this->setShopId($iShopId1);
        $oCreditPassStorage->setValue($sKey, $sValueShop1);

        $this->setShopId($iShopId2);
        $oCreditPassStorage->setValue($sKey, $sValueShop2);

        $this->assertSame($sValueShop1, $this->_getData($iShopId1, $sKey));
    }

    /**
     * Tests get value with other shop.
     */
    public function testGetValueWithOtherShop()
    {
        if ($this->getConfig()->getEdition() != 'EE') {
            $this->markTestSkipped('This shop edition does not support multiple shops.');
        }

        $oCreditPassStorage = CreditPassStorage::createInstance();

        $sKey = 'test_key';
        $iShopId1 = 1;
        $iShopId2 = 2;
        $sValueShop1 = 'test_value_shop_1';
        $sValueShop2 = 'test_value_shop_2';

        $this->_setData($iShopId1, $sKey, $sValueShop1);
        $this->_setData($iShopId2, $sKey, $sValueShop2);

        $this->setShopId($iShopId1);
        $this->assertSame($sValueShop1, $oCreditPassStorage->getValue($sKey));
    }

    /**
     * Tests set value for other shop.
     */
    public function testSetValueForOtherShop()
    {
        if ($this->getConfig()->getEdition() != 'EE') {
            $this->markTestSkipped('This shop edition does not support multiple shops.');
        }

        $oCreditPassStorage = CreditPassStorage::createInstance();

        $sKey = 'test_key';
        $iShopId1 = 1;
        $iShopId2 = 2;
        $sValueShop1 = 'test_value_shop_1';
        $sValueShop2 = 'test_value_shop_2';

        $this->setShopId($iShopId1);
        $oCreditPassStorage->setValue($sKey, $sValueShop1);
        $this->setShopId($iShopId2);
        $oCreditPassStorage->setValue($sKey, $sValueShop2);

        $this->assertSame($sValueShop2, $this->_getData($iShopId2, $sKey));
    }

    /**
     * Tests get value for other shop.
     */
    public function testGetValueForOtherShop()
    {
        if ($this->getConfig()->getEdition() != 'EE') {
            $this->markTestSkipped('This shop edition does not support multiple shops.');
        }

        $oCreditPassStorage = CreditPassStorage::createInstance();

        $sKey = 'test_key';
        $iShopId1 = 1;
        $iShopId2 = 2;
        $sValueShop1 = 'test_value_shop_1';
        $sValueShop2 = 'test_value_shop_2';

        $this->_setData($iShopId1, $sKey, $sValueShop1);
        $this->_setData($iShopId2, $sKey, $sValueShop2);

        $this->setShopId($iShopId2);
        $this->assertSame($sValueShop2, $oCreditPassStorage->getValue($sKey));
    }

    /**
     * Data provider for testGetShopId.
     *
     * @return array
     */
    public function _dpTestGetShopId()
    {
        return array(
            array('oxbaseshop', 1),
            array(1, 1),
            array(2, 2),
        );
    }

    /**
     * Tests getShopId.
     *
     * @param mixed $mActualShopId   Actual Shop id.
     * @param mixed $mExpectedShopId Expected Shop id.
     *
     * @dataProvider _dpTestGetShopId
     */
    public function testGetShopId($mActualShopId, $mExpectedShopId)
    {
        /**
         * @var PHPUnit_Framework_MockObject_MockObject|ICreditPassStorageShopAwarePersistence $oShopAwarePersistence
         */
        $oShopAwarePersistence = $this->getMock(ICreditPassStorageShopAwarePersistence::class, array('setValue', 'getValue'));
        $oShopAwarePersistence->expects($this->any())->method('setValue');
        $oShopAwarePersistence->expects($this->any())
            ->method('getValue')
            ->with($mExpectedShopId, $this->anything());

        $oCreditPassStorage = new CreditPassStorage(
            Registry::getConfig(),
            $oShopAwarePersistence
        );

        $this->setShopId($mActualShopId);

        $oCreditPassStorage->getValue('test_key');
    }

    /**
     * Sets data into database for testing.
     *
     * @param int    $iShopId Shop Id.
     * @param string $sKey    Key.
     * @param mixed  $mValue  Value.
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function _setData($iShopId, $sKey, $mValue)
    {
        $sTable = CreditPassStorageDbShopAwarePersistence::DATABASE_TABLE;

        $sSql = "insert into `{$sTable}` (`SHOPID`, `KEY`, `VALUE`) values (?, ?, ?)";
        $aSqlParameters = array($iShopId, $sKey, $this->_encode($mValue));

        DatabaseProvider::getDb()->execute($sSql, $aSqlParameters);
    }

    /**
     * Gets data from database for testing.
     *
     * @param int    $iShopId Shop Id.
     * @param string $sKey    Key.
     *
     * @return mixed
     * @throws DatabaseConnectionException
     */
    private function _getData($iShopId, $sKey)
    {
        $sTable = CreditPassStorageDbShopAwarePersistence::DATABASE_TABLE;

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
}
