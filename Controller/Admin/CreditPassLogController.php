<?php

/**
 * @extend    AdminListController
 */

namespace OxidProfessionalServices\CreditPassModule\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminController;

/**
 * Log controller class
 *
 * @extend AdminController
 */
class CreditPassLogController extends AdminController
{

    /**
     * Template filename.
     *
     * @var string
     */
    protected $_sThisTemplate = 'oecreditpass_log.tpl';
}
