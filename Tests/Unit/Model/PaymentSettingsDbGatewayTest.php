<?php

namespace OxidProfessionalServices\CreditPassModule\Tests\Unit\Model;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\CreditPassModule\Model\DbGateways\PaymentSettingsDbGateway;

require_once __DIR__ . '/../OxidTestDatabase.php';

/**
 * Class Unit_Models_oeCreditPassPaymentSettingsDbGatewayTest
 * TODO: refactor and move to integration
 */
class PaymentSettingsDbGatewayTest extends UnitTestCase
{

    /**
     * Test oeCreditPass_Payment::getPaymentSettings
     * Check assuming first call to oeCreditPassPaymentSettingsDbGateway::_fetchPaymentSettingsFromDatabase() returns array
     */
    public function testLoadAllMethodFirstMethodSuccess()
    {
        /**
         * @var PaymentSettingsDbGateway $oePaymentSettingsDbController
         */
        $oePaymentSettingsDbController = $this->getMock(
            PaymentSettingsDbGateway::class, array('_fetchPaymentSettingsFromDatabase')
        );
        $oePaymentSettingsDbController->expects($this->once())->method('_fetchPaymentSettingsFromDatabase')->will(
            $this->returnValue(array("x" => "y"))
        );

        $this->assertEquals(array("x" => "y"), $oePaymentSettingsDbController->loadAll());
    }

    public function wrongDataProvider()
    {
        return array(
            array(false),
            array(null)
        );
    }

    /**
     * Test oeCreditPass_Payment::getPaymentSettings
     * Check assuming first call to oeCreditPassPaymentSettingsDbGateway::_fetchPaymentSettingsFromDatabase() fails
     *
     * @dataProvider wrongDataProvider
     *
     * @param $aWrongAnswer
     */
    public function testLoadAllMethodFirstFetchMethodFailureSecondSuccess($aWrongAnswer)
    {
        /**
         * @var PaymentSettingsDbGateway $oePaymentSettingsDbController
         */
        $oePaymentSettingsDbController = $this->getMock(
            PaymentSettingsDbGateway::class, array('_fetchPaymentSettingsFromDatabase')
        );
        $oePaymentSettingsDbController->expects($this->once())->method('_fetchPaymentSettingsFromDatabase')->will(
            $this->returnValue($aWrongAnswer)
        );

        $this->assertEquals(false, $oePaymentSettingsDbController->loadAll());
    }

    /**
     * Provides ids for lod method
     * array of "database" table values
     * and result
     *
     * @return array
     */
    public function idsAndArrays()
    {
        return array(
            array('testid', array('testid' => 2, 'noid' => 1), 2),
            array('testid', array('testidisgone' => 2, 'noid' => 1), false),
        );
    }

    /**
     * Tests loading by single id when nothing is found in database
     *
     * @dataProvider idsAndArrays
     */
    public function testLoad($sId, $aTableData, $sResult)
    {
        /**
         * @var PaymentSettingsDbGateway $oePaymentSettingsDbController
         */
        $oePaymentSettingsDbController = $this->getMock(
            PaymentSettingsDbGateway::class, array('_fetchPaymentSettingsFromDatabase')
        );
        $oePaymentSettingsDbController->expects($this->once())->method('_fetchPaymentSettingsFromDatabase')->will(
            $this->returnValue($aTableData[$sId])
        );

        $this->assertEquals($sResult, $oePaymentSettingsDbController->load($sId));
    }

    /**
     * Check getWhereClause when load method is called
     */
    public function testGetWhereClause()
    {
        /**
         * @var PaymentSettingsDbGateway $oePaymentSettingsDbController
         */
        $oePaymentSettingsDbController = $this->getMock(
            PaymentSettingsDbGateway::class, array('_fetchPaymentSettingsFromDatabase')
        );
        $oePaymentSettingsDbController->expects($this->once())->method('_fetchPaymentSettingsFromDatabase')->will(
            $this->returnValue(null)
        );
        $oePaymentSettingsDbController->load('testid');

        $this->assertEquals(
            " WHERE `oecreditpasspaymentsettings`.`PAYMENTID` = 'testid'",
            $oePaymentSettingsDbController->getWhereClause()
        );
    }

    /**
     * Check save method query generation
     */
    public function testSave()
    {
        oxTestDb::reset();
        oxTestDbResult::reset();

        $oTestDb = new oxTestDb(false);

        /**
         * @var PaymentSettingsDbGateway $oePaymentSettingsDbController
         */
        $oePaymentSettingsDbController = $this->getMock(PaymentSettingsDbGateway::class, array('_getDb'));
        $oePaymentSettingsDbController->expects($this->at(0))->method('_getDb')->will(
            $this->returnValue($oTestDb)
        );

        $aSave = array('PAYMENTID'    => "'cashanddel'",
                       'ACTIVE'       => '1',
                       'ALLOWONERROR' => '0',
                       'FALLBACK'     => '1',
                       'PURCHASETYPE' => '1'
        );
        $oePaymentSettingsDbController->save($aSave);
        // query asserted works when adding with database manager
        $this->assertEquals(
            "INSERT INTO `oecreditpasspaymentsettings` SET `PAYMENTID` = 'cashanddel', `ACTIVE` = 1, `ALLOWONERROR` = 0, `FALLBACK` = 1, `PURCHASETYPE` = 1, `SHOPID` = 1 ON DUPLICATE KEY UPDATE `PAYMENTID`=LAST_INSERT_ID(`PAYMENTID`), `PAYMENTID` = 'cashanddel', `ACTIVE` = 1, `ALLOWONERROR` = 0, `FALLBACK` = 1, `PURCHASETYPE` = 1, `SHOPID` = 1",
            oxTestDb::$sLastExecutedQuery['execute']
        );
    }
}

