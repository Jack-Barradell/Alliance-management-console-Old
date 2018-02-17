<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Privilege.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Privilege;
use AMC\Classes\User;
use AMC\Classes\UserPrivilege;
use AMC\Classes\Database;
use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\InvalidPrivilegeException;
use AMC\Exceptions\InvalidUserException;
use PHPUnit\Framework\TestCase;

class UserPrivilegeTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check the null constructor
        $testUserPrivilege = new UserPrivilege();

        $this->assertNull($testUserPrivilege->getID());
        $this->assertNull($testUserPrivilege->getUserID());
        $this->assertNull($testUserPrivilege->getPrivilegeID());

        // Check the non null constructor
        $testUserPrivilege = new UserPrivilege(1,2,3);

        $this->assertEquals(1, $testUserPrivilege->getID());
        $this->assertEquals(2, $testUserPrivilege->getUserID());
        $this->assertEquals(3, $testUserPrivilege->getPrivilegeID());
    }

    public function testCreate() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test privilege
        $testPrivilege = new Privilege();
        $testPrivilege->setName('testPrivilege');
        $testPrivilege->create();

        // Create a test user privilege
        $testUserPrivilege = new UserPrivilege();
        $testUserPrivilege->setUserID($testUser->getID());
        $testUserPrivilege->setPrivilegeID($testPrivilege->getID());
        $testUserPrivilege->create();

        // Check id is an int
        $this->assertInternalType('int', $testUserPrivilege->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `UserPrivilegeID`,`UserID`,`PrivilegeID` FROM `User_Privileges` WHERE `UserPrivilegeID`=?");
        $stmt->bind_param('i', $testUserPrivilege->getID());
        $stmt->execute();
        $stmt->bind_result($userPrivilegeID, $userID, $privilegeID);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testUserPrivilege->getID(), $userPrivilegeID);
        $this->assertEquals($testUserPrivilege->getUserID(), $userID);
        $this->assertEquals($testUserPrivilege->getPrivilegeID(), $privilegeID);

        $stmt->close();

        // Clean up
        $testUserPrivilege->delete();
        $testUser->delete();
        $testPrivilege->delete();
    }

    public function testBlankCreate() {
        // Create a test user privilege
        $testUserPrivilege = new UserPrivilege();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testUserPrivilege->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank User Privilege.', $e->getMessage());
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

        // Create a test privilege
        $testPrivilege = [];
        $testPrivilege[0] = new Privilege();
        $testPrivilege[0]->setName('testPrivilege');
        $testPrivilege[0]->create();

        $testPrivilege[1] = new Privilege();
        $testPrivilege[1]->setName('testPrivilege2');
        $testPrivilege[1]->create();

        // Create a test user privilege
        $testUserPrivilege = new UserPrivilege();
        $testUserPrivilege->setUserID($testUser[0]->getID());
        $testUserPrivilege->setPrivilegeID($testPrivilege[0]->getID());
        $testUserPrivilege->create();

        // Update it
        $testUserPrivilege->setUserID($testUser[1]->getID());
        $testUserPrivilege->setPrivilegeID($testPrivilege[1]->getID());
        $testUserPrivilege->update();

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `UserPrivilegeID`,`UserID`,`PrivilegeID` FROM `User_Privileges` WHERE `UserPrivilegeID`=?");
        $stmt->bind_param('i', $testUserPrivilege->getID());
        $stmt->execute();
        $stmt->bind_result($userPrivilegeID, $userID, $privilegeID);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testUserPrivilege->getID(), $userPrivilegeID);
        $this->assertEquals($testUserPrivilege->getUserID(), $userID);
        $this->assertEquals($testUserPrivilege->getPrivilegeID(), $privilegeID);

        $stmt->close();

        // Clean up
        $testUserPrivilege->delete();
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testPrivilege as $privilege) {
            $privilege->delete();
        }
    }

    public function testBlankUpdate() {
        // Create a test user privilege
        $testUserPrivilege = new UserPrivilege();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testUserPrivilege->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank User Privilege.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test privilege
        $testPrivilege = new Privilege();
        $testPrivilege->setName('testPrivilege');
        $testPrivilege->create();

        // Create a test user privilege
        $testUserPrivilege = new UserPrivilege();
        $testUserPrivilege->setUserID($testUser->getID());
        $testUserPrivilege->setPrivilegeID($testPrivilege->getID());
        $testUserPrivilege->create();

        // Save the id
        $id = $testUserPrivilege->getID();

        // Now delete it
        $testUserPrivilege->delete();

        // Check id is null
        $this->assertNull($testUserPrivilege->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `UserPrivilegeID`,`UserID`,`PrivilegeID` FROM `User_Privileges` WHERE `UserPrivilegeID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testUser->delete();
        $testPrivilege->delete();
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

        // Create a test privilege
        $testPrivilege = [];
        $testPrivilege[0] = new Privilege();
        $testPrivilege[0]->setName('testPrivilege');
        $testPrivilege[0]->create();

        $testPrivilege[1] = new Privilege();
        $testPrivilege[1]->setName('testPrivilege2');
        $testPrivilege[1]->create();

        // Create a test user privilege
        $testUserPrivilege = [];
        $testUserPrivilege[0] = new UserPrivilege();
        $testUserPrivilege[0]->setUserID($testUser[0]->getID());
        $testUserPrivilege[0]->setPrivilegeID($testPrivilege[0]->getID());
        $testUserPrivilege[0]->create();

        $testUserPrivilege[1] = new UserPrivilege();
        $testUserPrivilege[1]->setUserID($testUser[1]->getID());
        $testUserPrivilege[1]->setPrivilegeID($testPrivilege[1]->getID());
        $testUserPrivilege[1]->create();

        $testUserPrivilege[2] = new UserPrivilege();
        $testUserPrivilege[2]->setUserID($testUser[0]->getID());
        $testUserPrivilege[2]->setPrivilegeID($testPrivilege[1]->getID());
        $testUserPrivilege[2]->create();

        // Select and check a single
        $selectedSingle = UserPrivilege::select(array($testUserPrivilege[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserPrivilege::class, $selectedSingle[0]);
        $this->assertEquals($testUserPrivilege[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserPrivilege[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserPrivilege[0]->getPrivilegeID(), $selectedSingle[0]->getPrivilegeID());

        // Select and check multiple
        $selectedMultiple = UserPrivilege::select(array($testUserPrivilege[1]->getID(), $testUserPrivilege[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(UserPrivilege::class, $selectedMultiple[0]);
        $this->assertInstanceOf(UserPrivilege::class, $selectedMultiple[1]);

        if($testUserPrivilege[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testUserPrivilege[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testUserPrivilege[1]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testUserPrivilege[1]->getPrivilegeID(), $selectedMultiple[$i]->getPrivilegeID());

        $this->assertEquals($testUserPrivilege[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testUserPrivilege[2]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testUserPrivilege[2]->getPrivilegeID(), $selectedMultiple[$j]->getPrivilegeID());

        // Clean up
        foreach($testUserPrivilege as $userPrivilege) {
            $userPrivilege->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testPrivilege as $privilege) {
            $privilege->delete();
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

        // Create a test privilege
        $testPrivilege = [];
        $testPrivilege[0] = new Privilege();
        $testPrivilege[0]->setName('testPrivilege');
        $testPrivilege[0]->create();

        $testPrivilege[1] = new Privilege();
        $testPrivilege[1]->setName('testPrivilege2');
        $testPrivilege[1]->create();

        // Create a test user privilege
        $testUserPrivilege = [];
        $testUserPrivilege[0] = new UserPrivilege();
        $testUserPrivilege[0]->setUserID($testUser[0]->getID());
        $testUserPrivilege[0]->setPrivilegeID($testPrivilege[0]->getID());
        $testUserPrivilege[0]->create();

        $testUserPrivilege[1] = new UserPrivilege();
        $testUserPrivilege[1]->setUserID($testUser[1]->getID());
        $testUserPrivilege[1]->setPrivilegeID($testPrivilege[1]->getID());
        $testUserPrivilege[1]->create();

        // Select and check multiple
        $selectedMultiple = UserPrivilege::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(UserPrivilege::class, $selectedMultiple[0]);
        $this->assertInstanceOf(UserPrivilege::class, $selectedMultiple[1]);

        if($testUserPrivilege[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testUserPrivilege[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testUserPrivilege[0]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testUserPrivilege[0]->getPrivilegeID(), $selectedMultiple[$i]->getPrivilegeID());

        $this->assertEquals($testUserPrivilege[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testUserPrivilege[1]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testUserPrivilege[1]->getPrivilegeID(), $selectedMultiple[$j]->getPrivilegeID());

        // Clean up
        foreach($testUserPrivilege as $userPrivilege) {
            $userPrivilege->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testPrivilege as $privilege) {
            $privilege->delete();
        }
    }

    public function testEql() {
        // Create a test user privilege
        $testUserPrivilege = [];
        $testUserPrivilege[0] = new UserPrivilege();
        $testUserPrivilege[0]->setUserID(1);
        $testUserPrivilege[0]->setPrivilegeID(2);

        $testUserPrivilege[1] = new UserPrivilege();
        $testUserPrivilege[1]->setUserID(1);
        $testUserPrivilege[1]->setPrivilegeID(2);

        $testUserPrivilege[2] = new UserPrivilege();
        $testUserPrivilege[2]->setUserID(3);
        $testUserPrivilege[2]->setPrivilegeID(4);

        // Check same object is eql
        $this->assertTrue($testUserPrivilege[0]->eql($testUserPrivilege[0]));

        // Check same details are eql
        $this->assertTrue($testUserPrivilege[0]->eql($testUserPrivilege[0]));

        // Check different arent equal
        $this->assertFalse($testUserPrivilege[0]->eql($testUserPrivilege[0]));
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

        // Create a test privilege
        $testPrivilege = [];
        $testPrivilege[0] = new Privilege();
        $testPrivilege[0]->setName('testPrivilege');
        $testPrivilege[0]->create();

        $testPrivilege[1] = new Privilege();
        $testPrivilege[1]->setName('testPrivilege2');
        $testPrivilege[1]->create();

        // Create a test user privilege
        $testUserPrivilege = [];
        $testUserPrivilege[0] = new UserPrivilege();
        $testUserPrivilege[0]->setUserID($testUser[0]->getID());
        $testUserPrivilege[0]->setPrivilegeID($testPrivilege[0]->getID());
        $testUserPrivilege[0]->create();

        $testUserPrivilege[1] = new UserPrivilege();
        $testUserPrivilege[1]->setUserID($testUser[1]->getID());
        $testUserPrivilege[1]->setPrivilegeID($testPrivilege[1]->getID());
        $testUserPrivilege[1]->create();

        // Select and check a single
        $selectedSingle = UserPrivilege::getByUserID($testUser[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserPrivilege::class, $selectedSingle[0]);
        $this->assertEquals($testUserPrivilege[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserPrivilege[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserPrivilege[0]->getPrivilegeID(), $selectedSingle[0]->getPrivilegeID());

        // Clean up
        foreach($testUserPrivilege as $userPrivilege) {
            $userPrivilege->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testPrivilege as $privilege) {
            $privilege->delete();
        }
    }

    public function testGetByPrivilegeID() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test privilege
        $testPrivilege = [];
        $testPrivilege[0] = new Privilege();
        $testPrivilege[0]->setName('testPrivilege');
        $testPrivilege[0]->create();

        $testPrivilege[1] = new Privilege();
        $testPrivilege[1]->setName('testPrivilege2');
        $testPrivilege[1]->create();

        // Create a test user privilege
        $testUserPrivilege = [];
        $testUserPrivilege[0] = new UserPrivilege();
        $testUserPrivilege[0]->setUserID($testUser[0]->getID());
        $testUserPrivilege[0]->setPrivilegeID($testPrivilege[0]->getID());
        $testUserPrivilege[0]->create();

        $testUserPrivilege[1] = new UserPrivilege();
        $testUserPrivilege[1]->setUserID($testUser[1]->getID());
        $testUserPrivilege[1]->setPrivilegeID($testPrivilege[1]->getID());
        $testUserPrivilege[1]->create();

        // Select and check a single
        $selectedSingle = UserPrivilege::getByPrivilegeID($testPrivilege[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserPrivilege::class, $selectedSingle[0]);
        $this->assertEquals($testUserPrivilege[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserPrivilege[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserPrivilege[0]->getPrivilegeID(), $selectedSingle[0]->getPrivilegeID());

        // Clean up
        foreach($testUserPrivilege as $userPrivilege) {
            $userPrivilege->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testPrivilege as $privilege) {
            $privilege->delete();
        }
    }

    public function testSetUserID() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('test');
        $testUser->create();

        // Create a test user privilege
        $testUserPriv = new UserPrivilege();

        // Set the id
        try {
            $testUserPriv->setUserID($testUser->getID(), true);
            $this->assertEquals($testUser->getID(), $testUserPriv->getUserID());
        } finally {
            $testUser->delete();
        }
    }

    public function testInvalidUserSetUserID() {
        // Get max user id and add one
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

        // Create a test user priv
        $testUserPriv = new UserPrivilege();

        // Set expected exception
        $this->expectException(InvalidUserException::class);

        // Trigger it
        try {
            $testUserPriv->setUserID($useID, true);
        } catch(InvalidUserException $e) {
            $this->assertEquals('No user exists with id ' . $useID, $e->getMessage());
        }
    }

    public function testSetPrivilegeID() {
        // Create a test privilege
        $testPriv = new Privilege();
        $testPriv->setName('test');
        $testPriv->create();

        // Create test user priv
        $testUserPriv = new UserPrivilege();

        // Set the priv id
        try {
            $testUserPriv->setPrivilegeID($testPriv->getID(), true);
            $this->assertEquals($testPriv->getID(), $testUserPriv->getPrivilegeID());
        } finally {
            $testPriv->delete();
        }
    }

    public function testInvalidPrivilegeSetPrivilegeID() {
        // Get max priv id and add one to it
        $stmt = Database::getConnection()->prepare("SELECT `PrivilegeID` FROM `Privileges` ORDER BY `PrivilegeID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($privID);
        if($stmt->fetch()) {
            $useID = $privID + 1;
        }
        else {
            $useID = 1;
        }
        $stmt->close();

        // Create test user priv
        $testUserPriv = new UserPrivilege();

        // Set the expected exception
        $this->expectException(InvalidPrivilegeException::class);

        // Trigger it
        try {
            $testUserPriv->setPrivilegeID($useID, true);
        } catch(InvalidPrivilegeException $e) {
            $this->assertEquals('No privilege exists with id ' . $useID, $e->getMessage());
        }
    }

}
