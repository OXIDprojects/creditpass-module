<?php


/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       controllers
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 *
 * @extend        oxAdminView
 */

namespace oe\oecreditpass\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminController;

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
            oecreditpassassessment::OECREDITPASS_ANSWER_CODE_ACK    => 'OECREDITPASS_LOG_LIST_ACK',
            oecreditpassassessment::OECREDITPASS_ANSWER_CODE_NACK   => 'OECREDITPASS_LOG_LIST_NACK',
            oecreditpassassessment::OECREDITPASS_ANSWER_CODE_MANUAL => 'OECREDITPASS_LOG_LIST_MANUAL',
            oecreditpassassessment::OECREDITPASS_ANSWER_CODE_ERROR  => 'OECREDITPASS_LOG_LIST_ERROR',
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