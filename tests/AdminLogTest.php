<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/User.php';
require '../classes/AdminLog.php';
require '../classes/exceptions/BlankObjectException.php';
require '../classes/exceptions/IncorrectTypeException.php';

use AMC\Classes\AdminLog;
use AMC\Classes\Database;
use AMC\Classes\User;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class AdminLogTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('localhost', 'testingDB', 'testingDB', 'testingdb');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        $adminLog = new AdminLog();

        // Check the object initialised to have null vars
        self::assertTrue($adminLog->eql(new AdminLog()));
        self::assertNull($adminLog->getID());
        self::assertNull($adminLog->getAdminID());
        self::assertNull($adminLog->getEvent());
        self::assertNull($adminLog->getTimestamp());

        $adminLog = new AdminLog(1, 1, 'testEvent', 123);

        // Check the object vars are correct
        self::assertFalse($adminLog->eql(new AdminLog()));
        self::assertEquals(1, $adminLog->getID());
        self::assertEquals(1, $adminLog->getAdminID());
        self::assertEquals('testEvent', $adminLog->getEvent());
        self::assertEquals(123, $adminLog->getTimestamp());
    }

    public function testCreate() {
        $testAdminLog = new AdminLog();

        // Create a user to use as the admin
        $testAdmin = new User();
        $testAdmin->setUsername('testAdmin');
        $testAdmin->create();

        // Create an event by the admin
        $testAdminLog->setAdminID($testAdmin->getID());
        $testAdminLog->setEvent('event test');
        $testAdminLog->setTimestamp(123);
        $testAdminLog->create();

        // Check the id becomes numeric
        self::assertInternalType('int', $testAdminLog->getID());

        // Pull it from the db
        $stmt = $this->_connection->prepare("SELECT `AdminLogID`,`AdminID`,`AdminLogEvent`,`AdminLogTimestamp` FROM `Admin_Logs` WHERE `AdminLogID`=?");
        $stmt->bind_param('i', $testAdminLog->getID());
        $stmt->execute();
        $stmt->bind_result($adminLogID, $adminID, $adminLogEvent, $adminLogTimestamp);

        // Check only one result
        self::assertEquals(1, $stmt->num_rows);

        $stmt->fetch();
        $stmt->close();

        // Check the vars match the object
        self::assertEquals($testAdminLog->getID(), $adminLogID);
        self::assertEquals($testAdminLog->getAdminID(), $adminID);
        self::assertEquals($testAdminLog->getEvent(), $adminLogEvent);
        self::assertEquals($testAdminLog->getTimestamp(), $adminLogTimestamp);

        $testAdminLog->delete();
        $testAdmin->delete();
        return $testAdminLog;
    }

    public function testBlankCreate() {

        // Create admin log
        $adminLog  = new AdminLog();

        // Set the expected exception
        self::expectException(BlankObjectException::class);

        // Trigger exception
        $adminLog->create();
    }


    public function testUpdate() {
        $testAdminLog = new AdminLog();

        // Create a user to use as the admin
        $testAdmin = new User();
        $testAdmin->setUsername('testAdmin');
        $testAdmin->create();

        // Create an event by the admin
        $testAdminLog->setAdminID($testAdmin->getID());
        $testAdminLog->setEvent('event test');
        $testAdminLog->setTimestamp(123);
        $testAdminLog->create();

        // Create a user to use as the admin
        $testAdmin2 = new User();
        $testAdmin2->setUsername('testAdmin2');
        $testAdmin2->create();

        // Update the admin log
        $testAdminLog->setAdminID($testAdmin2->getID());
        $testAdminLog->setEvent('event 2');
        $testAdminLog->setTimestamp(321);
        $testAdminLog->update();

        // Pull it from the db
        $stmt = $this->_connection->prepare("SELECT `AdminLogID`,`AdminID`,`AdminLogEvent`,`AdminLogTimestamp` FROM `Admin_Logs` WHERE `AdminLogID`=?");
        $stmt->bind_param('i', $testAdminLog->getID());
        $stmt->execute();
        $stmt->bind_result($adminLogID, $adminID, $adminLogEvent, $adminLogTimestamp);

        // Check only one result
        self::assertEquals(1, $stmt->num_rows);

        $stmt->fetch();
        $stmt->close();

        // Check the vars match the object
        self::assertEquals($testAdminLog->getID(), $adminLogID);
        self::assertEquals($testAdminLog->getAdminID(), $adminID);
        self::assertEquals($testAdminLog->getEvent(), $adminLogEvent);
        self::assertEquals($testAdminLog->getTimestamp(), $adminLogTimestamp);

        $testAdminLog->delete();
        $testAdmin->delete();
        $testAdmin2->delete();
    }

    public function testBlankUpdate() {
        // Create admin log
        $adminLog  = new AdminLog();

        // Set the expected exception
        self::expectException(BlankObjectException::class);

        // Trigger exception
        $adminLog->update();
    }

    public function testDelete() {
        $testAdminLog = new AdminLog();

        // Create a user to use as the admin
        $testAdmin = new User();
        $testAdmin->setUsername('testAdmin');
        $testAdmin->create();

        // Create an event by the admin
        $testAdminLog->setAdminID($testAdmin->getID());
        $testAdminLog->setEvent('event test');
        $testAdminLog->setTimestamp(123);
        $testAdminLog->create();

        // Grab the ID before delete removes it
        $id = $testAdminLog->getID();

        // Call delete
        $testAdminLog->delete();

        $stmt = $this->_connection->prepare("SELECT `AdminLogID`,`AdminID`,`AdminLogEvent`,`AdminLogTimestamp` FROM `Admin_Logs` WHERE `AdminLogID`=?");
        $stmt->bind_param('i', $testAdminLog->getID());
        $stmt->execute();
        $stmt->bind_result($adminLogID, $adminID, $adminLogEvent, $adminLogTimestamp);

        // Check only one result
        self::assertEquals(0, $stmt->num_rows);

        $stmt->close();
        $testAdmin->delete();
    }

    public function testSelectWithInput() {
        // Create a test user to use as admin
        $testAdmin = new User();
        $testAdmin->setUsername('testAdmin2');
        $testAdmin->create();

        // Create an array of admin logs
        $testAdminLog = [];

        $testAdminLog[0] = new AdminLog();
        $testAdminLog[0]->setAdminID($testAdmin->getID());
        $testAdminLog[0]->setEvent('event test');
        $testAdminLog[0]->setTimestamp(123);
        $testAdminLog[0]->create();

        $testAdminLog[1] = new AdminLog();
        $testAdminLog[1]->setAdminID($testAdmin->getID());
        $testAdminLog[1]->setEvent('event test2');
        $testAdminLog[1]->setTimestamp(1234);
        $testAdminLog[1]->create();

        $testAdminLog[2] = new AdminLog();
        $testAdminLog[2]->setAdminID($testAdmin->getID());
        $testAdminLog[2]->setEvent('event test3');
        $testAdminLog[2]->setTimestamp(12345);
        $testAdminLog[2]->create();

        $selectedSingle = AdminLog::get($testAdminLog[0]->getID());

        self::assertInstanceOf(AdminLog::class, $selectedSingle);
        self::assertEquals($testAdminLog[0]->getID(), $selectedSingle->getID());
        self::assertEquals($testAdminLog[0]->getAdminID(), $selectedSingle->getAdminID());
        self::assertEquals($testAdminLog[0]->getEvent(), $selectedSingle->getEvent());
        self::assertEquals($testAdminLog[0]->getTimestamp(), $selectedSingle->getTimestamp());

        $selectedMultiple = AdminLog::get(array($testAdminLog[0]->getID(), $testAdminLog[2]->getID()));

        self::assertTrue(\is_array($selectedMultiple));
        self::assertEquals(2, \count($selectedMultiple));

        if($testAdminLog[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        self::assertInstanceOf(AdminLog::class, $selectedSingle[$i]);
        self::assertEquals($testAdminLog[0]->getID(), $selectedSingle[$i]->getID());
        self::assertEquals($testAdminLog[0]->getAdminID(), $selectedSingle[$i]->getAdminID());
        self::assertEquals($testAdminLog[0]->getEvent(), $selectedSingle[$i]->getEvent());
        self::assertEquals($testAdminLog[0]->getTimestamp(), $selectedSingle[$i]->getTimestamp());

        self::assertInstanceOf(AdminLog::class, $selectedSingle[$j]);
        self::assertEquals($testAdminLog[1]->getID(), $selectedSingle[$j]->getID());
        self::assertEquals($testAdminLog[1]->getAdminID(), $selectedSingle[$j]->getAdminID());
        self::assertEquals($testAdminLog[1]->getEvent(), $selectedSingle[$j]->getEvent());
        self::assertEquals($testAdminLog[1]->getTimestamp(), $selectedSingle[$j]->getTimestamp());

        // Clean it up
        $testAdmin->delete();
        foreach($testAdminLog as $test) {
            $test->delete();
        }
    }

    public function testSelectAll() {
        // Create a test user to use as admin
        $testAdmin = new User();
        $testAdmin->setUsername('testAdmin2');
        $testAdmin->create();

        // Create an array of admin logs
        $testAdminLog = [];

        $testAdminLog[0] = new AdminLog();
        $testAdminLog[0]->setAdminID($testAdmin->getID());
        $testAdminLog[0]->setEvent('event test');
        $testAdminLog[0]->setTimestamp(123);
        $testAdminLog[0]->create();

        $testAdminLog[1] = new AdminLog();
        $testAdminLog[1]->setAdminID($testAdmin->getID());
        $testAdminLog[1]->setEvent('event test2');
        $testAdminLog[1]->setTimestamp(1234);
        $testAdminLog[1]->create();

        $testAdminLog[2] = new AdminLog();
        $testAdminLog[2]->setAdminID($testAdmin->getID());
        $testAdminLog[2]->setEvent('event test3');
        $testAdminLog[2]->setTimestamp(12345);
        $testAdminLog[2]->create();

        $selectedAll = AdminLog::get();

        self::assertInstanceOf(AdminLog::class, $selectedAll[0]);
        self::assertEquals($testAdminLog[0]->getID(), $selectedAll[0]->getID());
        self::assertEquals($testAdminLog[0]->getAdminID(), $selectedAll[0]->getAdminID());
        self::assertEquals($testAdminLog[0]->getEvent(), $selectedAll[0]->getEvent());
        self::assertEquals($testAdminLog[0]->getTimestamp(), $selectedAll[0]->getTimestamp());

        self::assertInstanceOf(AdminLog::class, $selectedAll[1]);
        self::assertEquals($testAdminLog[1]->getID(), $selectedAll[1]->getID());
        self::assertEquals($testAdminLog[1]->getAdminID(), $selectedAll[1]->getAdminID());
        self::assertEquals($testAdminLog[1]->getEvent(), $selectedAll[1]->getEvent());
        self::assertEquals($testAdminLog[1]->getTimestamp(), $selectedAll[1]->getTimestamp());

        self::assertInstanceOf(AdminLog::class, $selectedAll[2]);
        self::assertEquals($testAdminLog[2]->getID(), $selectedAll[2]->getID());
        self::assertEquals($testAdminLog[2]->getAdminID(), $selectedAll[2]->getAdminID());
        self::assertEquals($testAdminLog[2]->getEvent(), $selectedAll[2]->getEvent());
        self::assertEquals($testAdminLog[2]->getTimestamp(), $selectedAll[2]->getTimestamp());

        // Clean it up
        $testAdmin->delete();
        foreach($testAdminLog as $test) {
            $test->delete();
        }
    }

    public function testEql() {
        // Create an array of admin logs
        $testAdminLog = [];

        $testAdminLog[0] = new AdminLog();
        $testAdminLog[0]->setAdminID(1);
        $testAdminLog[0]->setEvent('event test');
        $testAdminLog[0]->setTimestamp(123);

        $testAdminLog[1] = new AdminLog();
        $testAdminLog[1]->setAdminID(1);
        $testAdminLog[1]->setEvent('event test');
        $testAdminLog[1]->setTimestamp(123);

        $testAdminLog[2] = new AdminLog();
        $testAdminLog[2]->setAdminID(2);
        $testAdminLog[2]->setEvent('event test2');
        $testAdminLog[2]->setTimestamp(1234);

        // Check same object is eql
        self::assertTrue($testAdminLog[0]->eql($testAdminLog[0]));

        // Check same details are eql
        self::assertTrue($testAdminLog[0]->eql($testAdminLog[1]));

        // Check different arent equal
        self::assertFalse($testAdminLog[0]->eql($testAdminLog[2]));
    }

    public function testGetByAdminID() {

        // Create a test user to use as admin
        $testAdmin = [];

        $testAdmin[0] = new User();
        $testAdmin[0]->setUsername('testAdmin');
        $testAdmin[0]->create();

        $testAdmin[1] = new User();
        $testAdmin[1]->setUsername('testAdmin2');
        $testAdmin[1]->create();

        // Create an array of admin logs
        $testAdminLog = [];

        $testAdminLog[0] = new AdminLog();
        $testAdminLog[0]->setAdminID($testAdmin[0]->getID());
        $testAdminLog[0]->setEvent('event test');
        $testAdminLog[0]->setTimestamp(123);
        $testAdminLog[0]->create();

        $testAdminLog[1] = new AdminLog();
        $testAdminLog[1]->setAdminID($testAdmin[1]->getID());
        $testAdminLog[1]->setEvent('event test2');
        $testAdminLog[1]->setTimestamp(1234);
        $testAdminLog[1]->create();

        $selected = AdminLog::getByAdminID($testAdmin[0]->getID());

        self::assertTrue(\is_array($selected));
        self::assertEquals(1, \count($selected));
        self::assertEquals($testAdminLog[0]->getID(), $selected[0]->getID());
        self::assertEquals($testAdminLog[0]->getAdminID(), $selected[0]->getAdminID());
        self::assertEquals($testAdminLog[0]->getEvent(), $selected[0]->getEvent());
        self::assertEquals($testAdminLog[0]->getTimestamp(), $selected[0]->getTimestamp());

        foreach($testAdmin as $admin) {
            $admin->delete();
        }
        foreach ($testAdminLog as $log) {
            $log->delete();
        }
    }

}
