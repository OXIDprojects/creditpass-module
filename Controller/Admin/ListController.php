<?php

/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       controllers
 * @copyright (c) anzido GmbH, Andreas Ziethen 2008-2011
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 *
 * @extend        oxAdminView
 */

namespace oe\oecreditpass\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminListController;

class ListController extends AdminListController
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
        return oxNew("oeCreditPassConfig")->getModuleAdminUrl($sFile);
    }
}