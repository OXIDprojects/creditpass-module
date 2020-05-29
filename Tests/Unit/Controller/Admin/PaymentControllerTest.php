<?php

namespace OxidProfessionalServices\CreditPassModule\Tests\Unit\Controller\Admin;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\CreditPassPaymentController;
use OxidProfessionalServices\CreditPassModule\Model\DbGateways\CreditPassPaymentSettingsDbGateway;

/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       tests
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 */
class PaymentControllerTest extends UnitTestCase
{

    /**
     * Test that render return proper template
     */
    public function testRender()
    {
        $oPaymentController = new CreditPassPaymentController();
        $this->assertEquals('oecreditpass_payment.tpl', $oPaymentController->render());
    }

    /**
     * Test that payments are properly received
     * It should save to database, but we want to avoid database connection
     */
    public function testSave()
    {
        $aSave = array(
            'cashanddel-1' => array(
                'ID'           => "'cashanddel-1'",
                'PAYMENTID'    => "'cashanddel'",
                'ACTIVE'       => '1',
                'ALLOWONERROR' => '0',
                'FALLBACK'     => '1',
                'PURCHASETYPE' => '1'
            ),
            'creditcard-1' => array(
                'ID'           => "'creditcard-1'",
                'PAYMENTID'    => "'creditcard'",
                'ACTIVE'       => '1',
                'ALLOWONERROR' => '0',
                'FALLBACK'     => '1',
                'PURCHASETYPE' => '1'
            ),
        );
        $this->getConfig()->setParameter('aPaymentSettings', $aSave);

        /**
         * @var CreditPassPaymentSettingsDbGateway $oPaymentController
         */
        $oPaymentDbGateway = $this->getMock(CreditPassPaymentSettingsDbGateway::class, array('save'));
        $oPaymentDbGateway->expects($this->exactly(2))->method('save');

        /**
         * @var CreditPassPaymentController $oPaymentController
         */
        $oPaymentController = $this->getMock(CreditPassPaymentController::class, array('_getDbGateWay'));
        $oPaymentController->expects($this->exactly(2))->method('_getDbGateWay')->will(
            $this->returnValue($oPaymentDbGateway)
        );
        $oPaymentController->save($aSave);
    }

    public function testSavePurchaseTypeNotSet()
    {
        $aSave = array(
            'cashanddel-1' => array(
                'ID'           => "'cashanddel-1'",
                'PAYMENTID'    => "'cashanddel'",
                'ACTIVE'       => '1',
                'ALLOWONERROR' => '0',
                'FALLBACK'     => '1',
                'PURCHASETYPE' => null
            ),
            'creditcard-1' => array(
                'ID'           => "'creditcard-1'",
                'PAYMENTID'    => "'creditcard'",
                'ACTIVE'       => '1',
                'ALLOWONERROR' => '0',
                'FALLBACK'     => '1',
                'PURCHASETYPE' => '1'
            ),
        );
        $this->getConfig()->setParameter('aPaymentSettings', $aSave);

        /**
         * @var CreditPassPaymentController $oPaymentController
         */
        $oPaymentController = $this->getMock(CreditPassPaymentController::class, array('_getDbGateWay'));
        $oPaymentController->expects($this->once())->method('_getDbGateWay')->will(
            $this->returnValue(new CreditPassPaymentSettingsDbGateway())
        );
        $oPaymentController->save($aSave);

        $aErr = $this->getSessionParam('Errors');
        $oErr = unserialize($aErr['default'][0]);
        $this->assertEquals('OECREDITPASS_EXCEPTION_PURCHASETYPENOTSET', $oErr->getOxMessage());
    }

    public function testSaveWithNoParameters()
    {
        $aSave = $this->getConfig()->setParameter('aPaymentSettings', null);

        /**
         * @var CreditPassPaymentSettingsDbGateway $oPaymentController
         */
        $oPaymentDbGateway = $this->getMock(CreditPassPaymentSettingsDbGateway::class, array('save'));
        $oPaymentDbGateway->expects($this->never())->method('save');

        /**
         * @var CreditPassPaymentController $oPaymentController
         */
        $oPaymentController = $this->getMock(CreditPassPaymentController::class, array('_getDbGateWay'));
        $oPaymentController->expects($this->never())->method('_getDbGateWay')->will(
            $this->returnValue($oPaymentDbGateway)
        );
        $oPaymentController->save($aSave);
    }

    /**
     * Test getSubmittedPaymentSettings
     * getSubmittedPaymentSettings should return array like this:
     * * array( 'group' => array( 'key' => value ) ),
     * * where group - Payment setting group, key - Payment setting ID, value - its value
     */
    public function testSubmittedPaymentSettingsMethod()
    {
        $this->getConfig()->setParameter(
            'aPaymentSettings',
            array('g1' => array('one' => 'two', 'oneone' => 'twotwo'), 'g2' => array('three' => 'four'))
        );

        $oPaymentController = new CreditPassPaymentController();
        $this->assertEquals(
            array('g1' => array('one' => 'two', 'oneone' => 'twotwo'), 'g2' => array('three' => 'four')),
            $oPaymentController->getSubmittedPaymentSettings()
        );
    }

    /**
     * Tests if Payment settings are retrieved and handled properly
     */
    public function testGetPaymentSettings()
    {
        $aTestData = $this->_getTestPaymentSettings('1', null, '0');
        /**
         * @var CreditPassPaymentSettingsDbGateway $oPaymentController
         */
        $oPaymentDbGateway = $this->getMock(CreditPassPaymentSettingsDbGateway::class, array('loadAll'));
        $oPaymentDbGateway->expects($this->once())->method('loadAll')->will($this->returnValue($aTestData));

        /**
         * @var CreditPassPaymentController $oPaymentController
         */
        $oPaymentController = $this->getMock(CreditPassPaymentController::class, array('_getDbGateWay'));
        $oPaymentController->expects($this->once())->method('_getDbGateWay')->will(
            $this->returnValue($oPaymentDbGateway)
        );

        $this->assertEquals($aTestData, $oPaymentController->getPaymentSettings());
    }

    /**
     * Test if same data is returned when calling the method twice
     */
    public function testGetPaymentSettingsGettingTwice()
    {
        $aTestData = $this->_getTestPaymentSettings('1', null, '0');
        /**
         * @var CreditPassPaymentSettingsDbGateway $oPaymentController
         */
        $oPaymentDbGateway = $this->getMock(CreditPassPaymentSettingsDbGateway::class, array('loadAll'));
        $oPaymentDbGateway->expects($this->once())->method('loadAll')->will($this->returnValue($aTestData));

        /**
         * @var CreditPassPaymentController $oPaymentController
         */
        $oPaymentController = $this->getMock(CreditPassPaymentController::class, array('_getDbGateWay'));
        $oPaymentController->expects($this->once())->method('_getDbGateWay')->will(
            $this->returnValue($oPaymentDbGateway)
        );


        $oPaymentController->getPaymentSettings();
        $oPaymentController->getPaymentSettings();

        $this->assertEquals($aTestData, $oPaymentController->getPaymentSettings());
    }

    /**
     * Test if payment settings are retrieved and handled properly when it was not retrieved from database
     */
    public function testGetPaymentSettingsWithDefaultValues()
    {
        $sPaymentId = 'pmid';
        $sShopId = 3;
        $sCreditPassId = md5($sPaymentId . '-' . $sShopId);

        $aTestData = $this->_getTestPaymentSettings(null, null, null, $sPaymentId, null, null);
        $aTestResults['id'] = $aTestData[0]->oxpayments__id->value;
        $aTestResults['paymentid'] = $aTestData[0]->oxpayments__paymentid->value;
        $aTestResults['active'] = $aTestData[0]->oxpayments__active->value;
        $aTestResults['allowonerror'] = $aTestData[0]->oxpayments__allowonerror->value;
        $aTestResults['fallback'] = $aTestData[0]->oxpayments__fallback->value;

        /**
         * @var CreditPassPaymentSettingsDbGateway $oPaymentController
         */
        $oPaymentDbGateway = $this->getMock(CreditPassPaymentSettingsDbGateway::class, array('loadAll'));
        $oPaymentDbGateway->expects($this->once())->method('loadAll')->will($this->returnValue($aTestData));

        /**
         * @var CreditPassPaymentController $oPaymentController
         */
        $oPaymentController = $this->getMock(CreditPassPaymentController::class, array('_getDbGateWay', 'getUniqueId'));
        $oPaymentController->expects($this->once())->method('_getDbGateWay')->will(
            $this->returnValue($oPaymentDbGateway)
        );
        $oPaymentController->expects($this->once())->method('getUniqueId')->with($this->equalTo($sPaymentId))
            ->will($this->returnValue($sCreditPassId));

        $aResult = $oPaymentController->getPaymentSettings();

        $this->assertNotEquals($aTestResults['id'], $aResult[0]->oxpayments__id->value);
        $this->assertNotEquals($aTestResults['paymentid'], $aResult[0]->oxpayments__paymentid->value);
        $this->assertNotEquals($aTestResults['active'], $aResult[0]->oxpayments__active->value);
        $this->assertNotEquals($aTestResults['allowonerror'], $aResult[0]->oxpayments__allowonerror->value);
        $this->assertNotEquals($aTestResults['fallback'], $aResult[0]->oxpayments__fallback->value);
        $this->assertEquals($sCreditPassId, $aResult[0]->oxpayments__id->value);
        $this->assertEquals($sPaymentId, $aResult[0]->oxpayments__paymentid->value);
        $this->assertEquals('0', $aResult[0]->oxpayments__active->value);
        $this->assertEquals('0', $aResult[0]->oxpayments__allowonerror->value);
        $this->assertEquals('0', $aResult[0]->oxpayments__fallback->value);
    }

    /**
     * Gives test data for database mock, for method loadAll()
     *
     * @param string $sActiveReturns
     * @param string $sFallbackReturns
     * @param string $sAllowOnErrorReturns
     * @param string $sId
     * @param string $sCreditPassPaymentId
     * @param string $sPaymentId
     *
     * @return array
     */
    protected function _getTestPaymentSettings($sActiveReturns, $sFallbackReturns, $sAllowOnErrorReturns, $sPaymentId = 'paymentmethodid', $sId = '', $sCreditPassPaymentId = 'paymentmethodid')
    {
        $oPayment = new stdClass();
        $oPayment->oxpayments__oxid = new stdClass();
        $oPayment->oxpayments__oxid->value = $sPaymentId;
        $oPayment->oxpayments__oecreditpassid = new stdClass();
        $oPayment->oxpayments__oecreditpassid->value = $sId;
        $oPayment->oxpayments__oecreditpasspaymentid = new stdClass();
        $oPayment->oxpayments__oecreditpasspaymentid->value = $sCreditPassPaymentId;
        $oPayment->oxpayments__oecreditpassactive = new stdClass();
        $oPayment->oxpayments__oecreditpassactive->value = $sActiveReturns;
        $oPayment->oxpayments__oecreditpassfallback = new stdClass();
        $oPayment->oxpayments__oecreditpassfallback->value = $sFallbackReturns;
        $oPayment->oxpayments__oecreditpassallowonerror = new stdClass();
        $oPayment->oxpayments__oecreditpassallowonerror->value = $sAllowOnErrorReturns;

        return array($oPayment);
    }
}
