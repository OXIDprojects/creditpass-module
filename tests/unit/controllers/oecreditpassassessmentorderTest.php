<?php

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;

/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       core
 * @copyright (c) anzido GmbH, Andreas Ziethen 2008-2011
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 */
class Unit_Controllers_oeCreditPassAssessmentOrderTest extends OxidTestCase
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
        oxRegistry::getSession()->setVariable('usr', 'test_azcr_oxuser');

        $oConfig = new oeCreditPassConfig();
        $oConfig->setModuleActive();
    }

    public function tearDown()
    {
        // delete the dummy user:
        oxRegistry::getSession()->deleteVariable('usr');
        $oDummyUser = oxNew('oxUser');
        if ($oDummyUser->load('test_azcr_oxuser')) {
            $oDummyUser->delete();
        }

        parent::tearDown();
    }

    public function testInit()
    {
        // test without 'fnc' param and enabled module (and a non-redirect result):
        $oCrAssessment = $this->getMock('oecreditpassassessment', array('clearDebugData', 'checkAll'));
        $oCrAssessment->expects($this->once())->method('clearDebugData')->will($this->returnValue(null));
        $oCrAssessment->expects($this->once())->method('checkAll')->will($this->returnValue(true));
        $oView = $this->getMock('oecreditpassorder', array('_getCrAssessment', '_redirect'));
        $oView->expects($this->once())->method('_getCrAssessment')->will($this->returnValue($oCrAssessment));
        $oView->expects($this->never())->method('_redirect');
        $oView->init();

        // test without 'fnc' param and enabled module (and a redirect result):
        $oCrAssessment = $this->getMock('oecreditpassassessment', array('clearDebugData', 'checkAll'));
        $oCrAssessment->expects($this->once())->method('clearDebugData')->will($this->returnValue(null));
        $oCrAssessment->expects($this->once())->method('checkAll')->will($this->returnValue(false));
        $oView = $this->getMock('oecreditpassorder', array('_getCrAssessment', '_Redirect'));
        $oView->expects($this->once())->method('_getCrAssessment')->will($this->returnValue($oCrAssessment));
        $oView->expects($this->once())->method('_Redirect')->will($this->returnValue(null));
        $oView->init();

        // test with fnc=execute param:
        $this->setRequestParam('fnc', 'execute');
        $oView = $this->getMock('order', array('_getCrAssessment', '_Redirect'));
        $oView->expects($this->never())->method('_getCrAssessment');
        $oView->expects($this->never())->method('_Redirect');
        $oView->init();

        // clean up:
        $this->setRequestParam('fnc', null);
    }

    public function testInitInactive()
    {
        $oConfig = new oeCreditPassConfig();
        $oConfig->setModuleActive(false);

        $oView = $this->getMock('oecreditpassorder', array('_getCrAssessment', '_Redirect'));
        $oView->expects($this->never())->method('_getCrAssessment');
        $oView->expects($this->never())->method('_Redirect');
        $oView->init();
    }

    public function testgetCrAssessment()
    {
        $oView = $this->getProxyClass('oecreditpassorder');
        $this->assertTrue($oView->UNITgetCrAssessment() instanceof oeCreditPassAssessment);
    }

    public function testRedirect()
    {
        // cannot be tested because of exit() call
    }
}
