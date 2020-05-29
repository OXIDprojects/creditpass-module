<?php

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassAssessment;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassResponseLogger;
use OxidProfessionalServices\CreditPassModule\Model\Order;

/**
 * Response logger integration test
 */
class ResponseLoggerTest extends UnitTestCase
{

    /**
     * Set up
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function setUp()
    {
        DatabaseProvider::getDb()->execute("DELETE FROM oxuser WHERE oxid LIKE 'test_azcr_%'");
        // we need to setup an active user, otherwise some tests will fail because $this->getUser() does not return a valid object:
        $oDummyUser = oxNew(User::class);
        $oDummyUser->setId('test_azcr_oxuser');
        $oDummyUser->oxuser__oxusername = new Field('test_azcr_oxuser');
        $oDummyUser->save();
        Registry::getSession()->setVariable('usr', 'test_azcr_oxuser');
        // TODO: truncate test logs on setup, instead of clearing one test log before assertion (in case of fatal err, test fails)
    }

    /**
     * Tear down
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function tearDown()
    {
        Registry::getSession()->deleteVariable('usr');
        $oDummyUser = oxNew(User::class);
        if ($oDummyUser->load('test_azcr_oxuser')) {
            $oDummyUser->delete();
        }
        DatabaseProvider::getDb()->execute("DELETE FROM oxobject2category WHERE oxid = 'test_azcr_o2a'");
    }

    /**
     * Clear test data of log table
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected function _clearLog($sId)
    {
        DatabaseProvider::getDb()->execute("DELETE FROM `oecreditpasslog` WHERE `id` = '{$sId}'");
    }

    /**
     * @return string
     */
    protected function _getLogTableFieldsString()
    {
        $sFields = "USERID, ORDERID, ANSWERCODE, ANSWERTEXT, ";
        $sFields .= "ANSWERDETAILS, TRANSACTIONID, CUSTOMERTRANSACTIONID";

        return $sFields;
    }

    /**
     * @return string
     */
    protected function _getLogTableFieldsArray()
    {
        $aFields = array(
            "USERID",
            "ORDERID",
            "ANSWERCODE",
            "ANSWERTEXT",
            "ANSWERDETAILS",
            "TRANSACTIONID",
            "CUSTOMERTRANSACTIONID"
        );

        return $aFields;
    }

    /**
     * Data provider.
     */
    public function _dpResponseXMLDataProvider()
    {
        return array(
            array(
                file_get_contents(__DIR__ . '/../fixtures/error.xml'),
                array(
                    'USERID'                => null,
                    'ORDERID'               => null,
                    'ANSWERCODE'            => -1,
                    'ANSWERTEXT'            => 'keine Aussage: Simulation',
                    'ANSWERDETAILS'         => null,
                    'TRANSACTIONID'         => '21E2C7A511504EF1A65FD38FACB8533E',
                    'CUSTOMERTRANSACTIONID' => '8e0b49fe06add3932379b10ec23cad95'
                )
            ),
            array(
                file_get_contents(__DIR__ . '/../fixtures/manual.xml'),
                array(
                    'USERID'                => null,
                    'ORDERID'               => null,
                    'ANSWERCODE'            => 2,
                    'ANSWERTEXT'            => 'Bitte manuell pruefen',
                    'ANSWERDETAILS'         => 'INFOSCORE~Aehnlich~Mittel(UF 10.04.2007)$INFORMASCORE~undefined$ADRESSCHECK~PersonBekannt~Testweg~1~123445~Testhausen',
                    'TRANSACTIONID'         => '6A4E6994C1594A3EBA98C05EFD3E1CA7',
                    'CUSTOMERTRANSACTIONID' => 'b4f4705c511f8396b3d5db879a341c0a'
                )
            ),
            array(
                file_get_contents(__DIR__ . '/../fixtures/not_authorized.xml'),
                array(
                    'USERID'                => null,
                    'ORDERID'               => null,
                    'ANSWERCODE'            => 1,
                    'ANSWERTEXT'            => 'NICHT autorisiert',
                    'ANSWERDETAILS'         => 'INFOSCORE~Treffer~Hart(EV 12.03.2008)$INFORMASCORE~undefined$ADRESSCHECK~PersonBekannt~Testweg~1~12345~Testhausen',
                    'TRANSACTIONID'         => 'AD1452815D9B4223805ED2DB02C31EA8',
                    'CUSTOMERTRANSACTIONID' => '93dd4bff1e2284c0eb9dcd6523d3afa4'
                )
            ),
            array(
                file_get_contents(__DIR__ . '/../fixtures/authorized.xml'),
                array(
                    'USERID'                => null,
                    'ORDERID'               => null,
                    'ANSWERCODE'            => 0,
                    'ANSWERTEXT'            => 'autorisiert',
                    'ANSWERDETAILS'         => 'INFOSCORE~Kein$INFORMASCORE~530$ADRESSCHECK~PersonBekanntKorrAnschrift~Testweg~30~54321~Teststadt',
                    'TRANSACTIONID'         => '9A4372EF5A1643A8A001561787431D55',
                    'CUSTOMERTRANSACTIONID' => '66b81eae2682d70a8db2a9600b282d3c'
                )
            )
        );
    }

    /**
     * Test case for logging different data.
     *
     * @dataProvider _dpResponseXMLDataProvider
     * @throws       DatabaseConnectionException
     */
    public function testSave($sResponseXML, $aDatabaseResults)
    {
        $oeCreditPassAssessment = $this->getProxyClass(CreditPassAssessment::class);
        $oDb = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
        $oLogger = new CreditPassResponseLogger();

        $sTransId = $aDatabaseResults['TRANSACTIONID'];

        $aResponseXML = $oeCreditPassAssessment->xmlParser($sResponseXML, 'testSave');
        $this->assertTrue($oLogger->save($aResponseXML));

        $aResult = $oDb->getRow("SELECT * FROM `oecreditpasslog` WHERE `TRANSACTIONID` = '{$sTransId}'");

        $this->assertEquals($aResult['ID'], $oLogger->getLastID(), "Last inserted ID");

        foreach ($aDatabaseResults as $sField => $sValue) {
            $this->assertEquals($sValue, $aResult[$sField], $sField);
        }
    }

    /**
     * Test case for updating log with different data.
     *
     * @throws DatabaseConnectionException
     */
    public function testUpdate()
    {
        $oeCreditPassAssessment = new CreditPassAssessment();
        $sResponseXML = file_get_contents(__DIR__ . '/../fixtures/authorized.xml');
        $oDb = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);

        $oLogger = new CreditPassResponseLogger();

        $aResponseXML = $oeCreditPassAssessment->xmlParser($sResponseXML, 'testUpdate');

        $oLogger->save($aResponseXML);
        $sCreditPassId = $oLogger->getLastID();

        /**
         * @var User $oDummyUser
         */
        $oDummyUser = oxNew(User::class);
        $oDummyUser->load('test_azcr_oxuser');
        $sUserId = $oDummyUser->getId();

        $oLogger->getLogger()->setPrimaryUpdateFieldName('ID');
        $oLogger->update(
            array(
                'CACHED'  => 1,
                'USER_ID' => $sUserId,
                'ID'      => $sCreditPassId,
            )
        );

        $aResult = $oDb->getRow("SELECT * FROM `oecreditpasslog` WHERE `ID` = '{$sCreditPassId}'");

        $this->_clearLog($sCreditPassId);

        $this->assertEquals($aResult['USERID'], $sUserId, "User ID");
        $this->assertEquals($aResult['CACHED'], 1, "Cached entry");
    }

    /**
     * Test case for updating log:
     * First - without user check (3rd checkout step logging)
     * Second - with user check (5th checkout step logging)
     *
     * Using same user for both checks, expecting order id to be saved
     *
     * @ticket https://bugs.oxid-esales.com/view.php?id=5623
     * @throws DatabaseConnectionException
     */
    public function testUpdateWithUserCheckSameUser()
    {
        $oeCreditPassAssessment = new CreditPassAssessment();
        $sResponseXML = file_get_contents(__DIR__ . '/../fixtures/authorized.xml');
        $oDb = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);

        $oLogger = new CreditPassResponseLogger();

        $aResponseXML = $oeCreditPassAssessment->xmlParser($sResponseXML, 'testUpdate');

        $oLogger->save($aResponseXML);
        $sCreditPassId = $oLogger->getLastID();

        /**
         * @var User $oDummyUser
         */
        $oDummyUser = oxNew(User::class);
        $oDummyUser->load('test_azcr_oxuser');
        $sUserId = $oDummyUser->getId();

        $oLogger->getLogger()->setPrimaryUpdateFieldName('ID');
        $oLogger->update(
            array(
                'CACHED'  => 1,
                'USER_ID' => $sUserId,
                'ID'      => $sCreditPassId,
            )
        );

        $aResult = $oDb->getRow("SELECT * FROM `oecreditpasslog` WHERE `ID` = '{$sCreditPassId}'");

        $this->assertEquals($aResult['USERID'], $sUserId, "User ID");
        $this->assertEquals($aResult['CACHED'], 1, "Cached entry");

        // now will try to update
        $oLogger->getLogger()->setUserId($sUserId);
        $oLogger->update(
            array(
                'ORDER_ID' => 12345,
                'ID'       => $sCreditPassId,
            )
        );

        $aResult = $oDb->getRow("SELECT * FROM `oecreditpasslog` WHERE `ID` = '{$sCreditPassId}'");
        $this->_clearLog($sCreditPassId);

        $this->assertEquals($aResult['ORDERID'], 12345, "Order ID");
    }

    /**
     * Test case for updating log with different data.
     * First - without user check (3rd checkout step logging)
     * Second - with user check (5th checkout step logging)
     *
     * Using different user for both checks, expecting order id to not be saved
     *
     * @ticket https://bugs.oxid-esales.com/view.php?id=5623
     * @throws DatabaseConnectionException
     */
    public function testUpdateWithUserCheckDifferentUser()
    {
        $oeCreditPassAssessment = new CreditPassAssessment();
        $sResponseXML = file_get_contents(__DIR__ . '/../fixtures/authorized.xml');
        $oDb = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);

        $oLogger = new CreditPassResponseLogger();

        $aResponseXML = $oeCreditPassAssessment->xmlParser($sResponseXML, 'testUpdate');

        $oLogger->save($aResponseXML);
        $sCreditPassId = $oLogger->getLastID();

        /**
         * @var User $oDummyUser
         */
        $oDummyUser = oxNew(User::class);
        $oDummyUser->load('test_azcr_oxuser');
        $sUserId = $oDummyUser->getId();

        $oLogger->getLogger()->setPrimaryUpdateFieldName('ID');
        $oLogger->update(
            array(
                'CACHED'  => 1,
                'USER_ID' => $sUserId,
                'ID'      => $sCreditPassId,
            )
        );

        $aResult = $oDb->getRow("SELECT * FROM `oecreditpasslog` WHERE `ID` = '{$sCreditPassId}'");

        $this->assertEquals($aResult['USERID'], $sUserId, "User ID");
        $this->assertEquals($aResult['CACHED'], 1, "Cached entry");

        // now will try to update
        $oLogger->getLogger()->setUserId('different_user');
        $oLogger->update(
            array(
                'ORDER_ID' => 12345,
                'ID'       => $sCreditPassId,
            )
        );

        $aResult = $oDb->getRow("SELECT * FROM `oecreditpasslog` WHERE `ID` = '{$sCreditPassId}'");
        $this->_clearLog($sCreditPassId);

        $this->assertEquals($aResult['ORDERID'], '', "Order ID");
    }

    /**
     * Test case for loading log data.
     *
     * @throws DatabaseConnectionException
     */
    public function testLoad()
    {
        $oeCreditPassAssessment = new CreditPassAssessment();
        $sResponseXML = file_get_contents(__DIR__ . '/../fixtures/authorized.xml');
        $oDb = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);

        $oLogger = new CreditPassResponseLogger();

        $aResponseXML = $oeCreditPassAssessment->xmlParser($sResponseXML, 'testLoad');

        $oLogger->save($aResponseXML);
        $sCreditPassId = $oLogger->getLastID();

        /**
         * @var User $oDummyUser
         */
        $oDummyUser = oxNew(User::class);
        $oDummyUser->load('test_azcr_oxuser');
        $sUserId = $oDummyUser->getId();

        /**
         * @var Order $oDummyOrder
         */
        $oDummyOrder = oxNew(Order::class);
        $oDummyOrder->save();
        $sOrderId = $oDummyOrder->getId();

        $oLogger->getLogger()->setPrimaryUpdateFieldName('ID');

        $oLogger->update(
            array(
                'USER_ID' => $sUserId,
                'ID'      => $sCreditPassId,
            )
        );

        $oLogger->update(
            array(
                'ORDER_ID' => $sOrderId,
                'ID'       => $sCreditPassId,
            )
        );

        $aResult = $oLogger->load($sCreditPassId);
        $aExpected = $oDb->getRow(
            "SELECT {$this->_getLogTableFieldsString()} FROM `oecreditpasslog` WHERE `ID` = '{$sCreditPassId}'"
        );

        $aFields = $this->_getLogTableFieldsArray();

        $this->_clearLog($sCreditPassId);

        foreach ($aFields as $sField) {
            $this->assertEquals($aExpected[$sField], $aResult[$sField], $sField);
        }

        $sExpCustNr = $oDb->getOne("SELECT oxcustnr FROM `oxuser` WHERE `oxid` = '{$sUserId}'");
        $sExpOrderNr = $oDb->getOne("SELECT oxordernr FROM `oxorder` WHERE `oxid` = '{$sOrderId}'");

        $this->assertEquals($sExpCustNr, $aResult['CUSTNR'], 'CUSTNR');
        $this->assertEquals($sExpOrderNr, $aResult['ORDERNR'], 'ORDERNR');
    }

    /**
     * Test loading all log list.
     */
    public function testLoadAll()
    {
        $this->markTestIncomplete("incomplete");
    }

    /**
     * Tests searching log data.
     */
    public function testSearch()
    {
        $this->markTestIncomplete("incomplete");
    }
}
