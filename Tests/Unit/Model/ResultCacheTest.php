<?php

namespace OxidProfessionalServices\CreditPassModule\Tests\Unit\Model;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\CreditPassModule\Model\DbGateways\ResponseCacheDbGateway;
use OxidProfessionalServices\CreditPassModule\Model\ResultCache;

class ResultCacheTest extends UnitTestCase
{

    public function testGetData()
    {
        $sXML = "this is test xml";
        $sUser = "testUser";
        $sAddress = "testAddress";
        $sPayment = "testPayment";
        $sPayData = "testPaymentData";

        $oDB = $this->getMock(
            ResponseCacheDbGateway::class, array('load', 'setAddressId', 'setPaymentId'), array(), '', false
        );
        $oDB->expects($this->once())->method('load')->will($this->returnValue($sXML));

        $oCreditPassResultCache = $this->getMock(
            ResultCache::class, array('_getDbGateway', '_deleteExpCache')
        );
        $oCreditPassResultCache->expects($this->once())->method('_getDbGateway')->will(
            $this->returnValue($oDB)
        );
        $oCreditPassResultCache->expects($this->once())->method('_deleteExpCache');
        $oCreditPassResultCache->setUserId($sUser);
        $oCreditPassResultCache->setPaymentId($sAddress);
        $oCreditPassResultCache->setAddressIdentification($sPayment);
        $oCreditPassResultCache->setPaymentDataHash($sPayData);
        $sResult = $oCreditPassResultCache->getData();

        $this->assertSame($sXML, $sResult);
    }

    public function testGetDataHasNoCache()
    {
        $oDB = $this->getMock(
            ResponseCacheDbGateway::class, array('load', 'setAddressId', 'setPaymentId'), array(), '', false
        );
        $oDB->expects($this->once())->method('load')->will($this->returnValue(false));

        $oCreditPassResultCache = $this->getMock(
            ResultCache::class, array('_getDbGateway', '_deleteExpCache')
        );
        $oCreditPassResultCache->expects($this->once())->method('_getDbGateway')->will(
            $this->returnValue($oDB)
        );
        $oCreditPassResultCache->expects($this->once())->method('_deleteExpCache');
        $sResult = $oCreditPassResultCache->getData();

        $this->assertFalse($sResult);
    }

    public function testGetDataRemoveOldData()
    {
        $this->setConfigParam('iOECreditPassCheckCacheTimeout', 1);
        $oDB = $this->getMock(
            ResponseCacheDbGateway::class, array('load', 'delete', 'setAddressId', 'setPaymentId'), array(), '',
            false
        );
        $oDB->expects($this->once())->method('load')->will($this->returnValue(false));
        $oDB->expects($this->once())->method('delete')->with('1970-01-01 01:00:05'); //86405 - 1*86400

        $oCreditPassResultCache = $this->getMock(ResultCache::class, array('_getDbGateway', '_getTime'));
        $oCreditPassResultCache->expects($this->exactly(2))->method('_getDbGateway')->will(
            $this->returnValue($oDB)
        );
        $oCreditPassResultCache->expects($this->any())->method('_getTime')->will($this->returnValue(86405));
        $sResult = $oCreditPassResultCache->getData();

        $this->assertFalse($sResult);
    }

    public function testGetCheckCacheTimeout()
    {
        $this->setConfigParam('iOECreditPassCheckCacheTimeout', 10);
        $oCreditPassResultCache = new ResultCache();
        $sResult = $oCreditPassResultCache->getCheckCacheTimeout();

        $this->assertSame(864000, $sResult); //10*86400
    }

    public function testStoreData()
    {
        $sXML = "this is test xml";
        $sUser = "testUser";
        $sAddress = "testAddress";
        $sPayment = "testPayment";
        $sPayData = "testPaymentData";
        $iAnswerCode = "testPaymentData";
        $sNowDate = 123456;

        $aData = array(
            'USERID'           => $sUser,
            'ASSESSMENTRESULT' => $sXML,
            'TIMESTAMP'        => date('Y-m-d H:i:s', $sNowDate),
            'USERIDENT'        => $sAddress,
            'PAYMENTID'        => $sPayment,
            'PAYMENTDATA'      => $sPayData,
            'ANSWERCODE'       => $iAnswerCode,
        );

        $oDB = $this->getMock(ResponseCacheDbGateway::class, array('save'));
        $oDB->expects($this->once())->method('save')->with($aData)->will($this->returnValue(true));

        $oCreditPassResultCache = $this->getMock(ResultCache::class, array('_getDbGateway', '_getTime'));
        $oCreditPassResultCache->expects($this->once())->method('_getDbGateway')->will(
            $this->returnValue($oDB)
        );
        $oCreditPassResultCache->expects($this->once())->method('_getTime')->will(
            $this->returnValue($sNowDate)
        );
        $oCreditPassResultCache->setUserId($sUser);
        $oCreditPassResultCache->setPaymentId($sPayment);
        $oCreditPassResultCache->setAddressIdentification($sAddress);
        $oCreditPassResultCache->setResponse($sXML);
        $oCreditPassResultCache->setAnswerCode($iAnswerCode);
        $oCreditPassResultCache->setPaymentDataHash($sPayData);
        $oCreditPassResultCache->storeData();
    }

    public function testGetRejectedPaymentIds()
    {
        $sUser = "testUser";
        $sAddress = "testAddress";
        $aIds = array("paymentid" => "testPaymentData");

        $oDB = $this->getMock(ResponseCacheDbGateway::class, array('loadPaymentIdsByAnswer', 'setAddressId'));
        $oDB->expects($this->once())->method('loadPaymentIdsByAnswer')->will($this->returnValue(array($aIds)));
        $oDB->expects($this->once())->method('setAddressId');

        $oCreditPassResultCache = $this->getMock(ResultCache::class, array('_getDbGateway'));
        $oCreditPassResultCache->expects($this->any())->method('_getDbGateway')->will(
            $this->returnValue($oDB)
        );
        $oCreditPassResultCache->setUserId($sUser);
        $oCreditPassResultCache->setAddressIdentification($sAddress);
        $aResult = $oCreditPassResultCache->getRejectedPaymentIds();

        $this->assertEquals(array('testPaymentData'), $aResult);
    }

    public function testGetRejectedPaymentIdsNoCache()
    {
        $sUser = "testUser";
        $sAddress = "testAddress";

        $oDB = $this->getMock(ResponseCacheDbGateway::class, array('loadPaymentIdsByAnswer', 'setAddressId'));
        $oDB->expects($this->once())->method('loadPaymentIdsByAnswer')->will($this->returnValue(false));
        $oDB->expects($this->once())->method('setAddressId');

        $oCreditPassResultCache = $this->getMock(ResultCache::class, array('_getDbGateway'));
        $oCreditPassResultCache->expects($this->any())->method('_getDbGateway')->will(
            $this->returnValue($oDB)
        );
        $oCreditPassResultCache->setUserId($sUser);
        $oCreditPassResultCache->setAddressIdentification($sAddress);
        $aResult = $oCreditPassResultCache->getRejectedPaymentIds();

        $this->assertFalse($aResult);
    }

}