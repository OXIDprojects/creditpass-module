<?php

/**
 * @extend    AdminDetailsController
 */

namespace OxidProfessionalServices\CreditPassModule\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidProfessionalServices\CreditPassModule\Core\ResponseLogger;
use OxidProfessionalServices\CreditPassModule\Model\Log;

class LogOverviewController extends AdminDetailsController
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
         * @var ResponseLogger $oLogger
         */
        $oLogger = oxNew(ResponseLogger::class);
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
         * @var Log $oCreditPassLog
         */
        $oCreditPassLog = oxNew(Log::class);
        $aAnswerCodes = $oCreditPassLog->getAnswerCodesForLog();

        return $aAnswerCodes;
    }
}
