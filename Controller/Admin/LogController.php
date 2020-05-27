<?php

/**
 * @extend    AdminListController
 */

namespace OxidProfessionalServices\CreditPassModule\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidProfessionalServices\CreditPassModule\Core\Assessment;

class LogController extends AdminController
{

    /**
     * Template filename.
     *
     * @var string
     */
    protected $_sThisTemplate = 'oecreditpass_log.tpl';

    /**
     * Get list of answer codes and their descriptions.
     *
     * @return array
     */
    public function getAnswerCodes()
    {
        $aAnswerCodes = array(
            Assessment::OECREDITPASS_ANSWER_CODE_ACK    => 'OECREDITPASS_LOG_LIST_ACK',
            Assessment::OECREDITPASS_ANSWER_CODE_NACK   => 'OECREDITPASS_LOG_LIST_NACK',
            Assessment::OECREDITPASS_ANSWER_CODE_MANUAL => 'OECREDITPASS_LOG_LIST_MANUAL',
            Assessment::OECREDITPASS_ANSWER_CODE_ERROR  => 'OECREDITPASS_LOG_LIST_ERROR',
        );

        return $aAnswerCodes;
    }

    /**
     * Get answer codes for translating to human readable text.
     *
     * @return array
     */
    public function getAnswerCodesForLog()
    {
        $aAnswerCodes = $this->getAnswerCodes();
        // include description for empty or no answer code
        $aAnswerCodes[''] = 'OECREDITPASS_LOG_LIST_EMPTY';

        return $aAnswerCodes;
    }
}
