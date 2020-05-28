<?php

namespace OxidProfessionalServices\CreditPassModule\Tests\Unit\Model;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassAssessment;
use OxidProfessionalServices\CreditPassModule\Core\Mail;
use OxidProfessionalServices\CreditPassModule\Model\Order;

class AssessmentOxOrderTest extends UnitTestCase
{

    public function testOECreditPassSendEmail()
    {
        $this->setSessionParam('aBoniSessionData', array('azIntLogicResponse' => 2));
        $this->setConfigParam('iOECreditPassManualWorkflow', '2');
        $this->setConfigParam('sOECreditPassManualEmail', 'testemail');
        $oEmail = $this->getMock(Mail::class, array('setRecipient', 'sendCreditPassAdminEmail'));
        $oEmail->expects($this->once())->method('sendCreditPassAdminEmail');

        $oOrder = $this->getMock(Order::class, array('_getEmailObject'));
        $oOrder->expects($this->once())->method('_getEmailObject')->will($this->returnValue($oEmail));
        $oOrder->oeCreditPassSendEmail();
    }

    public function testOECreditPassSendEmailNotManualReviewResponse()
    {
        $this->setSessionParam('aBoniSessionData', array('azIntLogicResponse' => 0));
        $this->setConfigParam('iOECreditPassManualWorkflow', '2');
        $this->setConfigParam('sOECreditPassManualEmail', 'testemail');

        $oOrder = $this->getMock(Order::class, array('_getEmailObject'));
        $oOrder->expects($this->never())->method('_getEmailObject');
        $oOrder->oeCreditPassSendEmail();
    }

    public function testOECreditPassSendEmailDoNotSendEmail()
    {
        $this->setSessionParam('aBoniSessionData', array('azIntLogicResponse' => 0));
        $this->setConfigParam('iOECreditPassManualWorkflow', '2');
        $this->setConfigParam('sOECreditPassManualEmail', 'testemail');

        $oOrder = $this->getMock(Order::class, array('_getEmailObject'));
        $oOrder->expects($this->never())->method('_getEmailObject');
        $oOrder->oeCreditPassSendEmail();
    }

    public function testOECreditPassSendEmailSetAsNotAuthorized()
    {
        $this->setSessionParam('aBoniSessionData', array('azIntLogicResponse' => 2));
        $this->setConfigParam('iOECreditPassManualWorkflow', '1');
        $this->setConfigParam('sOECreditPassManualEmail', 'testemail');

        $oOrder = $this->getMock(Order::class, array('_getEmailObject'));
        $oOrder->expects($this->never())->method('_getEmailObject');
        $oOrder->oeCreditPassSendEmail();
    }

    public function testOECreditPassSendEmailIfEmailIsNotSet()
    {
        $this->setSessionParam('aBoniSessionData', array('azIntLogicResponse' => 2));
        $this->setConfigParam('iOECreditPassManualWorkflow', '2');
        $this->setConfigParam('sOECreditPassManualEmail', '');

        $oOrder = $this->getMock(Order::class, array('_getEmailObject'));
        $oOrder->expects($this->never())->method('_getEmailObject');
        $oOrder->oeCreditPassSendEmail();
    }

    /**
     * Data provider for testOeCreditPassUpdateOrderFolder
     *
     * @return array
     */
    public function _dpTestOeCreditPassUpdateOrderFolder()
    {
        return array(
            array(0, 0, null),
            array(1, 0, null),
            array(2, 1, CreditPassAssessment::OECREDITPASS_ORDERFOLDER_MANUAL_REVIEW),
            array(-1, 0, null),
        );
    }

    /**
     * @dataProvider _dpTestOeCreditPassUpdateOrderFolder
     *
     * @param $sAnswerCode
     * @param $iOeCreditPassSetOrderFolderExpectsToBeCalled
     * @param $sOeCreditPassSetOrderFolderParameter
     */
    public function testOECreditPassUpdateOrderFolder($sAnswerCode, $iOeCreditPassSetOrderFolderExpectsToBeCalled, $sOeCreditPassSetOrderFolderParameter)
    {
        $this->setSessionParam('aBoniSessionData', array('azIntLogicResponse' => $sAnswerCode));

        $oOrder = $this->getMock(Order::class, array('_oeCreditPassSetOrderFolder'));
        if ($iOeCreditPassSetOrderFolderExpectsToBeCalled > 0) {
            $oOrder->expects($this->exactly($iOeCreditPassSetOrderFolderExpectsToBeCalled))->method(
                '_oeCreditPassSetOrderFolder'
            )->with($this->equalTo($sOeCreditPassSetOrderFolderParameter));
        } else {
            $oOrder->expects($this->never())->method('_oeCreditPassSetOrderFolder');
        }

        $oOrder->oeCreditPassUpdateOrderFolder();
    }
}
