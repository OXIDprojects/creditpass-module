<?php

namespace OxidProfessionalServices\CreditPassModule\Core\Exceptions;

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
