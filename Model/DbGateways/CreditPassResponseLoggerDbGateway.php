<?php

namespace OxidProfessionalServices\CreditPassModule\Model\DbGateways;

use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassModelDbGateway;

/**
 * ResponseLoggerDbGateway
 *
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
class CreditPassResponseLoggerDbGateway extends CreditPassModelDbGateway
{

    /**
     * @var string
     */
    protected $_sTableName = 'oecreditpasslog';

    /**
     * @var string
     */
    protected $_sPrimaryFieldName = 'ID';

    /**
     * @var string
     */
    protected $_sPrimaryUpdateFieldName = 'TRANSACTIONID';

    /**
     * @var array
     */
    protected $_aNodesToFieldsMap = array(
        'ID'             => 'ID',
        'USER_ID'        => 'USERID',
        'ORDER_ID'       => 'ORDERID',
        'TIMESTAMP'      => 'TIMESTAMP',
        'ANSWER_CODE'    => 'ANSWERCODE',
        'ANSWER_TEXT'    => 'ANSWERTEXT',
        'ANSWER_DETAILS' => 'ANSWERDETAILS',
        'CACHED'         => 'CACHED',
        'TA_ID'          => 'TRANSACTIONID',
        'CUSTOMER_TA_ID' => 'CUSTOMERTRANSACTIONID',
    );

    /**
     * @var array
     */
    protected $_aFields = array(
        'ID',
        'SHOPID',
        'USERID',
        'ORDERID',
        'TIMESTAMP',
        'ANSWERCODE',
        'ANSWERTEXT',
        'ANSWERDETAILS',
        'CACHED',
        'TRANSACTIONID',
        'CUSTOMERTRANSACTIONID',
    );

    /**
     * Current shop id.
     *
     * @var int
     */
    protected $_iShopId = 1;

    /**
     * User Id
     *
     * @var string
     */
    protected $_sUserId = null;

    /**
     * Returns assigned user id
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->_sUserId;
    }

    /**
     * Sets assigned user id for logging.
     *
     * @param string $sUserId User id.
     */
    public function setUserId($sUserId)
    {
        $this->_sUserId = $sUserId;
    }


    /**
     * Constructor. Sets up the _iShopId property.
     */
    public function __construct()
    {
        $this->_iShopId = Registry::getConfig()->getShopId();
        if ($this->_iShopId == "oxbaseshop") {
            $this->_iShopId = 1;
        }
    }

    /**
     * Last inserted id.
     *
     * @var string
     */
    protected $_sLastInsertedID = null;

    /**
     * Returns database resource
     *
     * @param int $iFetchMode
     *
     * @return DatabaseInterface
     * @throws DatabaseConnectionException
     */
    protected function _getDb($iFetchMode = DatabaseProvider::FETCH_MODE_ASSOC)
    {
        return DatabaseProvider::getDb($iFetchMode);
    }

    /**
     * @return string
     */
    protected function _getTableName()
    {
        return $this->_sTableName;
    }

    /**
     * @return string
     */
    protected function _getPrimaryFieldName()
    {
        return $this->_sPrimaryFieldName;
    }

    /**
     * @param string $sName
     */
    public function setPrimaryUpdateFieldName($sName)
    {
        $aFields = $this->_getFields();
        $aNodeToFieldsMap = $this->_getNodesToFieldsMap();

        if (in_array($sName, $aFields)) {
            $this->_sPrimaryUpdateFieldName = $sName;
        } elseif (array_key_exists($sName, $aNodeToFieldsMap)) {
            $this->_sPrimaryUpdateFieldName = $aNodeToFieldsMap[$sName];
        }
    }

    /**
     * @return string
     */
    public function getPrimaryUpdateFieldName()
    {
        return $this->_sPrimaryUpdateFieldName;
    }

    /**
     * @return array
     */
    protected function _getFields()
    {
        return $this->_aFields;
    }

    /**
     * @return array
     */
    protected function _getNodesToFieldsMap()
    {
        return $this->_aNodesToFieldsMap;
    }

    /**
     * Sets last inserted ID.
     *
     * @param string $sID
     */
    protected function _setLastInsertedID($sID)
    {
        $this->_sLastInsertedID = $sID;
    }

    /**
     * Gets last inserted ID.
     *
     * @return string
     */
    public function getLastInsertedID()
    {
        return $this->_sLastInsertedID;
    }

    /**
     * Return data array converted to string while using special delimiter
     *
     * @param array  $aData
     * @param string $sDelimiter
     *
     * @return string
     */
    protected function _convertArrToStr($aData, $sDelimiter = ', ')
    {
        return implode($sDelimiter, $aData);
    }

    /**
     * Get new unique log id based on transaction id and current time.
     *
     * @param string $sTransactionId
     *
     * @return string
     */
    protected function _getUniqueId($sTransactionId)
    {
        return md5($sTransactionId . microtime());
    }

    /**
     * Saves new data to log.
     *
     * @param array $aData
     *
     * @return bool
     */
    public function save($aData)
    {
        $this->_setLastInsertedID(null);

        if (!$this->_isValid($aData)) {
            return false;
        }

        $oDb = $this->_getDb();

        $sTableName = $this->_getTableName();

        $aData = $this->_extractData($aData);
        $aData['SHOPID'] = $this->_iShopId;

        $sTransactionId = $aData['TRANSACTIONID'];

        $aData[$this->_getPrimaryFieldName()] = $this->_getUniqueId($sTransactionId);

        $aSql = $this->_prepareData($aData);

        $sSql = $this->_convertArrToStr($aSql);

        $sSql = "INSERT INTO `{$sTableName}` SET {$sSql}";

        $rs = $oDb->execute($sSql);
        if ($rs) {
            $this->_setLastInsertedID($aData[$this->_getPrimaryFieldName()]);

            $blReturn = true;
        } else {
            $blReturn = false;
        }

        return $blReturn;
    }

    /**
     * Updates existing data to a log.
     *
     * @param array $aData
     *
     * @return bool
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function update($aData)
    {
        if (!$this->_isValid($aData)) {
            return false;
        }

        $oDb = $this->_getDb();

        $sTableName = $this->_getTableName();

        $sWhereValue = $this->_shiftPrimaryUpdateField($aData);

        $sEscapedWhereValue = $oDb->quote($sWhereValue);

        $aData = $this->_convertAliasesToDbFieldKeys($aData);

        $aSetData = $this->_prepareData($aData);

        $sSetSql = $this->_convertArrToStr($aSetData);

        $sSql = "UPDATE `{$sTableName}` ";
        $sSql .= "SET {$sSetSql} ";
        $sSql .= "WHERE `{$sTableName}`.`{$this->getPrimaryUpdateFieldName()}` = {$sEscapedWhereValue} ";
        $sSql .= "AND `{$sTableName}`.`SHOPID` = {$this->_iShopId} ";
        $sUserId = $this->getUserId();
        if ($sUserId) {
            $sSql .= "AND `{$sTableName}`.`USERID` = {$oDb->quote($sUserId)}";
        }

        $rs = $oDb->execute($sSql);
        if ($rs) {
            return true;
        }

        return false;
    }

    /**
     * Converted XML node aliases to actual DB field names
     *
     * @param array $aData
     *
     * @return array
     */
    protected function _convertAliasesToDbFieldKeys($aData)
    {
        $aConvertedData = array();
        $aNodesToFieldsMap = $this->_getNodesToFieldsMap();
        $aFields = $this->_getFields();

        $aDataKeys = array_keys($aData);

        foreach ($aDataKeys as $sKey) {
            if (!in_array($sKey, $aFields)) {
                if (array_key_exists($sKey, $aNodesToFieldsMap)) {
                    $aConvertedData[$aNodesToFieldsMap[$sKey]] = $aData[$sKey];
                }
            } else {
                $aConvertedData[$sKey] = $aData[$sKey];
            }
        }

        return $aConvertedData;
    }

    /**
     * Extracting data
     *
     * @param array $aValues
     *
     * @return array
     */
    protected function _extractData($aValues)
    {
        $aData = array();

        $aFields = $this->_getNodesToFieldsMap();

        foreach ($aValues as $aValue) {
            if (array_key_exists($aValue['tag'], $aFields)) {
                $aData[$aFields[$aValue['tag']]] = $aValue['value'];
            }
        }

        return $aData;
    }

    /**
     * Loads data from log.
     *
     * @param string $sID
     *
     * @return array
     * @throws DatabaseConnectionException
     */
    public function load($sID)
    {
        $oDB = $this->_getDb();

        $sEscapedID = $oDB->quote($sID);

        $sSql = "SELECT ";
        $sSql .= "`{$this->_getTableName()}`.*, ";
        $sSql .= "`oxuser`.`OXCUSTNR` as `CUSTNR`, ";
        $sSql .= "`oxorder`.`OXORDERNR` as `ORDERNR` ";
        $sSql .= "FROM ";
        $sSql .= "`{$this->_getTableName()}` ";
        $sSql .= " LEFT JOIN `oxuser` ON (`{$this->_getTableName()}`.`USERID` = `oxuser`.`OXID`) ";
        $sSql .= " LEFT JOIN `oxorder` ON (`{$this->_getTableName()}`.`ORDERID` = `oxorder`.`OXID`) ";
        $sSql .= "WHERE ";
        $sSql .= "`{$this->_getTableName()}`.`{$this->_getPrimaryFieldName()}` = {$sEscapedID} ";
        $sSql .= "AND `{$this->_getTableName()}`.`SHOPID` = {$this->_iShopId}";

        $aData = $oDB->getRow($sSql);

        return $aData;
    }

    /**
     * @param string $sID
     *
     * @throws oeCreditPassNotSupportedException
     */
    public function delete($sID)
    {
        throw new oeCreditPassNotSupportedException('Method ' . __METHOD__ . 'not supported.');
    }

    /**
     * Returns if data array is valid.
     *
     * @param array $aData
     *
     * @return bool
     */
    protected function _isValid($aData)
    {
        if (is_array($aData) && !empty($aData)) {
            return true;
        }

        return false;
    }

    /**
     * Load all log list.
     *
     * @return array
     * @throws DatabaseConnectionException
     */
    public function loadAll()
    {
        $oDB = $this->_getDb();

        $sSql = "SELECT ";
        $sSql .= "`{$this->_getTableName()}`.*, ";
        $sSql .= "`oxuser`.`OXCUSTNR` as `CUSTNR`, ";
        $sSql .= "`oxorder`.`OXORDERNR` as `ORDERNR` ";
        $sSql .= "FROM ";
        $sSql .= "`{$this->_getTableName()}` ";
        $sSql .= " LEFT JOIN `oxuser` ON (`{$this->_getTableName()}`.`USERID` = `oxuser`.`OXID`) ";
        $sSql .= " LEFT JOIN `oxorder` ON (`{$this->_getTableName()}`.`ORDERID` = `oxorder`.`OXID`) ";
        $sSql .= "WHERE `{$this->_getTableName()}`.`SHOPID` = {$this->_iShopId}";

        $aLogList = $oDB->getAll($sSql);

        return $aLogList;
    }

    /**
     * Searches data in log.
     *
     * @param array $aSearchQuery
     *
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function search($aSearchQuery)
    {
        $oDB = $this->_getDb();

        $aWhere = array();
        foreach ($aSearchQuery as $sField => $sValue) {
            $aWhere[] = "`{$this->_getTableName()}`.`{$sField}` = {$oDB->quote( $sValue )}";
        }
        $aWhere[] = "`{$this->_getTableName()}`.`SHOPID` = {$this->_iShopId}";

        $sWhere = implode(' AND ', $aWhere);

        $sSql = "SELECT ";
        $sSql .= "`{$this->_getTableName()}`.*, ";
        $sSql .= "`oxuser`.`OXCUSTNR` as `CUSTNR`, ";
        $sSql .= "`oxorder`.`OXORDERNR` as `ORDERNR` ";
        $sSql .= "FROM ";
        $sSql .= "`{$this->_getTableName()}` ";
        $sSql .= " LEFT JOIN `oxuser` ON (`{$this->_getTableName()}`.`USERID` = `oxuser`.`OXID`) ";
        $sSql .= " LEFT JOIN `oxorder` ON (`{$this->_getTableName()}`.`ORDERID` = `oxorder`.`OXID`)";
        $sSql .= "WHERE {$sWhere}";

        $aData = $oDB->getAll($sSql);

        return $aData;
    }

    /**
     * Wrap fields with apostrophes and quote values
     *
     * @param array $aData
     *
     * @return array
     * @throws DatabaseConnectionException
     */
    protected function _prepareData($aData)
    {
        $aSql = array();

        foreach ($aData as $sField => $sData) {
            $sEscapedData = $this->_getDb()->quote($sData);
            $aSql[] = "`{$sField}` = {$sEscapedData}";
        }

        return $aSql;
    }

    /**
     * Shift primary update field from data
     *
     * @param array $aData
     *
     * @return string
     */
    protected function _shiftPrimaryUpdateField($aData)
    {
        $sWhereValue = $aData[$this->getPrimaryUpdateFieldName()];
        if (!$sWhereValue) {
            $aNodesToFieldsMapFlipped = array_flip($this->_aNodesToFieldsMap);
            $sWhereValue = $aData[$aNodesToFieldsMapFlipped[$this->getPrimaryUpdateFieldName()]];
            unset($aData[$aNodesToFieldsMapFlipped[$this->getPrimaryUpdateFieldName()]]);
        } else {
            unset($aData[$this->getPrimaryUpdateFieldName()]);
        }

        return $sWhereValue;
    }
}
