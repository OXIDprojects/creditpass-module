<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       tests
 * @copyright (c) anzido GmbH, Andreas Ziethen 2008-2011
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 */


class Unit_Models_oeCreditPassAssessmentOxOrderTest extends OxidTestCase
{

    public function testOECreditPassSendEmail()
    {
        $this->setSessionParam('aBoniSessionData', array('azIntLogicResponse' => 2));
        $this->setConfigParam('iOECreditPassManualWorkflow', '2');
        $this->setConfigParam('sOECreditPassManualEmail', 'testemail');
        $oEmail = $this->getMock('oeCreditPassMail', array('setRecipient', 'sendCreditPassAdminEmail'));
        $oEmail->expects($this->once())->method('sendCreditPassAdminEmail');

        $oOrder = $this->getMock('oeCreditPassOxOrder', array('_getEmailObject'));
        $oOrder->expects($this->once())->method('_getEmailObject')->will($this->returnValue($oEmail));
        $oOrder->oeCreditPassSendEmail();
    }

    public function testOECreditPassSendEmailNotManualReviewResponse()
    {
        $this->setSessionParam('aBoniSessionData', array('azIntLogicResponse' => 0));
        $this->setConfigParam('iOECreditPassManualWorkflow', '2');
        $this->setConfigParam('sOECreditPassManualEmail', 'testemail');

        $oOrder = $this->getMock('oeCreditPassOxOrder', array('_getEmailObject'));
        $oOrder->expects($this->never())->method('_getEmailObject');
        $oOrder->oeCreditPassSendEmail();
    }

    public function testOECreditPassSendEmailDoNotSendEmail()
    {
        $this->setSessionParam('aBoniSessionData', array('azIntLogicResponse' => 0));
        $this->setConfigParam('iOECreditPassManualWorkflow', '2');
        $this->setConfigParam('sOECreditPassManualEmail', 'testemail');

        $oOrder = $this->getMock('oeCreditPassOxOrder', array('_getEmailObject'));
        $oOrder->expects($this->never())->method('_getEmailObject');
        $oOrder->oeCreditPassSendEmail();
    }

    public function testOECreditPassSendEmailSetAsNotAuthorized()
    {
        $this->setSessionParam('aBoniSessionData', array('azIntLogicResponse' => 2));
        $this->setConfigParam('iOECreditPassManualWorkflow', '1');
        $this->setConfigParam('sOECreditPassManualEmail', 'testemail');

        $oOrder = $this->getMock('oeCreditPassOxOrder', array('_getEmailObject'));
        $oOrder->expects($this->never())->method('_getEmailObject');
        $oOrder->oeCreditPassSendEmail();
    }

    public function testOECreditPassSendEmailIfEmailIsNotSet()
    {
        $this->setSessionParam('aBoniSessionData', array('azIntLogicResponse' => 2));
        $this->setConfigParam('iOECreditPassManualWorkflow', '2');
        $this->setConfigParam('sOECreditPassManualEmail', '');

        $oOrder = $this->getMock('oeCreditPassOxOrder', array('_getEmailObject'));
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
            array(2, 1, oeCreditPassAssessment::OECREDITPASS_ORDERFOLDER_MANUAL_REVIEW),
            array(-1, 0, null),
        );
    }

    /**
     * @dataProvider _dpTestOeCreditPassUpdateOrderFolder
     */
    public function testOECreditPassUpdateOrderFolder($sAnswerCode, $iOeCreditPassSetOrderFolderExpectsToBeCalled, $sOeCreditPassSetOrderFolderParameter)
    {
        $this->setSessionParam('aBoniSessionData', array('azIntLogicResponse' => $sAnswerCode));

        $oOrder = $this->getMock('oeCreditPassOxOrder', array('_oeCreditPassSetOrderFolder'));
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
