<?php

/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       controllers
 * @copyright (c) anzido GmbH, Andreas Ziethen 2008-2011
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 *
 * @extend        oxAdminDetails
 */

namespace oe\oecreditpass\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;

class OrderController extends AdminDetailsController
{

    /**
     * Template filename.
     *
     * @var string
     */
    protected $_sThisTemplate = 'oecreditpass_order.tpl';

    /**
     * Get log details.
     *
     * @return array
     */
    public function getLogDetails()
    {
        /** @var oeCreditPassResponseLogger $oLogger */
        $oLogger = oxNew('oeCreditPassResponseLogger');
        $aLogDetails = $oLogger->searchOrder($this->getEditObjectId());

        return $aLogDetails;
    }

    /**
     * Get answer codes for translating to human readable text.
     *
     * @return array
     */
    public function getAnswerCodesForLog()
    {
        /** @var oeCreditPass_Log $oCreditPassLog */
        $oCreditPassLog = oxNew('oeCreditPass_Log');
        $aAnswerCodes = $oCreditPassLog->getAnswerCodesForLog();

        return $aAnswerCodes;
    }
}