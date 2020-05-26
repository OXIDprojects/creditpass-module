<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       tests
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 */

abstract class DefaultTestMock
{

    /**
     * number of times execute was called
     *
     * @var array
     */
    public static $iExecutionCount = array();

    /**
     * Resets execution caches.
     */
    public static function reset()
    {
        oxTestDb::$iExecutionCount = array();
    }
}

class oxTestDbResult extends DefaultTestMock
{

    /**
     * @var bool
     */
    public $EOF = false;

    /**
     * @var array
     */
    public $fields = array();

    /**
     * @var mixed
     */
    public $result = null;

    /**
     * @var int
     */
    protected $recordCount = null;

    public function recordCount()
    {
        oxTestDbResult::$iExecutionCount[__FUNCTION__]++;

        return $this->recordCount;
    }

    /**
     * @return null
     */
    public function moveNext()
    {
        oxTestDbResult::$iExecutionCount[__FUNCTION__]++;
        // TODO add count down to reduce records and eventually reach EFO
        $this->EOF = true;
    }

    /**
     * @param array $fields
     * @param int   $recordCount
     * @param mixed $result
     */
    public function __construct($fields = array(), $recordCount = 0, $result = null)
    {
        $this->fields = $fields;
        $this->recordCount = $recordCount;
        $this->result = $result;
        // define when EOF ends
    }

    /**
     * @return mixed
     */
    public function result()
    {
        oxTestDbResult::$iExecutionCount[__FUNCTION__]++;

        return $this->result;
    }
}

class oxTestDb extends DefaultTestMock
{

    /**
     * Executed queries
     *
     * @var array
     */
    public static $sExecutedQueries = array();

    /**
     * Last executed query
     *
     * @var array
     */
    public static $sLastExecutedQuery = array();

    /**
     * @var oxTestDbResult
     */
    public $oResult = null;

    /**
     * Resets execution caches.
     */
    public static function reset()
    {
        oxTestDb::$iExecutionCount = array();
        oxTestDb::$sExecutedQueries = array();
        oxTestDb::$sLastExecutedQuery = array();
    }

    /**
     * @param string $sQuery
     *
     * @return mixed
     */
    public function execute($sQuery)
    {
        oxTestDb::$iExecutionCount[__FUNCTION__]++;
        oxTestDb::$sExecutedQueries[__FUNCTION__][] = $sQuery;
        oxTestDb::$sLastExecutedQuery[__FUNCTION__] = $sQuery;

        return $this->oResult->result;
    }

    /**
     * @param string $sQuery
     *
     * @return mixed
     */
    public function getOne($sQuery)
    {
        oxTestDb::$iExecutionCount[__FUNCTION__]++;
        oxTestDb::$sExecutedQueries[__FUNCTION__][] = $sQuery;
        oxTestDb::$sLastExecutedQuery[__FUNCTION__] = $sQuery;

        return $this->oResult->result;
    }

    /**
     * @param string $sQuery
     *
     * @return mixed
     */
    public function getRow($sQuery)
    {
        oxTestDb::$iExecutionCount[__FUNCTION__]++;
        oxTestDb::$sExecutedQueries[__FUNCTION__][] = $sQuery;
        oxTestDb::$sLastExecutedQuery[__FUNCTION__] = $sQuery;

        return $this->oResult->result;
    }

    /**
     * @param string $sQuery
     *
     * @return mixed
     */
    public function getAll($sQuery)
    {
        oxTestDb::$iExecutionCount[__FUNCTION__]++;
        oxTestDb::$sExecutedQueries[__FUNCTION__][] = $sQuery;
        oxTestDb::$sLastExecutedQuery[__FUNCTION__] = $sQuery;

        return $this->oResult->result;
    }

    /**
     * @param string $sQuery
     *
     * @return array
     */
    public function getArray($sQuery)
    {
        oxTestDb::$iExecutionCount[__FUNCTION__]++;
        oxTestDb::$sExecutedQueries[__FUNCTION__][] = $sQuery;
        oxTestDb::$sLastExecutedQuery[__FUNCTION__] = $sQuery;

        return $this->oResult->result;
    }

    /**
     * @param string $sQuery
     *
     * @return string
     */
    public function quote($sQuery)
    {
        oxTestDb::$iExecutionCount[__FUNCTION__]++;
        oxTestDb::$sExecutedQueries[__FUNCTION__][] = $sQuery;
        oxTestDb::$sLastExecutedQuery[__FUNCTION__] = $sQuery;

        return $sQuery;
    }

    /**
     * @param oxTestDbResult $oResultMock
     */
    public function __construct($oResultMock = null)
    {
        $this->oResult = $oResultMock;
        if (is_null($oResultMock)) {
            $this->oResult = new oxTestDbResult();
        }
    }
}