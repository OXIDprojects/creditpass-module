<?php

/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       core
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 */

namespace oe\oecreditpass\Core;

use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;

abstract class ModelDbGateway
{

    /**
     * Returns database resource
     *
     * @return DatabaseInterface
     * @throws DatabaseConnectionException
     */
    protected function _getDb()
    {
        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
    }

    /**
     * Abstract method for data saving (insert and update)
     *
     * @param array $aData model data
     */
    abstract public function save($aData);

    /**
     * Abstract method for loading model data
     *
     * @param string $sId model id
     */
    abstract public function load($sId);

    /**
     * Abstract method for delete model data
     *
     * @param string $sId model id
     */
    abstract public function delete($sId);
}