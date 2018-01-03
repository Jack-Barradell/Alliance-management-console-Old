<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Merit.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Merit;
use AMC\Classes\Database;
use AMC\Classes\User;
use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\InvalidUserException;
use PHPUnit\Framework\TestCase;

class MeritTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Test null constructor
        $merit = new Merit();

        $this->assertTrue($merit->eql(new Merit()));
        $this->assertNull($merit->getID());
        $this->assertNull($merit->getUserID());
        $this->assertNull($merit->getAdminID());
        $this->assertNull($merit->getValue());
        $this->assertNull($merit->getReason());
        $this->assertNull($merit->getTimestamp());

        // Test non null constructor
        $merit = new Merit(1, 2, 3, 5, 'test', 123);

        $this->assertFalse($merit->eql(new Merit()));
        $this->assertEquals(1, $merit->getID());
        $this->assertEquals(2, $merit->getUserID());
        $this->assertEquals(3, $merit->getAdminID());
        $this->assertEquals(5, $merit->getValue());
        $this->assertEquals('test', $merit->getReason());
        $this->assertEquals(123, $merit->getTimestamp());
    }

    public function testCreate() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test admin
        $testAdmin = new User();
        $testAdmin->setUsername('testAdmin');
        $testAdmin->create();

        // Create a test merit
        $testMerit = new Merit();
        $testMerit->setUserID($testUser->getID());
        $testMerit->setAdminID($testAdmin->getID());
        $testMerit->setValue(1);
        $testMerit->setReason('test');
        $testMerit->setTimestamp(123);
        $testMerit->create();

        // Check id is an int now
        $this->assertInternalType('int', $testMerit->getID());

        // Now pull it
        $stmt = $this->_connection->prepare("SELECT `MeritID`,`UserID`,`AdminID`,`MeritValue`,`MeritReason`,`MeritTimestamp` FROM `Merits` WHERE `MeritID`=?");
        $stmt->bind_param('i', $testMerit->getID());
        $stmt->execute();
        $stmt->bind_result($meritID, $userID, $adminID, $value, $reason, $timestamp);

        // Check there is only one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testMerit->getID(), $meritID);
        $this->assertEquals($testMerit->getUserID(), $userID);
        $this->assertEquals($testMerit->getAdminID(), $adminID);
        $this->assertEquals($testMerit->getValue(), $value);
        $this->assertEquals($testMerit->getReason(), $reason);
        $this->assertEquals($testMerit->getTimestamp(), $timestamp);

        $stmt->close();

        // Clean up
        $testMerit->delete();
        $testUser->delete();
        $testAdmin->delete();
    }

    public function testBlankCreate() {
        // Create a merit
        $merit = new Merit();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $merit->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Merit.', $e->getMessage());
        }
    }

    public function testUpdate() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test admin
        $testAdmin = [];
        $testAdmin[0] = new User();
        $testAdmin[0]->setUsername('testAdmin');
        $testAdmin[0]->create();

        $testAdmin[1] = new User();
        $testAdmin[1]->setUsername('testAdmin2');
        $testAdmin[1]->create();

        // Create a test merit
        $testMerit = new Merit();
        $testMerit->setUserID($testUser[0]->getID());
        $testMerit->setAdminID($testAdmin[0]->getID());
        $testMerit->setValue(1);
        $testMerit->setReason('test');
        $testMerit->setTimestamp(123);
        $testMerit->create();

        // Now update it
        $testMerit->setUserID($testUser[1]->getID());
        $testMerit->setAdminID($testAdmin[1]->getID());
        $testMerit->setValue(2);
        $testMerit->setReason('test2');
        $testMerit->setTimestamp(321);
        $testMerit->update();

        // Now pull it
        $stmt = $this->_connection->prepare("SELECT `MeritID`,`UserID`,`AdminID`,`MeritValue`,`MeritReason`,`MeritTimestamp` FROM `Merits` WHERE `MeritID`=?");
        $stmt->bind_param('i', $testMerit->getID());
        $stmt->execute();
        $stmt->bind_result($meritID, $userID, $adminID, $value, $reason, $timestamp);

        // Check there is only one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testMerit->getID(), $meritID);
        $this->assertEquals($testMerit->getUserID(), $userID);
        $this->assertEquals($testMerit->getAdminID(), $adminID);
        $this->assertEquals($testMerit->getValue(), $value);
        $this->assertEquals($testMerit->getReason(), $reason);
        $this->assertEquals($testMerit->getTimestamp(), $timestamp);

        $stmt->close();

        // Clean up
        $testMerit->delete();
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testAdmin as $admin) {
            $admin->delete();
        }
    }

    public function testBlankUpdate() {
        // Create a merit
        $merit = new Merit();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $merit->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Merit.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test admin
        $testAdmin = new User();
        $testAdmin->setUsername('testAdmin');
        $testAdmin->create();

        // Create a test merit
        $testMerit = new Merit();
        $testMerit->setUserID($testUser->getID());
        $testMerit->setAdminID($testAdmin->getID());
        $testMerit->setValue(1);
        $testMerit->setReason('test');
        $testMerit->setTimestamp(123);
        $testMerit->create();

        // Store the id
        $id = $testMerit->getID();

        // Delete it
        $testMerit->delete();

        // Check its gone
        $stmt = $this->_connection->prepare("SELECT `MeritID` FROM `Merits` WHERE `MeritID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testUser->delete();
        $testAdmin->delete();
    }

    public function testSelectWithInput() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test admin
        $testAdmin = [];
        $testAdmin[0] = new User();
        $testAdmin[0]->setUsername('testAdmin');
        $testAdmin[0]->create();

        $testAdmin[1] = new User();
        $testAdmin[1]->setUsername('testAdmin2');
        $testAdmin[1]->create();

        // Create a test merit
        $testMerit = [];
        $testMerit[0] = new Merit();
        $testMerit[0]->setUserID($testUser[0]->getID());
        $testMerit[0]->setAdminID($testAdmin[0]->getID());
        $testMerit[0]->setValue(1);
        $testMerit[0]->setReason('test');
        $testMerit[0]->setTimestamp(123);
        $testMerit[0]->create();

        $testMerit[1] = new Merit();
        $testMerit[1]->setUserID($testUser[1]->getID());
        $testMerit[1]->setAdminID($testAdmin[1]->getID());
        $testMerit[1]->setValue(2);
        $testMerit[1]->setReason('test2');
        $testMerit[1]->setTimestamp(12345);
        $testMerit[1]->create();

        $testMerit[2] = new Merit();
        $testMerit[2]->setUserID($testUser[0]->getID());
        $testMerit[2]->setAdminID($testAdmin[1]->getID());
        $testMerit[2]->setValue(3);
        $testMerit[2]->setReason('tes4t');
        $testMerit[2]->setTimestamp(1234567);
        $testMerit[2]->create();

        // Select and check a single
        $selectedSingle = Merit::select(array($testMerit[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(Merit::class, $selectedSingle[0]);
        $this->assertEquals($testMerit[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testMerit[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testMerit[0]->getAdminID(), $selectedSingle[0]->getAdminID());
        $this->assertEquals($testMerit[0]->getValue(), $selectedSingle[0]->getValue());
        $this->assertEquals($testMerit[0]->getReason(), $selectedSingle[0]->getReason());
        $this->assertEquals($testMerit[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Select and check multiple
        $selectedMultiple = Merit::select(array($testMerit[1]->getID(), $testMerit[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(Merit::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Merit::class, $selectedMultiple[1]);

        if($testMerit[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testMerit[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testMerit[1]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testMerit[1]->getAdminID(), $selectedMultiple[$i]->getAdminID());
        $this->assertEquals($testMerit[1]->getValue(), $selectedMultiple[$i]->getValue());
        $this->assertEquals($testMerit[1]->getReason(), $selectedMultiple[$i]->getReason());
        $this->assertEquals($testMerit[1]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());

        $this->assertEquals($testMerit[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testMerit[2]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testMerit[2]->getAdminID(), $selectedMultiple[$j]->getAdminID());
        $this->assertEquals($testMerit[2]->getValue(), $selectedMultiple[$j]->getValue());
        $this->assertEquals($testMerit[2]->getReason(), $selectedMultiple[$j]->getReason());
        $this->assertEquals($testMerit[2]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());

        // Clean up
        foreach($testMerit as $merit) {
            $merit->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testAdmin as $admin) {
            $admin->delete();
        }
    }

    public function testSelectAll() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test admin
        $testAdmin = [];
        $testAdmin[0] = new User();
        $testAdmin[0]->setUsername('testAdmin');
        $testAdmin[0]->create();

        $testAdmin[1] = new User();
        $testAdmin[1]->setUsername('testAdmin2');
        $testAdmin[1]->create();

        // Create a test merit
        $testMerit = [];
        $testMerit[0] = new Merit();
        $testMerit[0]->setUserID($testUser[0]->getID());
        $testMerit[0]->setAdminID($testAdmin[0]->getID());
        $testMerit[0]->setValue(1);
        $testMerit[0]->setReason('test');
        $testMerit[0]->setTimestamp(123);
        $testMerit[0]->create();

        $testMerit[1] = new Merit();
        $testMerit[1]->setUserID($testUser[1]->getID());
        $testMerit[1]->setAdminID($testAdmin[1]->getID());
        $testMerit[1]->setValue(2);
        $testMerit[1]->setReason('test2');
        $testMerit[1]->setTimestamp(12345);
        $testMerit[1]->create();

        // Select and check multiple
        $selectedMultiple = Merit::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(Merit::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Merit::class, $selectedMultiple[1]);

        if($testMerit[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testMerit[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testMerit[0]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testMerit[0]->getAdminID(), $selectedMultiple[$i]->getAdminID());
        $this->assertEquals($testMerit[0]->getValue(), $selectedMultiple[$i]->getValue());
        $this->assertEquals($testMerit[0]->getReason(), $selectedMultiple[$i]->getReason());
        $this->assertEquals($testMerit[0]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());

        $this->assertEquals($testMerit[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testMerit[1]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testMerit[1]->getAdminID(), $selectedMultiple[$j]->getAdminID());
        $this->assertEquals($testMerit[1]->getValue(), $selectedMultiple[$j]->getValue());
        $this->assertEquals($testMerit[1]->getReason(), $selectedMultiple[$j]->getReason());
        $this->assertEquals($testMerit[1]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());

        // Clean up
        foreach($testMerit as $merit) {
            $merit->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testAdmin as $admin) {
            $admin->delete();
        }
    }

    public function testEql() {
        // Create a test merit
        $testMerit = [];
        $testMerit[0] = new Merit();
        $testMerit[0]->setID(1);
        $testMerit[0]->setUserID(1);
        $testMerit[0]->setAdminID(2);
        $testMerit[0]->setValue(1);
        $testMerit[0]->setReason('test');
        $testMerit[0]->setTimestamp(123);

        $testMerit[1] = new Merit();
        $testMerit[1]->setID(1);
        $testMerit[1]->setUserID(1);
        $testMerit[1]->setAdminID(2);
        $testMerit[1]->setValue(1);
        $testMerit[1]->setReason('test');
        $testMerit[1]->setTimestamp(123);

        $testMerit[2] = new Merit();
        $testMerit[2]->setID(2);
        $testMerit[2]->setUserID(2);
        $testMerit[2]->setAdminID(3);
        $testMerit[2]->setValue(10);
        $testMerit[2]->setReason('test1');
        $testMerit[2]->setTimestamp(1232);

        // Check same object is eql
        $this->assertTrue($testMerit[0]->eql($testMerit[0]));

        // Check same details are eql
        $this->assertTrue($testMerit[0]->eql($testMerit[1]));

        // Check different arent equal
        $this->assertFalse($testMerit[0]->eql($testMerit[2]));
    }

    public function testGetByUserID() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test admin
        $testAdmin = [];
        $testAdmin[0] = new User();
        $testAdmin[0]->setUsername('testAdmin');
        $testAdmin[0]->create();

        $testAdmin[1] = new User();
        $testAdmin[1]->setUsername('testAdmin2');
        $testAdmin[1]->create();

        // Create a test merit
        $testMerit = [];
        $testMerit[0] = new Merit();
        $testMerit[0]->setUserID($testUser[0]->getID());
        $testMerit[0]->setAdminID($testAdmin[0]->getID());
        $testMerit[0]->setValue(1);
        $testMerit[0]->setReason('test');
        $testMerit[0]->setTimestamp(123);
        $testMerit[0]->create();

        $testMerit[1] = new Merit();
        $testMerit[1]->setUserID($testUser[1]->getID());
        $testMerit[1]->setAdminID($testAdmin[1]->getID());
        $testMerit[1]->setValue(2);
        $testMerit[1]->setReason('test2');
        $testMerit[1]->setTimestamp(12345);
        $testMerit[1]->create();

        // Now select it
        $selected = Merit::getByUserID($testUser[0]->getID());

        $this->assertTrue(\is_array($selected));
        $this->assertEquals(1, \count($selected));
        $this->assertInstanceOf(Merit::class, $selected[0]);

        $this->assertEquals($testMerit[0]->getID(), $selected->getID());
        $this->assertEquals($testMerit[0]->getUserID(), $selected->getUserID());
        $this->assertEquals($testMerit[0]->getAdminID(), $selected->getAdminID());
        $this->assertEquals($testMerit[0]->getValue(), $selected->getValue());
        $this->assertEquals($testMerit[0]->getReason(), $selected->getReason());
        $this->assertEquals($testMerit[0]->getTimestamp(), $selected->getTimestamp());

        // Clean up
        foreach($testMerit as $merit) {
            $merit->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testAdmin as $admin) {
            $admin->delete();
        }
    }

    public function testGetByAdminID() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test admin
        $testAdmin = [];
        $testAdmin[0] = new User();
        $testAdmin[0]->setUsername('testAdmin');
        $testAdmin[0]->create();

        $testAdmin[1] = new User();
        $testAdmin[1]->setUsername('testAdmin2');
        $testAdmin[1]->create();

        // Create a test merit
        $testMerit = [];
        $testMerit[0] = new Merit();
        $testMerit[0]->setUserID($testUser[0]->getID());
        $testMerit[0]->setAdminID($testAdmin[0]->getID());
        $testMerit[0]->setValue(1);
        $testMerit[0]->setReason('test');
        $testMerit[0]->setTimestamp(123);
        $testMerit[0]->create();

        $testMerit[1] = new Merit();
        $testMerit[1]->setUserID($testUser[1]->getID());
        $testMerit[1]->setAdminID($testAdmin[1]->getID());
        $testMerit[1]->setValue(2);
        $testMerit[1]->setReason('test2');
        $testMerit[1]->setTimestamp(12345);
        $testMerit[1]->create();

        // Now select it
        $selected = Merit::getByAdminID($testAdmin[0]->getID());

        $this->assertTrue(\is_array($selected));
        $this->assertEquals(1, \count($selected));
        $this->assertInstanceOf(Merit::class, $selected[0]);

        $this->assertEquals($testMerit[0]->getID(), $selected->getID());
        $this->assertEquals($testMerit[0]->getUserID(), $selected->getUserID());
        $this->assertEquals($testMerit[0]->getAdminID(), $selected->getAdminID());
        $this->assertEquals($testMerit[0]->getValue(), $selected->getValue());
        $this->assertEquals($testMerit[0]->getReason(), $selected->getReason());
        $this->assertEquals($testMerit[0]->getTimestamp(), $selected->getTimestamp());

        // Clean up
        foreach($testMerit as $merit) {
            $merit->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testAdmin as $admin) {
            $admin->delete();
        }
    }

    public function testSetUserID() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('test');
        $testUser->create();

        // Create a test merit
        $testMerit = new Merit();

        // Try and set the user id
        try {
            $testMerit->setUserID($testUser->getID(), true);
            $this->assertEquals($testUser->getID(), $testMerit->getUserID());
        } finally {
            $testUser->delete();
        }
    }

    public function testInvalidUserSetUserID() {
        // Get max user id and add one to it
        $stmt = Database::getConnection()->prepare("SELECT `UserID` FROM `Users` ORDER BY `UserID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($userID);
        if($stmt->fetch()) {
            $useID = $userID + 1;
        }
        else {
            $useID = 1;
        }
        $stmt->close();

        // Set expected exception
        $this->expectException(InvalidUserException::class);

        // Create test merit
        $testMerit = new Merit();

        // Trigger it
        try {
            $testMerit->setUserID($useID, true);
        } catch(InvalidUserException $e) {
            $this->assertEquals('No user exists with id ' . $useID, $e->getMessage());
        }
    }

    public function testSetAdminID() {
        // Create a test admin
        $testAdmin = new User();
        $testAdmin->setUsername('test');
        $testAdmin->create();

        // Create a test merit
        $testMerit = new Merit();

        // Try and set the admin id
        try {
            $testMerit->setAdminID($testAdmin->getID(), true);
            $this->assertEquals($testAdmin->getID(), $testMerit->getAdminID());
        } finally {
            $testAdmin->delete();
        }
    }

    public function testInvalidUserSetAdminID() {
        // Get max user id and add one to it
        $stmt = Database::getConnection()->prepare("SELECT `UserID` FROM `Users` ORDER BY `UserID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($userID);
        if($stmt->fetch()) {
            $useID = $userID + 1;
        }
        else {
            $useID = 1;
        }
        $stmt->close();

        // Set expected exception
        $this->expectException(InvalidUserException::class);

        // Create test merit
        $testMerit = new Merit();

        // Trigger it
        try {
            $testMerit->setAdminID($useID, true);
        } catch(InvalidUserException $e) {
            $this->assertEquals('No user exists with id ' . $useID, $e->getMessage());
        }
    }
}
