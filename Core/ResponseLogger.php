<?php

namespace OxidProfessionalServices\CreditPassModule\Core;

use OxidProfessionalServices\CreditPassModule\Model\DbGateways\ResponseLoggerDbGateway;

/**
 * oeCreditPassResponseLogger
 */
class ResponseLogger
{

    /**
     * @var ResponseLoggerDbGateway
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
     * @param ResponseLoggerDbGateway $oLogger
     *
     * @return null
     */
    public function setLogger(ResponseLoggerDbGateway $oLogger)
    {
        $this->_oLogger = $oLogger;
    }

    /**
     * Gets logger.
     *
     * @return ResponseLoggerDbGateway
     */
    public function getLogger()
    {
        if (is_null($this->_oLogger)) {
            /**
             * @var ResponseLoggerDbGateway $oDatabaseLogger
             */
            $oDatabaseLogger = oxNew(ResponseLoggerDbGateway::class);
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
