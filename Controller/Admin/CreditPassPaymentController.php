<?php

/**
 * @extend    AdminDetailsController
 */

namespace OxidProfessionalServices\CreditPassModule\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\CreditPassModule\Model\DbGateways\CreditPassPaymentSettingsDbGateway;

/**
 * CreditPass Payment controller class
 *
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
class CreditPassPaymentController extends AdminDetailsController
{

    /**
     * @var $_oDbGateway CreditPassPaymentSettingsDbGateway variable
     */
    protected $_oDbGateway = null;

    // TODO DEFINE where defaults are stored
    /**
     * Default Payment settings Fallback
     *
     * @var string
     */
    protected $_sDefaultFallback = '0';

    /**
     * Default Payment settings  allowonerror value
     *
     * @var string
     */
    protected $_sDefaultAllowOnError = '0';

    /**
     * Default Payment settings active value
     *
     * @var string
     */
    protected $_sDefaultActive = '0';

    /**
     * Template filename.
     *
     * @var string
     */
    protected $_sThisTemplate = 'oecreditpass_payment.tpl';

    /**
     * Retrieved payment settings
     *
     * @var null
     */
    protected $_aPaymentSettings = null;

    /**
     * Submitted payment settings.
     *
     * @var null
     */
    protected $_aSubmittedPaymentSettings = null;

    /**
     * List of payment setting id's to reuse payment settings from submitted data.
     *
     * @var null
     */
    protected $_aReuseSubmittedPaymentSettingsByID = array();

    /**
     * Current shop id.
     *
     * @var int
     */
    protected $_iShopId = 1;

    /**
     * Constructor. Sets up the _iShopId property.
     */
    public function __construct()
    {
        $myConfig = $this->getConfig();
        $this->_iShopId = $myConfig->getShopId();
        if ($this->_iShopId == "oxbaseshop") {
            $this->_iShopId = 1;
        }
    }

    /**
     * Sets database gateway
     *
     * @param CreditPassPaymentSettingsDbGateway $oDbGateway
     */
    protected function _setDbGateway($oDbGateway)
    {
        $this->_oDbGateway = $oDbGateway;
    }

    /**
     * Returns database gateway
     *
     * @return CreditPassPaymentSettingsDbGateway
     */
    protected function _getDbGateway()
    {
        if (is_null($this->_oDbGateway)) {
            $this->_setDbGateway(oxNew(CreditPassPaymentSettingsDbGateway::class));
        }

        return $this->_oDbGateway;
    }

    /**
     * Update payment settings to database oecreditpasspaymentsettings table
     *
     * @return null
     */
    protected function _updatePaymentSettings()
    {
        if (!$aAllPaymentSettings = $this->getSubmittedPaymentSettings()) {
            return;
        }

        $aAllPaymentSettings = $this->_parseAllPaymentSettingsParameters($aAllPaymentSettings);

        $aErrors = array();

        foreach ($aAllPaymentSettings as $aPaymentSettings) {
            if ($aPaymentSettings['ACTIVE'] && (!isset($aPaymentSettings['PURCHASETYPE']) || empty($aPaymentSettings['PURCHASETYPE']))) {
                $aErrors[] = 'OECREDITPASS_EXCEPTION_PURCHASETYPENOTSET';

                // reuse same payment settings to display
                $this->_addReuseSubmittedPaymentSettingID($aPaymentSettings['PAYMENTID']);
            } else {
                $this->_getDbGateway()->save($aPaymentSettings);
            }
        }

        $aErrors = array_unique($aErrors);
        foreach ($aErrors as $sError) {
            Registry::getUtilsView()->addErrorToDisplay($sError);
        }
    }

    /**
     * Parse payment settings parameters and set values depending on other values.
     *
     * @param array $aAllPaymentSettings
     *
     * @return array
     */
    protected function _parseAllPaymentSettingsParameters($aAllPaymentSettings)
    {
        $aParsedPaymentSettings = array();

        foreach ($aAllPaymentSettings as $sKey => $aPaymentSettings) {
            if (!$this->_isInt($aPaymentSettings['PURCHASETYPE'])) {
                $aPaymentSettings['PURCHASETYPE'] = '';
            }

            if ($aPaymentSettings['ACTIVE'] == 1) {
                // "Fallback" can't be set on payment method which is active for creditPass check.
                $aPaymentSettings['FALLBACK'] = 0;
            } else {
                // Purchase type must not be set on payment method which is not active for creditPass check.
                $aPaymentSettings['PURCHASETYPE'] = '';
            }
            $aParsedPaymentSettings[$sKey] = $aPaymentSettings;
        }

        return $aParsedPaymentSettings;
    }

    /**
     * Checks whether a value is (signed) integer.
     *
     * @param mixed $mValue
     *
     * @return bool
     */
    protected function _isInt($mValue)
    {
        return is_numeric($mValue) && "$mValue" == (int) $mValue;
    }

    /**
     * Get current payment settings from database payments table
     * Tries to get payment options for oecreditpasspaymentsettings table joined with oxpayments table
     *
     * @return array
     */
    public function getPaymentSettings()
    {
        if (is_null($this->_aPaymentSettings)) {
            $oDb = $this->_getDbGateway();
            $this->_aPaymentSettings = $this->_parseResults($oDb->loadAll());
            $this->_aPaymentSettings = $this->_reuseAllSubmittedPaymentSettings($this->_aPaymentSettings);
        }

        return $this->_aPaymentSettings;
    }

    /**
     * Add payment setting id to list for reusing submitted data later.
     *
     * @param string $PaymentSettingID
     */
    protected function _addReuseSubmittedPaymentSettingID($PaymentSettingID)
    {
        $this->_aReuseSubmittedPaymentSettingsByID[] = $PaymentSettingID;
    }

    /**
     * Get payment setting id list for reusing submitted data later
     *
     * @return array
     */
    protected function _getReuseSubmittedPaymentSettingIDs()
    {
        return $this->_aReuseSubmittedPaymentSettingsByID;
    }

    /**
     * Change all payment settings on error.
     *
     * @param array $aAllPaymentSettings
     *
     * @return array
     */
    protected function _reuseAllSubmittedPaymentSettings($aAllPaymentSettings)
    {
        $aChangedPaymentSettings = array();

        foreach ($aAllPaymentSettings as $sKey => $aPaymentSettings) {
            $aChangedPaymentSettings[$sKey] = $this->_reuseSubmittedPaymentSettings($aPaymentSettings);
        }

        return $aChangedPaymentSettings;
    }

    /**
     * Change payment settings.
     *
     * @param array $aPaymentSettings
     *
     * @return array
     */
    protected function _reuseSubmittedPaymentSettings($aPaymentSettings)
    {
        $sPaymentSettingID = $aPaymentSettings->oxpayments__paymentid->value;

        $sDoNotOverwriteFields = array('ID', 'PAYMENTID');

        if (in_array($sPaymentSettingID, $this->_getReuseSubmittedPaymentSettingIDs())) {
            $aAllSubmittedPaymentSettings = $this->_parseAllPaymentSettingsParameters(
                $this->getSubmittedPaymentSettings()
            );

            // go through each field and set the value
            foreach ($aAllSubmittedPaymentSettings[$sPaymentSettingID] as $sKey => $sValue) {
                // check if field can be overwritten
                if (!in_array($sKey, $sDoNotOverwriteFields)) {
                    $sPaymentSettingField = 'oxpayments__' . strtolower($sKey);
                    // check if field exists
                    if ($aPaymentSettings->$sPaymentSettingField) {
                        $aPaymentSettings->$sPaymentSettingField->value = $sValue;
                    }
                }
            }
        }

        return $aPaymentSettings;
    }

    /**
     * Get new unique payment setting id based on payment id and current shop id.
     *
     * @param string $sPaymentId
     *
     * @return string
     */
    protected function getUniqueId($sPaymentId)
    {
        return md5($sPaymentId . '-' . $this->_iShopId);
    }

    /**
     * In order to remove logic from templates, some data has to be parsed
     *
     * @param array $aPaymentSettings
     *
     * @return array
     */
    protected function _parseResults($aPaymentSettings)
    {
        foreach ($aPaymentSettings as $aPayment) {
            if (!$aPayment->oxpayments__id->value) {
                // create unique payment setting id
                $aPayment->oxpayments__id = new Field($this->getUniqueId($aPayment->oxpayments__oxid->value));
                // associate with payment id
                $aPayment->oxpayments__paymentid = new Field($aPayment->oxpayments__oxid->value);
                // set default settings
                $aPayment->oxpayments__active = new Field($this->_sDefaultActive);
                $aPayment->oxpayments__allowonerror = new Field($this->_sDefaultAllowOnError);
                $aPayment->oxpayments__fallback = new Field($this->_sDefaultFallback);
            }
        }

        return $aPaymentSettings;
    }

    /**
     * Fetches aPaymentSettings array from request
     * It should follow this structure:
     * * array( 'group' => array( 'key' => value ) ),
     * * where group - Payment setting group, key - Payment setting ID, value - its value
     *
     * @return array
     */
    public function getSubmittedPaymentSettings()
    {
        if (is_null($this->_aSubmittedPaymentSettings)) {
            $this->_aSubmittedPaymentSettings = $this->getViewParameter('aPaymentSettings');
        }

        return $this->_aSubmittedPaymentSettings;
    }

    /**
     * Provided default value for AllowOnError option
     *
     * @return string
     */
    public function getDefaultAllowOnErrorValue()
    {
        return $this->_sDefaultAllowOnError;
    }

    /**
     * Saves payments to database
     */
    public function save()
    {
        $this->_updatePaymentSettings();
    }

    /**
     * Renders current payment selection template
     *
     * @return string
     */
    public function render()
    {
        return $this->_sThisTemplate;
    }
}
