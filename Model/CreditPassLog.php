<?php

/**
 * @extend    BaseModel
 */

namespace OxidProfessionalServices\CreditPassModule\Model;

use OxidEsales\Eshop\Core\Model\BaseModel;

/**
 * CreditPassLog class
 */
class CreditPassLog extends BaseModel
{

    /**
     * Current class name
     *
     * @var string
     */
    protected $_sClassName = 'OxidProfessionalServices\CreditPassModule\Model\CreditPassLog';

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
