<?php

namespace OxidProfessionalServices\CreditPassModule\Tests\Unit\Controller;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\OrderController;
use OxidProfessionalServices\CreditPassModule\Core\Assessment;
use OxidProfessionalServices\CreditPassModule\Core\Config;

class AssessmentOrderTest extends UnitTestCase
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
        Registry::getSession()->setVariable('usr', 'test_azcr_oxuser');

        $oConfig = new oeCreditPassConfig();
        $oConfig->setModuleActive();
    }

    public function tearDown()
    {
        // delete the dummy user:
        Registry::getSession()->deleteVariable('usr');
        $oDummyUser = oxNew(User::class);
        if ($oDummyUser->load('test_azcr_oxuser')) {
            $oDummyUser->delete();
        }

        parent::tearDown();
    }

    public function testInit()
    {
        // test without 'fnc' param and enabled module (and a non-redirect result):
        $oCrAssessment = $this->getMock(Assessment::class, array('clearDebugData', 'checkAll'));
        $oCrAssessment->expects($this->once())->method('clearDebugData')->will($this->returnValue(null));
        $oCrAssessment->expects($this->once())->method('checkAll')->will($this->returnValue(true));
        $oView = $this->getMock(OrderController::class, array('_getCrAssessment', '_redirect'));
        $oView->expects($this->once())->method('_getCrAssessment')->will($this->returnValue($oCrAssessment));
        $oView->expects($this->never())->method('_redirect');
        $oView->init();

        // test without 'fnc' param and enabled module (and a redirect result):
        $oCrAssessment = $this->getMock(Assessment::class, array('clearDebugData', 'checkAll'));
        $oCrAssessment->expects($this->once())->method('clearDebugData')->will($this->returnValue(null));
        $oCrAssessment->expects($this->once())->method('checkAll')->will($this->returnValue(false));
        $oView = $this->getMock(OrderController::class, array('_getCrAssessment', '_Redirect'));
        $oView->expects($this->once())->method('_getCrAssessment')->will($this->returnValue($oCrAssessment));
        $oView->expects($this->once())->method('_Redirect')->will($this->returnValue(null));
        $oView->init();

        // test with fnc=execute param:
        $this->setRequestParam('fnc', 'execute');
        $oView = $this->getMock(OrderController::class, array('_getCrAssessment', '_Redirect'));
        $oView->expects($this->never())->method('_getCrAssessment');
        $oView->expects($this->never())->method('_Redirect');
        $oView->init();

        // clean up:
        $this->setRequestParam('fnc', null);
    }

    public function testInitInactive()
    {
        $oConfig = new Config();
        $oConfig->setModuleActive(false);

        $oView = $this->getMock(OrderController::class, array('_getCrAssessment', '_Redirect'));
        $oView->expects($this->never())->method('_getCrAssessment');
        $oView->expects($this->never())->method('_Redirect');
        $oView->init();
    }

    public function testgetCrAssessment()
    {
        $oView = $this->getProxyClass(OrderController::class);
        $this->assertTrue($oView->UNITgetCrAssessment() instanceof Assessment);
    }

    public function testRedirect()
    {
        // cannot be tested because of exit() call
    }
}
