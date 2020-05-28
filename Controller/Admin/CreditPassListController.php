<?php

/**
 * @extend    AdminListController
 */

namespace OxidProfessionalServices\CreditPassModule\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminListController;

/**
 * CreditPass List controller class
 */
class CreditPassListController extends AdminListController
{
    /**
     * Template filename.
     *
     * @var string
     */
    protected $_sThisTemplate = 'oecreditpass_list.tpl';
}
