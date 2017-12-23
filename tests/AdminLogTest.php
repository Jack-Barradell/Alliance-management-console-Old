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
use AMC\Exceptions\InvalidUserException;
use PHPUnit\Framework\TestCase;

class AdminLogTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        $adminLog = new AdminLog();

        // Check the object initialised to have null vars
        $this->assertTrue($adminLog->eql(new AdminLog()));
        $this->assertNull($adminLog->getID());
        $this->assertNull($adminLog->getAdminID());
        $this->assertNull($adminLog->getEvent());
        $this->assertNull($adminLog->getTimestamp());

        $adminLog = new AdminLog(1, 1, 'testEvent', 123);

        // Check the object vars are correct
        $this->assertFalse($adminLog->eql(new AdminLog()));
        $this->assertEquals(1, $adminLog->getID());
        $this->assertEquals(1, $adminLog->getAdminID());
        $this->assertEquals('testEvent', $adminLog->getEvent());
        $this->assertEquals(123, $adminLog->getTimestamp());
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
        $this->assertInternalType('int', $testAdminLog->getID());

        // Pull it from the db
        $stmt = $this->_connection->prepare("SELECT `AdminLogID`,`AdminID`,`AdminLogEvent`,`AdminLogTimestamp` FROM `Admin_Logs` WHERE `AdminLogID`=?");
        $stmt->bind_param('i', $testAdminLog->getID());
        $stmt->execute();
        $stmt->bind_result($adminLogID, $adminID, $adminLogEvent, $adminLogTimestamp);

        // Check only one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();
        $stmt->close();

        // Check the vars match the object
        $this->assertEquals($testAdminLog->getID(), $adminLogID);
        $this->assertEquals($testAdminLog->getAdminID(), $adminID);
        $this->assertEquals($testAdminLog->getEvent(), $adminLogEvent);
        $this->assertEquals($testAdminLog->getTimestamp(), $adminLogTimestamp);

        $testAdminLog->delete();
        $testAdmin->delete();
        return $testAdminLog;
    }

    public function testBlankCreate() {

        // Create admin log
        $adminLog = new AdminLog();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger exception
        try {
            $adminLog->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Admin Log.', $e->getMessage());
        }

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
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();
        $stmt->close();

        // Check the vars match the object
        $this->assertEquals($testAdminLog->getID(), $adminLogID);
        $this->assertEquals($testAdminLog->getAdminID(), $adminID);
        $this->assertEquals($testAdminLog->getEvent(), $adminLogEvent);
        $this->assertEquals($testAdminLog->getTimestamp(), $adminLogTimestamp);

        $testAdminLog->delete();
        $testAdmin->delete();
        $testAdmin2->delete();
    }

    public function testBlankUpdate() {
        // Create admin log
        $adminLog  = new AdminLog();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger exception
        try {
            $adminLog->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Admin Log.', $e->getMessage());
        }
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

        // Check id is now null
        $this->assertNull($testAdminLog->getID());

        $stmt = $this->_connection->prepare("SELECT `AdminLogID`,`AdminID`,`AdminLogEvent`,`AdminLogTimestamp` FROM `Admin_Logs` WHERE `AdminLogID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($adminLogID, $adminID, $adminLogEvent, $adminLogTimestamp);

        // Check only one result
        $this->assertEquals(0, $stmt->num_rows);

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

        $selectedSingle = AdminLog::select(array($testAdminLog[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(AdminLog::class, $selectedSingle[0]);
        $this->assertEquals($testAdminLog[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testAdminLog[0]->getAdminID(), $selectedSingle[0]->getAdminID());
        $this->assertEquals($testAdminLog[0]->getEvent(), $selectedSingle[0]->getEvent());
        $this->assertEquals($testAdminLog[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        $selectedMultiple = AdminLog::select(array($testAdminLog[0]->getID(), $testAdminLog[2]->getID()));

        // Check they it is an array
        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));

        // Check they are admin logs
        $this->assertInstanceOf(AdminLog::class, $selectedSingle[0]);
        $this->assertInstanceOf(AdminLog::class, $selectedSingle[1]);

        if($testAdminLog[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testAdminLog[0]->getID(), $selectedSingle[$i]->getID());
        $this->assertEquals($testAdminLog[0]->getAdminID(), $selectedSingle[$i]->getAdminID());
        $this->assertEquals($testAdminLog[0]->getEvent(), $selectedSingle[$i]->getEvent());
        $this->assertEquals($testAdminLog[0]->getTimestamp(), $selectedSingle[$i]->getTimestamp());

        $this->assertEquals($testAdminLog[1]->getID(), $selectedSingle[$j]->getID());
        $this->assertEquals($testAdminLog[1]->getAdminID(), $selectedSingle[$j]->getAdminID());
        $this->assertEquals($testAdminLog[1]->getEvent(), $selectedSingle[$j]->getEvent());
        $this->assertEquals($testAdminLog[1]->getTimestamp(), $selectedSingle[$j]->getTimestamp());

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

        $selectedAll = AdminLog::select(array());

        // Check its an array
        $this->assertTrue(\is_array($selectedAll));
        $this->assertEquals(2, \count($selectedAll));

        // Check they are admin logs
        $this->assertInstanceOf(AdminLog::class, $selectedAll[0]);
        $this->assertInstanceOf(AdminLog::class, $selectedAll[1]);

        if($testAdminLog[0]->getID() == $selectedAll[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testAdminLog[0]->getID(), $selectedAll[$i]->getID());
        $this->assertEquals($testAdminLog[0]->getAdminID(), $selectedAll[$i]->getAdminID());
        $this->assertEquals($testAdminLog[0]->getEvent(), $selectedAll[$i]->getEvent());
        $this->assertEquals($testAdminLog[0]->getTimestamp(), $selectedAll[$i]->getTimestamp());

        $this->assertEquals($testAdminLog[1]->getID(), $selectedAll[$j]->getID());
        $this->assertEquals($testAdminLog[1]->getAdminID(), $selectedAll[$j]->getAdminID());
        $this->assertEquals($testAdminLog[1]->getEvent(), $selectedAll[$j]->getEvent());
        $this->assertEquals($testAdminLog[1]->getTimestamp(), $selectedAll[$j]->getTimestamp());

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
        $testAdminLog[0]->setID(1);
        $testAdminLog[0]->setAdminID(1);
        $testAdminLog[0]->setEvent('event test');
        $testAdminLog[0]->setTimestamp(123);

        $testAdminLog[1] = new AdminLog();
        $testAdminLog[1]->setID(1);
        $testAdminLog[1]->setAdminID(1);
        $testAdminLog[1]->setEvent('event test');
        $testAdminLog[1]->setTimestamp(123);

        $testAdminLog[2] = new AdminLog();
        $testAdminLog[2]->setID(2);
        $testAdminLog[2]->setAdminID(2);
        $testAdminLog[2]->setEvent('event test2');
        $testAdminLog[2]->setTimestamp(1234);

        // Check same object is eql
        $this->assertTrue($testAdminLog[0]->eql($testAdminLog[0]));

        // Check same details are eql
        $this->assertTrue($testAdminLog[0]->eql($testAdminLog[1]));

        // Check different arent equal
        $this->assertFalse($testAdminLog[0]->eql($testAdminLog[2]));
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

        $this->assertTrue(\is_array($selected));
        $this->assertEquals(1, \count($selected));
        $this->assertEquals($testAdminLog[0]->getID(), $selected[0]->getID());
        $this->assertEquals($testAdminLog[0]->getAdminID(), $selected[0]->getAdminID());
        $this->assertEquals($testAdminLog[0]->getEvent(), $selected[0]->getEvent());
        $this->assertEquals($testAdminLog[0]->getTimestamp(), $selected[0]->getTimestamp());

        foreach($testAdmin as $admin) {
            $admin->delete();
        }
        foreach ($testAdminLog as $log) {
            $log->delete();
        }
    }

    public function testSetAdminID() {
        // Create a test admin
        $testAdmin = new User();
        $testAdmin->setUsername('testAdmin');
        $testAdmin->create();

        // Create a test admin log
        $testAdminLog = new AdminLog();

        // Check it can be set
        try {
            $testAdminLog->setAdminID($testAdmin->getID(), true);
            $this->assertEquals($testAdmin->getID(), $testAdminLog->getAdminID());
        } finally {

            // Clean up
            $testAdmin->delete();
        }
    }

    public function testInvalidUserSetAdminID() {
        // Get max user id
        $stmt = Database::getConnection()->prepare("SELECT `UserID` FROM `Users` ORDER BY `UserID` DESCENDING LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($userID);
        if($stmt->fetch()) {
            $tryID = $userID+1;
        }
        else {
            $tryID = 1;
        }

        // Create a test admin log
        $testAdminLog = new AdminLog();

        $this->expectException(InvalidUserException::class);

        // Check it cant be set
        try {
            $testAdminLog->setAdminID($tryID, true);
        } catch(InvalidUserException $e) {
            $this->assertEquals('No user exists with id ' . $tryID, $e->getMessage());
        }

    }

}
