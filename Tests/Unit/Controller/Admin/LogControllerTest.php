<?php

namespace OxidProfessionalServices\CreditPassModule\Tests\Unit\Controller\Admin;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\CreditPassLogController;

class LogControllerTest extends UnitTestCase
{

    /**
     * Test that render return proper template
     */
    public function testRender()
    {
        $oLogController = new CreditPassLogController();
        $this->assertEquals('oecreditpass_log.tpl', $oLogController->render());
    }
}