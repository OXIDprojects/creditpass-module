<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       tests
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 */

class Unit_Controllers_Admin_oeCreditPassLogTest extends OxidTestCase
{

    /**
     * Test that render return proper template
     */
    public function testRender()
    {
        $oLogController = new oeCreditPass_Log();
        $this->assertEquals('oecreditpass_log.tpl', $oLogController->render());
    }
}