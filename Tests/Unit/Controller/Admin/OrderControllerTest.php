<?php

namespace OxidProfessionalServices\CreditPassModule\Tests\Unit\Controller\Admin;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\CreditPassOrderController;
use OxidProfessionalServices\CreditPassModule\Model\DbGateways\CreditPassResponseCacheDbGateway;
use OxidProfessionalServices\CreditPassModule\Model\Order;

class OrderControllerTest extends UnitTestCase
{

    /**
     * Tear Down
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function tearDown()
    {
        // clean up any database entries created by this test:
        DatabaseProvider::getDb()->execute("DELETE FROM oxorder WHERE oxid LIKE 'test_azcr_%'");
        DatabaseProvider::getDb()->execute("DELETE FROM oecreditpasscache WHERE id LIKE 'test_azcr_%'");

        parent::tearDown();
    }

    /**
     * Test Render
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function testRender()
    {
        $oView = $this->getProxyClass(CreditPassOrderController::class);

        // test without oxid param:
        $this->setRequestParam('id', null);
        $this->assertEquals('oecreditpass_order.tpl', $oView->render());
        $aViewData = $oView->getNonPublicVar('_aViewData');
        $this->assertNull($aViewData['assessmentValue']);

        // test with oxid and pre-generated order data:
        // prepare data:
        $oOrder = oxNew(Order::class);
        $oOrder->setId('test_azcr_oxorder');
        $oOrder->oxorder__oxuserid = new Field('aztestusr');
        $oOrder->save();

        $sCacheId = 'test_azcr_oecreditpassresults';
        $oCacheGateway = $this->getMock(CreditPassResponseCacheDbGateway::class, array('_getId'));
        $oCacheGateway->expects($this->once())->method('_getId')->will($this->returnValue($sCacheId));
        $iTimestamp = time();
        $oCacheGateway->save(
            array(
                'USERID'    => 'aztestusr',
                'TIMESTAMP' => $iTimestamp
            )
        );

        // test:
        $this->setRequestParam('id', 'test_azcr_oxorder');
        $this->assertEquals('oecreditpass_order.tpl', $oView->render());
        // clean up:
        $this->setRequestParam('id', null);
        $oOrder->delete();

        DatabaseProvider::getDb()->execute("DELETE FROM `oecreditpasscache` WHERE `ID` = '{$sCacheId}'");
    }
}