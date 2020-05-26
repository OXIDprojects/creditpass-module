<?php

/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       models
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 *
 * @extend        oxBase
 */

namespace oe\oecreditpass\Model;

use OxidEsales\Eshop\Core\Model\BaseModel;

class Log extends BaseModel
{

    /**
     * Current class name
     *
     * @var string
     */
    protected $_sClassName = 'oecreditpasslog';

    /**
     * Core database table name. $sCoreTable could be only original data table name and not view name.
     *
     * @var string
     */
    protected $_sCoreTable = 'oecreditpasslog';

    /**
     * Field name list
     *
     * @var array
     */
    protected $_aFieldNames = array('id' => 0);
}
