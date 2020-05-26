<?php

/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       core
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 *
 * @extend        oxException
 */

namespace oe\oecreditpass\Core\Exceptions;

use OxidEsales\Eshop\Core\Exception\StandardException;

/**
 * Exception base class for creditPass
 */
class Exception extends StandardException
{

    /**
     * Exception constructor.
     *
     * @param string  $sMessage exception message
     * @param integer $iCode    exception code
     */
    public function __construct($sMessage = "", $iCode = 0)
    {
        parent::__construct($sMessage, $iCode);
    }
}
