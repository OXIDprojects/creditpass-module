<?php

namespace OxidProfessionalServices\CreditPassModule\Tests\Unit\Core;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\CreditPassModule\Core\Interfaces\ICreditPassStorageShopAwarePersistence;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassStorage;

/**
 * Test class for CreditPass Storage
 *
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
class StorageTest extends UnitTestCase
{

    /**
     * Data provider for testSetValue
     *
     * @return array
     */
    public function _dpTestSetValue()
    {
        return array(
            // same key different value
            array('shop_id', 'test_same_key', 'test_value_1'),
            array('shop_id', 'test_same_key', 'test_value_2'),
            // different key same value
            array('shop_id', 'test_key_1', 'test_same_value'),
            array('shop_id', 'test_key_2', 'test_same_value'),
            // different value types
            array('shop_id', 'test_key', 'string'),
            array('shop_id', 'test_key', 'string with spaces'),
            array('shop_id', 'test_key', 123),
            array('shop_id', 'test_key', 123.45),
            array('shop_id', 'test_key', '123'),
            array('shop_id', 'test_key', '123.45'),
            array('shop_id', 'test_key', array('mixed', 3, 'value' => 'array')),
            array('shop_id', 'test_key', 'a:1:{s:10:"serialized";s:5:"array";}'),
            array('shop_id', 'test_key', true),
            array('shop_id', 'test_key', false),
            array('shop_id', 'test_key', null),
            array('shop_id', 'test_key', 'true'),
            array('shop_id', 'test_key', 'false'),
            array('shop_id', 'test_key', 'null'),
            // special characters
            array('shop_id', 'test_key', '£$%^&*()}{@\'#~?><>,@|-=-_+-¬✷☹❱@ᴧ⋁⋀⊿⊽⊼∃∄∆∉∋ꟽⓢⓈŭŬ℟ℛṁ℞ⱤM₲þð⌦'),
            array('shop_id', 'test_key', '૱꠸┯┰┱❗┲►◄Ăă0123456789ǕǖꞀ¤Ð¢℥Ω℧Kℶℷℸⅇ⅊⚌⚍⚎⚏⚭⚮⌀⏑⏒⏓⏔⏕⏖⏗⏘⏙⏠⏡⏦ᶁᶀᶂᶃᶄᶆᶇᶈᶊᶉᶋᶌᶍᶎᶏ'),
            array('shop_id', 'test_key', 'ᶖᶗᶘᶙᶚᶸᵯᵰᵴᵶᵹᵼᵽᵾᵿ   ⁁⁊ ⸜⸝¶¥£⅕⅙⅛⅔⅖⅗⅘⅜↛↚↙↘↗↖↕↔↓→↑←⅞⅒⅑⅓↉⅝⅐⅚↜↝↞↟↠↡↢↣↤↥↦↧↨↩⤍⤎⤏⤐'),
            array('shop_id', 'test_key', 'ᶕ↪↫↬↭↮↯↰⇄⇃⇂⇁⇀↿↾↽↼↻↺↹↸↷↶↵↴↳↲↱⇅⇆⇇⇉⇈⇊⇋⇌⇍⇎⇏⇐⇑⇒⇓⇔⇕⇖⇗⇘⇬⇫⇧⇪⇩⇨⇦⇥⇤⇣⇢⇠⇡⇟⇞⇝⇜⇛⇚⇙ᶒ⤋⤌ᶑ'),
            array('shop_id', 'test_key', 'ᶓᶔ⇭⇮⇯⇰⇱⇲⇳⇴⇵⇶⇷⇸⇹⇺⇻⇼⇽⇾⇿⟰⟾⟽⟼⟻⟺⟹⟸⟷⟶⟵⟴⟳⟲⟱⟿⤀⤁⤂⤃⤄⤅⤆⤇⤈⤉⤊⤑⤒ᶐ'),
            array('shop_id', 'test_key', 'ᾹὙΚⒶϾΗΔΓΒΐΏΌΉᾥὀᾡ╇╅◎ț₨☹℈₲₭⁂ẟ@®©Ὡ⧴❷☹☹⦝ƴṧṥȘẔŹ⧑ẄqℚĒⒸⓒ⒞ḀÂÃǍǎẤὤὙὛΖᾮᾭᾬᾫ⊛⊘⦿⍂✜'),
            // different shops
            array('shop_id_1', 'test_key_1', 'test_value_1_1'),
            array('shop_id_2', 'test_key_2', 'test_value_2_2'),
        );
    }

    /**
     * Tests set value.
     *
     * @param int    $iShopId Shop ID.
     * @param string $sKey    Key.
     * @param string $sValue  Value.
     *
     * @dataProvider _dpTestSetValue
     */
    public function testSetValue($iShopId, $sKey, $sValue)
    {
        /**
         * @var PHPUnit_Framework_MockObject_MockObject|Config $oConfig
         */
        $oConfig = $this->getMock(Config::class, array('getShopId'));
        $oConfig->expects($this->any())->method('getShopId')->will($this->returnValue($iShopId));

        /**
         * @var PHPUnit_Framework_MockObject_MockObject|ICreditPassStorageShopAwarePersistence $oShopAwarePersistence
         */
        $oShopAwarePersistence = $this->getMock(ICreditPassStorageShopAwarePersistence::class, array('setValue', 'getValue'));
        $oShopAwarePersistence->expects($this->once())->method('setValue')->with($iShopId, $sKey, $sValue);
        $oShopAwarePersistence->expects($this->any())->method('getValue');

        $oStorage = new CreditPassStorage($oConfig, $oShopAwarePersistence);

        $oStorage->setValue($sKey, $sValue);
    }

    /**
     * Tests get value.
     */
    public function testGetValue()
    {
        $iShopId = 'shop_id_1';
        $sKey = 'key_1';
        $mValue = 'value_1';

        $aValueMap = array(
            array($iShopId, $sKey, $mValue)
        );

        /**
         * @var PHPUnit_Framework_MockObject_MockObject|Config $oConfig
         */
        $oConfig = $this->getMock(Config::class, array('getShopId'));
        $oConfig->expects($this->any())->method('getShopId')->will($this->returnValue($iShopId));

        /**
         * @var PHPUnit_Framework_MockObject_MockObject|ICreditPassStorageShopAwarePersistence $oShopAwarePersistence
         */
        $oShopAwarePersistence = $this->getMock(ICreditPassStorageShopAwarePersistence::class, array('setValue', 'getValue'));
        $oShopAwarePersistence->expects($this->any())->method('setValue');
        $oShopAwarePersistence->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap($aValueMap));

        $oStorage = new CreditPassStorage($oConfig, $oShopAwarePersistence);

        $this->assertSame($mValue, $oStorage->getValue($sKey, $mValue));
    }
}
