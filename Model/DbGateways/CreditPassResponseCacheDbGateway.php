<?php

namespace OxidProfessionalServices\CreditPassModule\Model\DbGateways;

use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassModelDbGateway;

/**
 * Response cache db gateway class
 *
 * @codingStandardsIgnoreFile
 */
class CreditPassResponseCacheDbGateway extends CreditPassModelDbGateway
{

    /**
     * Address identification
     *
     * @var string
     * @codingStandardsIgnoreStart
     */
    public $_sAddressId = null;

    /**
     * Payment id
     *
     * @var string
     */
    public $_sPaymentId = null;

    /**
     * Payment data hash
     *
     * @var string
     */
    public $_sPaymentHash = null;

    /**
     * Save object to payment settings table
     *
     * @param array $aData model data
     *
     * @return int
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function save($aData)
    {
        $oDb = $this->_getDb();

        $aData['ID'] = $this->_getId();
        foreach ($aData as $sField => $sData) {
            $aSql[] = '`' . $sField . '` = ' . $oDb->quote($sData);
        }

        $sSql = 'INSERT INTO `oecreditpasscache` SET ';
        $sSql .= implode(', ', $aSql);

        $oDb->execute($sSql);

        return $aData['ID'];
    }

    /**
     * Abstract method for delete model data
     *
     * @param string $sCurrentTime current timestamp
     *
     * @return bool
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function delete($sCurrentTime)
    {
        $oDb = $this->_getDb();
        $blDeleteResult = $oDb->execute("DELETE FROM `oecreditpasscache` WHERE `TIMESTAMP` < '{$sCurrentTime}'");

        return $blDeleteResult;
    }

    /**
     * Abstract method for loading model data
     *
     * @param string $sId user id
     *
     * @return array
     * @throws DatabaseConnectionException
     */
    public function load($sId)
    {
        $sAddressId = $this->getAddressId();
        $sPaymentId = $this->getPaymentId();
        $sPaymentData = $this->getPaymentDataHash();

        $oDb = $this->_getDb();
        $sSelect = "SELECT assessmentresult, paymentid FROM oecreditpasscache WHERE `userid` = '" . $sId;
        if ($sAddressId) {
            $sSelect .= "' and `userident` = '" . $sAddressId;
        }
        if ($sPaymentId) {
            $sSelect .= "' and `paymentid` = '" . $sPaymentId;
        }
        if ($sPaymentData) {
            $sSelect .= "' and `paymentdata` = '" . $sPaymentData;
        }
        $sSelect .= "' ORDER BY `timestamp` DESC";
        $oResult = $oDb->getRow($sSelect);

        return $oResult;
    }

    /**
     * Returns array of payment ids, that are not allowed for particular address
     *
     * @param string  $sId     user id
     * @param integer $iAnswer answer code to search for
     *
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function loadPaymentIdsByAnswer($sId, $iAnswer = 1)
    {
        $sAddressId = $this->getAddressId();

        $oDb = $this->_getDb();
        $sSelect = "SELECT paymentid FROM oecreditpasscache WHERE `userid` = '" . $sId;
        $sSelect .= "' and `answercode` = '" . $iAnswer;
        if ($sAddressId) {
            $sSelect .= "' and `userident` = '" . $sAddressId;
        }
        $sSelect .= "' ORDER BY `timestamp` DESC";
        $oResult = $oDb->getAll($sSelect);

        return $oResult;
    }

    /**
     * Set address identification string
     *
     * @param string $sAddress
     */
    public function setAddressId($sAddress)
    {
        $this->_sAddressId = $sAddress;
    }

    /**
     * Get address identification string
     *
     * @return string
     */
    public function getAddressId()
    {
        return $this->_sAddressId;
    }

    /**
     * Set payment id
     *
     * @param string $sId
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
     * Set payment data hash
     *
     * @param string $sHash
     */
    public function setPaymentDataHash($sHash)
    {
        $this->_sPaymentHash = $sHash;
    }

    /**
     * Get payment id
     *
     * @return string
     */
    public function getPaymentDataHash()
    {
        return $this->_sPaymentHash;
    }

    /**
     * Returns generated unique ID.
     *
     * @return integer
     */
    protected function _getId()
    {
        return substr(md5(uniqid('', true) . '|' . microtime()), 0, 32);
    }
}
