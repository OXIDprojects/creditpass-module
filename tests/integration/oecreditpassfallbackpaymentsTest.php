<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       tests
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 */

use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Field;

/**
 * Testing payment fallback implementation
 */
class Integration_oeCreditPassFallbackPaymentsTest extends OxidTestCase
{

    var $aAllPaymentSettings = null;

    public function tearDown()
    {
        $this->setConfigParam('blOECreditPassIsActive', null);
        $this->setConfigParam('sOECreditPassUrl', null);
        $this->setConfigParam('sOECreditPassAuthId', null);
        $this->setConfigParam('sOECreditPassAuthPw', null);
        $this->setConfigParam('blOECreditPassTestMode', null);
        $this->setConfigParam('iOECreditPassCheckCacheTimeout', null);
        try {
            DatabaseProvider::getDb()->query("TRUNCATE oecreditpasscache");
        } catch (DatabaseConnectionException $e) {
        }
        parent::tearDown();
    }

    /**
     * Configuration for creditPass
     */
    public function _setPaymentSettings()
    {
        $this->setConfigParam('blOECreditPassIsActive', 1);
        $this->setConfigParam('sOECreditPassUrl', 'https://secure.creditpass.de/atgw/authorize.cfm');
        $this->setConfigParam('sOECreditPassAuthId', 'T901019');
        $this->setConfigParam('sOECreditPassAuthPw', 'nUSAUcjTW9VVnq');
        $this->setConfigParam('blOECreditPassTestMode', 1);
    }

    /**
     * Configuration for creditPass
     */
    protected function _setupCreditPass()
    {
        $this->setConfigParam('blOECreditPassIsActive', 1);
        $this->setConfigParam('sOECreditPassUrl', 'https://secure.creditpass.de/atgw/authorize.cfm');
        $this->setConfigParam('sOECreditPassAuthId', 'T901019');
        $this->setConfigParam('sOECreditPassAuthPw', 'nUSAUcjTW9VVnq');
        $this->setConfigParam('blOECreditPassTestMode', 1);
    }

    /**
     * Configuration for payment settings
     */
    protected function _setupPaymentSettings($aAllPaymentSettings)
    {
        foreach ($aAllPaymentSettings as $aPaymentSettings) {
            $oDbGateway = oxNew('oeCreditPassPaymentSettingsDbGateway');
            $oDbGateway->save($aPaymentSettings);
        }
    }

    /**
     * Creates basket with some articles
     */
    protected function _setupBasket($iAmount)
    {
        // basket preparation
        $oBasket = new oxBasket();
        $oUser = $this->_setupUser();
        $oBasket->setBasketUser($oUser);

        $aArtsForBasket = $this->_setupArticles($iAmount);
        // adding articles to basket
        foreach ($aArtsForBasket as $aArt) {
            if (is_null($aArt['amount']) || ($aArt['amount']) == 0) {
                continue;
            }
            $oBasket->addToBasket($aArt['id'], $aArt['amount']);
        }
        $oBasket->calculateBasket();

        return $oBasket;
    }

    /**
     * Returns default admin user object
     *
     * @return oxUser
     */
    protected function _setupUser()
    {
        // basket preparation
        $oUser = new oxUser();
        $oUser->load('oxdefaultadmin');
        $oUser->oxuser__oxzip = new oxField('12345');
        $oUser->addToGroup('oxidnewcustomer');
        $oUser->save();

        return $oUser;
    }

    /**
     * Returns array of articles added to basket
     *
     * @return array
     */
    protected function _setupArticles($iAmount)
    {
        $aArt = array(
            array(
                'id'      => 9100,
                'oxprice' => 100.01,
                'amount'  => $iAmount,
            )
        );

        foreach ($aArt as $aArtItem) {
            $oArticle = new Article();
            $oArticle->setId($aArtItem['id']);
            $oArticle->oxarticles__oxprice = new Field($aArtItem['oxprice']);
            $oArticle->save();
        }

        return $aArt;
    }

    /**
     * Sets User cache to database
     */
    protected function _setupUserCache()
    {
        $sCheckStr = $this->_getAddressIdent();
        $oCache = oxNew('oeCreditPassResultCache');
        $oCache->setAddressIdentification($sCheckStr);
        $oCache->setUserId('oxdefaultadmin');
        $oCache->setPaymentId('oxidcashondel');
        $oCache->setResponse(serialize($this->_getResponse()));
        $oCache->setAnswerCode(1);
        $oCache->storeData();
    }

    /**
     * builds md5-hash of most important user-data
     *
     * @return string
     */
    protected function _getAddressIdent()
    {
        $oUser = $this->_setupUser();
        $sCheckStr = md5(
            trim($oUser->oxuser__oxfname->value) .
            trim($oUser->oxuser__oxlname->value) .
            trim($oUser->oxuser__oxstreet->value) .
            trim($oUser->oxuser__oxstreetnr->value) .
            trim($oUser->oxuser__oxzip->value) .
            trim($oUser->oxuser__oxcity->value) .
            trim($oUser->oxuser__oxcountryid->value)
        );

        return $sCheckStr;
    }

    /**
     * builds md5-hash of most important user-data
     *
     * @return string
     */
    protected function _getResponse()
    {
        $sXML = file_get_contents(__DIR__ . '/../fixtures/not_authorized.xml');

        return trim($sXML);
    }

    /**
     * Data provider.
     */
    public function _fallbackPaymentDataProvider()
    {
        $aAllPaymentSettings = array(
            'oxidcashondel'  =>
                array('ID'           => 'f3d6a7fe2782ba1ddef35475b32a1e99',
                      'PAYMENTID'    => 'oxidcashondel',
                      'ACTIVE'       => '1',
                      'PURCHASETYPE' => '1',
                      'ALLOWONERROR' => '0'),
            'oxidpayadvance' =>
                array('ID'           => 'f308d57ce25fb09dde57c9ca7833a43b',
                      'PAYMENTID'    => 'oxidpayadvance',
                      'ACTIVE'       => '1',
                      'PURCHASETYPE' => '1',
                      'ALLOWONERROR' => '0'),
            'oxidinvoice'    =>
                array('ID'           => 'efb7d9735b35af78a1febefb1dabc16b',
                      'PAYMENTID'    => 'oxidinvoice',
                      'ACTIVE'       => '0',
                      'FALLBACK'     => '1',
                      'ALLOWONERROR' => '0')
        );

        $aAllPaymentSettings2 = array(
            'oxidcashondel'  =>
                array('ID'           => 'f3d6a7fe2782ba1ddef35475b32a1e99',
                      'PAYMENTID'    => 'oxidcashondel',
                      'ACTIVE'       => '1',
                      'PURCHASETYPE' => '1',
                      'ALLOWONERROR' => '0'),
            'oxidpayadvance' =>
                array('ID'           => 'f308d57ce25fb09dde57c9ca7833a43b',
                      'PAYMENTID'    => 'oxidpayadvance',
                      'ACTIVE'       => '0',
                      'PURCHASETYPE' => '0',
                      'ALLOWONERROR' => '0'),
            'oxidinvoice'    =>
                array('ID'           => 'efb7d9735b35af78a1febefb1dabc16b',
                      'PAYMENTID'    => 'oxidinvoice',
                      'ACTIVE'       => '0',
                      'FALLBACK'     => '0',
                      'ALLOWONERROR' => '0')
        );

        return array(
            array(
                $aAllPaymentSettings,
                1
            ),
            array(
                $aAllPaymentSettings2,
                0
            )
        );
    }

    /**
     * Test case if user gets response "Not Authorize" and
     * only one payment method should be shown as fallback method
     *
     * @dataProvider _fallbackPaymentDataProvider
     */
    public function testFallbackPaymentsAfterNotAuthorizedResponse($aAllPaymentSettings, $iExpectedPaymentCnt)
    {
        //preset payment data value
        $oOECreditPassAssessment = new oeCreditPassAssessment();
        $this->getSession()->setVar('dynvalue', array('testData1'));
        $oOECreditPassAssessment->checkPaymentDataChange();

        $this->_setupCreditPass();
        $this->setConfigParam('iOECreditPassCheckCacheTimeout', 0);
        // setting risky payment settings
        $this->_setupPaymentSettings($aAllPaymentSettings);
        $oBasket = $this->_setupBasket(1);
        $this->getSession()->setBasket($oBasket);
        $oUser = $this->_setupUser();

        //get payment methods shown in third basket step
        $this->setRequestParam('sShipSet', 'oxidstandard');
        $oPayment = new oeCreditPassPayment();
        $oPayment->setUser($oUser);
        $aPayments = $oPayment->getPaymentList();

        $this->assertEquals(5, count($aPayments));

        $this->setSessionParam('paymentid', 'oxidcashondel');
        $this->setSessionParam('usr', 'oxdefaultadmin');

        $oOrder = $this->getMock('oeCreditPassOrder', array('_redirect'));
        $oOrder->expects($this->once())->method('_redirect');
        $oOrder->init();
        $oPayment = new oeCreditPassPayment();
        $oPayment->setUser($this->_setupUser());
        $aPayments = $oPayment->getPaymentList();
        $this->assertEquals($iExpectedPaymentCnt, count($aPayments));
        if ($iExpectedPaymentCnt > 0) {
            $this->assertEquals('oxidinvoice', $aPayments['oxidinvoice']->getId());
        }
    }

    /**
     * Test case if user gets response "Not Authorize" and
     * only one payment method should be shown as fallback method
     */
    public function testFallbackPaymentsAfterNotAuthorizedResponseAndUserIsCached()
    {
        $this->_setupCreditPass();
        // setting risky payment settings
        $aAllPaymentSettings = array(
            'oxidcashondel'  =>
                array('ID'           => 'f3d6a7fe2782ba1ddef35475b32a1e99',
                      'PAYMENTID'    => 'oxidcashondel',
                      'ACTIVE'       => '1',
                      'PURCHASETYPE' => '1',
                      'ALLOWONERROR' => '0'),
            'oxidpayadvance' =>
                array('ID'           => 'f308d57ce25fb09dde57c9ca7833a43b',
                      'PAYMENTID'    => 'oxidpayadvance',
                      'ACTIVE'       => '1',
                      'PURCHASETYPE' => '1',
                      'ALLOWONERROR' => '0'),
            'oxidinvoice'    =>
                array('ID'           => 'efb7d9735b35af78a1febefb1dabc16b',
                      'PAYMENTID'    => 'oxidinvoice',
                      'ACTIVE'       => '0',
                      'FALLBACK'     => '1',
                      'ALLOWONERROR' => '0')
        );
        $this->_setupPaymentSettings($aAllPaymentSettings);
        $this->_setupUserCache();
        $this->setConfigParam('iOECreditPassCheckCacheTimeout', 30);

        $oBasket = $this->_setupBasket(1);
        $this->getSession()->setBasket($oBasket);
        $oUser = $this->_setupUser();

        //get payment methods shown in third basket step
        $this->setRequestParam('sShipSet', 'oxidstandard');
        $this->setSessionParam('usr', 'oxdefaultadmin');
        $oPayment = new oeCreditPassPayment();
        $oPayment->setUser($oUser);
        $aPayments = $oPayment->getPaymentList();

        $this->assertEquals(4, count($aPayments));
    }

    /**
     * Test case if user gets response "Not Authorize" and
     * only one payment method should be shown as fallback method
     */
    public function testFallbackPaymentsAfterAuthorizedResponse()
    {
        $oOECreditPassAssessment = new oeCreditPassAssessment();
        $this->getSession()->setVar('dynvalue', array('testData1'));
        $oOECreditPassAssessment->checkPaymentDataChange();

        $this->_setupCreditPass();
        // setting risky payment settings
        $aAllPaymentSettings = array(
            'oxidcashondel'  =>
                array('ID'           => 'f3d6a7fe2782ba1ddef35475b32a1e99',
                      'PAYMENTID'    => 'oxidcashondel',
                      'ACTIVE'       => '1',
                      'PURCHASETYPE' => '1',
                      'ALLOWONERROR' => '0'),
            'oxidpayadvance' =>
                array('ID'           => 'f308d57ce25fb09dde57c9ca7833a43b',
                      'PAYMENTID'    => 'oxidpayadvance',
                      'ACTIVE'       => '1',
                      'PURCHASETYPE' => '1',
                      'ALLOWONERROR' => '0'),
            'oxidinvoice'    =>
                array('ID'           => 'efb7d9735b35af78a1febefb1dabc16b',
                      'PAYMENTID'    => 'oxidinvoice',
                      'ACTIVE'       => '0',
                      'FALLBACK'     => '1',
                      'ALLOWONERROR' => '0')
        );

        $this->setConfigParam('iOECreditPassCheckCacheTimeout', 0);
        // setting risky payment settings
        $this->_setupPaymentSettings($aAllPaymentSettings);
        $oBasket = $this->_setupBasket(5);
        $this->getSession()->setBasket($oBasket);
        $oUser = $this->_setupUser();

        //get payment methods shown in third basket step
        $this->setRequestParam('sShipSet', 'oxidstandard');
        $oPayment = new oeCreditPassPayment();
        $oPayment->setUser($oUser);
        $aPayments = $oPayment->getPaymentList();

        $this->assertEquals(5, count($aPayments));

        $this->setSessionParam('paymentid', 'oxidcashondel');
        $this->setSessionParam('usr', 'oxdefaultadmin');

        $oOrder = new oeCreditPassOrder();
        $oOrder->init();
        $oPayment = new oeCreditPassPayment();
        $oPayment->setUser($this->_setupUser());
        $aPayments = $oPayment->getPaymentList();
        $this->assertEquals(5, count($aPayments));
    }

    /**
     * Testing special char handling in the request
     * Test case for ESDEV-2478
     */
    public function testFallbackPaymentsValidRequstSpecialchars()
    {

        $oOECreditPassAssessment = new oeCreditPassAssessment();
        $this->getSession()->setVar('dynvalue', array('testData1'));
        $oOECreditPassAssessment->checkPaymentDataChange();

        $this->_setupCreditPass();
        // setting risky payment settings
        $aAllPaymentSettings = array(
            'oxidcashondel'  =>
                array('ID'           => 'f3d6a7fe2782ba1ddef35475b32a1e99',
                      'PAYMENTID'    => 'oxidcashondel',
                      'ACTIVE'       => '1',
                      'PURCHASETYPE' => '1',
                      'ALLOWONERROR' => '1'),
            'oxidpayadvance' =>
                array('ID'           => 'f308d57ce25fb09dde57c9ca7833a43b',
                      'PAYMENTID'    => 'oxidpayadvance',
                      'ACTIVE'       => '1',
                      'PURCHASETYPE' => '1',
                      'ALLOWONERROR' => '1'),
            'oxidinvoice'    =>
                array('ID'           => 'efb7d9735b35af78a1febefb1dabc16b',
                      'PAYMENTID'    => 'oxidinvoice',
                      'ACTIVE'       => '0',
                      'FALLBACK'     => '1',
                      'ALLOWONERROR' => '1')
        );

        $this->setConfigParam('iOECreditPassCheckCacheTimeout', 0);
        // setting risky payment settings
        $this->_setupPaymentSettings($aAllPaymentSettings);
        $oBasket = $this->_setupBasket(5);
        $this->getSession()->setBasket($oBasket);
        $oUser = $this->_setupUser();

        //get payment methods shown in third basket step
        $this->setRequestParam('sShipSet', 'oxidstandard');
        $oPayment = new oeCreditPassPayment();

        $oPayment->setUser($oUser);
        $aPayments = $oPayment->getPaymentList();

        $this->assertEquals(5, count($aPayments));

        $this->setSessionParam('paymentid', 'oxidcashondel');


        //creating user with special chars
        $oUsr = oxNew("oxUser");
        $oUsr->load('oxdefaultadmin');
        $oUsr->oxuser__oxfname = new oxField("test&User");
        $oUsr->save();

        $this->setSessionParam('usr', 'oxdefaultadmin');

        $oOrder = new oeCreditPassOrder();
        $oOrder->init();
        $oPayment = new oeCreditPassPayment();
        $oPayment->setUser($this->_setupUser());
        $aPayments = $oPayment->getPaymentList();
        $this->assertEquals(5, count($aPayments));
    }

}