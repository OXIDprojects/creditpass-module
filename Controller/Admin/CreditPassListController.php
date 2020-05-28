<?php

/**
 * @extend    AdminListController
 */

namespace OxidProfessionalServices\CreditPassModule\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminListController;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassConfig;

class CreditPassListController extends AdminListController
{

    /**
     * Template filename.
     *
     * @var string
     */
    protected $_sThisTemplate = 'oecreditpass_list.tpl';

    /**
     * Returns module path for admin. SSL aware method.
     *
     * @param string $sFile Relative file name
     *
     * @return mixed
     */
    public function getModuleAdminUrl($sFile)
    {
        return oxNew(CreditPassConfig::class)->getModuleAdminUrl($sFile);
    }
}
