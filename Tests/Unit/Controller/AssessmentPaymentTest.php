<?php

namespace OxidProfessionalServices\CreditPassModule\Tests\Unit\Controller;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\CreditPassPaymentController;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassAssessment;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassConfig;

class Payment_testmod extends CreditPassPaymentController
{

    public function setNonPublicVar($sVar, $mValue)
    {
        $this->$sVar = $mValue;
    }

    public function getNonPublicVar($sVar)
    {
        return $this->$sVar;
    }
}

/**
 * Class AssessmentPaymentTest
 *
 * @phpcs:ignoreFile
 */
class AssessmentPaymentTest extends UnitTestCase
{

    public function setUp()
    {
        parent::setUp();

        try {
            DatabaseProvider::getDb()->execute("DELETE FROM oxuser WHERE oxid LIKE 'test_azcr_%'");
        } catch (DatabaseConnectionException $e) {
        } catch (DatabaseErrorException $e) {
        }
        // we need to setup an active user, otherwise some tests will fail because $this->getUser() does not return a valid object:
        $oDummyUser = oxNew(User::class);
        $oDummyUser->setId('test_azcr_oxuser');
        $oDummyUser->oxuser__oxusername = new Field('test_azcr_oxuser');
        $oDummyUser->save();
        $this->setSessionParam('usr', 'test_azcr_oxuser');

        $oConfig = new CreditPassConfig();
        $oConfig->setModuleActive();
    }

    public function tearDown()
    {
        // delete the dummy user:
        $this->setSessionParam('usr', null);
        $oDummyUser = oxNew(User::class);
        if ($oDummyUser->load('test_azcr_oxuser')) {
            $oDummyUser->delete();
        }

        Registry::getSession()->deleteVariable('aBoniSessionData');

        parent::tearDown();
    }

    public function testProperties()
    {
        $oView = $this->getProxyClass(CreditPassPaymentController::class);
        $this->assertNull($oView->getNonPublicVar('_oCrAssessment'));
        $this->assertNull($oView->getNonPublicVar('_azIntLogicResponse'));
    }

    public function testGetPaymentListInactiveDoesNotCallProcessPaymentList()
    {
        $oConfig = new CreditPassConfig();
        $oConfig->setModuleActive(false);

        $oView = $this->getMock(CreditPassPaymentController::class, array('_processPaymentList'));
        $oView->expects($this->never())->method('_processPaymentList')->will($this->returnValue(null));
        $oView->getPaymentList();
    }

    public function testGetPaymentListCallsProcessPaymentListOnce()
    {
        $oView = $this->getMock(CreditPassPaymentController::class, array('_processPaymentList'));
        $oView->expects($this->once())->method('_processPaymentList')->will($this->returnValue(null));
        $oView->getPaymentList();
    }

    public function testprocessPaymentList()
    {
        $oReturnPayment = array('test_azcr_keeppayment' => new stdClass());
        $oCrAssessment = $this->getMock(CreditPassAssessment::class, array('filterPaymentMethods'));
        $oCrAssessment->expects($this->once())->method('filterPaymentMethods')->will(
            $this->returnValue($oReturnPayment)
        );
        $oView = $this->getMock(Payment_testmod::class, array('_getCrAssessment', '_azUnsetPayment'));
        $oView->expects($this->once())->method('_getCrAssessment')->will($this->returnValue($oCrAssessment));

        $oView->setNonPublicVar(
            '_oPaymentList',
            array('test_azcr_unsetpayment' => new stdClass(), 'test_azcr_keeppayment' => new stdClass())
        );
        $oView->UNITprocessPaymentList();
        $this->assertEquals($oReturnPayment, $oView->getNonPublicVar('_oPaymentList'));
    }

    public function testgetCrAssessment()
    {
        $oView = $this->getProxyClass(CreditPassPaymentController::class);
        $this->assertTrue($oView->UNITgetCrAssessment() instanceof CreditPassAssessment);
    }
}
