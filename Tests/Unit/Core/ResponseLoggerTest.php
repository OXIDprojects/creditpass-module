<?php

namespace OxidProfessionalServices\CreditPassModule\Tests\Unit\Core;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassResponseLogger;
use OxidProfessionalServices\CreditPassModule\Model\DbGateways\CreditPassResponseLoggerDbGateway;

require_once __DIR__ . '/../OxidTestDatabase.php';

/**
 * Class TestResponseLoggerDbGateway for testing purposes
 */
class TestResponseLoggerDbGateway extends CreditPassResponseLoggerDbGateway
{

    // reusing implementation
}

/**
 * Class ResponseLoggerTest - Data Logger test cases
 *
 * @phpcs:ignoreFile
 */
class ResponseLoggerTest extends UnitTestCase
{

    /**
     * Tests get default logger.
     */
    public function testGetLoggerDefault()
    {
        $oLogger = new CreditPassResponseLogger();

        $this->isInstanceOf(CreditPassResponseLoggerDbGateway::class, $oLogger->getLogger());
    }

    /**
     * Tests set/get logger.
     */
    public function testSetGetLogger()
    {
        $oLogger = new CreditPassResponseLogger();

        $oDatabaseLogger = new TestResponseLoggerDbGateway();
        $oLogger->setLogger($oDatabaseLogger);
        $this->assertSame($oDatabaseLogger, $oLogger->getLogger());
    }

    /**
     * Data provider for testSave.
     *
     * @return array
     */
    public function _dpTestSave()
    {
        return array(
            array(
                array('field1' => 'value1', 'field2' => 'value2'),
                true, // assume save is successful
                'test_last_id',
            ),
            array(
                array('field1' => 'value1', 'field2' => 'value2'),
                false, // assume save is failed
                null,
            ),
        );
    }

    /**
     * Tests saving data and retrieving last ID.
     *
     * @dataProvider _dpTestSave
     *
     * @param $aSaveData
     * @param $blSaveReturns
     * @param $sLasInsertedIDReturns
     */
    public function testSave($aSaveData, $blSaveReturns, $sLasInsertedIDReturns)
    {
        $oDatabaseLogger = $this->getMock(TestResponseLoggerDbGateway::class, array('save', 'getLastInsertedID'));

        $oDatabaseLogger->expects($this->once())->method('save')->with($this->equalTo($aSaveData))->will(
            $this->returnValue($blSaveReturns)
        );
        $oDatabaseLogger->expects($this->once())->method('getLastInsertedID')->will(
            $this->returnValue($sLasInsertedIDReturns)
        );

        $oLogger = new CreditPassResponseLogger();
        $oLogger->setLogger($oDatabaseLogger);

        $this->assertSame($blSaveReturns, $oLogger->save($aSaveData));
        $this->assertSame($sLasInsertedIDReturns, $oLogger->getLastID());
    }

    /**
     * Tests updating data.
     */
    public function testUpdate()
    {
        $oDatabaseLogger = $this->getMock(TestResponseLoggerDbGateway::class, array('update'));
        // assume updating data is successful
        $oDatabaseLogger->expects($this->at(0))->method('update')->with($this->equalTo('test_id_ok'))->will(
            $this->returnValue(true)
        );
        // assume updating data is failed
        $oDatabaseLogger->expects($this->at(1))->method('update')->with($this->equalTo('test_id_failed'))
            ->will($this->returnValue(false));

        $oLogger = new CreditPassResponseLogger();

        $oLogger->setLogger($oDatabaseLogger);

        $this->assertTrue($oLogger->update('test_id_ok', array('test_data_ok')));
        $this->assertFalse($oLogger->update('test_id_failed', array('test_data_failed')));
    }

    /**
     * Tests loading data.
     */
    public function testLoad()
    {
        $sID = 'test_id';
        $aData = array('test_data');

        $oDatabaseLogger = $this->getMock(TestResponseLoggerDbGateway::class, array('load'));
        $oDatabaseLogger->expects($this->once())->method('load')->with($this->equalTo($sID))->will(
            $this->returnValue($aData)
        );

        $oLogger = new CreditPassResponseLogger();

        $oLogger->setLogger($oDatabaseLogger);

        $this->assertSame($aData, $oLogger->load($sID));
    }

    /**
     * Tests if data exists.
     */
    public function testExists()
    {
        $this->markTestIncomplete('Method changed to protected.');

        $oDatabaseLogger = $this->getMock(TestResponseLoggerDbGateway::class, array('exists'));
        // assume data exists
        $oDatabaseLogger->expects($this->at(0))->method('exists')->with($this->equalTo('test_id_exists'))
            ->will($this->returnValue(true));
        // assume data does not exist
        $oDatabaseLogger->expects($this->at(1))->method('exists')->with(
            $this->equalTo('test_id_does_not_exist')
        )->will($this->returnValue(false));

        $oLogger = new CreditPassResponseLogger();

        $oLogger->setLogger($oDatabaseLogger);

        $this->assertTrue($oLogger->exists('test_id_exists'));
        $this->assertFalse($oLogger->exists('test_id_does_not_exist'));
    }

    /**
     * Tests if data is valid.
     */
    public function testIsValid()
    {
        $this->markTestIncomplete('Method changed to protected.');

        $oDatabaseLogger = $this->getMock(TestResponseLoggerDbGateway::class, array('isValid'));
        // assume data is valid
        $oDatabaseLogger->expects($this->at(0))->method('isValid')->with($this->equalTo('test_id_valid'))
            ->will($this->returnValue(true));
        // assume data is not valid
        $oDatabaseLogger->expects($this->at(1))->method('isValid')->with($this->equalTo('test_id_not_valid'))
            ->will($this->returnValue(false));

        $oLogger = new CreditPassResponseLogger();

        $oLogger->setLogger($oDatabaseLogger);

        $this->assertTrue($oLogger->isValid('test_id_valid', array('test_data_valid')));
        $this->assertFalse($oLogger->isValid('test_id_not_valid', array('test_data_not_valid')));
    }

    /**
     * Test loading log list.
     */
    public function testLoadAll()
    {
        $oLogger = new CreditPassResponseLogger();

        $aLogList = array(
            array('test_field_1' => 'test_value_1'),
            array('test_field_2' => 'test_value_2'),
        );

        $oDataBaseLogger = $this->getMock(TestResponseLoggerDbGateway::class, array('loadAll'));
        $oDataBaseLogger->expects($this->any())->method('loadAll')->will($this->returnValue($aLogList));

        $oLogger->setLogger($oDataBaseLogger);

        $this->assertEquals($aLogList, $oLogger->loadAll());
    }

    /**
     * Tests searching log data for an order.
     */
    public function testSearchOrder()
    {
        $sSearchOrderFieldName = 'order_id_field';
        $sOrderID = 'order_id_value';
        $sOrderLogData = array('order_data');
        $aSearchData = array($sOrderLogData);
        $aSearchQuery = array($sSearchOrderFieldName => $sOrderID);

        $oDatabaseLogger = $this->getMock(TestResponseLoggerDbGateway::class, array('search'));
        $oDatabaseLogger->expects($this->once())->method('search')->with($aSearchQuery)->will(
            $this->returnValue($aSearchData)
        );

        $oLogger = $this->getMock(CreditPassResponseLogger::class, array('_getSearchOrderFieldName'));
        $oLogger->expects($this->once())->method('_getSearchOrderFieldName')->will(
            $this->returnValue($sSearchOrderFieldName)
        );

        $oLogger->setLogger($oDatabaseLogger);

        $this->assertEquals($sOrderLogData, $oLogger->searchOrder($sOrderID));
    }

    /**
     * Tests searching log ist for the user.
     */
    public function testSearchUser()
    {
        $sSearchUserFieldName = 'user_id_field';
        $sUserID = 'user_id_value';
        $aSearchQuery = array($sSearchUserFieldName => $sUserID);
        $aResult = array('order_data');

        $oDatabaseLogger = $this->getMock(TestResponseLoggerDbGateway::class, array('search'));
        $oDatabaseLogger->expects($this->once())->method('search')->with($aSearchQuery)->will(
            $this->returnValue($aResult)
        );

        $oLogger = $this->getMock(CreditPassResponseLogger::class, array('_getSearchUserFieldName'));
        $oLogger->expects($this->once())->method('_getSearchUserFieldName')->will(
            $this->returnValue($sSearchUserFieldName)
        );

        $oLogger->setLogger($oDatabaseLogger);

        $this->assertEquals($aResult, $oLogger->searchUser($sUserID));
    }
}
