<?php

namespace OxidProfessionalServices\CreditPassModule\Core;

use OxidEsales\Eshop\Core\SepaValidator;
use OxidProfessionalServices\CreditPassModule\Model\DbGateways\CreditPassPaymentSettingsDbGateway;
use OxidEsales\Eshop\Application\Model\PaymentList;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\CreditPassModule\Model\CreditPassResultCache;

/**
 * CreditPass Assessment class
 */
class CreditPassAssessment
{

    /**
     * defines what specific transaction needs to be performed
     *
     * @var integer
     */
    const TATYPE = 11202;

    /**
     * the number of seconds to wait while trying to connect to creditPass
     *
     * @var integer
     */
    const CONNECTIONTIMEOUT = 15;

    /**
     * creditPass answer codes
     */
    const OECREDITPASS_ANSWER_CODE_ACK = '0';
    const OECREDITPASS_ANSWER_CODE_NACK = '1';
    const OECREDITPASS_ANSWER_CODE_MANUAL = '2';
    const OECREDITPASS_ANSWER_CODE_ERROR = '-1';

    /**
     * creditPass answer codes
     */
    const OECREDITPASS_MANUAL_CHECK_TYPE_NACK = 0;
    const OECREDITPASS_MANUAL_CHECK_TYPE_ACK = 1;
    const OECREDITPASS_MANUAL_CHECK_TYPE_MANUAL = 2;

    /**
     * Order folder name for creditPass manual review (translation constant, max 32 characters).
     */
    const OECREDITPASS_ORDERFOLDER_MANUAL_REVIEW = 'OECREDITPASS_ORDERFOLDER_REVIEW';

    /**
     * @var string
     */
    protected $_sPurchaseType = null;

    /**
     * available payments if the internal logic was used
     *
     * @var array
     */
    public $aIntLogicPaymentFallback = array();

    /**
     * available payments if the internal logic has failed
     *
     * @var array
     */
    public $aIntLogicPaymentOnError = array();

    /**
     * array of already stored session data for assessment module
     * possible values:
     * $aBoniSessionData['addressIdent']        - md5 hash from user-address (oxfname, oxlname, oxstreet, oxstreetnr, oxzip, oxcity, oxcountry)
     *
     * @var array
     */
    protected $_aBoniSessionData = array();

    /**
     * takes the response of assessment check (xml)
     *
     * @var string
     */
    protected $_sBoniResponseXML;

    /**
     * stores if check-result is coming from cP or from cache
     * if comes from cache, not storing and capturing
     *
     * @var bool
     */
    protected $_blCachedResult = false;

    /**
     * how many loops should be done for waiting for result
     * comes to effect if user clicks multiple times on button
     *
     * @var int
     */
    protected $_iLoopTimes = 10;

    /**
     * if set to true, goto order stop
     * if set to false, redirect to payment stop
     *
     * @var bool
     */
    protected $_blOrderContinue = true;

    /**
     * flag for group check result
     * true if user is in a group that is excluded from boni check
     *
     * @var bool
     */
    protected $_blGroupExcluded = false;

    /**
     * instance of oxuser
     *
     * @var object
     */
    protected $_oUser = null;

    /**
     * @var object
     */
    protected $_oOECreditPassConfig = null;

    /**
     * oxid shop id
     *
     * @var int
     */
    protected $_iShopId = 1;

    /**
     * selected payment id
     *
     * @var string
     */
    protected $_sPaymentId = null;

    /**
     * Payment settings
     *
     * @var array
     */
    protected $_aPaymentSettings = null;

    /**
     * Currently loaded database gateway
     *
     * @var object
     */
    protected $_oDbGateway = null;

    /**
     * Logger object
     *
     * @var object
     */
    protected $_oLogger = null;

    /**
     * Session object
     *
     * @var object
     */
    protected $_oSession = null;

    /**
     * Config object
     *
     * @var object
     */
    protected $_oConfig = null;

    /**
     * class constructor
     * gets some basic values like:
     * - shop-id
     * - user-object
     * - basket object
     * - flag for address change
     */
    public function __construct()
    {
        $this->_iShopId = $this->getConfig()->getShopId();
        if ("oxbaseshop" == $this->_iShopId) {
            $this->_iShopId = 1;
        }

        $this->_getInitialData();
    }

    /**
     * Initializes the class, pre-fills values, etc.
     */
    protected function _getInitialData()
    {
        $this->getUser();
        $this->_checkStreetNo();
        // unset answer if user changed address
        if ($this->checkAddressChange() || $this->checkPaymentDataChange()) {
            $this->setSessionData('azIntLogicResponse', null);
        }

        $this->_setPaymentId($this->getSession()->getVariable('paymentid'));
    }

    /**
     * @return CreditPassConfig
     */
    protected function _getOECreditPassConfig()
    {
        if (!isset($this->_oOECreditPassConfig)) {
            $this->_oOECreditPassConfig = oxNew(CreditPassConfig::class);
        }

        return $this->_oOECreditPassConfig;
    }

    /**
     * checks if in this session user has changed important address data
     * if something was changed, return true - otherwise return false
     *
     * @return bool
     */
    public function checkAddressChange()
    {
        $sActualAddressIdent = $this->_getAddressIdent();
        $aSessionData = $this->getSessionData();
        $sSessionAddress = $aSessionData['addressIdent'];

        if (!empty($sSessionAddress) && $sSessionAddress == $sActualAddressIdent) {
            $this->debugLog("user address changed", "no");
            $blReturn = false;
        } else {
            $this->debugLog("user address changed or new", "yes - new");
            $blReturn = true;
        }

        $this->setSessionData('addressIdent', $sActualAddressIdent);

        return $blReturn;
    }

    /**
     * checks if in this session payment data has changed
     * if something was changed, return true - otherwise return false
     *
     * @return bool
     */
    public function checkPaymentDataChange()
    {
        $sActualPaymentDataIdent = $this->_getPaymentDataHash();
        $aSessionData = $this->getSessionData();
        $sSessionPaymentDataIdent = $aSessionData['paymentdataIdent'];

        if (!empty($sSessionPaymentDataIdent) && $sSessionPaymentDataIdent == $sActualPaymentDataIdent) {
            $this->debugLog("user payment data changed", "no");
            $blReturn = false;
        } else {
            $this->debugLog("user payment data changed or new", "yes - new");
            $blReturn = true;
        }

        $this->setSessionData('paymentdataIdent', $sActualPaymentDataIdent);

        return $blReturn;
    }

    /**
     * Returns only allowed payment methods
     * Check in cache or for fallback methods
     *
     * @return array
     */
    public function getAllowedPaymentMethods()
    {
        $aAllowedPayments = false;
        //load only fallback methods?
        $aBoniSessionData = $this->getSessionData();
        $iAnswerCode = $aBoniSessionData['azIntLogicResponse'];

        if (!is_null($iAnswerCode)) {
            //load all payment methods
            $aAllowedPayments = $this->getAllowedPaymentsAfterResponse($iAnswerCode);
        }

        return $aAllowedPayments;
    }

    /**
     * Returns fallback payment methods
     * reads config and sets fallback payment methods for when the integrated logic fails
     *
     * @param $iAnswerCode
     *
     * @return array
     */
    public function getAllowedPaymentsAfterResponse($iAnswerCode)
    {
        $aPaymentSettings = false;
        if ($this->_allowOnlyFallbackMethods($iAnswerCode)) {
            $aPaymentSettings = $this->getFallbackPayments();
        } elseif ($this->_allowOnlyErrorMethods($iAnswerCode)) {
            $aPaymentSettings = $this->getOnErrorPayments();
        }

        return $aPaymentSettings;
    }

    /**
     * Checks if only fallback payment methods are allowed
     *
     * @param $iAnswerCode
     *
     * @return bool
     */
    protected function _allowOnlyFallbackMethods($iAnswerCode)
    {
        $blIsFallback = false;
        $iType = $this->_getManualWorkflowType();
        if ($iAnswerCode == self::OECREDITPASS_ANSWER_CODE_NACK
            || ($iAnswerCode == self::OECREDITPASS_ANSWER_CODE_MANUAL && $iType == self::OECREDITPASS_MANUAL_CHECK_TYPE_NACK)
        ) {
            $blIsFallback = true;
        }

        return $blIsFallback;
    }

    /**
     * Checks if only on error payment methods are allowed
     *
     * @param $iAnswerCode
     *
     * @return bool
     */
    protected function _allowOnlyErrorMethods($iAnswerCode)
    {
        $blIsError = false;
        if ($iAnswerCode == self::OECREDITPASS_ANSWER_CODE_ERROR) {
            $blIsError = true;
        }

        return $blIsError;
    }

    /**
     * Returns fallback payment methods
     *
     * @return array
     */
    public function getFallbackPayments()
    {
        $aPaymentFallback = array();
        $aPaymentSettings = $this->getPaymentSettings();
        if (is_array($aPaymentSettings)) {
            foreach ($aPaymentSettings as $aSettings) {
                if ($aSettings['PAYMENTFALLBACK']) {
                    $aPaymentFallback[] = $aSettings['PAYMENTMETHOD'];
                }
            }
        }

        return $aPaymentFallback;
    }

    /**
     * Returns Error payment methods
     *
     * @return array
     */
    public function getOnErrorPayments()
    {
        $aPaymentOnError = array();
        $aPaymentSettings = $this->getPaymentSettings();
        if (is_array($aPaymentSettings)) {
            foreach ($aPaymentSettings as $aSettings) {
                if ($aSettings['ALLOWPAYMENTONERROR']) {
                    $aPaymentOnError[] = $aSettings['PAYMENTMETHOD'];
                }
            }
        }

        return $aPaymentOnError;
    }

    /**
     * Returns only allowed payment methods
     * Check in cache or for fallback methods
     *
     * @param $oPaymentMethods
     *
     * @return array PaymentList
     */
    public function filterPaymentMethods($oPaymentMethods)
    {
        $oPaymentMethods = $this->_filterRejectedPayments($oPaymentMethods);
        $oPaymentMethods = $this->_filterAllowedPayments($oPaymentMethods);

        return $oPaymentMethods;
    }

    /**
     * Returns only allowed payment methods
     *
     * @param $oPaymentMethods
     *
     * @return array PaymentList
     */
    protected function _filterAllowedPayments($oPaymentMethods)
    {
        $aAllowedPaymentMethods = $this->getAllowedPaymentMethods();
        if (count($oPaymentMethods) && is_array($aAllowedPaymentMethods)) {
            // if there is no valid method specified, show empty payment list
            if (!count($aAllowedPaymentMethods)) {
                return array();
            }
            foreach ($oPaymentMethods as $sPaymentId => $oPayment) {
                if (!in_array($sPaymentId, $aAllowedPaymentMethods)) {
                    unset($oPaymentMethods[$sPaymentId]);
                }
            }
        }

        return $oPaymentMethods;
    }

    /**
     * Returns only allowed payment methods and unsets rejected ones
     *
     * @param $oPaymentMethods
     *
     * @return array PaymentList
     */
    protected function _filterRejectedPayments($oPaymentMethods)
    {
        $aRejectedMethods = $this->getCachedAndRejectedPayments();
        if ($aRejectedMethods) {
            foreach ($oPaymentMethods as $sPaymentId => $oPayment) {
                if (in_array($sPaymentId, $aRejectedMethods)) {
                    unset($oPaymentMethods[$sPaymentId]);
                }
            }
        }

        return $oPaymentMethods;
    }

    /**
     * Returns array of payment ids that are rejected and cached
     *
     * @return array
     */
    public function getCachedAndRejectedPayments()
    {
        $oUser = $this->getUser();
        if (!$oUser) {
            return false;
        }
        $sUserId = $oUser->getId();
        $sCheckStr = $this->_getAddressIdent();
        $oResultCache = $this->_getResultCacheObject();
        $oResultCache->setUserId($sUserId);
        $oResultCache->setAddressIdentification($sCheckStr);
        $aRejectedPaymentMethods = $oResultCache->getRejectedPaymentIds(self::OECREDITPASS_ANSWER_CODE_NACK);

        return $aRejectedPaymentMethods;
    }

    /**
     * Returns database gateway for database connections
     *
     * @return object PaymentSettingsDbGateway
     */
    protected function _getDbGateway()
    {
        if (is_null($this->_oDbGateway)) {
            $this->_oDbGateway = oxNew(CreditPassPaymentSettingsDbGateway::class);
        }

        return $this->_oDbGateway;
    }

    /**
     * logs errors during xml parsing
     *
     * @param string $sError
     */
    protected function _xmlErrorHandler($sError)
    {
        $sShopDir = $this->getConfig()->getConfigParam('sShopDir');
        $sLogDir = $sShopDir . 'modules/oxps/creditpass/log/';
        $sError = str_replace("<br>", "\n\t", $sError);
        $sErrorMsg = date("Y-m-d H:i:s") . " " . $sError . "\n";

        $fp = @fopen($sLogDir . "xml_errors.log", "a");
        if ($fp) {
            fwrite($fp, $sErrorMsg);
            fclose($fp);
        } else {
            $sLogDir = $sShopDir . 'modules/oxps/creditpass/';
            $fp = @fopen($sLogDir . "xml_errors.log", "a");
            fwrite($fp, $sErrorMsg);
            fclose($fp);
        }
    }

    /**
     * if street-no is empty the address check fails at creditPass
     * but some customers put their streetno into street-field and leave streetno-field empty
     * so we check if streetno is empty an in this case we take the last part of street-field
     * as streetno
     */
    protected function _checkStreetNo()
    {
        if ($this->_oUser != null) {
            $sStreet = $this->_oUser->oxuser__oxstreet->value;
            $sStreetNo = $this->_oUser->oxuser__oxstreetnr->value;

            // if streetno is empty, split street-name an take last element as no
            if (empty($sStreetNo)) {
                $aStreet = explode(" ", $sStreet);
                $iKey = count($aStreet) - 1;
                $sStreetNo = $aStreet[$iKey];
                $sStreet = str_replace(" $sStreetNo", "", $sStreet);
            }

            $this->_oUser->oxuser__oxstreet = new Field($sStreet);
            $this->_oUser->oxuser__oxstreetnr = new Field($sStreetNo);
        }
    }

    /**
     * gets and sets current oxuser object to _oUser property
     *
     * @return object User
     */
    public function getUser()
    {
        if ($this->_oUser === null) {
            $this->setUser();
        }

        return $this->_oUser;
    }

    /**
     * Sets current oxuser object to _oUser property
     *
     * @param object $oUser User object
     *
     * @return null
     */
    public function setUser($oUser = null)
    {
        if ($oUser === null) {
            $this->_oUser = oxNew(User::class);
            $this->_oUser->loadActiveUser();
        } else {
            $this->_oUser = $oUser;
        }
    }

    /**
     * Checks several assessment criteria and gives back true or false
     * On false order module redirects to payment class
     * Logic here: check if user group is excluded from creditPass check - if so return true
     *
     * @return bool
     */
    public function checkAll()
    {
        $aBoniSessionData = $this->getSessionData();
        $blContinue = $this->_doCheck_groupExcl();
        if ($blContinue && !isset($aBoniSessionData['azIntLogicResponse'])) {
            $this->_doAssessment();
        }

        return $this->_blOrderContinue;
    }

    /**
     * Checks if the user-group is excluded from creditPass checks
     * Set $this->_blOrderContinue -> defining if order can be continued or if redirect to payments has to be done
     * Return true or false -> defining if further checks have to be done or not
     *
     * @return bool
     */
    protected function _doCheck_groupExcl()
    {
        // first check if actual user group is excluded:
        if ($this->_oUser) {
            $this->_blGroupExcluded = $this->_checkUserGroup();
        } else {
            $this->_blGroupExcluded = false;
        }

        // if group is excluded no checks at all will be done
        if ($this->_blGroupExcluded) {
            $this->_blOrderContinue = true;

            return false;
        } else {
            $this->_blOrderContinue = true;

            return true;
        }
    }

    /**
     * checks the payment for debit note payment type
     *
     * @return bool
     */
    protected function _isDebitNote()
    {
        if ($this->_getPaymentId() == "oxiddebitnote") {
            return true;
        }

        return false;
    }

    /**
     * Does integrated logic check
     */
    protected function _doAssessment()
    {
        $this->_blOrderContinue = true;
        $this->getResponse();
        if (!empty($this->_sBoniResponseXML)) {
            $this->parseResponse();
        }
    }

    /**
     * Parse result for active payment
     */
    public function parseResponse()
    {
        $aSettings = $this->_getPaymentSettings($this->_getPaymentId());
        $oUser = $this->getUser();
        $sUserId = $oUser->getId();
        $sAnswerCode = $this->_getTagResult('ANSWER_CODE', 'PROCESS');
        // If timeout happened, set to NOT authorized
        if ($sAnswerCode == "999") {
            $sAnswerCode = self::OECREDITPASS_ANSWER_CODE_ERROR;
        }

        // Getting payments' setting for ERROR workflow
        $this->_handleAnswerCode($sAnswerCode, $aSettings['ALLOWPAYMENTONERROR']);

        $this->setSessionData('azIntLogicResponse', $sAnswerCode);

        $this->debugLog("int. logic", "User-Id: $sUserId - storing result for " . $aSettings['PAYMENTMETHOD']);

        $this->_logResponse();

        $this->_storeCache($sAnswerCode);
    }

    /**
     * Logs parsed response
     */
    protected function _logResponse()
    {
        /**
         * @var CreditPassResponseLogger $oLogger
         */
        $oLogger = $this->getLogger();

        $aValues = $this->xmlParser($this->_sBoniResponseXML, '_doIlCheck');
        $oUser = $this->getUser();
        $sUserId = $oUser->getId();

        $oLogger->save($aValues);
        $sCreditPassId = $oLogger->getLastID();

        $oLogger->getLogger()->setPrimaryUpdateFieldName('ID');
        $aAdditionalValues = array(
            'USER_ID' => $sUserId,
            'ID'      => $sCreditPassId,
        );
        if ($this->_blCachedResult) {
            $aAdditionalValues['CACHED'] = 1;
        }
        $oLogger->update($aAdditionalValues);

        $this->setSessionData('sOECreditPassId', $sCreditPassId);
    }

    /**
     * Returns logger object
     *
     * @return object ResponseLogger
     */
    public function getLogger()
    {
        if ($this->_oLogger === null) {
            $this->setLogger();
        }

        return $this->_oLogger;
    }

    /**
     * Sets logger object
     *
     * @param object $oLogger logger object
     */
    public function setLogger($oLogger = null)
    {
        /**
         * @var CreditPassResponseLogger $oLogger
         */
        if ($this->_oLogger === null) {
            $this->_oLogger = oxNew(CreditPassResponseLogger::class);
        } else {
            $this->_oLogger = $oLogger;
        }
    }

    /**
     * Handling different answer codes:
     * -1 - Error
     *  0 - Authorized
     *  1 - NOT authorized
     *  2 - Manual testing
     *
     * @param $sAnswerCode
     * @param $blAllowPaymentOnErr
     */
    protected function _handleAnswerCode($sAnswerCode, $blAllowPaymentOnErr)
    {
        switch ($sAnswerCode) {
            case self::OECREDITPASS_ANSWER_CODE_ACK:
                // It is authorized
                $this->_setOrderContinue(true);
                break;
            case self::OECREDITPASS_ANSWER_CODE_ERROR:
                // An error occurred
                if ($blAllowPaymentOnErr) {
                    $this->_setOrderContinue(true);
                } else {
                    $this->_setOrderContinue(false);
                    $this->_setIntegratedLogicError();
                }
                break;
            case self::OECREDITPASS_ANSWER_CODE_NACK:
                // NOT authorized
                $this->_setOrderContinue(false);
                $this->_setIntegratedLogicError();
                break;
            case self::OECREDITPASS_ANSWER_CODE_MANUAL;
                // Manual workflow
                $this->_handleManualWorkflow();
                break;
            default:
                $this->_setOrderContinue(false);
                $this->_setIntegratedLogicError();
                break;
        }
    }

    /**
     * Method to handle manual workflow depending on iOECreditPassManualWorkflow setting
     */
    protected function _handleManualWorkflow()
    {
        $iType = $this->_getManualWorkflowType();

        if ($iType === self::OECREDITPASS_MANUAL_CHECK_TYPE_NACK) {
            // NOT authorize
            $this->_setOrderContinue(false);
            $this->_setIntegratedLogicError();
        } elseif ($iType > self::OECREDITPASS_MANUAL_CHECK_TYPE_NACK) {
            // Authorize
            $this->_setOrderContinue(true);
        }
    }

    /**
     * Set _blOrderContinue
     *
     * @param $blValue
     */
    protected function _setOrderContinue($blValue)
    {
        $this->_blOrderContinue = $blValue;
    }

    /**
     * Set session values for integrated logic error
     */
    protected function _setIntegratedLogicError()
    {
        $sPayError = 'oecreditpassunauthorized_error';

        $sPayErrorText = $this->_getOECreditPassConfig()->getUnauthorizedErrorMsg();

        if ("" === $sPayErrorText) {
            $sPayError = '7';
        }

        $this->getSession()->setVariable('payerror', $sPayError);

        $this->getSession()->setVariable('payerrortext', $sPayErrorText);
    }

    /**
     * get excluded groups from config and check if actual user is member of
     * one of these groups
     *
     * @return bool $blRet
     */
    protected function _checkUserGroup()
    {
        $blRet = false;

        $aExGroups = $this->getConfig()->getConfigParam('aOECreditPassExclUserGroups');
        if (count($aExGroups)) {
            foreach ($aExGroups as $groupid) {
                if ($this->_oUser->inGroup($groupid)) {
                    $this->debugLog("group excluded", $groupid);
                    $this->setSessionData('blBoniGroupExclude', 1);
                    $blRet = true;
                }
            }
        }

        // reset session and debug data
        if (!$blRet) {
            $this->debugLog('group excluded', '');
            $this->setSessionData('blBoniGroupExclude', 0);
        }

        return $blRet;
    }

    /**
     * Load payment settings from database
     *
     * @return array
     */
    public function loadPaymentSettings()
    {
        $oPaymentSettings = $this->_getDbGateway();

        if (!$oPaymentSettings || !$aSettings = $oPaymentSettings->loadAll()) {
            return false;
        }
        $aPaymentSettings = array();

        foreach ($aSettings as $sKey => $aSetting) {
            $aContainer = array();
            $aContainer['PAYMENTMETHOD'] = $sKey;
            $aContainer['DOVERIFICATION'] = $aSetting->oxpayments__active->value;
            $aContainer['PAYMENTFALLBACK'] = $aSetting->oxpayments__fallback->value;
            $aContainer['CREDITPASSLOGICNR'] = $aSetting->oxpayments__purchasetype->value;
            $aContainer['ALLOWPAYMENTONERROR'] = $aSetting->oxpayments__allowonerror->value;
            $aPaymentSettings[] = $aContainer;
        }

        return $aPaymentSettings;
    }

    /**
     * Load payment settings from database
     *
     * @return array
     */
    public function getPaymentSettings()
    {
        if (is_null($this->_aPaymentSettings)) {
            $this->_aPaymentSettings = $this->loadPaymentSettings();
        }

        return $this->_aPaymentSettings;
    }

    /**
     * function only for debugging purpose
     * takes $key and $string where $key is some title of debug message and $string is the
     * content
     * if $blDelete is set to true, the array element (with key=$key) is deleted
     *
     * @param string $key
     * @param string $string
     * @param bool   $blDelete
     */
    public function debugLog($key, $string, $blDelete = false)
    {
        if ($this->getConfig()->getConfigParam('blOECreditPassDebug')) {
            if ($blDelete) {
                //oxSession::deleteVar('aBoniDebug');
                //unset($aBoniDebugData[$key]);
            } else {
                $aBoniDebugData = $this->getSession()->getVariable('aBoniDebugData');
                $aBoniDebugData[$key] = $string;
            }

            $sPath = getShopBasePath() . "modules/oxps/creditpass/log/session.log";
            error_log(date("Y-m-d H:i:s") . " - " . $key . ": $string\n\r", 3, $sPath);

            $this->getSession()->setVariable('aBoniDebugData', $aBoniDebugData);
        }
    }

    /**
     * removed debug information from session
     */
    public function clearDebugData()
    {
        $this->getSession()->deleteVariable('aBoniDebugData');
    }

    /**
     * builds md5-hash of most important user-data
     *
     * @return string
     */
    protected function _getAddressIdent()
    {
        $sCheckStr = md5(
            trim($this->_oUser->oxuser__oxfname->value) .
            trim($this->_oUser->oxuser__oxlname->value) .
            trim($this->_oUser->oxuser__oxstreet->value) .
            trim($this->_oUser->oxuser__oxstreetnr->value) .
            trim($this->_oUser->oxuser__oxzip->value) .
            trim($this->_oUser->oxuser__oxcity->value) .
            trim($this->_oUser->oxuser__oxcountryid->value)
        );

        return $sCheckStr;
    }

    /**
     * builds md5-hash of payment method data
     *
     * @return string
     */
    protected function _getPaymentDataHash()
    {
        $sCheckHash = false;
        $aDynValues = $this->getSession()->getVariable('dynvalue');
        if ($aDynValues && count($aDynValues)) {
            $sDataStr = '';
            if (!is_array($aDynValues)) {
                $aDynValues = array($aDynValues);
            }
            foreach ($aDynValues as $sDynValue) {
                $sDataStr .= trim($sDynValue);
            }
            $sCheckHash = md5($sDataStr);
        }

        return $sCheckHash;
    }

    /**
     * simple function to write content of $this->_aBoniSessionData into session
     * executed in order module after all checks
 */
    public function writeSessionData()
    {
        $this->getSession()->setVariable('aBoniSessionData', $this->_aBoniSessionData);
    }

    /**
     * Returns data from session
     *
     * @return array
     */
    public function getSessionData()
    {
        if (empty($this->_aBoniSessionData)) {
            $this->_aBoniSessionData = $this->getSession()->getVariable('aBoniSessionData');
        }

        return $this->_aBoniSessionData;
    }

    /**
     * Returns data from session
     *
     * @param $sName
     * @param $sValue
     *
     * @return void
     */
    public function setSessionData($sName, $sValue)
    {
        $this->_aBoniSessionData[$sName] = $sValue;
        $this->writeSessionData();
    }

    /**
     * Take response data from cache (xml, paymentid)
     *
     * @param array $aCachedResult array of cached result
     *
     * @return null
     */
    protected function _getResponseFromCache($aCachedResult)
    {
        $this->_blCachedResult = true;
        $sXml = $aCachedResult['assessmentresult'];
        $sPaymentId = $aCachedResult['paymentid'];
        $this->_setPaymentId($sPaymentId);

        return unserialize($sXml);
    }

    /**
     * Gets response from creditPass
     *
     * @return string $sResponseXML xml response
     */
    protected function _getResponseFromCreditPass()
    {
        // get selected payment settings
        $aPaymentSettings = $this->_getPaymentSettings($this->_getPaymentId());

        // if payment is not selected or do not need verification, do not perform check
        if (!$aPaymentSettings || !$aPaymentSettings['DOVERIFICATION'] || strlen(
                                                                              $aPaymentSettings['CREDITPASSLOGICNR']
                                                                          ) <= 0
        ) {
            return;
        }

        $this->_sPurchaseType = $aPaymentSettings['CREDITPASSLOGICNR'];

        $bl4Safe = 0;
        if ($this->_isDebitNote()) {
            $bl4Safe = 1;
        }
        // build XML
        $sRequestXML = $this->_getBoniRequestXML($bl4Safe);
        $sUrl = $this->getConfig()->getConfigParam('sOECreditPassUrl');

        $sResponseXML = $this->_callCreditPass($sRequestXML, $sUrl);

        // in case of no response generate some dummy xml
        if (empty($sResponseXML)) {
            $sResponseXML = $this->_makeErrorXML();
        }

        if ($this->getConfig()->getConfigParam('blOECreditPassDebug')) {
            $this->writeDebugXML($sRequestXML, 'requ');
        }

        return $sResponseXML;
    }

    /**
     * Get active payment settings
     */
    protected function _getPaymentSettings($sPaymentId)
    {
        $aActPaymentSettings = false;
        $aPaymentSettings = $this->getPaymentSettings();
        if ($aPaymentSettings) {
            foreach ($aPaymentSettings as $aSettings) {
                if ($sPaymentId == $aSettings['PAYMENTMETHOD']) {
                    $aActPaymentSettings = $aSettings;
                }
            }
        }

        return $aActPaymentSettings;
    }

    /**
     * Calls creditPass
     *
     * @return null
     */
    protected function _callCreditPass($sRequestXML, $sUrl)
    {
        // init curl transfer
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECTIONTIMEOUT);

        $sCurlRequest = "req=" . urlencode($sRequestXML);

        // send request and get result
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sCurlRequest);

        $ResponseXML = curl_exec($ch);

        curl_close($ch);

        //something else but not xml returned
        if (stripos($ResponseXML, '<?xml') === false) {
            return false;
        }

        return $ResponseXML;
    }

    /**
     * Gets check result xml.
     * Checks if response is already cached, if not calls creditPass
     */
    public function getResponse()
    {
        $aCachedResult = $this->getCachedResult();

        if (!$aCachedResult) {
            $this->_sBoniResponseXML = $this->_getResponseFromCreditPass();
        } else {
            $this->_sBoniResponseXML = $this->_getResponseFromCache($aCachedResult);
        }

        if ($this->getConfig()->getConfigParam('blOECreditPassDebug')) {
            $this->writeDebugXML($this->_sBoniResponseXML, 'resp');
        }
    }

    /**
     * Check if there is any old but still valid result for user in database
     * Check for user address, for selected payment method and time cache.
     *
     * @return bool
     */
    public function getCachedResult()
    {
        $oUser = $this->getUser();
        if (!$oUser) {
            return false;
        }
        $sUserId = $oUser->getId();
        $sPaymentId = $this->_getPaymentId();
        $sCheckStr = $this->_getAddressIdent();
        $sPaymentDataHash = $this->_getPaymentDataHash();

        $oResultCache = $this->_getResultCacheObject();
        $oResultCache->setUserId($sUserId);
        $oResultCache->setPaymentId($sPaymentId);
        $oResultCache->setAddressIdentification($sCheckStr);
        $oResultCache->setPaymentDataHash($sPaymentDataHash);

        return $oResultCache->getData();
    }

    /**
     * Returns active payment method id
     *
     * @return string
     */
    protected function _getPaymentId()
    {
        return $this->_sPaymentId;
    }

    /**
     * Sets active payment method id
     *
     * @param string $sPaymentId payment id
     *
     * @return null
     */
    protected function _setPaymentId($sPaymentId)
    {
        $this->_sPaymentId = $sPaymentId;
    }

    /**
     * store creditPass result into database
     *
     * @param string $sAnswerCode
     *
     * @return void
     */
    protected function _storeCache($sAnswerCode)
    {
        // store if no error was returned and if it is cached response
        if ($this->_blCachedResult || $sAnswerCode < 0 || $sAnswerCode >= 2 || $this->_getOECreditPassConfig()->getCacheTtl() <= 0) {
            return;
        }

        $oUser = $this->getUser();
        // if we have no active user do not store cache
        if (!$oUser) {
            return;
        }
        $sUserId = $oUser->getId();
        $sPaymentId = $this->_getPaymentId();
        $sCheckStr = $this->_getAddressIdent();
        $sPaymentDataHash = $this->_getPaymentDataHash();
        $sResponseXML = serialize($this->_sBoniResponseXML);
        $oResultCache = $this->_getResultCacheObject();
        $oResultCache->setUserId($sUserId);
        $oResultCache->setPaymentId($sPaymentId);
        $oResultCache->setPaymentDataHash($sPaymentDataHash);
        $oResultCache->setAddressIdentification($sCheckStr);
        $oResultCache->setResponse($sResponseXML);
        $oResultCache->setAnswerCode($sAnswerCode);
        $oResultCache->storeData();
    }

    /**
     * Returns result cache object
     *
     * @return CreditPassResultCache
     */
    protected function _getResultCacheObject()
    {
        return oxNew(CreditPassResultCache::class);
    }

    /**
     * build request XML for boni-check
     *
     * @param bool $bl4Safe
     *
     * @return string
     * @throws DatabaseConnectionException
     */
    protected function _getBoniRequestXML($bl4Safe)
    {
        $sid = $this->getSession()->getId();

        // general
        $sBoniRequestXML = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        $sBoniRequestXML .= "<REQUEST>";

        // customer part
        $sBoniRequestXML .= "<CUSTOMER>";
        $sBoniRequestXML .= "<AUTH_ID>" . $this->PrepareXML($this->getConfig()->getConfigParam('sOECreditPassAuthId'), 32) . "</AUTH_ID>";
        $sBoniRequestXML .= "<AUTH_PW>" . $this->PrepareXML($this->getConfig()->getConfigParam('sOECreditPassAuthPw'), 32) . "</AUTH_PW>";
        $sBoniRequestXML .= "<CUSTOMER_TA_ID>" . $this->PrepareXML(md5($sid . $this->_oUser->oxuser__oxid->value), 32) . "</CUSTOMER_TA_ID>";
        $sBoniRequestXML .= "</CUSTOMER>";

        // process part
        $sBoniRequestXML .= "<PROCESS>";
        $sBoniRequestXML .= "<TA_TYPE>" . self::TATYPE . "</TA_TYPE>";

        if (true === $this->_isTestMode()) {
            $sBoniRequestXML .= "<PROCESSING_CODE>8</PROCESSING_CODE>";
        } else {
            $sBoniRequestXML .= "<PROCESSING_CODE>1</PROCESSING_CODE>";
        }

        $sBoniRequestXML .= "<REQUESTREASON>ABK</REQUESTREASON>";

        $sBoniRequestXML .= "</PROCESS>";

        // query part
        $sBoniRequestXML .= "<QUERY>";

        $sBoniRequestXML .= "<PURCHASE_TYPE>" . $this->_sPurchaseType . "</PURCHASE_TYPE>";

        $sBoniRequestXML .=
            "<FIRST_NAME>" . $this->PrepareXML($this->_oUser->oxuser__oxfname->value, 64) . "</FIRST_NAME>";
        $sBoniRequestXML .= "<LAST_NAME>" . $this->PrepareXML($this->_oUser->oxuser__oxlname->value, 64) . "</LAST_NAME>";
        // TODO: if company name should be transfered, we have to know the field name from creditPass for the company
        // <COMPANY_NAME> is just a guess ...
        $sBoniRequestXML .=
            "<COMPANY_NAME>" . $this->PrepareXML($this->_oUser->oxuser__oxcompany->value, 64) . "</COMPANY_NAME>";
        $sBoniRequestXML .=
            "<ADDR_STREET>" . $this->PrepareXML($this->_oUser->oxuser__oxstreet->value, 50) . "</ADDR_STREET>";
        $sBoniRequestXML .=
            "<ADDR_STREET_NO>" . $this->PrepareXML($this->_oUser->oxuser__oxstreetnr->value, 32) . "</ADDR_STREET_NO>";
        $sBoniRequestXML .= "<ADDR_ZIP>" . $this->PrepareXML($this->_oUser->oxuser__oxzip->value, 8) . "</ADDR_ZIP>";
        $sBoniRequestXML .= "<ADDR_CITY>" . $this->PrepareXML($this->_oUser->oxuser__oxcity->value, 32) . "</ADDR_CITY>";
        $country = DatabaseProvider::getDb()->GetOne(
            "select oxisoalpha2 from oxcountry where oxid = '" . $this->_oUser->oxuser__oxcountryid->value . "'"
        );
        $sBoniRequestXML .= "<ADDR_COUNTRY>" . $country . "</ADDR_COUNTRY>";

        if (!empty($this->_oUser->oxuser__oxbirthdate->value)
            && $this->_oUser->oxuser__oxbirthdate->value != "0000-00-00"
        ) {
            $sBoniRequestXML .= "<DOB>" . $this->_oUser->oxuser__oxbirthdate->value . "</DOB>";
        } else {
            $sBoniRequestXML .= "<DOB />";
        }
        $sBoniRequestXML .= "<CUSTOMERGROUP />";
        $dAmount = $this->getSession()->getBasket()->getPrice()->getBruttoPrice() * 100;
        $sBoniRequestXML .= "<AMOUNT>$dAmount</AMOUNT>";

        // 4safe part
        if ($bl4Safe) {
            $aDynValues = $this->getSession()->getVariable('dynvalue');

            $kto = $aDynValues['lsktonr'];

            // Check for valid IBAN
            $blIBANValid = $this->isValidIBAN($kto);

            if ($blIBANValid) {
                $sBoniRequestXML .= "<IBAN>$kto</IBAN>";
            } else {
                // fill up $kto with leading zeros cause string must have length of 10
                $aDynValues = $this->_prepareAccountData($aDynValues);

                $blz = $aDynValues['lsblz'];

                $iAccountLength = strlen($kto);
                $iDiff = 10 - $iAccountLength;
                if ($iDiff > 0) {
                    $sFill = '';
                    for ($i = 0; $i < $iDiff; $i++) {
                        $sFill .= "0";
                    }
                    $kto = $sFill . $kto;
                }

                $sBoniRequestXML .= "<BLZ>$blz</BLZ>";
                $sBoniRequestXML .= "<KONTONR>$kto</KONTONR>";
            }
        }

        $sBoniRequestXML .= "</QUERY>";

        // general
        $sBoniRequestXML .= "</REQUEST>";

        return $sBoniRequestXML;
    }

    /**
     * convert special characters for XML
     *
     * @param string $sInput
     * @param int    $iMaxLen
     *
     * @return string
     */
    public function PrepareXML($sInput, $iMaxLen = null)
    {
        $sInput = trim($sInput);

        if (isset($iMaxLen) && $iMaxLen) {
            $sInput = substr($sInput, 0, $iMaxLen);
        }

        $sOutput = strip_tags($sInput);
        $sOutput = html_entity_decode($sOutput);

        $sOutput = str_replace("&amp;", "&", $sOutput);
        $sOutput = str_replace("&#", "@#", $sOutput);
        $sOutput = str_replace("&", "&amp;", $sOutput);
        $sOutput = str_replace("@#", "&#", $sOutput);
        //$sOutput = utf8_decode($sOutput);

        $sOutput = str_replace(">", "&gt;", $sOutput);
        $sOutput = str_replace("<", "&lt;", $sOutput);

        $sShopCharset = Registry::getLang()->translateString("charset");
        if ($sShopCharset && strtolower($sShopCharset) != "utf-8") {
            $sOutput = iconv($sShopCharset, "UTF-8", $sOutput);
        }

        return $sOutput;
    }

    /**
     * convert bank account data into fitting format for creditPass
     * remove white spaces and other not numeric values
     *
     * @param array $aDynValues
     *
     * @return array
     */
    protected function _prepareAccountData($aDynValues)
    {
        $blz = $aDynValues['lsblz'];
        $kto = $aDynValues['lsktonr'];

        $iBlzLen = strlen($blz);
        $iKtoLen = strlen($kto);
        $sBlz = '';
        for ($i = 0; $i < $iBlzLen; $i++) {
            if (is_numeric(substr($blz, $i, 1))) {
                $sBlz .= substr($blz, $i, 1);
            }
        }
        $aDynValues['lsblz'] = $sBlz;
        $sKto = '';
        for ($i = 0; $i < $iKtoLen; $i++) {
            if (is_numeric(substr($kto, $i, 1))) {
                $sKto .= substr($kto, $i, 1);
            }
        }
        $aDynValues['lsktonr'] = $sKto;

        return $aDynValues;
    }

    /**
     * writes request or response XML from cP to file
     * depending from $mode
     * file-name is: $mode-[Date].xml
     *
     * @param string $sContent
     * @param string $sMode
     */
    public function writeDebugXML($sContent, $sMode)
    {
        $sSessionContent = var_export($this->getSessionData(), true);
        $sSessionContent .= "\r\n" . var_export($this->getSession()->getVariable('aBoniDebugData'), true);

        $now = date("Y-m-d-H-i-s", $this->_getCurrentTime());
        $sShopDir = $this->getConfig()->getConfigParam('sShopDir');
        $sXMLDir = $sShopDir . 'modules/oxps/creditpass/xml/';

        if (is_dir($sXMLDir)) {
            $fp = @fopen($sXMLDir . $now . "-" . $sMode . ".xml", "w");
            if ($fp) {
                fwrite($fp, $sContent);
                fclose($fp);

                // write down session debug data
                $fp2 = @fopen($sXMLDir . $now . "-session.txt", "w");
                fwrite($fp2, $sSessionContent);
                fclose($fp2);
            } else {
                $sXMLDir = $sShopDir . 'modules/oxps/creditpass/';
                $fp = @fopen($sXMLDir . $now . "-" . $sMode . ".xml", "w");
                fwrite($fp, $sContent);
                fclose($fp);

                // write down session debug data
                $fp2 = @fopen($sXMLDir . $now . "-session.txt", "w");
                fwrite($fp2, $sSessionContent);
                fclose($fp2);
            }
        }
    }

    /**
     * gets content of xml-field $tag - but only from block named $sBlock
     * cause unfortunately many tags have same names but in different blocks
     * returns value of xml-field
     *
     * @param string $sTag
     * @param string $sBlock
     * @param string $sMode
     * @param string $sXML
     *
     * @return string
     */
    protected function _getTagResult($sTag, $sBlock, $sMode = null, $sXML = null)
    {
        if ($sMode == "user") {
            $sBoniResponse = $this->getBoniResult($this->_oUser->oxuser__oxid->value);
        } elseif ($sXML) {
            $sBoniResponse = $sXML;
        } else {
            $sBoniResponse = $this->_sBoniResponseXML;
        }

        $aValues = $this->xmlParser($sBoniResponse, '_getTagResult');

        foreach ($aValues as $aContent) {
            if ($aContent['tag'] == $sBlock && $aContent['type'] == "open") {
                continue;
            }

            if ($aContent['tag'] == $sBlock && $aContent['type'] == "close") {
                break;
            }

            if ($aContent['tag'] == $sTag) {
                $sResult = $aContent['value'];

                $this->debugLog($sTag, $sResult);
            }
        }

        return $sResult;
    }

    /**
     * parses given xml using php xml parser and returns the result as an array
     *
     * @param string $sXML
     * @param string $sCalledFrom
     * @param string $mode enc/dec
     *
     * @return array $aValues
     */
    public function xmlParser($sXML, $sCalledFrom = '', $mode = '')
    {
        $aValues = array();
        $aIndexes = array();

        $sXML_orig = $sXML;

        switch ($mode) {
            case "enc":
                $sXML = utf8_encode($sXML);
                break;
            case "dec":
                $sXML = utf8_decode($sXML);
                break;
            default:
                break;
        }

        $oParser = xml_parser_create();
        xml_parser_set_option($oParser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($oParser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($oParser, $sXML, $aValues, $aIndexes);
        $iErr = xml_get_error_code($oParser);
        $sErr = xml_error_string($iErr);
        xml_parser_free($oParser);

        if ($iErr) {
            $this->_xmlErrorHandler($sErr . " - $sCalledFrom - trying to decode now ...");
            unset($iErr);
            unset($sErr);
            $sXML = utf8_decode($sXML_orig);
            $oParser = xml_parser_create();
            xml_parser_set_option($oParser, XML_OPTION_CASE_FOLDING, 0);
            xml_parser_set_option($oParser, XML_OPTION_SKIP_WHITE, 1);
            xml_parse_into_struct($oParser, $sXML, $aValues, $aIndexes);
            $iErr = xml_get_error_code($oParser);
            $sErr = xml_error_string($iErr);
            xml_parser_free($oParser);
            if ($iErr) {
                $this->_xmlErrorHandler(
                    $sErr . " - $sCalledFrom - decoding failed - trying to ENcode now ..."
                );
                unset($iErr);
                unset($sErr);
                $sXML = utf8_encode($sXML_orig);
                $oParser = xml_parser_create();
                xml_parser_set_option($oParser, XML_OPTION_CASE_FOLDING, 0);
                xml_parser_set_option($oParser, XML_OPTION_SKIP_WHITE, 1);
                xml_parse_into_struct($oParser, $sXML, $aValues, $aIndexes);
                $iErr = xml_get_error_code($oParser);
                $sErr = xml_error_string($iErr);
                xml_parser_free($oParser);
                if ($iErr) {
                    $this->_xmlErrorHandler(
                        $sErr . " - $sCalledFrom - encoding failed - you have a problem ..."
                    );
                }
            }
        }

        return $aValues;
    }

    /**
     * build sort of dummy XML if creditPass response shows an error
     *
     * @return string
     */
    protected function _makeErrorXML()
    {
        $sRet = "";
        $sRet .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $sRet .= "<RESPONSE>\n";
        $sRet .= "<CUSTOMER>\n";
        $sRet .= "<AUTH_ID>" . $this->getConfig()->getConfigParam('sOECreditPassAuthId') . "</AUTH_ID>\n";
        $sRet .= "<CUSTOMER_TA_ID>" . md5(
                $this->_oxidGetTime() . $this->_oUser->oxuser__oxid->value
            ) . "</CUSTOMER_TA_ID>\n";
        $sRet .= "</CUSTOMER>\n";
        $sRet .= "<PROCESS>\n";
        $sRet .= "<TA_TYPE>" . self::TATYPE . "</TA_TYPE>\n";
        $sRet .= "<TA_ID>0</TA_ID>\n";
        $sRet .= "<PROCESSING_CODE>1</PROCESSING_CODE>\n";
        $sRet .= "<REQUESTREASON>ABK</REQUESTREASON>\n";
        $sRet .= "<ANSWER_CODE>" . self::OECREDITPASS_ANSWER_CODE_ERROR . "</ANSWER_CODE>\n"; // changed from 999 to -1
        $sRet .= "<ANSWER_TEXT>TIMEOUT</ANSWER_TEXT>\n"; // changed from ERROR to TIMEOUT
        $sRet .= "<KONTOCHECKS>-1</KONTOCHECKS>\n";
        $sRet .= "<ADDR_CHECK>-1</ADDR_CHECK>\n";
        $sRet .= "<PURCHASE_TYPE>0</PURCHASE_TYPE>\n";
        $sRet .= "<INFOSCORE>-1</INFOSCORE>\n";
        $sRet .= "<INFORMASCORE>0</INFORMASCORE>\n";
        $sRet .= "<CONSUMERCREDITCHECK>0</CONSUMERCREDITCHECK>\n";
        $sRet .= "</PROCESS>\n";
        $sRet .= "<COST>\n";
        $sRet .= "<ADDR_CHECK>0.00</ADDR_CHECK>\n";
        $sRet .= "<INFOSCORE>0.00</INFOSCORE>\n";
        $sRet .= "<INFORMASCORE>0.00</INFORMASCORE>\n";
        $sRet .= "<CONSUMERCREDITCHECK>0.00</CONSUMERCREDITCHECK>\n";
        $sRet .= "<TOTAL>0.00</TOTAL>\n";
        $sRet .= "</COST>\n";
        $sRet .= "</RESPONSE>\n";

        return $sRet;
    }

    /**
     * returns current time
     *
     * @return integer
     */
    protected function _oxidGetTime()
    {
        return time();
    }

    /**
     * Returns the current time (unix epoch).
     *
     * @return int time
     */
    protected function _getCurrentTime()
    {
        return time();
    }

    /**
     * @return bool
     */
    protected function _isTestMode()
    {
        return (bool) $this->getConfig()->getConfigParam('blOECreditPassTestMode');
    }

    /**
     * @return object
     */
    public function getSession()
    {
        if ($this->_oSession === null) {
            $this->_oSession = Registry::getSession();
        }

        return $this->_oSession;
    }

    /**
     * @return object
     */
    public function getConfig()
    {
        if ($this->_oConfig === null) {
            $this->_oConfig = Registry::getConfig();
        }

        return $this->_oConfig;
    }

    /**
     * @return object
     */
    public function isAdmin()
    {
        if ($this->_oConfig === null) {
            $this->_oConfig = Registry::getConfig();
        }

        return $this->_oConfig;
    }

    /**
     * Returns value of config iOECreditPassManualWorkflow
     *
     * @return int
     */
    protected function _getManualWorkflowType()
    {
        return (int) $this->getConfig()->getConfigParam('iOECreditPassManualWorkflow');
    }

    /**
     * Validate IBAN
     *
     * @param $sAccNr
     *
     * @return bool
     */
    protected function isValidIBAN($sAccNr)
    {
        if (class_exists('oxSepaValidator')) {
            /**
             * @var SepaValidator $oSepaValidator
             */
            $oSepaValidator = oxNew(SepaValidator::class);
            if (method_exists($oSepaValidator, 'getIBANRegistry') && !array_key_exists(
                    substr($sAccNr, 0, 2),
                    $oSepaValidator->getIBANRegistry()
                )
            ) {
                return false;
            }
            if (method_exists($oSepaValidator, 'isValidIBAN') && $oSepaValidator->isValidIBAN($sAccNr)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Get list of answer codes and their descriptions.
     *
     * @return array
     */
    public function getAnswerCodes()
    {
        $aAnswerCodes = array(
            self::OECREDITPASS_ANSWER_CODE_ACK    => 'OECREDITPASS_LOG_LIST_ACK',
            self::OECREDITPASS_ANSWER_CODE_NACK   => 'OECREDITPASS_LOG_LIST_NACK',
            self::OECREDITPASS_ANSWER_CODE_MANUAL => 'OECREDITPASS_LOG_LIST_MANUAL',
            self::OECREDITPASS_ANSWER_CODE_ERROR  => 'OECREDITPASS_LOG_LIST_ERROR',
        );

        return $aAnswerCodes;
    }
}
