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
class oeCreditPassPayment_testmod extends oeCreditPassPayment
{

    public function setNonPublicVar($sVar, $mValue)
    {
        $this->$sVar = $mValue;
    }

    public function getNonPublicVar($sVar)
    {
        return $this->$sVar;
    }
}

class Unit_Controllers_oeCreditPassAssessmentPaymentTest extends OxidTestCase
{

    public function setUp()
    {
        parent::setUp();

        try {
            DatabaseProvider::getDb()->execute("DELETE FROM oxuser WHERE oxid LIKE 'test_azcr_%'");
        } catch (DatabaseConnectionException $e) {
        } catch (DatabaseErrorException $e) {
        }
        // we need to setup an active user, otherwise some tests will fail because $this->getUser() does not return a valid object:
        $oDummyUser = oxNew('oxUser');
        $oDummyUser->setId('test_azcr_oxuser');
        $oDummyUser->oxuser__oxusername = new oxField('test_azcr_oxuser');
        $oDummyUser->save();
        $this->setSessionParam('usr', 'test_azcr_oxuser');

        $oConfig = new oeCreditPassConfig();
        $oConfig->setModuleActive();
    }

    public function tearDown()
    {
        // delete the dummy user:
        $this->setSessionParam('usr', null);
        $oDummyUser = oxNew('oxUser');
        if ($oDummyUser->load('test_azcr_oxuser')) {
            $oDummyUser->delete();
        }

        oxRegistry::getSession()->deleteVariable('aBoniSessionData');

        parent::tearDown();
    }

    public function testProperties()
    {
        $oView = $this->getProxyClass('oeCreditPassPayment');
        $this->assertNull($oView->getNonPublicVar('_oCrAssessment'));
        $this->assertNull($oView->getNonPublicVar('_azIntLogicResponse'));
    }

    public function testGetPaymentListInactiveDoesNotCallProcessPaymentList()
    {
        $oConfig = new oeCreditPassConfig();
        $oConfig->setModuleActive(false);

        $oView = $this->getMock('oeCreditPassPayment', array('_processPaymentList'));
        $oView->expects($this->never())->method('_processPaymentList')->will($this->returnValue(null));
        $oView->getPaymentList();
    }

    public function testGetPaymentListCallsProcessPaymentListOnce()
    {
        $oView = $this->getMock('oeCreditPassPayment', array('_processPaymentList'));
        $oView->expects($this->once())->method('_processPaymentList')->will($this->returnValue(null));
        $oView->getPaymentList();
    }

    public function testprocessPaymentList()
    {
        $oReturnPayment = array('test_azcr_keeppayment' => new stdClass());
        $oCrAssessment = $this->getMock('oeCreditPassAssessment', array('filterPaymentMethods'));
        $oCrAssessment->expects($this->once())->method('filterPaymentMethods')->will(
            $this->returnValue($oReturnPayment)
        );
        $oView = $this->getMock('oeCreditPassPayment_testmod', array('_getCrAssessment', '_azUnsetPayment'));
        $oView->expects($this->once())->method('_getCrAssessment')->will($this->returnValue($oCrAssessment));

        $oView->setNonPublicVar(
            '_oPaymentList',
            array('test_azcr_unsetpayment' => new stdClass(), 'test_azcr_keeppayment' => new stdClass())
        );
        $oView->UNITprocessPaymentList();
        $this->assertEquals($oReturnPayment, $oView->getNonPublicVar('_oPaymentList'));
    }

    public function testgetCrAssessment()
    {
        $oView = $this->getProxyClass('oeCreditPassPayment');
        $this->assertTrue($oView->UNITgetCrAssessment() instanceof oecreditpassassessment);
    }

}
