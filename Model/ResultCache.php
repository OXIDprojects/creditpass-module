<?php

namespace OxidProfessionalServices\CreditPassModule\Model;

/**
 * CreditPass check result cache class
 */

use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\CreditPassModule\Model\DbGateways\ResponseCacheDbGateway;

class ResultCache
{

    /**
     * Address identification
     *
     * @var string
     */
    var $_sAddressId = null;

    /**
     * User identification
     *
     * @var string
     */
    var $_sUserId = null;

    /**
     * Payment id
     *
     * @var string
     */
    var $_sPaymentId = null;

    /**
     * Response xml
     *
     * @var string
     */
    var $_sResponse = null;

    /**
     * Payment data hash
     *
     * @var string
     */
    var $_sPaymentHash = null;

    /**
     * Answer code
     *
     * @var integer
     */
    var $_iAnswerCode = null;

    /**
     * Currently loaded database gateway
     *
     * @var object
     */
    protected $_oDbGateway = null;

    /**
     * Returns xml if cached data is still valid
     * Returns false if there is no cache or it is out dated
     *
     * @return string
     */
    public function getData()
    {
        // before delete expired data from cache
        $this->_deleteExpCache();

        $sUserId = $this->getUserId();
        $sPaymentId = $this->getPaymentId();
        $sAddressId = $this->getAddressIdentification();
        $sPaymentData = $this->getPaymentDataHash();

        // get existing and valid cache data
        // check if user address or payment method was changes after last check
        $oResultCache = $this->_getDbGateway();
        $oResultCache->setAddressId($sAddressId);
        $oResultCache->setPaymentId($sPaymentId);
        $oResultCache->setPaymentDataHash($sPaymentData);

        return $oResultCache->load($sUserId);
    }

    /**
     * Returns array of payment ids, that got NACK from CreditPass
     *
     * @param integer $iNAnswerCode answer code for NACK
     *
     * @return array|bool
     */
    public function getRejectedPaymentIds($iNAnswerCode = 1)
    {
        //delete expired cache
        $this->_deleteExpCache();

        $sUserId = $this->getUserId();
        $sAddressId = $this->getAddressIdentification();

        //check if user cache exists and return not allowed payment method ids
        $oResultCache = $this->_getDbGateway();
        $oResultCache->setAddressId($sAddressId);
        $aResult = $oResultCache->loadPaymentIdsByAnswer($sUserId, $iNAnswerCode);

        if (is_array($aResult) && count($aResult)) {
            $aIds = array();
            foreach ($aResult as $aFields) {
                $aIds[] = $aFields['paymentid'];
            }

            return $aIds;
        }

        //cache do not exists or all payments are allowed
        return false;
    }

    /**
     * Stores data: user id, payment id, user address md5, response xml
     *
     * @return string
     */
    public function storeData()
    {
        $iNowTime = $this->_getTime();
        $sNowDate = date('Y-m-d H:i:s', $iNowTime);
        $sUserId = $this->getUserId();
        $sPaymentId = $this->getPaymentId();
        $sAddressId = $this->getAddressIdentification();
        $sPaymentData = $this->getPaymentDataHash();
        $sResponse = $this->getResponse();
        $iAnswerCode = $this->getAnswerCode();

        $aData = array('USERID'           => $sUserId,
                       'ASSESSMENTRESULT' => $sResponse,
                       'TIMESTAMP'        => $sNowDate,
                       'USERIDENT'        => $sAddressId,
                       'PAYMENTID'        => $sPaymentId,
                       'PAYMENTDATA'      => $sPaymentData,
                       'ANSWERCODE'       => $iAnswerCode,
        );

        $oResultCache = $this->_getDbGateway();
        $oResultCache->save($aData);
    }

    /**
     * Set address identification string
     *
     * @param string $sAddress
     *
     * @return null
     */
    public function setAddressIdentification($sAddress)
    {
        $this->_sAddressId = $sAddress;
    }

    /**
     * Get address identification string
     *
     * @return string
     */
    public function getAddressIdentification()
    {
        return $this->_sAddressId;
    }

    /**
     * Set user id
     *
     * @param string $sId
     *
     * @return null
     */
    public function setUserId($sId)
    {
        $this->_sUserId = $sId;
    }

    /**
     * Get user id
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->_sUserId;
    }

    /**
     * Set payment id
     *
     * @param string $sId
     *
     * @return null
     */
    public function setPaymentId($sId)
    {
        $this->_sPaymentId = $sId;
    }

    /**
     * Get payment id
     *
     * @return string
     */
    public function getPaymentId()
    {
        return $this->_sPaymentId;
    }

    /**
     * Set Response xml
     *
     * @param string $sResponse
     *
     * @return null
     */
    public function setResponse($sResponse)
    {
        $this->_sResponse = $sResponse;
    }

    /**
     * Get Response xml
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->_sResponse;
    }

    /**
     * Set answer code
     *
     * @param integer $iAnswerCode
     *
     * @return null
     */
    public function setAnswerCode($iAnswerCode)
    {
        $this->_iAnswerCode = $iAnswerCode;
    }

    /**
     * Get answer code
     *
     * @return integer
     */
    public function getAnswerCode()
    {
        return $this->_iAnswerCode;
    }

    /**
     * Set payment data hash
     *
     * @param string $sHash
     *
     * @return null
     */
    public function setPaymentDataHash($sHash)
    {
        $this->_sPaymentHash = $sHash;
    }

    /**
     * Get payment data hash
     *
     * @return string
     */
    public function getPaymentDataHash()
    {
        return $this->_sPaymentHash;
    }

    /**
     * Get address identification string
     *
     * @return string
     */
    public function getCheckCacheTimeout()
    {
        $iDaysForNewCheck = Registry::getConfig()->getConfigParam('iOECreditPassCheckCacheTimeout');
        $iSecondsForNewCheck = $iDaysForNewCheck * 86400;

        return $iSecondsForNewCheck;
    }

    /**
     * Returns database gateway for database connections
     *
     * @return object ResponseCacheDbGateway
     */
    protected function _getDbGateway()
    {
        if (is_null($this->_oDbGateway)) {
            $this->_oDbGateway = oxNew(ResponseCacheDbGateway::class);
        }

        return $this->_oDbGateway;
    }

    /**
     * Deletes expired cache results
     *
     * @return null
     */
    protected function _deleteExpCache()
    {
        $iNowTS = $this->_getTime();
        $iSecondsForNewCheck = $this->getCheckCacheTimeout();
        $iExpTime = $iNowTS - $iSecondsForNewCheck;
        $iExpDate = date('Y-m-d H:i:s', $iExpTime);

        $oResultCache = $this->_getDbGateway();
        $oResultCache->delete($iExpDate);
    }

    /**
     * Returns current time
     *
     * @return integer
     */
    protected function _getTime()
    {
        return time();
    }

}