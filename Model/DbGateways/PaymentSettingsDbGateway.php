<?php

/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       models
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 */

namespace oe\oecreditpass\Model\DbGateways;

class PaymentSettingsDbGateway extends oe\oecreditpass\Core\ModelDbGateway
{

    /**
     * @var $_sPaymentSettingsTableName string variable
     */
    protected $_sPaymentSettingsTableName = "oecreditpasspaymentsettings";

    /**
     * @var $_sWhereClause string variable
     */
    protected $_sWhereClause = "";

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
        $this->_iShopId = oxRegistry::getConfig()->getShopId();
        if ($this->_iShopId == "oxbaseshop") {
            $this->_iShopId = 1;
        }
    }

    /**
     * $_sWhereClause setter
     *
     * @param string $sWhereClause
     */
    public function setWhereClause($sWhereClause)
    {
        $this->_sWhereClause = $sWhereClause;
    }

    /**
     * $_sWhereClause getter
     *
     * @return string
     */
    public function getWhereClause()
    {
        return $this->_sWhereClause;
    }


    /**
     * $_sPaymentSettingsTableName setter
     *
     * @param string $sPaymentSettingsTableName
     */
    public function setPaymentSettingsTableName($sPaymentSettingsTableName)
    {
        $this->_sPaymentSettingsTableName = $sPaymentSettingsTableName;
    }

    /**
     * $_sPaymentSettingsTableName getter
     *
     * @return string
     */
    public function getPaymentSettingsTableName()
    {
        return $this->_sPaymentSettingsTableName;
    }

    /**
     * Save object to payment settings table
     *
     * @param array $aData model data
     *
     * @return int
     */
    public function save($aData)
    {
        $aData['SHOPID'] = $this->_iShopId;
        $oDb = $this->_getDb();

        foreach ($aData as $sField => $sData) {
            $sDbValue = $oDb->quote($sData);

            // non-numeric-integer strings should not be casted to (signed) integer which results in setting 0 integer value
            if ('PURCHASETYPE' == strtoupper($sField) && !$this->_isInt($sData)) {
                $sDbValue = 'NULL';
            }

            $aSql[] = '`' . $sField . '` = ' . $sDbValue;
        }
        $sTableName = $this->getPaymentSettingsTableName();

        $sSql = "INSERT INTO `{$sTableName}` SET ";
        $sSql .= implode(', ', $aSql);
        $sSql .= ' ON DUPLICATE KEY UPDATE ';
        $sSql .= '`PAYMENTID`=LAST_INSERT_ID(`PAYMENTID`), ';
        $sSql .= implode(', ', $aSql);

        $oDb->execute($sSql);

        $iId = $aData['PAYMENTID'];
        if (empty($iId)) {
            $iId = $oDb->getOne('SELECT LAST_INSERT_ID()');
        }

        return $iId;
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
     * Loads single element payment settings from database
     *
     * @param string $sId model id
     *
     * @return array|bool
     */
    public function load($sId)
    {
        $this->_addWhere($sId);
        if ($aReturn = $this->_fetchPaymentSettingsFromDatabase()) {
            return $aReturn;
        }

        return false;
    }

    /**
     * Abstract method for delete model data
     * This method is not used for payment settings.
     *
     * @param string $sId model id
     *
     * @return null
     */
    public function delete($sId)
    {
        return false;
    }

    /**
     * Load all payment settings from credit pass payment settings table
     * Or all payment settings of oxpayment table
     *
     * @return array|bool
     */
    public function loadAll()
    {
        if ($aReturn = $this->_fetchPaymentSettingsFromDatabase()) {
            return $aReturn;
        }

        return false;
    }

    /**
     * Forms where clause
     * Used for single element retrieval
     *
     * @param string $sId
     * @param string $sFieldName
     *
     * @return string
     */
    protected function _addWhere($sId, $sFieldName = 'PAYMENTID')
    {
        $sTableName = $this->getPaymentSettingsTableName();
        $sId = $this->_getDb()->quote($sId);
        $this->setWhereClause(" WHERE `{$sTableName}`.`{$sFieldName}` = {$sId}");
    }

    /**
     * Gets payment method options from database oecreditpasspaymentsettings table
     *
     * @return oxList
     */
    protected function _fetchPaymentSettingsFromDatabase()
    {
        /** @var oxList $oPaymentSettingsList */
        $oPaymentSettingsList = oxNew("oxList");
        $sShopPaymentsTable = getViewName("oxpayments");
        $oPaymentSettingsList->init("oxpayment");

        $sCreditPassPaymentsTable = $this->getPaymentSettingsTableName();
        $sAndJoin = "AND `{$sCreditPassPaymentsTable}`.`SHOPID` = {$this->_iShopId}";
        $sWhere = $this->getWhereClause();

        $sQuery = "SELECT * FROM `{$sShopPaymentsTable}` LEFT JOIN `{$sCreditPassPaymentsTable}` ON
                                   `{$sShopPaymentsTable}`.`OXID` = `{$sCreditPassPaymentsTable}`.`PAYMENTID` {$sAndJoin} {$sWhere}";

        $oPaymentSettingsList->selectString($sQuery);

        return $oPaymentSettingsList;
    }

} 