<?php

/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       models
 * @copyright (c) anzido GmbH, Andreas Ziethen 2008-2011
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 *
 * @extend        oxOrder
 *
 */

namespace oe\oecreditpass\Model;

class Order extends Order_parent
{

    /**
     * Send manual review check email to shop owner
     */
    public function oeCreditPassSendEmail()
    {
        $sTo = $this->getConfig()->getConfigParam('sOECreditPassManualEmail');
        if ($this->_sendEmailForManualReview() && $sTo) {
            $oEmail = $this->_getEmailObject();
            $oEmail->sendCreditPassAdminEmail($this, $sTo);
        }
    }

    /**
     * Update order folder if required.
     */
    public function oeCreditPassUpdateOrderFolder()
    {
        $aSessionData = $this->getSession()->getVariable('aBoniSessionData');
        $sCreditPassAnswerCode = $aSessionData['azIntLogicResponse'];

        if ($sCreditPassAnswerCode == oeCreditPassAssessment::OECREDITPASS_ANSWER_CODE_MANUAL) {
            $this->_oeCreditPassSetOrderFolder(oeCreditPassAssessment::OECREDITPASS_ORDERFOLDER_MANUAL_REVIEW);
        }
    }

    /**
     * return oeCreditPassMail object
     *
     * @return oxEmail
     */
    protected function _getEmailObject()
    {
        return oxNew('oxEmail');
    }

    /**
     * Send order to shop owner and user
     *
     * @param oxUser        $oUser    order user
     * @param oxBasket      $oBasket  current order basket
     * @param oxUserPayment $oPayment order payment
     *
     * @return bool
     */
    protected function _sendOrderByEmail($oUser = null, $oBasket = null, $oPayment = null)
    {
        $iRet = parent::_sendOrderByEmail($oUser, $oBasket, $oPayment);

        $this->oeCreditPassSendEmail();

        $this->oeCreditPassUpdateOrderFolder();

        $this->_updateLog();

        //clean session data
        $this->getSession()->deleteVariable('aBoniSessionData');

        return $iRet;
    }

    /**
     * Set order folder.
     */
    protected function _oeCreditPassSetOrderFolder($sFolder)
    {
        $this->{$this->getCoreTableName() . '__oxfolder'} = new oxField($sFolder, oxField::T_RAW);
        $this->save();
    }

    /**
     * Checks if emails should be send to admin for order manual review
     *
     * @return bool
     */
    protected function _sendEmailForManualReview()
    {
        $aSessionData = $this->getSession()->getVariable('aBoniSessionData');
        $iOeIntLogicResponse = (int) $aSessionData['azIntLogicResponse'];
        $iType = (int) $this->getConfig()->getConfigParam('iOECreditPassManualWorkflow');

        return (bool) ($iType == 2 && $iOeIntLogicResponse == oeCreditPassAssessment::OECREDITPASS_ANSWER_CODE_MANUAL);
    }

    /**
     * Update log record of current transaction
     */
    protected function _updateLog()
    {
        /** @var oeCreditPassResponseLogger $oLogger */
        $oLogger = oxNew('oeCreditPassResponseLogger');

        $aSessionData = $this->getSession()->getVariable('aBoniSessionData');
        $sCreditPassId = $aSessionData['sOECreditPassId'];

        /** @var oxUser $oUser */
        $oUser = $this->getOrderUser();

        $oLogger->getLogger()->setPrimaryUpdateFieldName('ID');
        $oLogger->getLogger()->setUserId($oUser->getId());

        $oLogger->update(
            array(
                'ORDER_ID' => $this->getId(),
                'ID'       => $sCreditPassId,
            )
        );
    }
}