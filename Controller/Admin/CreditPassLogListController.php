<?php

/**
 * @extend    AdminListController
 */

namespace OxidProfessionalServices\CreditPassModule\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminListController;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\CreditPassModule\Model\CreditPassLog;

class CreditPassLogListController extends AdminListController
{

    const ORDER_FILTER_ALL = 1;
    const ORDER_FILTER_WITH_ORDER = 2;
    const ORDER_FILTER_NO_ORDER = 3;

    const ANSWER_CODE_FILTER_ALL = 'all';

    /**
     * Template filename.
     *
     * @var string
     */
    protected $_sThisTemplate = 'oecreditpass_log_list.tpl';

    /**
     * Name of chosen object class.
     *
     * @var string
     */
    protected $_sListClass = 'OxidProfessionalServices\CreditPassModule\Model\CreditPassLog';

    /**
     * Default SQL sorting parameter.
     *
     * @var string
     */
    protected $_sDefSortField = "timestamp";

    /**
     * Enable/disable sorting by DESC (SQL) (defaultfalse - disable).
     *
     * @var bool
     */
    protected $_blDesc = true;

    /**
     * Current shop id.
     *
     * @var int
     */
    protected $_iShopId = 1;

    /**
     * Constructor. Sets up the _iShopId property.
     */
    public function __construct()
    {
        $this->_iShopId = Registry::getConfig()->getShopId();
        if ($this->_iShopId == "oxbaseshop") {
            $this->_iShopId = 1;
        }
    }

    /**
     * Executes parent method parent::render() and returns name of template file.
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        $myConfig = Registry::getConfig();

        $sSelectedOrderFilter = $myConfig->getRequestParameter("pwrsearchfld");

        $this->_aViewData['pwrsearchfld'] = $sSelectedOrderFilter ? $sSelectedOrderFilter : self::ORDER_FILTER_ALL;

        return $this->_sThisTemplate;
    }

    /**
     * Get order filters.
     *
     * @return array
     */
    public function getOrderFilters()
    {
        $aFilters = array(
            array('code' => self::ORDER_FILTER_ALL, 'desc' => 'OECREDITPASS_LOG_LIST_ALL'),
            array('code' => self::ORDER_FILTER_WITH_ORDER, 'desc' => 'OECREDITPASS_LOG_LIST_WITH_ORDER'),
            array('code' => self::ORDER_FILTER_NO_ORDER, 'desc' => 'OECREDITPASS_LOG_LIST_NO_ORDER'),
        );

        return $aFilters;
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
     * Get answer code filters for list.
     *
     * @return array
     */
    public function getAnswerCodeFilters()
    {
        /**
         * @var CreditPassLog $oCreditPassLog
         */
        $oCreditPassLog = oxNew(CreditPassLog::class);
        $aAnswerCodes = $oCreditPassLog->getAnswerCodes();

        $aFilters = array();

        // include option to filter all answer codes as first option
        $aFilters[] = array('code' => (string) self::ANSWER_CODE_FILTER_ALL, 'desc' => 'OECREDITPASS_LOG_LIST_ALL');

        // filters are using different array format than answer code array
        foreach ($aAnswerCodes as $sKey => $sValue) {
            $aFilters[] = array('code' => (string) $sKey, 'desc' => $sValue);
        }

        return $aFilters;
    }

    /**
     * Builds and returns array of SQL WHERE conditions.
     * Additionally re-formats specific fields which should not be selected with "like" statement.
     *
     * @return array
     */
    public function buildWhere()
    {
        $aWhere = parent::buildWhere();

        $aExactSearchFields = array('oecreditpasslog.answercode');
        foreach ($aExactSearchFields as $sExactSearchField) {
            if (isset($aWhere[$sExactSearchField]) && $this->_isSearchValue($aWhere[$sExactSearchField])) {
                $aWhere[$sExactSearchField] = $this->_processFilter($aWhere[$sExactSearchField]);
            }
        }

        return $aWhere;
    }

    /**
     * Builds and returns SQL query string.
     * Additionally loads customer number.
     *
     * @param object $oListObject list main object
     *
     * @return string
     */
    protected function _buildSelectString($oListObject = null)
    {
        $sSql = parent::_buildSelectString($oListObject);

        $sSelectCustNr = 'oxuser.oxcustnr as custnr';
        $sSelectOrderNr = 'oxorder.oxordernr as ordernr';

        $sJoinUser = 'left join oxuser on (oecreditpasslog.userid = oxuser.oxid)';
        $sJoinOrder = 'left join oxorder on (oecreditpasslog.orderid = oxorder.oxid)';

        $sSql = str_replace(' from ', ", $sSelectCustNr, $sSelectOrderNr from ", $sSql);
        $sSql = str_replace(
            ' oecreditpasslog where ',
            " oecreditpasslog $sJoinUser $sJoinOrder where `oecreditpasslog`.`SHOPID`={$this->_iShopId} and ",
            $sSql
        );

        return $sSql;
    }

    /**
     * Adding order filter check
     *
     * @param array  $aWhere  SQL condition array
     * @param string $sqlFull SQL query string
     *
     * @return string
     */
    protected function _prepareWhereQuery($aWhere, $sqlFull)
    {
        $sSql = parent::_prepareWhereQuery($aWhere, $sqlFull);

        $myConfig = Registry::getConfig();

        $sOrderFilter = $myConfig->getRequestParameter("pwrsearchfld");

        switch ($sOrderFilter) {
            case self::ORDER_FILTER_WITH_ORDER:
                $sSql .= " and ( oecreditpasslog.orderid != '' )";
                break;
            case self::ORDER_FILTER_NO_ORDER:
                $sSql .= " and ( oecreditpasslog.orderid = '' )";
                break;
        }

        return $sSql;
    }

    /**
     * Returns list filter array.
     * Additionally removes special fields which are used in searching for data in database.
     *
     * @return array
     */
    public function getListFilter()
    {
        $aListFilter = parent::getListFilter();

        if ($aListFilter['oecreditpasslog']['answercode'] == self::ANSWER_CODE_FILTER_ALL) {
            unset($aListFilter['oecreditpasslog']['answercode']);
        }

        return $aListFilter;
    }
}
