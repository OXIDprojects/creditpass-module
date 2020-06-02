<?php

namespace OxidProfessionalServices\CreditPassModule\Model;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Application\Model\UserPayment;
use OxidEsales\Eshop\Core\Field;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassAssessment;
use OxidProfessionalServices\CreditPassModule\Core\Email;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassResponseLogger;

/**
 * Order class
 *
 * @extend    oxOrder
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
class Order extends \OxidEsales\Eshop\Application\Model\Order
{

    /**
     * Send manual review check email to shop owner
     */
    public function oeCreditPassSendEmail()
    {
        //      $sTo = $this->getConfig()->getConfigParam('sOECreditPassManualEmail');
        $shop = oxNew(\OxidEsales\Eshop\Application\Model\Shop::class);
        $shop->load($this->getConfig()->getShopId());
        $sTo = $shop->oxshops__oxowneremail->value;
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

        if ($sCreditPassAnswerCode == CreditPassAssessment::OECREDITPASS_ANSWER_CODE_MANUAL) {
            $this->_oeCreditPassSetOrderFolder(CreditPassAssessment::OECREDITPASS_ORDERFOLDER_MANUAL_REVIEW);
        }
    }

    /**
     * return oeCreditPassEmail object
     *
     * @return Email
     */
    protected function _getEmailObject()
    {
        return oxNew(Email::class);
    }

    /**
     * Send order to shop owner and user
     *
     * @param User        $oUser    order user
     * @param Basket      $oBasket  current order basket
     * @param UserPayment $oPayment order payment
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
     *
     * @param string $sFolder
     */
    protected function _oeCreditPassSetOrderFolder($sFolder)
    {
        $this->{$this->getCoreTableName() . '__oxfolder'} = new Field($sFolder, Field::T_RAW);
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

        return (bool)($iType == 2 && $iOeIntLogicResponse == CreditPassAssessment::OECREDITPASS_ANSWER_CODE_MANUAL);
    }

    /**
     * Update log record of current transaction
     */
    protected function _updateLog()
    {
        /**
         * @var CreditPassResponseLogger $oLogger
         */
        $oLogger = oxNew(CreditPassResponseLogger::class);

        $aSessionData = $this->getSession()->getVariable('aBoniSessionData');
        $sCreditPassId = $aSessionData['sOECreditPassId'];

        /**
         * @var User $oUser
         */
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
