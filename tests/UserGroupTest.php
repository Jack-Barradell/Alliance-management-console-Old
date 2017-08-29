<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Award.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Group;
use AMC\Classes\User;
use AMC\Classes\UserGroup;
use AMC\Classes\Database;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class UserGroupTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('jirbj.co.uk', 'testingDB', 'testingDB', 'testingdb');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check the null constructor
        $testUserGroup = new UserGroup();

        $this->assertNull($testUserGroup->getID());
        $this->assertNull($testUserGroup->getUserID());
        $this->assertNull($testUserGroup->getGroupID());
        $this->assertNull($testUserGroup->getAdmin());

        // Check the non null constructor
        $testUserGroup = new UserGroup(1,2,3, false);

        $this->assertEquals(1, $testUserGroup->getID());
        $this->assertEquals(2, $testUserGroup->getUserID());
        $this->assertEquals(3, $testUserGroup->getGroupID());
        $this->assertFalse($testUserGroup->getAdmin());
    }

    public function testCreate() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test Group
        $testGroup = new Group();
        $testGroup->setName('testGroup');
        $testGroup->create();

        // Now create a test user group
        $testUserGroup = new UserGroup();
        $testUserGroup->setUserID($testUser->getID());
        $testUserGroup->setGroupID($testGroup->getID());
        $testUserGroup->setAdmin(false);
        $testUserGroup->create();

        // Now check id is an int
        $this->assertInternalType('int', $testUserGroup->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `UserGroupID`,`UserID`,`GroupID`,`UserGroupAdmin` FROM `User_Groups` WHERE `UserGroupID` WHERE `UserGroupID`=?");
        $stmt->bind_param('i', $testUserGroup->getID());
        $stmt->execute();
        $stmt->bind_result($userGroupID, $userID, $groupID, $admin);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        if($admin == 1) {
            $admin = true;
        }
        else {
            $admin = false;
        }

        $this->assertEquals($testUserGroup->getID(), $userGroupID);
        $this->assertEquals($testUserGroup->getUserID(), $userGroupID);
        $this->assertEquals($testUserGroup->getGroupID(), $groupID);
        $this->assertEquals($testUserGroup->getAdmin(), $admin);

        $stmt->close();

        // Clean up
        $testUserGroup->delete();
        $testUser->delete();
        $testGroup->delete();
    }

    public function testBlankCreate() {
        // Create a test user group
        $testUserGroup = new UserGroup();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testUserGroup->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank User Group.', $e->getMessage());
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

        // Create a test Group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('testGroup');
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('testGroup2');
        $testGroup[1]->create();

        // Now create a test user group
        $testUserGroup = new UserGroup();
        $testUserGroup->setUserID($testUser[0]->getID());
        $testUserGroup->setGroupID($testGroup[0]->getID());
        $testUserGroup->setAdmin(false);
        $testUserGroup->create();

        // Now update it
        $testUserGroup->setUserID($testUser[1]->getID());
        $testUserGroup->setGroupID($testGroup[1]->getID());
        $testUserGroup->setAdmin(true);
        $testUserGroup->create();

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `UserGroupID`,`UserID`,`GroupID`,`UserGroupAdmin` FROM `User_Groups` WHERE `UserGroupID` WHERE `UserGroupID`=?");
        $stmt->bind_param('i', $testUserGroup->getID());
        $stmt->execute();
        $stmt->bind_result($userGroupID, $userID, $groupID, $admin);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        if($admin == 1) {
            $admin = true;
        }
        else {
            $admin = false;
        }

        $this->assertEquals($testUserGroup->getID(), $userGroupID);
        $this->assertEquals($testUserGroup->getUserID(), $userGroupID);
        $this->assertEquals($testUserGroup->getGroupID(), $groupID);
        $this->assertEquals($testUserGroup->getAdmin(), $admin);

        $stmt->close();

        // Clean up
        $testUserGroup->delete();
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testGroup as $group) {
            $group->delete();
        }
    }

    public function testBlankUpdate() {
        // Create a test user group
        $testUserGroup = new UserGroup();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testUserGroup->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank User Group.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test Group
        $testGroup = new Group();
        $testGroup->setName('testGroup');
        $testGroup->create();

        // Now create a test user group
        $testUserGroup = new UserGroup();
        $testUserGroup->setUserID($testUser->getID());
        $testUserGroup->setGroupID($testGroup->getID());
        $testUserGroup->setAdmin(false);
        $testUserGroup->create();

        // Store the id
        $id = $testUserGroup->getID();

        // Now delete it
        $testUserGroup->delete();

        // Check id is now null
        $this->assertNull($testUserGroup->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `UserGroupID`,`UserID`,`GroupID`,`UserGroupAdmin` FROM `User_Groups` WHERE `UserGroupID` WHERE `UserGroupID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testGroup->delete();
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

        // Create a test Group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('testGroup');
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('testGroup2');
        $testGroup[1]->create();

        // Now create a test user group
        $testUserGroup = [];
        $testUserGroup[0] = new UserGroup();
        $testUserGroup[0]->setUserID($testUser[0]->getID());
        $testUserGroup[0]->setGroupID($testGroup[0]->getID());
        $testUserGroup[0]->setAdmin(false);
        $testUserGroup[0]->create();

        $testUserGroup[1] = new UserGroup();
        $testUserGroup[1]->setUserID($testUser[1]->getID());
        $testUserGroup[1]->setGroupID($testGroup[1]->getID());
        $testUserGroup[1]->setAdmin(true);
        $testUserGroup[1]->create();

        $testUserGroup[2] = new UserGroup();
        $testUserGroup[2]->setUserID($testUser[0]->getID());
        $testUserGroup[2]->setGroupID($testGroup[1]->getID());
        $testUserGroup[2]->setAdmin(false);
        $testUserGroup[2]->create();

        // Select and check a single
        $selectedSingle = UserGroup::select(array($testUserGroup[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserGroup::class, $selectedSingle[0]);
        $this->assertEquals($testUserGroup[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserGroup[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserGroup[0]->getGroupID(), $selectedSingle[0]->getGroupID());
        $this->assertEquals($testUserGroup[0]->getAdmin(), $selectedSingle[0]->getAdmin());

        // Select and check multiple
        $selectedMultiple = UserGroup::select(array($testUserGroup[1]->getID(), $testUserGroup[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(1, \count($selectedMultiple));
        $this->assertInstanceOf(UserGroup::class, $selectedMultiple[0]);
        $this->assertInstanceOf(UserGroup::class, $selectedMultiple[1]);

        if($testUserGroup[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testUserGroup[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testUserGroup[1]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testUserGroup[1]->getGroupID(), $selectedMultiple[$i]->getGroupID());
        $this->assertEquals($testUserGroup[1]->getAdmin(), $selectedMultiple[$i]->getAdmin());

        $this->assertEquals($testUserGroup[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testUserGroup[2]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testUserGroup[2]->getGroupID(), $selectedMultiple[$j]->getGroupID());
        $this->assertEquals($testUserGroup[2]->getAdmin(), $selectedMultiple[$j]->getAdmin());

        // Clean up
        foreach($testUserGroup as $userGroup) {
            $userGroup->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testGroup as $group) {
            $group->delete();
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

        // Create a test Group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('testGroup');
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('testGroup2');
        $testGroup[1]->create();

        // Now create a test user group
        $testUserGroup = [];
        $testUserGroup[0] = new UserGroup();
        $testUserGroup[0]->setUserID($testUser[0]->getID());
        $testUserGroup[0]->setGroupID($testGroup[0]->getID());
        $testUserGroup[0]->setAdmin(false);
        $testUserGroup[0]->create();

        $testUserGroup[1] = new UserGroup();
        $testUserGroup[1]->setUserID($testUser[1]->getID());
        $testUserGroup[1]->setGroupID($testGroup[1]->getID());
        $testUserGroup[1]->setAdmin(true);
        $testUserGroup[1]->create();

        // Select and check multiple
        $selectedMultiple = UserGroup::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(1, \count($selectedMultiple));
        $this->assertInstanceOf(UserGroup::class, $selectedMultiple[0]);
        $this->assertInstanceOf(UserGroup::class, $selectedMultiple[1]);

        if($testUserGroup[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testUserGroup[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testUserGroup[0]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testUserGroup[0]->getGroupID(), $selectedMultiple[$i]->getGroupID());
        $this->assertEquals($testUserGroup[0]->getAdmin(), $selectedMultiple[$i]->getAdmin());

        $this->assertEquals($testUserGroup[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testUserGroup[1]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testUserGroup[1]->getGroupID(), $selectedMultiple[$j]->getGroupID());
        $this->assertEquals($testUserGroup[1]->getAdmin(), $selectedMultiple[$j]->getAdmin());

        // Clean up
        foreach($testUserGroup as $userGroup) {
            $userGroup->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testGroup as $group) {
            $group->delete();
        }
    }

    public function testEql() {
        // Create a test user group
        $testUserGroup = [];
        $testUserGroup[0] = new UserGroup();
        $testUserGroup[0]->setUserID(1);
        $testUserGroup[0]->setGroupID(2);
        $testUserGroup[0]->setAdmin(false);

        $testUserGroup[1] = new UserGroup();
        $testUserGroup[1]->setUserID(1);
        $testUserGroup[1]->setGroupID(2);
        $testUserGroup[1]->setAdmin(false);

        $testUserGroup[2] = new UserGroup();
        $testUserGroup[2]->setUserID(3);
        $testUserGroup[2]->setGroupID(4);
        $testUserGroup[2]->setAdmin(true);

        // Check same object is eql
        $this->assertTrue($testUserGroup[0]->eql($testUserGroup[0]));

        // Check same details are eql
        $this->assertTrue($testUserGroup[0]->eql($testUserGroup[0]));

        // Check different arent equal
        $this->assertFalse($testUserGroup[0]->eql($testUserGroup[0]));
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

        // Create a test Group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('testGroup');
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('testGroup2');
        $testGroup[1]->create();

        // Now create a test user group
        $testUserGroup = [];
        $testUserGroup[0] = new UserGroup();
        $testUserGroup[0]->setUserID($testUser[0]->getID());
        $testUserGroup[0]->setGroupID($testGroup[0]->getID());
        $testUserGroup[0]->setAdmin(false);
        $testUserGroup[0]->create();

        $testUserGroup[1] = new UserGroup();
        $testUserGroup[1]->setUserID($testUser[1]->getID());
        $testUserGroup[1]->setGroupID($testGroup[1]->getID());
        $testUserGroup[1]->setAdmin(true);
        $testUserGroup[1]->create();

        // Select and check a single
        $selectedSingle = UserGroup::getByUserID($testUser[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserGroup::class, $selectedSingle[0]);
        $this->assertEquals($testUserGroup[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserGroup[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserGroup[0]->getGroupID(), $selectedSingle[0]->getGroupID());
        $this->assertEquals($testUserGroup[0]->getAdmin(), $selectedSingle[0]->getAdmin());

        // Clean up
        foreach($testUserGroup as $userGroup) {
            $userGroup->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testGroup as $group) {
            $group->delete();
        }
    }

    public function testGetByGroupID() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test Group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('testGroup');
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('testGroup2');
        $testGroup[1]->create();

        // Now create a test user group
        $testUserGroup = [];
        $testUserGroup[0] = new UserGroup();
        $testUserGroup[0]->setUserID($testUser[0]->getID());
        $testUserGroup[0]->setGroupID($testGroup[0]->getID());
        $testUserGroup[0]->setAdmin(false);
        $testUserGroup[0]->create();

        $testUserGroup[1] = new UserGroup();
        $testUserGroup[1]->setUserID($testUser[1]->getID());
        $testUserGroup[1]->setGroupID($testGroup[1]->getID());
        $testUserGroup[1]->setAdmin(true);
        $testUserGroup[1]->create();

        // Select and check a single
        $selectedSingle = UserGroup::getByGroupID($testGroup[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserGroup::class, $selectedSingle[0]);
        $this->assertEquals($testUserGroup[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserGroup[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserGroup[0]->getGroupID(), $selectedSingle[0]->getGroupID());
        $this->assertEquals($testUserGroup[0]->getAdmin(), $selectedSingle[0]->getAdmin());

        // Clean up
        foreach($testUserGroup as $userGroup) {
            $userGroup->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testGroup as $group) {
            $group->delete();
        }
    }

}
