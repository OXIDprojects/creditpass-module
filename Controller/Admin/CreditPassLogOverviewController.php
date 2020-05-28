<?php

/**
 * @extend    AdminDetailsController
 */

namespace OxidProfessionalServices\CreditPassModule\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassResponseLogger;
use OxidProfessionalServices\CreditPassModule\Model\CreditPassLog;

class CreditPassLogOverviewController extends AdminDetailsController
{

    /**
     * Template filename.
     *
     * @var string
     */
    protected $_sThisTemplate = 'oecreditpass_log_overview.tpl';

    /**
     * Get log details.
     *
     * @return array
     */
    public function getLogDetails()
    {
        /**
         * @var CreditPassResponseLogger $oLogger
         */
        $oLogger = oxNew(CreditPassResponseLogger::class);
        $aLogDetails = $oLogger->load($this->getEditObjectId());

        return $aLogDetails;
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
}
