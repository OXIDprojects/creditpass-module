<?php

namespace OxidProfessionalServices\CreditPassModule\Tests\Unit\Controller\Admin;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\LogController;

class LogControllerTest extends UnitTestCase
{

    /**
     * Test that render return proper template
     */
    public function testRender()
    {
        $oLogController = new LogController();
        $this->assertEquals('oecreditpass_log.tpl', $oLogController->render());
    }
}