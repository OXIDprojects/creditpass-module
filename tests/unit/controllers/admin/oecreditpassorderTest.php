<?php

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;

/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       tests
 * @copyright (c) anzido GmbH, Andreas Ziethen 2008-2011
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 */
class Unit_Controllers_Admin_oeCreditPassOrderTest extends OxidTestCase
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
        $oView = $this->getProxyClass('oeCreditPass_Order');

        // test without oxid param:
        $this->setRequestParam('id', null);
        $this->assertEquals('oecreditpass_order.tpl', $oView->render());
        $aViewData = $oView->getNonPublicVar('_aViewData');
        $this->assertNull($aViewData['assessmentValue']);

        // test with oxid and pre-generated order data:
        // prepare data:
        $oOrder = oxNew('oxOrder');
        $oOrder->setId('test_azcr_oxorder');
        $oOrder->oxorder__oxuserid = new oxField('aztestusr');
        $oOrder->save();

        $sCacheId = 'test_azcr_oecreditpassresults';
        $oCacheGateway = $this->getMock('oeCreditPassResponseCacheDbGateway', array('_getId'));
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