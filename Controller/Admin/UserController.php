<?php

/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       controllers
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 *
 * @extend        oxAdminDetails
 */

namespace oe\oecreditpass\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;

class UserController extends AdminDetailsController
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
        /** @var oeCreditPassResponseLogger $oLogger */
        $oLogger = oxNew('oeCreditPassResponseLogger');
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
        /** @var oeCreditPass_Log $oCreditPassLog */
        $oCreditPassLog = oxNew('oeCreditPass_Log');
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