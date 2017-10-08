<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Warning.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\User;
use AMC\Classes\Warning;
use AMC\Classes\Database;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class WarningTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check null constructor
        $testWarning = new Warning();

        $this->assertNull($testWarning->getID());
        $this->assertNull($testWarning->getUserID());
        $this->assertNull($testWarning->getAdminID());
        $this->assertNull($testWarning->getReason());
        $this->assertNull($testWarning->getTimestamp());

        // Check non null constructor
        $testWarning = new Warning(1,2,3, 'reason', 123);

        $this->assertEquals(1, $testWarning->getID());
        $this->assertEquals(2, $testWarning->getUserID());
        $this->assertEquals(3, $testWarning->getAdminID());
        $this->assertEquals('reason', $testWarning->getReason());
        $this->assertEquals(123, $testWarning->getTimestamp());
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

        // Create a test warning
        $testWarning = new Warning();
        $testWarning->setUserID($testUser->getID());
        $testWarning->setAdminID($testAdmin->getID());
        $testWarning->setReason('testReason');
        $testWarning->setTimestamp(123);
        $testWarning->create();

        // Check id is now an int
        $this->assertInternalType('int', $testWarning->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `WarningID`,`UserID`,`AdminID`,`WarningReason`,`WarningTimestamp` FROM `Warnings` WHERE `WarningID`=?");
        $stmt->bind_param('i', $testWarning->getID());
        $stmt->execute();
        $stmt->bind_result($warningID, $userID, $adminID, $reason, $timestamp);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testWarning->getID(), $warningID);
        $this->assertEquals($testWarning->getUserID(), $userID);
        $this->assertEquals($testWarning->getAdminID(), $adminID);
        $this->assertEquals($testWarning->getReason(), $reason);
        $this->assertEquals($testWarning->getTimestamp(), $timestamp);

        $stmt->close();

        // Clean up
        $testWarning->delete();
        $testUser->delete();
        $testAdmin->delete();
    }

    public function testBlankCreate() {
        // Create a test warning
        $testWarning = new Warning();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testWarning->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Warning.', $e->getMessage());
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

        // Create a test warning
        $testWarning = new Warning();
        $testWarning->setUserID($testUser[0]->getID());
        $testWarning->setAdminID($testAdmin[0]->getID());
        $testWarning->setReason('testReason');
        $testWarning->setTimestamp(123);
        $testWarning->create();

        // Update it
        $testWarning->setUserID($testUser[1]->getID());
        $testWarning->setAdminID($testAdmin[1]->getID());
        $testWarning->setReason('testReason2');
        $testWarning->setTimestamp(12345);
        $testWarning->update();

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `WarningID`,`UserID`,`AdminID`,`WarningReason`,`WarningTimestamp` FROM `Warnings` WHERE `WarningID`=?");
        $stmt->bind_param('i', $testWarning->getID());
        $stmt->execute();
        $stmt->bind_result($warningID, $userID, $adminID, $reason, $timestamp);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testWarning->getID(), $warningID);
        $this->assertEquals($testWarning->getUserID(), $userID);
        $this->assertEquals($testWarning->getAdminID(), $adminID);
        $this->assertEquals($testWarning->getReason(), $reason);
        $this->assertEquals($testWarning->getTimestamp(), $timestamp);

        $stmt->close();

        // Clean up
        $testWarning->delete();
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testAdmin as $admin) {
            $admin->delete();
        }
    }

    public function testBlankUpdate() {
        // Create a test warning
        $testWarning = new Warning();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testWarning->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Warning.', $e->getMessage());
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

        // Create a test warning
        $testWarning = new Warning();
        $testWarning->setUserID($testUser->getID());
        $testWarning->setAdminID($testAdmin->getID());
        $testWarning->setReason('testReason');
        $testWarning->setTimestamp(123);
        $testWarning->create();

        // Save id
        $id = $testWarning->getID();

        // Now delete it
        $testWarning->delete();

        // Check id is null
        $this->assertNull($testWarning->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `WarningID`,`UserID`,`AdminID`,`WarningReason`,`WarningTimestamp` FROM `Warnings` WHERE `WarningID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testAdmin->delete();
        $testUser->delete();
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

        // Create a test warning
        $testWarning = [];
        $testWarning[0] = new Warning();
        $testWarning[0]->setUserID($testUser[0]->getID());
        $testWarning[0]->setAdminID($testAdmin[0]->getID());
        $testWarning[0]->setReason('testReason');
        $testWarning[0]->setTimestamp(123);
        $testWarning[0]->create();

        $testWarning[1] = new Warning();
        $testWarning[1]->setUserID($testUser[1]->getID());
        $testWarning[1]->setAdminID($testAdmin[1]->getID());
        $testWarning[1]->setReason('testReason2');
        $testWarning[1]->setTimestamp(12345);
        $testWarning[1]->create();

        $testWarning[2] = new Warning();
        $testWarning[2]->setUserID($testUser[0]->getID());
        $testWarning[2]->setAdminID($testAdmin[1]->getID());
        $testWarning[2]->setReason('testReason3');
        $testWarning[2]->setTimestamp(123456789);
        $testWarning[2]->create();

        // Select and check a single
        $selectedSingle = Warning::select(array($testWarning[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(Warning::class, $selectedSingle[0]);
        $this->assertEquals($testWarning[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testWarning[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testWarning[0]->getAdminID(), $selectedSingle[0]->getAdmin());
        $this->assertEquals($testWarning[0]->getReason(), $selectedSingle[0]->getReason());
        $this->assertEquals($testWarning[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Select and check multiple
        $selectedMultiple = Warning::select(array($testWarning[1]->getID(), $testWarning[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(Warning::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Warning::class, $selectedMultiple[1]);

        if($testWarning[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testWarning[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testWarning[1]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testWarning[1]->getAdminID(), $selectedMultiple[$i]->getAdmin());
        $this->assertEquals($testWarning[1]->getReason(), $selectedMultiple[$i]->getReason());
        $this->assertEquals($testWarning[1]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());

        $this->assertEquals($testWarning[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testWarning[2]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testWarning[2]->getAdminID(), $selectedMultiple[$j]->getAdmin());
        $this->assertEquals($testWarning[2]->getReason(), $selectedMultiple[$j]->getReason());
        $this->assertEquals($testWarning[2]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());

        // Clean up
        foreach($testWarning as $warning) {
            $warning->delete();
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

        // Create a test warning
        $testWarning = [];
        $testWarning[0] = new Warning();
        $testWarning[0]->setUserID($testUser[0]->getID());
        $testWarning[0]->setAdminID($testAdmin[0]->getID());
        $testWarning[0]->setReason('testReason');
        $testWarning[0]->setTimestamp(123);
        $testWarning[0]->create();

        $testWarning[1] = new Warning();
        $testWarning[1]->setUserID($testUser[1]->getID());
        $testWarning[1]->setAdminID($testAdmin[1]->getID());
        $testWarning[1]->setReason('testReason2');
        $testWarning[1]->setTimestamp(12345);
        $testWarning[1]->create();

        // Select and check multiple
        $selectedMultiple = Warning::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(Warning::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Warning::class, $selectedMultiple[1]);

        if($testWarning[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testWarning[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testWarning[0]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testWarning[0]->getAdminID(), $selectedMultiple[$i]->getAdmin());
        $this->assertEquals($testWarning[0]->getReason(), $selectedMultiple[$i]->getReason());
        $this->assertEquals($testWarning[0]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());

        $this->assertEquals($testWarning[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testWarning[1]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testWarning[1]->getAdminID(), $selectedMultiple[$j]->getAdmin());
        $this->assertEquals($testWarning[1]->getReason(), $selectedMultiple[$j]->getReason());
        $this->assertEquals($testWarning[1]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());

        // Clean up
        foreach($testWarning as $warning) {
            $warning->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testAdmin as $admin) {
            $admin->delete();
        }
    }

    public function testEql() {
        // Create a test warning
        $testWarning = [];
        $testWarning[0] = new Warning();
        $testWarning[0]->setUserID(1);
        $testWarning[0]->setAdminID(2);
        $testWarning[0]->setReason('testReason');
        $testWarning[0]->setTimestamp(123);

        $testWarning[1] = new Warning();
        $testWarning[1]->setUserID(1);
        $testWarning[1]->setAdminID(2);
        $testWarning[1]->setReason('testReason');
        $testWarning[1]->setTimestamp(123);

        $testWarning[2] = new Warning();
        $testWarning[2]->setUserID(1);
        $testWarning[2]->setAdminID(2);
        $testWarning[2]->setReason('testReason');
        $testWarning[2]->setTimestamp(123);

        // Check same object is eql
        $this->assertTrue($testWarning[0]->eql($testWarning[0]));

        // Check same details are eql
        $this->assertTrue($testWarning[0]->eql($testWarning[1]));

        // Check different arent equal
        $this->assertFalse($testWarning[0]->eql($testWarning[2]));
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

        // Create a test warning
        $testWarning = [];
        $testWarning[0] = new Warning();
        $testWarning[0]->setUserID($testUser[0]->getID());
        $testWarning[0]->setAdminID($testAdmin[0]->getID());
        $testWarning[0]->setReason('testReason');
        $testWarning[0]->setTimestamp(123);
        $testWarning[0]->create();

        $testWarning[1] = new Warning();
        $testWarning[1]->setUserID($testUser[1]->getID());
        $testWarning[1]->setAdminID($testAdmin[1]->getID());
        $testWarning[1]->setReason('testReason2');
        $testWarning[1]->setTimestamp(12345);
        $testWarning[1]->create();

        // Select and check a single
        $selectedSingle = Warning::getByUserID($testUser[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(Warning::class, $selectedSingle[0]);
        $this->assertEquals($testWarning[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testWarning[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testWarning[0]->getAdminID(), $selectedSingle[0]->getAdmin());
        $this->assertEquals($testWarning[0]->getReason(), $selectedSingle[0]->getReason());
        $this->assertEquals($testWarning[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Clean up
        foreach($testWarning as $warning) {
            $warning->delete();
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

        // Create a test warning
        $testWarning = [];
        $testWarning[0] = new Warning();
        $testWarning[0]->setUserID($testUser[0]->getID());
        $testWarning[0]->setAdminID($testAdmin[0]->getID());
        $testWarning[0]->setReason('testReason');
        $testWarning[0]->setTimestamp(123);
        $testWarning[0]->create();

        $testWarning[1] = new Warning();
        $testWarning[1]->setUserID($testUser[1]->getID());
        $testWarning[1]->setAdminID($testAdmin[1]->getID());
        $testWarning[1]->setReason('testReason2');
        $testWarning[1]->setTimestamp(12345);
        $testWarning[1]->create();

        // Select and check a single
        $selectedSingle = Warning::getByAdminID($testAdmin[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(Warning::class, $selectedSingle[0]);
        $this->assertEquals($testWarning[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testWarning[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testWarning[0]->getAdminID(), $selectedSingle[0]->getAdmin());
        $this->assertEquals($testWarning[0]->getReason(), $selectedSingle[0]->getReason());
        $this->assertEquals($testWarning[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Clean up
        foreach($testWarning as $warning) {
            $warning->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testAdmin as $admin) {
            $admin->delete();
        }
    }

    public function testSetUserID() {
        //TODO: Implement
    }

    public function testInvalidUserSetUserID() {
        //TODO: Implement
    }

    public function testSetAdminID() {
        //TODO: Implement
    }

    public function testInvalidUserSetAdminID() {
        //TODO: Implement
    }

}
