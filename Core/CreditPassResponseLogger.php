<?php

namespace OxidProfessionalServices\CreditPassModule\Core;

use OxidProfessionalServices\CreditPassModule\Model\DbGateways\CreditPassResponseLoggerDbGateway;

/**
 * CreditPassResponseLogger class
 */
class CreditPassResponseLogger
{

    /**
     * @var CreditPassResponseLoggerDbGateway
     */
    protected $_oLogger = null;

    /**
     * @var string
     */
    protected $_sSearchOrderFieldName = 'ORDERID';

    /**
     * @var string
     */
    protected $_sSearchUserFieldName = 'USERID';

    /**
     * Sets logger.
     *
     * @param CreditPassResponseLoggerDbGateway $oLogger
     */
    public function setLogger(CreditPassResponseLoggerDbGateway $oLogger)
    {
        $this->_oLogger = $oLogger;
    }

    /**
     * Gets logger.
     *
     * @return CreditPassResponseLoggerDbGateway
     */
    public function getLogger()
    {
        if (is_null($this->_oLogger)) {
            /**
             * @var CreditPassResponseLoggerDbGateway $oDatabaseLogger
             */
            $oDatabaseLogger = oxNew(CreditPassResponseLoggerDbGateway::class);
            $this->setLogger($oDatabaseLogger);
        }

        return $this->_oLogger;
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
        return $this->getLogger()->save($aData);
    }

    /**
     * Updates existing data to a log.
     *
     * @param array $aData
     *
     * @return bool
     */
    public function update($aData)
    {
        return $this->getLogger()->update($aData);
    }

    /**
     * Loads data from log.
     *
     * @param string $sID
     *
     * @return array
     */
    public function load($sID)
    {
        return $this->getLogger()->load($sID);
    }

    /**
     * Load all log list.
     *
     * @return array
     */
    public function loadAll()
    {
        return $this->getLogger()->loadAll();
    }

    /**
     * Loads data from log by order id.
     *
     * @param string $sOrderID
     *
     * @return array
     */
    public function searchOrder($sOrderID)
    {
        $aSearchQuery = array(
            $this->_getSearchOrderFieldName() => $sOrderID,
        );

        $aSearchResult = $this->getLogger()->search($aSearchQuery);

        return $aSearchResult[0];
    }

    /**
     * Loads log list from log by user id.
     *
     * @param string $sUserID
     *
     * @return array
     */
    public function searchUser($sUserID)
    {
        $aSearchQuery = array(
            $this->_getSearchUserFieldName() => $sUserID,
        );

        $aSearchResult = $this->getLogger()->search($aSearchQuery);

        return $aSearchResult;
    }

    /**
     * Gets field name to search log data for an order.
     *
     * @return string
     */
    protected function _getSearchOrderFieldName()
    {
        return $this->_sSearchOrderFieldName;
    }

    /**
     * Gets field name to search log data for the user.
     *
     * @return string
     */
    protected function _getSearchUserFieldName()
    {
        return $this->_sSearchUserFieldName;
    }

    /**
     * Gets last inserted ID.
     *
     * @return string
     */
    public function getLastID()
    {
        return $this->getLogger()->getLastInsertedID();
    }
}
