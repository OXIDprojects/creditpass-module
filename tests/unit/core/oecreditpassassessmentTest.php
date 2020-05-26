<?php

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;

/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       tests
 * @copyright (c) anzido GmbH, Andreas Ziethen 2008-2011
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 */
class oeCreditPassAssessment_testmod extends oeCreditPassAssessment
{

    public function __call($sMethod, $aArgs)
    {
        if (defined('OXID_PHP_UNIT')) {
            if (substr($sMethod, 0, 4) == "UNIT") {
                $sMethod = str_replace("UNIT", "_", $sMethod);
            }
            if (method_exists($this, $sMethod)) {
                return call_user_func_array(array(& $this, $sMethod), $aArgs);
            }
        }

        throw new oxSystemComponentException(
            "Function '$sMethod' does not exist or is not accessible! (" . get_class(
                $this
            ) . ")" . PHP_EOL
        );
    }

    public function setNonPublicVar($sVar, $mValue)
    {
        $this->$sVar = $mValue;
    }

    public function getNonPublicVar($sVar)
    {
        return $this->$sVar;
    }
}

class unit_core_oeCreditPassAssessmentTest extends OxidTestCase
{

    public function setUp()
    {
        try {
            DatabaseProvider::getDb()->execute("DELETE FROM oxuser WHERE oxid LIKE 'test_azcr_%'");
        } catch (DatabaseConnectionException $e) {
        } catch (DatabaseErrorException $e) {
        }
        // we need to setup an active user, otherwise some tests will fail because $this->getUser() does not return a valid object:
        $oDummyUser = oxNew('oxUser');
        $oDummyUser->setId('test_azcr_oxuser');
        $oDummyUser->oxuser__oxusername = new oxField('test_azcr_oxuser');
        $oDummyUser->save();
        oxRegistry::getSession()->setVariable('usr', 'test_azcr_oxuser');
    }

    public function tearDown()
    {
        // delete the dummy user:
        oxRegistry::getSession()->deleteVariable('usr');
        $oDummyUser = oxNew('oxUser');
        if ($oDummyUser->load('test_azcr_oxuser')) {
            $oDummyUser->delete();
        }
        try {
            DatabaseProvider::getDb()->Execute("DELETE FROM oxobject2category WHERE oxid = 'test_azcr_o2a'");
        } catch (DatabaseConnectionException $e) {
        } catch (DatabaseErrorException $e) {
        }
    }

    /**
     * helper function to create an functional oeCreditPassAssessment instance
     */
    protected function _azGetObjectWithConfig()
    {
        $sConfigFile = dirname(__FILE__) . '/../../examples/EfiPortletSettings.xml';
        $this->setConfigParam('azBoniSettings', file_get_contents($sConfigFile));
        $oCreditPass = $this->getProxyClass('oeCreditPassAssessment');
        $this->setConfigParam('azBoniSettings', '');

        return $oCreditPass;
    }

    public function testOECreditPassAssessment()
    {
        $iShopId = $this->getShopId();

        // default
        $oCreditPass = $this->getProxyClass('oeCreditPassAssessment');
        $this->assertEquals(1, $oCreditPass->getNonPublicVar('_iShopId'));

        // pe
        $this->setShopId('oxbaseshop');
        $oCreditPass = $this->getProxyClass('oeCreditPassAssessment');
        $this->assertEquals(1, $oCreditPass->getNonPublicVar('_iShopId'));

        // subshops
        $this->setShopId(2);
        $oCreditPass = $this->getProxyClass('oeCreditPassAssessment');
        $this->assertEquals(2, $oCreditPass->getNonPublicVar('_iShopId'));

        $this->setShopId($iShopId);
    }

    public function testXmlErrorHandler()
    {
        $this->markTestSkipped('SKIPPING DUE TO: -2012-07-26 17:27:57 error +2012-07-26 17:27:56 error');

        $oCreditPass = $this->getProxyClass('oeCreditPassAssessment');

        $sErrorFile = $this->getConfigParam('sShopDir') . 'modules/oe/oecreditpass/log/xml_errors.log';
        $sAlternateErrorFile = $this->getConfigParam('sShopDir') . 'modules/oe/oecreditpass/xml_errors.log';

        @file_put_contents($sErrorFile, '');
        @file_put_contents($sAlternateErrorFile, '');

        $oCreditPass->_xmlErrorHandler('error<br>in line<br>666');

        $sError = @file_get_contents($sErrorFile);
        if (!$sError) {
            $sError = @file_get_contents($sAlternateErrorFile);
        }

        $this->assertEquals(date("Y-m-d H:i:s") . " error\n\tin line\n\t666\n", $sError);
    }

    public function testGetInitialData()
    {
        // test with profile id and prio:
        $oCreditPass = $this->getMock('oeCreditPassAssessment_testmod', array('getUser', '_checkStreetNo'));
        $oCreditPass->expects($this->once())->method('getUser');
        $oCreditPass->expects($this->once())->method('_checkStreetNo');
        $oCreditPass->setNonPublicVar('_sPaymentId', null);

        $this->setSessionParam('paymentid', 'testPaymentID');

        $oCreditPass->_getInitialData();
        $this->assertEquals('testPaymentID', $oCreditPass->getNonPublicVar('_sPaymentId'));
    }

    public function testCheckStreetNo()
    {
        $oCreditPass = $this->getProxyClass('oeCreditPassAssessment');
        $oCreditPass->_getInitialData();

        // with errorous street nr
        $oUser = $oCreditPass->getNonPublicVar('_oUser');
        $oUser->oxuser__oxstreet->value = 'Kirchhörder str 12';
        $oUser->oxuser__oxstreetnr->value = '';
        $oCreditPass->setNonPublicVar('_oUser', $oUser);

        $oCreditPass->_checkStreetNo();

        $oUser = $oCreditPass->getNonPublicVar('_oUser');
        $this->assertEquals('Kirchhörder str', $oUser->oxuser__oxstreet->value);
        $this->assertEquals('12', $oUser->oxuser__oxstreetnr->value);

        //with correct street nr
        $oUser = $oCreditPass->getNonPublicVar('_oUser');
        $oUser->oxuser__oxstreet->value = 'Kirchhörder str';
        $oUser->oxuser__oxstreetnr->value = '12';
        $oUser = $oCreditPass->setNonPublicVar('_oUser', $oUser);

        $oCreditPass->_checkStreetNo();

        $oUser = $oCreditPass->getNonPublicVar('_oUser');
        $this->assertEquals('Kirchhörder str', $oUser->oxuser__oxstreet->value);
        $this->assertEquals('12', $oUser->oxuser__oxstreetnr->value);
    }

    public function testGetUser()
    {
        $oCreditPass = $this->getProxyClass('oeCreditPassAssessment');

        $oCreditPass->getUser();

        $this->assertTrue($oCreditPass->getNonPublicVar('_oUser') instanceof oxuser);
    }

    public function testCheckAll()
    {
        $oCrassessment = $this->getMock(
            'oeCreditPassAssessment_testmod', array('_doAssessment', '_doCheck_groupExcl')
        );
        $oCrassessment->expects($this->once())->method('_doAssessment');
        $oCrassessment->expects($this->once())->method('_doCheck_groupExcl')->will($this->returnValue(true));
        $oCrassessment->setNonPublicVar('_blOrderContinue', 'test');
        $this->assertEquals('test', $oCrassessment->checkAll());
    }

    public function testCheckAllIfUserIsExcluded()
    {
        $oCrassessment = $this->getMock(
            'oeCreditPassAssessment_testmod', array('_doIntegratedLogicCheck', '_doCheck_groupExcl')
        );
        $oCrassessment->expects($this->never())->method('_doIntegratedLogicCheck');
        $oCrassessment->expects($this->once())->method('_doCheck_groupExcl')->will($this->returnValue(false));
        $oCrassessment->setNonPublicVar('_blOrderContinue', 'test');
        $this->assertEquals('test', $oCrassessment->checkAll());
    }

    public function testDoCheckGroupExcl()
    {
        // test with user with group exclusion:
        $oCrassessment = $this->getMock('oeCreditPassAssessment_testmod', array('debugLog', '_checkUserGroup'));
        $oCrassessment->setNonPublicVar('_oUser', oxNew('oxUser'));
        $oCrassessment->setNonPublicVar('_blGroupExcluded', 'test');
        $oCrassessment->setNonPublicVar('_blOrderContinue', 'test');
        $oCrassessment->expects($this->once())->method('_checkUserGroup')->will($this->returnValue('testok'));
        $this->assertFalse($oCrassessment->UNITdoCheck_groupExcl());
        $this->assertTrue($oCrassessment->getNonPublicVar('_blOrderContinue'));
        $this->assertEquals('testok', $oCrassessment->getNonPublicVar('_blGroupExcluded'));

        // test with user without group exclusion:
        $oCrassessment = $this->getMock('oeCreditPassAssessment_testmod', array('debugLog', '_checkUserGroup'));
        $oCrassessment->setNonPublicVar('_oUser', oxNew('oxUser'));
        $oCrassessment->setNonPublicVar('_blGroupExcluded', 'test');
        $oCrassessment->setNonPublicVar('_blOrderContinue', 'test');
        $oCrassessment->expects($this->once())->method('_checkUserGroup')->will($this->returnValue(false));
        $this->assertTrue($oCrassessment->UNITdoCheck_groupExcl());
        $this->assertTrue($oCrassessment->getNonPublicVar('_blOrderContinue'));
        $this->assertFalse($oCrassessment->getNonPublicVar('_blGroupExcluded'));

        // test without user:
        $oCrassessment = $this->getMock('oeCreditPassAssessment_testmod', array('debugLog', '_checkUserGroup'));
        $oCrassessment->setNonPublicVar('_oUser', null);
        $oCrassessment->setNonPublicVar('_blGroupExcluded', 'test');
        $oCrassessment->setNonPublicVar('_blOrderContinue', 'test');
        $oCrassessment->expects($this->never())->method('_checkUserGroup');
        $this->assertTrue($oCrassessment->UNITdoCheck_groupExcl());
        $this->assertTrue($oCrassessment->getNonPublicVar('_blOrderContinue'));
        $this->assertFalse($oCrassessment->getNonPublicVar('_blGroupExcluded'));
    }

    public function testIsDebitNote()
    {
        // test with debit note and debit note mapping:
        $oCrassessment = $this->getProxyClass('oeCreditPassAssessment');
        $this->setRequestParam('aAzCreditPassDebitNoteMapping', array('oxiddebitnote'));
        $oCrassessment->setNonPublicVar('_sPaymentId', 'oxiddebitnote');
        $this->assertTrue($oCrassessment->UNITisDebitNote());

        // test with debit note and without debit note mapping:
        $oCrassessment = $this->getProxyClass('oeCreditPassAssessment');
        $this->setRequestParam('aAzCreditPassDebitNoteMapping', array());
        $oCrassessment->setNonPublicVar('_sPaymentId', 'oxiddebitnote');
        $this->assertTrue($oCrassessment->UNITisDebitNote());

        // test without debit note and with debit note mapping:
        $oCrassessment = $this->getProxyClass('oeCreditPassAssessment');
        $this->setRequestParam('aAzCreditPassDebitNoteMapping', array('oxiddebitnote'));
        $oCrassessment->setNonPublicVar('_sPaymentId', 'testnondebitnote');
        $this->assertFalse($oCrassessment->UNITisDebitNote());

        // test without debit not and without debit note mapping:
        $oCrassessment = $this->getProxyClass('oeCreditPassAssessment');
        $this->setRequestParam('aAzCreditPassDebitNoteMapping', array());
        $oCrassessment->setNonPublicVar('_sPaymentId', 'testnondebitnote');
        $this->assertFalse($oCrassessment->UNITisDebitNote());
    }


    public function testSetIntegratedLogicError()
    {
        $oSession = oxRegistry::getSession();
        $oSession->deleteVariable('payerror');
        $oSession->deleteVariable('payerrortext');

        $sErrMsg = oeCreditPassEvents::OECREDITPASS_DEFAULT_ERROR_MSG_DE;

        $oConfig = $this->getMock('oeCreditPassConfig', array('getUnauthorizedErrorMsg'));

        $oConfig->expects($this->at(0))->method('getUnauthorizedErrorMsg')->will(
            $this->returnValue($sErrMsg)
        );

        $oAssessment = $this->getMock('oeCreditPassAssessment_testmod', array('_getOECreditPassConfig'));
        $oAssessment->expects($this->any())->method('_getOECreditPassConfig')->will(
            $this->returnValue($oConfig)
        );

        $oConfig->expects($this->at(1))->method('getUnauthorizedErrorMsg')->will($this->returnValue(""));

        $oAssessment->UNITsetIntegratedLogicError();
        $this->assertEquals('oecreditpassunauthorized_error', $oSession->getVariable('payerror'));
        $this->assertEquals($sErrMsg, $oSession->getVariable('payerrortext'));

        $oAssessment->UNITsetIntegratedLogicError();
        $this->assertEquals(7, $oSession->getVariable('payerror'));
        $this->assertEquals("", $oSession->getVariable('payerrortext'));

        $oSession->deleteVariable('payerror');
        $oSession->deleteVariable('payerrortext');
    }

    public function testCheckUserGroup()
    {
        // test with excluded groups:
        $oUser = $this->getMock('oxUser', array('inGroup'));
        $oUser->expects($this->once())->method('inGroup')->with('testgroupid')->will(
            $this->returnValue(true)
        );

        $this->setConfigParam('aOECreditPassExclUserGroups', array('testgroupid'));
        $oCrassessment = $this->getMock('oeCreditPassAssessment_testmod', array('debugLog'));
        $oCrassessment->expects($this->once())->method('debugLog')->with("group excluded", 'testgroupid');
        $oCrassessment->setNonPublicVar('_oUser', $oUser);
        $oCrassessment->setNonPublicVar('_aBoniSessionData', array('blBoniGroupExclude' => 'test'));
        $this->assertTrue($oCrassessment->UNITcheckUserGroup());
        $aBoniData = $oCrassessment->getNonPublicVar('_aBoniSessionData');
        $this->assertEquals(1, $aBoniData['blBoniGroupExclude']);

        // test with excluded groups but with user not in theses groups:
        $oUser = $this->getMock('oxUser', array('inGroup'));
        $oUser->expects($this->once())->method('inGroup')->with('testgroupid')->will(
            $this->returnValue(false)
        );
        $oCrassessment = $this->getMock('oeCreditPassAssessment_testmod', array('debugLog'));
        $oCrassessment->expects($this->once())->method('debugLog')->with('group excluded', '');
        $oCrassessment->setNonPublicVar('_oUser', $oUser);
        $oCrassessment->setNonPublicVar('_aBoniSessionData', array('blBoniGroupExclude' => 'test'));
        $this->assertFalse($oCrassessment->UNITcheckUserGroup());
        $aBoniData = $oCrassessment->getNonPublicVar('_aBoniSessionData');
        $this->assertEquals(0, $aBoniData['blBoniGroupExclude']);

        // test without excluded groups:
        $this->setConfigParam('aOECreditPassExclUserGroups', array());
        $oCrassessment = $this->getMock('oeCreditPassAssessment_testmod', array('debugLog'));
        $oCrassessment->expects($this->once())->method('debugLog')->with('group excluded', '');
        $oCrassessment->setNonPublicVar('_aBoniSessionData', array('blBoniGroupExclude' => 'test'));
        $this->assertFalse($oCrassessment->UNITcheckUserGroup());
        $aBoniData = $oCrassessment->getNonPublicVar('_aBoniSessionData');
        $this->assertEquals(0, $aBoniData['blBoniGroupExclude']);
    }

    public function testdebugLog()
    {
        $sLogFile = getShopBasePath() . 'modules/oe/oecreditpass/log/session.log';
        file_put_contents($sLogFile, "test");
        $oSession = oxRegistry::getSession();
        $blOldDebug = $this->getConfigParam('blOECreditPassDebug');

        // test without logging:
        $this->setConfigParam('blOECreditPassDebug', false);
        $oSession->setVariable('aBoniDebugData', array());
        $oCrassessment = $this->getProxyClass('oeCreditPassAssessment');
        $oCrassessment->debugLog('testkey', 'testmessage1');
        $this->assertEquals("test", file_get_contents($sLogFile));
        $this->assertEquals(array(), $oSession->getVariable('aBoniDebugData'));

        // test with logging config param:
        $this->setConfigParam('blOECreditPassDebug', true);
        $oSession->setVariable('aBoniDebugData', array());
        $oCrassessment = $this->getProxyClass('oeCreditPassAssessment');
        $oCrassessment->debugLog('testkey', 'testmessage3');
        $this->assertTrue(strpos(file_get_contents($sLogFile), 'testmessage3') > 0);
        $aDebugData = $oSession->getVariable('aBoniDebugData');
        $this->assertEquals('testmessage3', $aDebugData['testkey']);

        // clean up:
        $this->setConfigParam('blOECreditPassDebug', $blOldDebug);
        $oSession->deleteVariable('aBoniDebugData');
        @unlink($sLogFile);
    }

    public function testclearDebugData()
    {
        $oSession = oxRegistry::getSession();
        $oSession->setVariable('aBoniDebugData', 'test');
        $oCrassessment = oxNew('oeCreditPassAssessment');
        $oCrassessment->clearDebugData();
        $this->assertNull($oSession->getVariable('aBoniDebugData'));
    }

    public function testGetPaymentDataHashForCreditCard()
    {
        $aDynValues = array(
            'kktype'   => 'testtype',
            'kknumber' => 'testnumber',
            'kkname'   => ' testname',
            'kkmonth'  => 'testmonth',
            'kkyear'   => 'testyear',
            'kkpruef'  => 'testcheck',
        );
        $oSession = oxRegistry::getSession();
        $oSession->setVariable('dynvalue', $aDynValues);
        $oCrassessment = oxNew('oeCreditPassAssessment_testmod');
        $sExpectedIdent = md5('testtypetestnumbertestnametestmonthtestyeartestcheck');
        $this->assertEquals($sExpectedIdent, $oCrassessment->UNITgetPaymentDataHash());
    }

    public function testGetPaymentDataHashNoData()
    {
        $oSession = oxRegistry::getSession();
        $oSession->setVariable('dynvalue', null);
        $oCrassessment = oxNew('oeCreditPassAssessment_testmod');
        $this->assertFalse($oCrassessment->UNITgetPaymentDataHash());
    }

    public function testGetPaymentDataHashForBank()
    {
        $aDynValues = array(
            'lsbankname'   => 'testbank',
            'lsblz'        => 'testblz',
            'lsktonr'      => ' testnumber',
            'lsktoinhaber' => 'testname',
        );
        $oSession = oxRegistry::getSession();
        $oSession->setVariable('dynvalue', $aDynValues);
        $oCrassessment = oxNew('oeCreditPassAssessment_testmod');
        $sExpectedIdent = md5('testbanktestblztestnumbertestname');
        $this->assertEquals($sExpectedIdent, $oCrassessment->UNITgetPaymentDataHash());
    }

    /**
     * Testing GetAddressIdent()
     */
    public function testGetAddressIdent()
    {
        $oUser = oxNew('oxUser');
        $oUser->oxuser__oxfname = new oxField('testfname');
        $oUser->oxuser__oxlname = new oxField('testlname');
        $oUser->oxuser__oxstreet = new oxField('teststreet');
        $oUser->oxuser__oxstreetnr = new oxField('teststreetnr');
        $oUser->oxuser__oxzip = new oxField('testzip');
        $oUser->oxuser__oxcity = new oxField('testcity');
        $oUser->oxuser__oxcountryid = new oxField('testcountryid');
        $oCrassessment = $this->getProxyClass('oeCreditPassAssessment');
        $oCrassessment->setNonPublicVar('_oUser', $oUser);
        $sExpectedIdent = md5('testfnametestlnameteststreetteststreetnrtestziptestcitytestcountryid');
        $this->assertEquals($sExpectedIdent, $oCrassessment->UNITgetAddressIdent());
    }

    public function testWriteSessionData()
    {
        $oSession = oxRegistry::getSession();
        $oSession->deleteVariable('aBoniSessionData');
        $oCrassessment = $this->getProxyClass('oeCreditPassAssessment');
        $oCrassessment->setNonPublicVar('_aBoniSessionData', 'testdata');
        $oCrassessment->writeSessionData();
        $this->assertEquals('testdata', $oSession->getVariable('aBoniSessionData'));

        // clean up:
        $oSession->deleteVariable('aBoniSessionData');
    }

    public function testDoAssessment()
    {
        //TODO cannot unit test because of curl calls
    }

    public function testGetBoniRequestXML()
    {
        $oSession = oxRegistry::getSession();
        $oOldBasket = $oSession->getBasket();

        try {
            $sCountryId = DatabaseProvider::getDb()->getOne("SELECT oxid FROM oxcountry WHERE oxisoalpha2 = 'DE'");
        } catch (DatabaseConnectionException $e) {
        }

        $oUser = oxNew('oxuser');
        $oUser->assign(
            array(
                'oxfname'     => 'testfname',
                'oxlname'     => 'testlname',
                'oxstreet'    => 'teststreet',
                'oxstreetnr'  => 'teststreetnr',
                'oxzip'       => 'testzip',
                'oxcity'      => 'testcity',
                'oxcompany'   => 'testcompany with very very long name aaaaaaaaaa bbbbbbbbbbbbbbbb cccccccccccccccc',
                'oxcountry'   => $sCountryId,
                'oxbirthdate' => '1989-11-09',
            )
        );

        $oPrice = oxNew('oxPrice', 23.34);
        $oPriceList = oxNew('oxPriceList');
        $oPriceList->addToPriceList(oxNew('oxPrice', 12.34));
        $oBasket = $this->getMock('oxBasket', array('getProductsPrice', 'getPrice'));
        $oBasket->expects($this->any())->method('getProductsPrice')->will($this->returnValue($oPriceList));
        $oBasket->expects($this->any())->method('getPrice')->will($this->returnValue($oPrice));
        $oSession->setBasket($oBasket);

        $aDynValues = array('lsblz' => 'testblz', 'lsktonr' => 'testkto');
        $oSession->setVariable('dynvalue', $aDynValues);

        $this->setConfigParam('sOECreditPassAuthId', 'testcpuser');
        $this->setConfigParam('sOECreditPassAuthPw', 'testcppass');

        $oCrassessment = $this->getMock(
            'oeCreditPassAssessment_testmod', array('_prepareAccountData', '_isTestMode')
        );
        $oCrassessment->expects($this->once())->method('_prepareAccountData')->with($aDynValues)->will(
            $this->returnValue($aDynValues)
        );
        $oCrassessment->expects($this->once())->method('_isTestMode')->will($this->returnValue(false));
        $oCrassessment->setNonPublicVar('_oUser', $oUser);
        $sExpectedXml = '<?xml version="1.0" encoding="UTF-8"?><REQUEST><CUSTOMER><AUTH_ID>testcpuser</AUTH_ID><AUTH_PW>testcppass</AUTH_PW><CUSTOMER_TA_ID>d41d8cd98f00b204e9800998ecf8427e</CUSTOMER_TA_ID></CUSTOMER><PROCESS><TA_TYPE>11202</TA_TYPE><PROCESSING_CODE>1</PROCESSING_CODE><REQUESTREASON>ABK</REQUESTREASON></PROCESS><QUERY><PURCHASE_TYPE></PURCHASE_TYPE><FIRST_NAME>testfname</FIRST_NAME><LAST_NAME>testlname</LAST_NAME><COMPANY_NAME>testcompany with very very long name aaaaaaaaaa bbbbbbbbbbbbbbbb</COMPANY_NAME><ADDR_STREET>teststreet</ADDR_STREET><ADDR_STREET_NO>teststreetnr</ADDR_STREET_NO><ADDR_ZIP>testzip</ADDR_ZIP><ADDR_CITY>testcity</ADDR_CITY><ADDR_COUNTRY></ADDR_COUNTRY><DOB>1989-11-09</DOB><CUSTOMERGROUP /><AMOUNT>2334</AMOUNT><BLZ>testblz</BLZ><KONTONR>000testkto</KONTONR></QUERY></REQUEST>';

        $this->assertEquals($sExpectedXml, $oCrassessment->UNITgetBoniRequestXML(true));

        // clean up:
        $oSession->setBasket($oOldBasket);
        $oSession->deleteVariable('dynvalue');
    }

    /**
     * @return array
     */
    public function _dpGetBoniRequestXMLLongFields()
    {
        return array(
            array(
                array(
                    'sOxfname'    => 'testfname very long very long very long very long very long very long very long very long',
                    'sOxlname'    => 'testlname very long very long very long very long very long very long very long very long',
                    'sOxstreet'   => 'teststreet very long very long very long very long very long very long very long very long very',
                    'sOxstreetnr' => 'teststreetnr very long very long very long very long very long very long very long very long',
                    'sOxzip'      => 'testzip very long very long very long very long very long very long very long very long very long',
                    'sOxcity'     => 'testcity very long very long very long very long very long very long very long very long very long',
                    'sOxcompany'  => 'testcompany very long very long very long very long very long very long very long very long',
                ),
                array(
                    'sOxfname'    => 'testfname very long very long very long very long very long very',
                    'sOxlname'    => 'testlname very long very long very long very long very long very',
                    'sOxstreet'   => 'teststreet very long very long very long very long',
                    'sOxstreetnr' => 'teststreetnr very long very long',
                    'sOxzip'      => 'testzip ',
                    'sOxcity'     => 'testcity very long very long ver',
                    'sOxcompany'  => 'testcompany very long very long very long very long very long ve',
                ),
            ),
            array(
                array(
                    'sOxfname'    => 'testfname < very > long very long very long very long very long very long very long very long',
                    'sOxlname'    => 'testlname very long very long very long very long very long very long very long very long',
                    'sOxstreet'   => 'teststreet very long very long very long very long very long very long very long very long very',
                    'sOxstreetnr' => 'teststreetnr very long very long very long very long very long very long very long very long',
                    'sOxzip'      => 'testzip very long very long very long very long very long very long very long very long very long',
                    'sOxcity'     => 'testcity very long very long very long very long very long very long very long very long very long',
                    'sOxcompany'  => 'testcompany very long very long very long very long very long very long very long very long',
                ),
                array(
                    'sOxfname'    => 'testfname &lt; very &gt; long very long very long very long very',
                    'sOxlname'    => 'testlname very long very long very long very long very long very',
                    'sOxstreet'   => 'teststreet very long very long very long very long',
                    'sOxstreetnr' => 'teststreetnr very long very long',
                    'sOxzip'      => 'testzip ',
                    'sOxcity'     => 'testcity very long very long ver',
                    'sOxcompany'  => 'testcompany very long very long very long very long very long ve',
                ),
            ),
        );
    }

    /**
     * @dataProvider _dpGetBoniRequestXMLLongFields
     * @throws DatabaseConnectionException
     */
    public function testGetBoniRequestXMLLongFields($aFieldValues, $aExpectedFieldValues)
    {
        $oSession = oxRegistry::getSession();
        $oOldBasket = $oSession->getBasket();

        $sCountryId = DatabaseProvider::getDb()->getOne("SELECT oxid FROM oxcountry WHERE oxisoalpha2 = 'DE'");

        $sOxcountry = $sCountryId;
        $sOxbirthdate = '1989-11-09';

        $oUser = oxNew('oxuser');
        $oUser->assign(
            array(
                'oxfname'     => $aFieldValues['sOxfname'],
                'oxlname'     => $aFieldValues['sOxlname'],
                'oxstreet'    => $aFieldValues['sOxstreet'],
                'oxstreetnr'  => $aFieldValues['sOxstreetnr'],
                'oxzip'       => $aFieldValues['sOxzip'],
                'oxcity'      => $aFieldValues['sOxcity'],
                'oxcompany'   => $aFieldValues['sOxcompany'],
                'oxcountry'   => $sOxcountry,
                'oxbirthdate' => $sOxbirthdate,
            )
        );

        $oPrice = oxNew('oxPrice', 23.34);
        $oPriceList = oxNew('oxPriceList');
        $oPriceList->addToPriceList(oxNew('oxPrice', 12.34));
        $oBasket = $this->getMock('oxBasket', array('getProductsPrice', 'getPrice'));
        $oBasket->expects($this->any())->method('getProductsPrice')->will($this->returnValue($oPriceList));
        $oBasket->expects($this->any())->method('getPrice')->will($this->returnValue($oPrice));
        $oSession->setBasket($oBasket);

        $aDynValues = array('lsblz' => 'testblz', 'lsktonr' => 'testkto');
        $oSession->setVariable('dynvalue', $aDynValues);

        $this->setConfigParam('sOECreditPassAuthId', 'testcpuser');
        $this->setConfigParam('sOECreditPassAuthPw', 'testcppass');

        $oCrassessment = $this->getMock(
            'oeCreditPassAssessment_testmod', array('_prepareAccountData', '_isTestMode')
        );
        $oCrassessment->expects($this->once())->method('_prepareAccountData')->with($aDynValues)->will(
            $this->returnValue($aDynValues)
        );
        $oCrassessment->expects($this->once())->method('_isTestMode')->will($this->returnValue(false));
        $oCrassessment->setNonPublicVar('_oUser', $oUser);

        $sExpectedXml = '<?xml version="1.0" encoding="UTF-8"?>'
                        . "<REQUEST>"
                        . "<CUSTOMER>"
                        . "<AUTH_ID>testcpuser</AUTH_ID>"
                        . "<AUTH_PW>testcppass</AUTH_PW>"
                        . "<CUSTOMER_TA_ID>d41d8cd98f00b204e9800998ecf8427e</CUSTOMER_TA_ID>"
                        . "</CUSTOMER>"
                        . "<PROCESS>"
                        . "<TA_TYPE>11202</TA_TYPE>"
                        . "<PROCESSING_CODE>1</PROCESSING_CODE>"
                        . "<REQUESTREASON>ABK</REQUESTREASON>"
                        . "</PROCESS>"
                        . "<QUERY>"
                        . "<PURCHASE_TYPE></PURCHASE_TYPE>"
                        . "<FIRST_NAME>{$aExpectedFieldValues['sOxfname']}</FIRST_NAME>"
                        . "<LAST_NAME>{$aExpectedFieldValues['sOxlname']}</LAST_NAME>"
                        . "<COMPANY_NAME>{$aExpectedFieldValues['sOxcompany']}</COMPANY_NAME>"
                        . "<ADDR_STREET>{$aExpectedFieldValues['sOxstreet']}</ADDR_STREET>"
                        . "<ADDR_STREET_NO>{$aExpectedFieldValues['sOxstreetnr']}</ADDR_STREET_NO>"
                        . "<ADDR_ZIP>{$aExpectedFieldValues['sOxzip']}</ADDR_ZIP>"
                        . "<ADDR_CITY>{$aExpectedFieldValues['sOxcity']}</ADDR_CITY>"
                        . "<ADDR_COUNTRY></ADDR_COUNTRY>"
                        . "<DOB>{$sOxbirthdate}</DOB>"
                        . "<CUSTOMERGROUP />"
                        . "<AMOUNT>2334</AMOUNT>"
                        . "<BLZ>testblz</BLZ>"
                        . "<KONTONR>000testkto</KONTONR>"
                        . "</QUERY>"
                        . "</REQUEST>";

        $this->assertEquals($sExpectedXml, $oCrassessment->UNITgetBoniRequestXML(true));

        // clean up:
        $oSession->setBasket($oOldBasket);
        $oSession->deleteVariable('dynvalue');
    }

    public function testPrepareXML()
    {
        $oCrassessment = oxNew('oeCreditPassAssessment');
        $this->assertEquals(
            'Test &amp; something { &gt; else',
            $oCrassessment->PrepareXML('<b>Test &amp;amp; something &#123; > else</b>')
        );
    }

    public function testPrepareAccountData()
    {
        $oCrassessment = oxNew('oeCreditPassAssessment_testmod');
        $this->assertEquals(
            array('lsblz' => '123456789', 'lsktonr' => '12345678'),
            $oCrassessment->UNITprepareAccountData(array('lsblz' => '123 456-78X9', 'lsktonr' => '123+45!6#7abc8'))
        );
    }

    public function testWriteDebugXML()
    {
        $oSession = oxRegistry::getSession();
        $iTime = time();
        $sTime = date("Y-m-d-H-i-s", $iTime);
        $sFile = getShopBasePath() . 'modules/oe/oecreditpass/xml/' . $sTime . '-testmode.xml';
        @unlink($sFile);
        $sSessionFile = getShopBasePath() . 'modules/oe/oecreditpass/xml/' . $sTime . '-session.txt';
        @unlink($sSessionFile);

        $oCrassessment = $this->getMock('oeCreditPassAssessment_testmod', array('_getCurrentTime'));
        $oCrassessment->expects($this->once())->method('_getCurrentTime')->will($this->returnValue($iTime));
        $oCrassessment->setNonPublicVar('_aBoniSessionData', 'testproperty');
        $oSession->setVariable('aBoniDebugData', 'testsession');
        $oCrassessment->writeDebugXML('testcontent', 'testmode');
        $this->assertEquals('testcontent', file_get_contents($sFile));
        $this->assertEquals("'testproperty'\r\n'testsession'", file_get_contents($sSessionFile));

        // clean up:
        $oSession->deleteVariable('aBoniDebugData');
        @unlink($sFile);
        @unlink($sSessionFile);
    }

    public function testGetTagResult()
    {
        $oCreditPass = $this->getMock(
            'oeCreditPassAssessment_testmod',
            array('getBoniResult', 'xmlParser', 'debugLog'),
            // don't call the constructor:
            array(),
            '',
            false
        );
        $oCreditPass->expects($this->any())->method('getBoniResult')->will($this->returnValue(null));
        $oCreditPass->expects($this->any())->method('debugLog')->will($this->returnValue(null));
        $aXml = array(
            array('tag' => 'testTag', 'type' => 'open'),
            array('tag' => 'testTag', 'type' => '', 'value' => 'testValue'),
            array('tag' => 'testTag', 'type' => 'close'),
        );
        $oCreditPass->expects($this->any())->method('xmlParser')->will($this->returnValue($aXml));

        $this->assertEquals('testValue', $oCreditPass->_getTagResult('testTag', 'testTag'));
    }

    public function testXmlParser()
    {
        $oCreditPass = $this->getProxyClass('oeCreditPassAssessment');
        $sXML = '<?xml version="1.0" encoding="UTF-8"?><root><parent><child>testA</child><child>testB</child></parent></root>';

        $aExample = array(
            array('tag' => 'root', 'type' => 'open', 'level' => '1'),
            array('tag' => 'parent', 'type' => 'open', 'level' => '2'),
            array('tag' => 'child', 'type' => 'complete', 'level' => '3', 'value' => 'testA'),
            array('tag' => 'child', 'type' => 'complete', 'level' => '3', 'value' => 'testB'),
            array('tag' => 'parent', 'type' => 'close', 'level' => '2'),
            array('tag' => 'root', 'type' => 'close', 'level' => '1'),
        );
        $this->assertEquals($aExample, $oCreditPass->xmlParser($sXML, 'test', 'enc'));
    }

    public function testMakeErrorXML()
    {
        $iTime = time();

        $this->setConfigParam('sOECreditPassAuthId', 'testA');
        $oCreditPass = $this->getMock('oeCreditPassAssessment_testmod', array('_oxidGetTime'));
        $oCreditPass->expects($this->once())->method('_oxidGetTime')->will($this->returnValue($iTime));
        $oCreditPass->getUser();

        $n = "\n";
        $sExample = '<?xml version="1.0" encoding="UTF-8"?>' . $n
                    . '<RESPONSE>' . $n
                    . '<CUSTOMER>' . $n
                    . '<AUTH_ID>testA</AUTH_ID>' . $n
                    . '<CUSTOMER_TA_ID>' . md5($iTime . 'test_azcr_oxuser') . '</CUSTOMER_TA_ID>' . $n
                    . '</CUSTOMER>' . $n
                    . '<PROCESS>' . $n
                    . '<TA_TYPE>11202</TA_TYPE>' . $n
                    . '<TA_ID>0</TA_ID>' . $n
                    . '<PROCESSING_CODE>1</PROCESSING_CODE>' . $n
                    . '<REQUESTREASON>ABK</REQUESTREASON>' . $n
                    . '<ANSWER_CODE>-1</ANSWER_CODE>' . $n
                    . '<ANSWER_TEXT>TIMEOUT</ANSWER_TEXT>' . $n
                    . '<KONTOCHECKS>-1</KONTOCHECKS>' . $n
                    . '<ADDR_CHECK>-1</ADDR_CHECK>' . $n
                    . '<PURCHASE_TYPE>0</PURCHASE_TYPE>' . $n
                    . '<INFOSCORE>-1</INFOSCORE>' . $n
                    . '<INFORMASCORE>0</INFORMASCORE>' . $n
                    . '<CONSUMERCREDITCHECK>0</CONSUMERCREDITCHECK>' . $n
                    . '</PROCESS>' . $n
                    . '<COST>' . $n
                    . '<ADDR_CHECK>0.00</ADDR_CHECK>' . $n
                    . '<INFOSCORE>0.00</INFOSCORE>' . $n
                    . '<INFORMASCORE>0.00</INFORMASCORE>' . $n
                    . '<CONSUMERCREDITCHECK>0.00</CONSUMERCREDITCHECK>' . $n
                    . '<TOTAL>0.00</TOTAL>' . $n
                    . '</COST>' . $n
                    . '</RESPONSE>' . $n;

        $this->assertEquals($sExample, $oCreditPass->_makeErrorXML());
    }

    public function testOxidGetTime()
    {
        $oCreditPass = $this->getProxyClass('oeCreditPassAssessment');
        $this->assertTrue(is_integer($oCreditPass->_oxidGetTime()));
    }

    public function testGetCurrentTime()
    {
        $oCreditPass = $this->getProxyClass('oeCreditPassAssessment');
        $this->assertGreaterThan(time() - 100, $oCreditPass->_getCurrentTime());
    }

    //--------------------------------------------------------------------------------------------------------
    //----------NEW TESTS SHOULD BE ADDED HERE----------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------

    /**
     * Test XML response with test mode
     */
    public function testOECreditPassGetRequestXMLWithTestMode()
    {
        $oOECreditPassAssessment = $this->getMock('oeCreditPassAssessment_testmod', array('_isTestMode'));
        $oOECreditPassAssessment->expects($this->any())->method('_isTestMode')->will($this->returnValue(true));

        $iExpected = 8;
        $oResponse = new SimpleXMLElement($oOECreditPassAssessment->UNITgetBoniRequestXML(false));
        $iCode = (int) $oResponse->PROCESS->PROCESSING_CODE;

        $this->assertEquals($iExpected, $iCode, "Processing mode must be set according to test mode");
    }

    /**
     * Test XML response without test mode
     */
    public function testOECreditPassGetRequestXMLWithoutTestMode()
    {
        $oOECreditPassAssessment = $this->getMock('oeCreditPassAssessment_testmod', array('_isTestMode'));
        $oOECreditPassAssessment->expects($this->any())->method('_isTestMode')->will($this->returnValue(false));

        $iExpected = 1;
        $oResponse = new SimpleXMLElement($oOECreditPassAssessment->UNITgetBoniRequestXML(false));
        $iCode = (int) $oResponse->PROCESS->PROCESSING_CODE;

        $this->assertEquals($iExpected, $iCode, "Processing mode must be set according to test mode");
    }

    /**
     * Data includes - $iAnswerCode, ALLOWONERROR, $blIsOrderContinue, $sMsg
     *
     * @return array
     */
    public function _dpTestHandleAnswerCode()
    {
        $sMsgPass = "Order must be allowed to continue";
        $sMsgFail = "Order must be not allowed to continue";

        return array(
            array('-1', false, false, $sMsgFail),
            array('-1', true, true, $sMsgPass),
            array('0', false, true, $sMsgPass),
            array('0', true, true, $sMsgPass),
            array('1', false, false, $sMsgFail),
            array('1', true, false, $sMsgFail),
        );
    }

    /**
     * Testing answer code handling
     *
     * @dataProvider _dpTestHandleAnswerCode
     */
    public function testHandleAnswerCode($iAnswerCode, $blAllowPaymentOnErr, $blIsOrderContinue, $sMsg)
    {
        $oOECreditPassAssessment = $this->getProxyClass('oeCreditPassAssessment_testmod');

        $oOECreditPassAssessment->UNIThandleAnswerCode($iAnswerCode, $blAllowPaymentOnErr);

        $this->assertEquals(
            $blIsOrderContinue, $oOECreditPassAssessment->getNonPublicVar("_blOrderContinue"), $sMsg
        );
    }

    /**
     * Testing manual workflow handling without option when email to admin must be send
     */
    public function testHandleManualWorkflow()
    {
        $oOECreditPassAssessment = $this->getProxyClass('oeCreditPassAssessment_testmod');

        $this->setConfigParam('iOECreditPassManualWorkflow', '0');
        $oOECreditPassAssessment->UNIThandleManualWorkflow();
        $this->assertFalse($oOECreditPassAssessment->getNonPublicVar("_blOrderContinue"), "Must not authorize");

        $this->setConfigParam('iOECreditPassManualWorkflow', '1');
        $oOECreditPassAssessment->UNIThandleManualWorkflow();
        $this->assertTrue($oOECreditPassAssessment->getNonPublicVar("_blOrderContinue"), "Must authorize");
    }

    /**
     * Payment settings provider
     *
     * @return array
     */
    public function paymentSettingsProvider()
    {
        return array(
            array(null, null, null, '1', 'cash'),
            array('1', '0', '0', '3', 'cashondelivery'),
            array(null, null, null, null, null),
        );
    }

    /**
     * Check if payments settings are returning correctly structured data
     *
     * @dataProvider paymentSettingsProvider
     */
    public function testLoadPaymentSettings($sActiveReturns, $sFallbackReturns, $sAllowOnErrorReturns, $sPurchaseType, $sKey)
    {
        $aTestData = $this->_getTestPaymentSettings(
            $sActiveReturns, $sFallbackReturns, $sAllowOnErrorReturns, $sPurchaseType, $sKey
        );
        /** @var oeCreditPassPaymentSettingsDbGateway $oeDbGateway */
        $oeDbGateway = $this->getMock('oeCreditPassPaymentSettingsDbGateway', array('loadAll'));
        $oeDbGateway->expects($this->once())->method('loadAll')->will($this->returnValue($aTestData));
        /** @var oeCreditPassAssessment $oCreditPass */
        $oCreditPass = $this->getMock('oeCreditPassAssessment', array('_getDbGateway'));
        $oCreditPass->expects($this->once())->method('_getDbGateway')->will($this->returnValue($oeDbGateway));

        $this->assertEquals(
            array(array('PAYMENTMETHOD' => $sKey, 'DOVERIFICATION' => $sActiveReturns, 'PAYMENTFALLBACK' => $sFallbackReturns, 'CREDITPASSLOGICNR' => $sPurchaseType, 'ALLOWPAYMENTONERROR' => $sAllowOnErrorReturns,)),
            $oCreditPass->loadPaymentSettings()
        );
    }

    /**
     * Test case for loading payment settings when null is returned
     */
    public function testLoadPaymentSettingsWhenNullReturned()
    {
        /** @var oeCreditPassPaymentSettingsDbGateway $oeDbGateway */
        $oeDbGateway = $this->getMock('oeCreditPassPaymentSettingsDbGateway', array('loadAll'));
        $oeDbGateway->expects($this->once())->method('loadAll')->will($this->returnValue(false));
        /** @var oeCreditPassAssessment $oCreditPass */
        $oCreditPass = $this->getMock('oeCreditPassAssessment', array('_getDbGateway'));
        $oCreditPass->expects($this->once())->method('_getDbGateway')->will($this->returnValue($oeDbGateway));

        $this->assertEquals(false, $oCreditPass->loadPaymentSettings());
    }

    /**
     * Gives test data for database mock, for method loadAll()
     *
     * @param        $sActiveReturns
     * @param        $sFallbackReturns
     * @param        $sAllowOnErrorReturns
     * @param string $sPurchaseType
     * @param string $sKey
     *
     * @return array
     */
    protected function _getTestPaymentSettings($sActiveReturns, $sFallbackReturns, $sAllowOnErrorReturns, $sPurchaseType = 'default', $sKey = 'defaultKey')
    {
        $oPayment = new stdClass();
        $oPayment->oxpayments__purchasetype = new stdClass();
        $oPayment->oxpayments__purchasetype->value = $sPurchaseType;
        $oPayment->oxpayments__active = new stdClass();
        $oPayment->oxpayments__active->value = $sActiveReturns;
        $oPayment->oxpayments__allowonerror = new stdClass();
        $oPayment->oxpayments__allowonerror->value = $sAllowOnErrorReturns;
        $oPayment->oxpayments__fallback = new stdClass();
        $oPayment->oxpayments__fallback->value = $sFallbackReturns;

        return array($sKey => $oPayment);
    }

    /**
     * Test case for building request XML using valid IBAN as account number
     */
    public function testGetBoniRequestXMLWithValidIBAN()
    {
        if (class_exists('oxSepaValidator')) {
            $oOECreditPassAssessment = new oeCreditPassAssessment_testmod();

            $sIBAN = 'DE89370400440532013000';

            $aDebitData = array('lsktonr' => $sIBAN);
            oxRegistry::getSession()->setVariable('dynvalue', $aDebitData);

            $oRequest = new SimpleXMLElement($oOECreditPassAssessment->UNITgetBoniRequestXML(true));

            $this->assertSame(
                $sIBAN, (string) $oRequest->QUERY->IBAN, 'IBAN is valid, so must be inserted to request'
            );
        } else {
            $this->markTestSkipped("Requires oxSepaValidator class from eShop 5.1.x core");
        }
    }

    /**
     * Test case for building request XML using invalid IBAN as account number
     */
    public function testGetBoniRequestXMLWithInvalidIBAN()
    {
        $oOECreditPassAssessment = new oeCreditPassAssessment_testmod();

        $sAccNr = '1234567895';
        $aDebitData = array('lsktonr' => $sAccNr);
        oxRegistry::getSession()->setVariable('dynvalue', $aDebitData);

        $oRequest = new SimpleXMLElement($oOECreditPassAssessment->UNITgetBoniRequestXML(true));

        $sEmpty = '';
        $sIBANInRequest = (string) $oRequest->QUERY->IBAN;
        $this->assertSame($sEmpty, $sIBANInRequest, 'IBAN is invalid, so must not be inserted to request');

        $sAccNrInRequest = (string) $oRequest->QUERY->KONTONR;
        $this->assertSame($sAccNr, $sAccNrInRequest, 'IBAN is invalid, but account number as KONTONR must be there');
    }

    public function testSetGetUser()
    {
        $sUser = "testUser";

        $oOECreditPassAssessment = new oeCreditPassAssessment();
        $oOECreditPassAssessment->setUser($sUser);

        $sResult = $oOECreditPassAssessment->getUser();

        $this->assertSame($sUser, $sResult);
    }

    public function testGetCachedResult()
    {
        $sXML = "this is test xml";
        $sUser = "testUser";
        $sAddress = "testAddress";
        $sPayment = "testPayment";

        $oUser = $this->getMock('oxUser', array('getId'));
        $oUser->expects($this->once())->method('getId')->will($this->returnValue($sUser));

        $oCreditPassResultCache = $this->getMock('oeCreditPassResultCache', array('getData'));
        $oCreditPassResultCache->expects($this->once())->method('getData')->will($this->returnValue($sXML));

        $oOECreditPassAssessment = $this->getMock(
            'oeCreditPassAssessment', array('_getResultCacheObject', '_getAddressIdent', '_getPaymentId')
        );
        $oOECreditPassAssessment->expects($this->once())->method('_getResultCacheObject')->will(
            $this->returnValue($oCreditPassResultCache)
        );
        $oOECreditPassAssessment->expects($this->once())->method('_getAddressIdent')->will(
            $this->returnValue($sAddress)
        );
        $oOECreditPassAssessment->expects($this->once())->method('_getPaymentId')->will(
            $this->returnValue($sPayment)
        );
        $oOECreditPassAssessment->setUser($oUser);

        $sResult = $oOECreditPassAssessment->getCachedResult();

        $this->assertSame($sXML, $sResult);
    }

    public function testCheckAddressChange()
    {
        $oUser = oxNew('oxUser');
        $oUser->setId('testuserid');

        // test without former values:
        $oCrassessment = $this->getMock(
            'oeCreditPassAssessment', array('_getAddressIdent', 'getSessionData', 'setSessionData')
        );
        $oCrassessment->expects($this->any())->method('_getAddressIdent')->will(
            $this->returnValue('testcurrentident1')
        );
        $oCrassessment->expects($this->any())->method('getSessionData')->will(
            $this->returnValue(array('addressIdent' => 'testoldident'))
        );
        $oCrassessment->expects($this->any())->method('setSessionData')->with(
            'addressIdent', 'testcurrentident1'
        );
        $oCrassessment->setUser($oUser);
        $this->assertTrue($oCrassessment->checkAddressChange());

        // test with unchanged ident:
        $oCrassessment = $this->getMock(
            'oeCreditPassAssessment', array('_getAddressIdent', 'getSessionData', 'setSessionData')
        );
        $oCrassessment->expects($this->any())->method('_getAddressIdent')->will(
            $this->returnValue('testcurrentident3')
        );
        $oCrassessment->expects($this->any())->method('getSessionData')->will(
            $this->returnValue(array('addressIdent' => 'testcurrentident3'))
        );
        $oCrassessment->expects($this->any())->method('setSessionData')->with(
            'addressIdent', 'testcurrentident3'
        );
        $oCrassessment->setUser($oUser);
        $this->assertFalse($oCrassessment->checkAddressChange());

        // if no session was set:
        $oCrassessment = $this->getMock(
            'oeCreditPassAssessment', array('_getAddressIdent', 'getSessionData', 'setSessionData')
        );
        $oCrassessment->expects($this->any())->method('_getAddressIdent')->will(
            $this->returnValue('testcurrentident3')
        );
        $oCrassessment->expects($this->any())->method('getSessionData')->will($this->returnValue(null));
        $oCrassessment->expects($this->any())->method('setSessionData')->with(
            'addressIdent', 'testcurrentident3'
        );
        $oCrassessment->setUser($oUser);
        $this->assertTrue($oCrassessment->checkAddressChange());
    }

    /**
     * Test case for bug #5899
     *
     * Checking checkAddressChange() method because at this place addressIdent hash is calculated and checked versus
     * same hash stored earlier in session for current User.
     * The result of this equation defines if CreditPass check results are taken from cache or being reset.
     *
     * (!) Could not test the whole workflow of getting fallback payments with integration test because framework
     * has incompatibilities when switching language and getting payments list in 3rd order step.
     */
    public function testCheckAddressChangeReturnsSameWhenSwitchingLanguage()
    {
        $this->setLanguage(0);

        $oeCreditPassAssessment = new oeCreditPassAssessment();

        $oUser = oxNew('oxUser');
        $oUser->load('oxdefaultadmin');
        $oeCreditPassAssessment->setUser($oUser);

        $oeCreditPassAssessment->checkAddressChange();

        $this->setLanguage(1);

        $oUser = oxNew('oxUser');
        $oUser->load('oxdefaultadmin');
        $oeCreditPassAssessment->setUser($oUser);

        $this->assertFalse($oeCreditPassAssessment->checkAddressChange());
    }

    public function testCheckPaymentDataChange()
    {
        $oOECreditPassAssessment = new oeCreditPassAssessment();
        $this->getSession()->setVar('dynvalue', array('testData1'));
        $oOECreditPassAssessment->checkPaymentDataChange();
        $this->getSession()->setVar('dynvalue', array('testData1'));
        $this->assertFalse($oOECreditPassAssessment->checkPaymentDataChange());
        $this->getSession()->setVar('dynvalue', array('testData2'));
        $this->assertTrue($oOECreditPassAssessment->checkPaymentDataChange());
        $this->getSession()->setVar('dynvalue', null);
    }

    public function testSetGetSessionData()
    {
        $oOECreditPassAssessment = $this->getMock('oeCreditPassAssessment', array('checkAddressChange', 'checkPaymentDataChange'));
        $oOECreditPassAssessment->expects($this->any())->method('checkAddressChange')->will(
            $this->returnValue(false)
        );
        $oOECreditPassAssessment->expects($this->any())->method('checkPaymentDataChange')->will(
            $this->returnValue(false)
        );

        $aData = array('addressIdent' => 'testcurrentident');
        $this->setSessionParam('aBoniSessionData', $aData);

        $this->assertEquals(array('addressIdent' => 'testcurrentident'), $oOECreditPassAssessment->getSessionData());

        $oOECreditPassAssessment->setSessionData('addressIdent', 'newaddress');

        $this->assertEquals(array('addressIdent' => 'newaddress'), $oOECreditPassAssessment->getSessionData());
    }

    public function testGetOnErrorPayments()
    {
        $aPayments = array(array('PAYMENTMETHOD' => 'testpaymentid', 'ALLOWPAYMENTONERROR' => 1),
                           array('PAYMENTMETHOD' => 'testpaymentid2', 'ALLOWPAYMENTONERROR' => 0));
        $aResultPayments = array('testpaymentid');
        $oCrassessment = $this->getMock('oeCreditPassAssessment', array('getPaymentSettings'));
        $oCrassessment->expects($this->any())->method('getPaymentSettings')->will(
            $this->returnValue($aPayments)
        );

        $this->assertSame($aResultPayments, $oCrassessment->getOnErrorPayments());
    }

    public function testGetOnErrorPaymentsNoPaymentsAssigned()
    {
        $oCrassessment = $this->getMock('oeCreditPassAssessment', array('getPaymentSettings'));
        $oCrassessment->expects($this->any())->method('getPaymentSettings')->will($this->returnValue(false));

        $this->assertSame(array(), $oCrassessment->getOnErrorPayments());
    }

    public function testGetFallbackPayments()
    {
        $aPayments = array(array('PAYMENTMETHOD' => 'testpaymentid', 'PAYMENTFALLBACK' => 1),
                           array('PAYMENTMETHOD' => 'testpaymentid2', 'PAYMENTFALLBACK' => 0));
        $aResultPayments = array('testpaymentid');
        $oCrassessment = $this->getMock('oeCreditPassAssessment', array('getPaymentSettings'));
        $oCrassessment->expects($this->any())->method('getPaymentSettings')->will(
            $this->returnValue($aPayments)
        );

        $this->assertSame($aResultPayments, $oCrassessment->getFallbackPayments());
    }

    public function testGetFallbackPaymentsNoPaymentsAssigned()
    {
        $oCrassessment = $this->getMock('oeCreditPassAssessment', array('getPaymentSettings'));
        $oCrassessment->expects($this->any())->method('getPaymentSettings')->will($this->returnValue(false));

        $this->assertSame(array(), $oCrassessment->getFallbackPayments());
    }

    public function testGetAllowedPaymentsAfterResponseAck()
    {
        $oCrassessment = $this->getMock(
            'oeCreditPassAssessment', array('getFallbackPayments', 'getOnErrorPayments', 'getPaymentSettings')
        );
        $oCrassessment->expects($this->never())->method('getFallbackPayments')->will(
            $this->returnValue('onFallback')
        );
        $oCrassessment->expects($this->never())->method('getOnErrorPayments')->will(
            $this->returnValue('onError')
        );

        $this->assertFalse($oCrassessment->getAllowedPaymentsAfterResponse(0));
    }

    public function testGetAllowedPaymentsAfterResponseNack()
    {
        $oCrassessment = $this->getMock('oeCreditPassAssessment', array('getFallbackPayments', 'getOnErrorPayments'));
        $oCrassessment->expects($this->once())->method('getFallbackPayments')->will(
            $this->returnValue('onFallback')
        );
        $oCrassessment->expects($this->never())->method('getOnErrorPayments')->will(
            $this->returnValue('onError')
        );

        $this->assertSame('onFallback', $oCrassessment->getAllowedPaymentsAfterResponse(1));
    }

    public function testGetAllowedPaymentsAfterResponseError()
    {
        $oCrassessment = $this->getMock('oeCreditPassAssessment', array('getFallbackPayments', 'getOnErrorPayments'));
        $oCrassessment->expects($this->never())->method('getFallbackPayments')->will(
            $this->returnValue('onFallback')
        );
        $oCrassessment->expects($this->once())->method('getOnErrorPayments')->will(
            $this->returnValue('onError')
        );

        $this->assertSame('onError', $oCrassessment->getAllowedPaymentsAfterResponse(-1));
    }

    public function testGetAllowedPaymentsAfterResponseManualAck()
    {
        $this->setConfigParam('iOECreditPassManualWorkflow', 1);
        $oCrassessment = $this->getMock('oeCreditPassAssessment', array('getFallbackPayments', 'getOnErrorPayments'));
        $oCrassessment->expects($this->never())->method('getFallbackPayments')->will(
            $this->returnValue('onFallback')
        );
        $oCrassessment->expects($this->never())->method('getOnErrorPayments')->will(
            $this->returnValue('onError')
        );

        $this->assertFalse($oCrassessment->getAllowedPaymentsAfterResponse(2));
    }

    public function testGetAllowedPaymentsAfterResponseManualNack()
    {
        $this->setConfigParam('iOECreditPassManualWorkflow', 0);
        $oCrassessment = $this->getMock('oeCreditPassAssessment', array('getFallbackPayments', 'getOnErrorPayments'));
        $oCrassessment->expects($this->once())->method('getFallbackPayments')->will(
            $this->returnValue('onFallback')
        );
        $oCrassessment->expects($this->never())->method('getOnErrorPayments')->will(
            $this->returnValue('onError')
        );

        $this->assertSame('onFallback', $oCrassessment->getAllowedPaymentsAfterResponse(2));
    }

    public function testGetAllowedPaymentsAfterResponseManual()
    {
        $this->setConfigParam('iOECreditPassManualWorkflow', 2);
        $oCrassessment = $this->getMock('oeCreditPassAssessment', array('getFallbackPayments', 'getOnErrorPayments'));
        $oCrassessment->expects($this->never())->method('getFallbackPayments')->will(
            $this->returnValue('onFallback')
        );
        $oCrassessment->expects($this->never())->method('getOnErrorPayments')->will(
            $this->returnValue('onError')
        );

        $this->assertFalse($oCrassessment->getAllowedPaymentsAfterResponse(2));
    }

    public function testGetAllowedPaymentMethodsNoCallToCreditPass()
    {
        $oCrassessment = $this->getMock('oeCreditPassAssessment', array('getAllowedPaymentsAfterResponse'));
        $oCrassessment->expects($this->never())->method('getAllowedPaymentsAfterResponse')->will(
            $this->returnValue('AllPayments')
        );

        $this->assertFalse($oCrassessment->getAllowedPaymentMethods());
    }

    public function testGetAllowedPaymentMethods()
    {
        $oCrassessment = $this->getMock('oeCreditPassAssessment', array('getAllowedPaymentsAfterResponse'));
        $oCrassessment->expects($this->once())->method('getAllowedPaymentsAfterResponse')->will(
            $this->returnValue('AllPayments')
        );
        $oCrassessment->setSessionData('azIntLogicResponse', 1);

        $this->assertSame('AllPayments', $oCrassessment->getAllowedPaymentMethods());
    }

    public function testGetCachedAndRejectedPayments()
    {
        $sUser = "testUser";
        $sAddress = "testAddress";

        $oUser = $this->getMock('oxUser', array('getId'));
        $oUser->expects($this->once())->method('getId')->will($this->returnValue($sUser));

        $oResultCache = $this->getMock('oeCreditPassResultCache', array('getRejectedPaymentIds'));
        $oResultCache->expects($this->once())->method('getRejectedPaymentIds')->with(1)->will(
            $this->returnValue('AllPayments')
        );

        $oCrassessment = $this->getMock('oeCreditPassAssessment', array('_getResultCacheObject', '_getAddressIdent'));
        $oCrassessment->expects($this->once())->method('_getResultCacheObject')->will(
            $this->returnValue($oResultCache)
        );
        $oCrassessment->expects($this->once())->method('_getAddressIdent')->will($this->returnValue($sAddress));
        $oCrassessment->setUser($oUser);

        $this->assertSame('AllPayments', $oCrassessment->getCachedAndRejectedPayments());
    }

    public function testFilterPaymentMethodsOnlyFallbackMethodsAllowed()
    {
        $aAllPayments = array('testpaymentid'  => 'testpaymentid',
                              'testpaymentid2' => 'testpaymentid2',
                              'testpaymentid3' => 'testpaymentid3');
        $aAllowedPayments = array('testpaymentid');
        $oCrassessment = $this->getMock('oeCreditPassAssessment', array('getAllowedPaymentMethods'));
        $oCrassessment->expects($this->any())->method('getAllowedPaymentMethods')->will(
            $this->returnValue($aAllowedPayments)
        );

        $this->assertEquals(1, count($oCrassessment->filterPaymentMethods($aAllPayments)));
    }

    public function testFilterPaymentMethodsNoFallbackMethodsAllowed()
    {
        $aAllPayments = array('testpaymentid'  => 'testpaymentid',
                              'testpaymentid2' => 'testpaymentid2',
                              'testpaymentid3' => 'testpaymentid3');
        $oCrassessment = $this->getMock('oeCreditPassAssessment', array('getAllowedPaymentMethods'));
        $oCrassessment->expects($this->any())->method('getAllowedPaymentMethods')->will(
            $this->returnValue(array())
        );

        $this->assertEquals(0, count($oCrassessment->filterPaymentMethods($aAllPayments)));
    }

    public function testFilterPaymentMethodsFromCache()
    {
        $aAllPayments = array('testpaymentid'  => 'testpaymentid',
                              'testpaymentid2' => 'testpaymentid2',
                              'testpaymentid3' => 'testpaymentid3');
        $aRejectedPayments = array('testpaymentid');
        $oCrassessment = $this->getMock(
            'oeCreditPassAssessment', array('getCachedAndRejectedPayments', 'getAllowedPaymentMethods')
        );
        $oCrassessment->expects($this->any())->method('getCachedAndRejectedPayments')->will(
            $this->returnValue($aRejectedPayments)
        );
        $oCrassessment->expects($this->any())->method('getAllowedPaymentMethods')->will(
            $this->returnValue(false)
        );

        $this->assertEquals(2, count($oCrassessment->filterPaymentMethods($aAllPayments)));
    }

    public function testFilterPaymentMethodsNoPaymentsToFilter()
    {
        $aAllPayments = array('testpaymentid'  => 'testpaymentid',
                              'testpaymentid2' => 'testpaymentid2',
                              'testpaymentid3' => 'testpaymentid3');
        $oCrassessment = $this->getMock('oeCreditPassAssessment', array('getAllowedPaymentMethods'));
        $oCrassessment->expects($this->any())->method('getAllowedPaymentMethods')->will(
            $this->returnValue(false)
        );

        $this->assertSame(3, count($oCrassessment->filterPaymentMethods($aAllPayments)));
    }

}
