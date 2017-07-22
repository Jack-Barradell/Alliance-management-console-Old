<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/ErrorLog.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\ErrorLog;
use AMC\Classes\Database;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class ErrorLogTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('jirbj.co.uk', 'testingDB', 'testingDB', 'testingdb');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Create a null object for testing
        $errorLog = new ErrorLog();

        $this->assertTrue($errorLog->eql(new ErrorLog()));
        $this->assertNull($errorLog->getID());
        $this->assertNull($errorLog->getType());
        $this->assertNull($errorLog->getMessage());
        $this->assertNull($errorLog->getSystemError());
        $this->assertNull($errorLog->getTimestamp());

        // Create a non null object
        $errorLog = new ErrorLog(1, 'test', 'message', 'error', 123);

        $this->assertFalse($errorLog->eql(new ErrorLog()));
        $this->assertEquals(1, $errorLog->getID());
        $this->assertEquals('test', $errorLog->getType());
        $this->assertEquals('message', $errorLog->getMessage());
        $this->assertEquals('error', $errorLog->getSystemError());
        $this->assertEquals(123, $errorLog->getTimestamp());
    }

    public function testCreate() {
        // Create a test error log
        $testErrorLog = new ErrorLog();
        $testErrorLog->setType('type');
        $testErrorLog->setMessage('message');
        $testErrorLog->setSystemError('error');
        $testErrorLog->setTimestamp(123);
        $testErrorLog->create();

        // Check id is a number
        $this->assertInternalType('int', $testErrorLog->getID());

        // Now pull it from the db
        $stmt = $this->_connection->prepare("SELECT `ErrorLogID`,`ErrorLogType`,`ErrorLogMessage`,`ErrorLogSystemError`,`ErrorLogTimestamp` FROM `Error_Log` WHERE `ErrorLogID`=?");
        $stmt->bind_param('i', $testErrorLog->getID());
        $stmt->execute();
        $stmt->bind_result($errorLogID, $type, $message, $sysError, $timestamp);

        // Check theres result
        $this->assertEquals(1, $stmt->num_rows);
        $stmt->fetch();

        $this->assertEquals($testErrorLog->getID(), $errorLogID);
        $this->assertEquals($testErrorLog->getType(), $type);
        $this->assertEquals($testErrorLog->getMessage(), $message);
        $this->assertEquals($testErrorLog->getSystemError(), $sysError);
        $this->assertEquals($testErrorLog->getTimestamp(), $timestamp);

        $stmt->close();

        // Clean up
        $testErrorLog->delete();
    }

    public function testBlankCreate() {
        // Create it
        $errorLog = new ErrorLog();

        // Expect exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        $errorLog->create();
    }

    public function testUpdate() {
        // Create a test error log
        $testErrorLog = new ErrorLog();
        $testErrorLog->setType('type');
        $testErrorLog->setMessage('message');
        $testErrorLog->setSystemError('error');
        $testErrorLog->setTimestamp(123);
        $testErrorLog->create();

        // Now update it
        $testErrorLog->setType('type2');
        $testErrorLog->setMessage('message2');
        $testErrorLog->setSystemError('error2');
        $testErrorLog->setTimestamp(1234);
        $testErrorLog->update();

        // Now pull it from the db
        $stmt = $this->_connection->prepare("SELECT `ErrorLogID`,`ErrorLogType`,`ErrorLogMessage`,`ErrorLogSystemError`,`ErrorLogTimestamp` FROM `Error_Log` WHERE `ErrorLogID`=?");
        $stmt->bind_param('i', $testErrorLog->getID());
        $stmt->execute();
        $stmt->bind_result($errorLogID, $type, $message, $sysError, $timestamp);

        // Check theres result
        $this->assertEquals(1, $stmt->num_rows);
        $stmt->fetch();

        $this->assertEquals($testErrorLog->getID(), $errorLogID);
        $this->assertEquals($testErrorLog->getType(), $type);
        $this->assertEquals($testErrorLog->getMessage(), $message);
        $this->assertEquals($testErrorLog->getSystemError(), $sysError);
        $this->assertEquals($testErrorLog->getTimestamp(), $timestamp);

        $stmt->close();

        // Clean up
        $testErrorLog->delete();
    }

    public function testBlankUpdate() {
        // Create it
        $errorLog = new ErrorLog();

        // Expect exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        $errorLog->update();
    }

    public function testDelete() {
        // Create a test error log
        $testErrorLog = new ErrorLog();
        $testErrorLog->setType('type');
        $testErrorLog->setMessage('message');
        $testErrorLog->setSystemError('error');
        $testErrorLog->setTimestamp(123);
        $testErrorLog->create();

        // Store id for when its gone
        $id = $testErrorLog->getID();

        // Now delete it
        $testErrorLog->delete();

        $stmt = $this->_connection->prepare("SELECT `ErrorLogID`,`ErrorLogType`,`ErrorLogMessage`,`ErrorLogSystemError`,`ErrorLogTimestamp` FROM `Error_Log` WHERE `ErrorLogID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Now there are no results
        $this->assertEquals(0, $stmt->num_rows);
        $stmt->close();
    }

    public function testSelectWithInput() {
        // Create a test error log
        $testErrorLog = [];

        $testErrorLog[0] = new ErrorLog();
        $testErrorLog[0]->setType('type');
        $testErrorLog[0]->setMessage('message');
        $testErrorLog[0]->setSystemError('error');
        $testErrorLog[0]->setTimestamp(123);
        $testErrorLog[0]->create();

        $testErrorLog[1] = new ErrorLog();
        $testErrorLog[1]->setType('type2');
        $testErrorLog[1]->setMessage('message2');
        $testErrorLog[1]->setSystemError('error2');
        $testErrorLog[1]->setTimestamp(1234);
        $testErrorLog[1]->create();

        $testErrorLog[2] = new ErrorLog();
        $testErrorLog[2]->setType('type3');
        $testErrorLog[2]->setMessage('message3');
        $testErrorLog[2]->setSystemError('error3');
        $testErrorLog[2]->setTimestamp(12345);
        $testErrorLog[2]->create();

        // Get a single
        $selectedSingle = ErrorLog::get($testErrorLog[0]->getID());

        $this->assertInstanceOf(ErrorLog::class, $selectedSingle);
        $this->assertEquals($testErrorLog[0]->getID(), $selectedSingle->getID());
        $this->assertEquals($testErrorLog[0]->getType(), $selectedSingle->getType());
        $this->assertEquals($testErrorLog[0]->getMessage(), $selectedSingle->getMessage());
        $this->assertEquals($testErrorLog[0]->getSystemError(), $selectedSingle->getSystemError());
        $this->assertEquals($testErrorLog[0]->getTimestamp(), $selectedSingle->getTimestamp());

        // Now do a multi check
        $selectedMultiple = ErrorLog::get(array($testErrorLog[1]->getID(), $testErrorLog[2]->getID()));

        // Check it is an array with 2 results
        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(ErrorLog::class, $selectedMultiple[0]);
        $this->assertInstanceOf(ErrorLog::class, $selectedMultiple[1]);

        if($testErrorLog[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testErrorLog[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testErrorLog[0]->getType(), $selectedMultiple[$i]->getType());
        $this->assertEquals($testErrorLog[0]->getMessage(), $selectedMultiple[$i]->getMessage());
        $this->assertEquals($testErrorLog[0]->getSystemError(), $selectedMultiple[$i]->getSystemError());
        $this->assertEquals($testErrorLog[0]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());

        $this->assertEquals($testErrorLog[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testErrorLog[1]->getType(), $selectedMultiple[$j]->getType());
        $this->assertEquals($testErrorLog[1]->getMessage(), $selectedMultiple[$j]->getMessage());
        $this->assertEquals($testErrorLog[1]->getSystemError(), $selectedMultiple[$j]->getSystemError());
        $this->assertEquals($testErrorLog[1]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());

        // Clean up
        foreach($testErrorLog as $error) {
            $error->delete();
        }
    }

    public function testSelectAll() {
        // Create a test error log
        $testErrorLog = [];

        $testErrorLog[0] = new ErrorLog();
        $testErrorLog[0]->setType('type');
        $testErrorLog[0]->setMessage('message');
        $testErrorLog[0]->setSystemError('error');
        $testErrorLog[0]->setTimestamp(123);
        $testErrorLog[0]->create();

        $testErrorLog[1] = new ErrorLog();
        $testErrorLog[1]->setType('type2');
        $testErrorLog[1]->setMessage('message2');
        $testErrorLog[1]->setSystemError('error2');
        $testErrorLog[1]->setTimestamp(1234);
        $testErrorLog[1]->create();

        // Now do a multi check
        $selectedMultiple = ErrorLog::get();

        // Check it is an array with 2 results
        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(ErrorLog::class, $selectedMultiple[0]);
        $this->assertInstanceOf(ErrorLog::class, $selectedMultiple[1]);

        if($testErrorLog[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testErrorLog[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testErrorLog[0]->getType(), $selectedMultiple[$i]->getType());
        $this->assertEquals($testErrorLog[0]->getMessage(), $selectedMultiple[$i]->getMessage());
        $this->assertEquals($testErrorLog[0]->getSystemError(), $selectedMultiple[$i]->getSystemError());
        $this->assertEquals($testErrorLog[0]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());

        $this->assertEquals($testErrorLog[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testErrorLog[1]->getType(), $selectedMultiple[$j]->getType());
        $this->assertEquals($testErrorLog[1]->getMessage(), $selectedMultiple[$j]->getMessage());
        $this->assertEquals($testErrorLog[1]->getSystemError(), $selectedMultiple[$j]->getSystemError());
        $this->assertEquals($testErrorLog[1]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());

        // Clean up
        foreach($testErrorLog as $error) {
            $error->delete();
        }
    }

    public function testEql() {
        // Create a test error log
        $testErrorLog = [];

        $testErrorLog[0] = new ErrorLog();
        $testErrorLog[0]->setType('type');
        $testErrorLog[0]->setMessage('message');
        $testErrorLog[0]->setSystemError('error');
        $testErrorLog[0]->setTimestamp(123);

        $testErrorLog[1] = new ErrorLog();
        $testErrorLog[1]->setType('type');
        $testErrorLog[1]->setMessage('message');
        $testErrorLog[1]->setSystemError('error');
        $testErrorLog[1]->setTimestamp(123);

        $testErrorLog[2] = new ErrorLog();
        $testErrorLog[2]->setType('type2');
        $testErrorLog[2]->setMessage('message2');
        $testErrorLog[2]->setSystemError('error2');
        $testErrorLog[2]->setTimestamp(1234);

        // Check same object is eql
        $this->assertTrue($testErrorLog[0]->eql($testErrorLog[0]));

        // Check same details are eql
        $this->assertTrue($testErrorLog[0]->eql($testErrorLog[1]));

        // Check different arent equal
        $this->assertFalse($testErrorLog[0]->eql($testErrorLog[2]));
    }

}
