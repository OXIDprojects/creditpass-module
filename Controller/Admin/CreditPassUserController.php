<?php

/**
 * @extend    AdminDetailsController
 */

namespace OxidProfessionalServices\CreditPassModule\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassResponseLogger;
use OxidProfessionalServices\CreditPassModule\Model\CreditPassLog;

class CreditPassUserController extends AdminDetailsController
{

    /**
     * Template filename.
     *
     * @var string
     */
    protected $_sThisTemplate = 'oecreditpass_user.tpl';

    /**
     * Default sorting parameter.
     *
     * @var string
     */
    protected $_sDefSortField = "TIMESTAMP";

    /**
     * Enable/disable sorting by descending.
     *
     * @var bool
     */
    protected $_blDesc = true;

    /**
     * Get log list.
     *
     * @return array
     */
    public function getLogList()
    {
        /**
         * @var CreditPassResponseLogger $oLogger
         */
        $oLogger = oxNew(CreditPassResponseLogger::class);
        $aLogList = $oLogger->searchUser($this->getEditObjectId());

        // sort list
        usort($aLogList, array(__CLASS__, '_sortByField'));

        return $aLogList;
    }

    /**
     * Get answer codes for translating to human readable text.
     *
     * @return array
     */
    public function getAnswerCodesForLog()
    {
        /**
         * @var CreditPassLog $oCreditPassLog
         */
        $oCreditPassLog = oxNew(CreditPassLog::class);
        $aAnswerCodes = $oCreditPassLog->getAnswerCodesForLog();

        return $aAnswerCodes;
    }

    /**
     * Sort array by field name.
     */
    protected function _sortByField($a, $b)
    {
        if (!$this->_blDesc) {
            $iResult = strcmp($a[$this->_sDefSortField], $b[$this->_sDefSortField]);
        } else {
            $iResult = strcmp($b[$this->_sDefSortField], $a[$this->_sDefSortField]);
        }

        return $iResult;
    }
}
