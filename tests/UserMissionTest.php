<?php
//TODO: Add role tests
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Mission.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Mission;
use AMC\Classes\User;
use AMC\Classes\UserMission;
use AMC\Classes\Database;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class UserMissionTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check null constructor
        $testUserMission = new UserMission();

        $this->assertNull($testUserMission->getID());
        $this->assertNull($testUserMission->getUserID());
        $this->assertNull($testUserMission->getMissionID());

        // Check non null constructor
        $testUserMission = new UserMission(1,2,3);

        $this->assertEquals(1, $testUserMission->getID());
        $this->assertEquals(2, $testUserMission->getUserID());
        $this->assertEquals(3, $testUserMission->getMissionID());
    }

    public function testCreate() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test mission
        $testMission = new Mission();
        $testMission->setTitle('testMission');
        $testMission->create();

        // Create a test user mission
        $testUserMission = new UserMission();
        $testUserMission->setUserID($testUser->getID());
        $testUserMission->setMissionID($testMission->getID());
        $testUserMission->create();

        // Check id is now an int
        $this->assertInternalType('int', $testUserMission->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `UserMissionID`,`UserID`,`MissionID` FROM `User_Missions` WHERE `UserMissionID`=?");
        $stmt->bind_param('i', $testUserMission->getID());
        $stmt->execute();
        $stmt->bind_result($userMissionID, $userID, $missionID);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testUserMission->getID(), $userMissionID);
        $this->assertEquals($testUserMission->getUserID(), $userID);
        $this->assertEquals($testUserMission->getMissionID(), $missionID);

        $stmt->close();

        // Clean up
        $testUserMission->delete();
        $testUser->delete();
        $testMission->delete();
    }

    public function testBlankCreate() {
        // Create a test user mission
        $testUserMission = new UserMission();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testUserMission->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank User Mission.', $e->getMessage());
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

        // Create a test mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('testMission');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('testMission2');
        $testMission[1]->create();

        // Create a test user mission
        $testUserMission = new UserMission();
        $testUserMission->setUserID($testUser[0]->getID());
        $testUserMission->setMissionID($testMission[0]->getID());
        $testUserMission->create();

        // Update it
        $testUserMission->setUserID($testUser[1]->getID());
        $testUserMission->setMissionID($testMission[1]->getID());
        $testUserMission->update();

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `UserMissionID`,`UserID`,`MissionID` FROM `User_Missions` WHERE `UserMissionID`=?");
        $stmt->bind_param('i', $testUserMission->getID());
        $stmt->execute();
        $stmt->bind_result($userMissionID, $userID, $missionID);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testUserMission->getID(), $userMissionID);
        $this->assertEquals($testUserMission->getUserID(), $userID);
        $this->assertEquals($testUserMission->getMissionID(), $missionID);

        $stmt->close();

        // Clean up
        $testUserMission->delete();
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testMission as $mission) {
            $mission->delete();
        }
    }

    public function testBlankUpdate() {
        // Create a test user mission
        $testUserMission = new UserMission();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testUserMission->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank User Mission.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test mission
        $testMission = new Mission();
        $testMission->setTitle('testMission');
        $testMission->create();

        // Create a test user mission
        $testUserMission = new UserMission();
        $testUserMission->setUserID($testUser->getID());
        $testUserMission->setMissionID($testMission->getID());
        $testUserMission->create();

        // Store the id
        $id = $testUserMission->getID();

        // Now delete it
        $testUserMission->delete();

        // Check id is null
        $this->assertNull($testUserMission->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `UserMissionID`,`UserID`,`MissionID` FROM `User_Missions` WHERE `UserMissionID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testUser->delete();
        $testMission->delete();
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

        // Create a test mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('testMission');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('testMission2');
        $testMission[1]->create();

        // Create a test user mission
        $testUserMission = [];
        $testUserMission[0] = new UserMission();
        $testUserMission[0]->setUserID($testUser[0]->getID());
        $testUserMission[0]->setMissionID($testMission[0]->getID());
        $testUserMission[0]->create();

        $testUserMission[1] = new UserMission();
        $testUserMission[1]->setUserID($testUser[1]->getID());
        $testUserMission[1]->setMissionID($testMission[1]->getID());
        $testUserMission[1]->create();

        $testUserMission[2] = new UserMission();
        $testUserMission[2]->setUserID($testUser[0]->getID());
        $testUserMission[2]->setMissionID($testMission[1]->getID());
        $testUserMission[2]->create();

        // Select and check a single
        $selectedSingle = UserMission::select(array($testUserMission[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserMission::class, $selectedSingle[0]);
        $this->assertEquals($testUserMission[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserMission[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserMission[0]->getMissionID(), $selectedSingle[0]->getMissionID());

        // Select and check multiple
        $selectedMultiple = UserMission::select(array($testUserMission[1]->getID(), $testUserMission[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(UserMission::class, $selectedMultiple[0]);
        $this->assertInstanceOf(UserMission::class, $selectedMultiple[1]);

        if($testUserMission[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testUserMission[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testUserMission[1]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testUserMission[1]->getMissionID(), $selectedMultiple[$i]->getMissionID());

        $this->assertEquals($testUserMission[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testUserMission[2]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testUserMission[2]->getMissionID(), $selectedMultiple[$j]->getMissionID());

        // Clean up
        foreach($testUserMission as $userMission) {
            $userMission->delete();
        }
        foreach($testMission as $mission) {
            $mission->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
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

        // Create a test mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('testMission');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('testMission2');
        $testMission[1]->create();

        // Create a test user mission
        $testUserMission = [];
        $testUserMission[0] = new UserMission();
        $testUserMission[0]->setUserID($testUser[0]->getID());
        $testUserMission[0]->setMissionID($testMission[0]->getID());
        $testUserMission[0]->create();

        $testUserMission[1] = new UserMission();
        $testUserMission[1]->setUserID($testUser[1]->getID());
        $testUserMission[1]->setMissionID($testMission[1]->getID());
        $testUserMission[1]->create();

        // Select and check multiple
        $selectedMultiple = UserMission::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(UserMission::class, $selectedMultiple[0]);
        $this->assertInstanceOf(UserMission::class, $selectedMultiple[1]);

        if($testUserMission[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testUserMission[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testUserMission[0]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testUserMission[0]->getMissionID(), $selectedMultiple[$i]->getMissionID());

        $this->assertEquals($testUserMission[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testUserMission[1]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testUserMission[1]->getMissionID(), $selectedMultiple[$j]->getMissionID());

        // Clean up
        foreach($testUserMission as $userMission) {
            $userMission->delete();
        }
        foreach($testMission as $mission) {
            $mission->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testEql() {
        // Create a test user mission
        $testUserMission = [];
        $testUserMission[0] = new UserMission();
        $testUserMission[0]->setUserID(1);
        $testUserMission[0]->setMissionID(2);

        $testUserMission[1] = new UserMission();
        $testUserMission[1]->setUserID(1);
        $testUserMission[1]->setMissionID(2);

        $testUserMission[2] = new UserMission();
        $testUserMission[2]->setUserID(3);
        $testUserMission[2]->setMissionID(4);

        // Check same object is eql
        $this->assertTrue($testUserMission[0]->eql($testUserMission[0]));

        // Check same details are eql
        $this->assertTrue($testUserMission[0]->eql($testUserMission[0]));

        // Check different arent equal
        $this->assertFalse($testUserMission[0]->eql($testUserMission[0]));
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

        // Create a test mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('testMission');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('testMission2');
        $testMission[1]->create();

        // Create a test user mission
        $testUserMission = [];
        $testUserMission[0] = new UserMission();
        $testUserMission[0]->setUserID($testUser[0]->getID());
        $testUserMission[0]->setMissionID($testMission[0]->getID());
        $testUserMission[0]->create();

        $testUserMission[1] = new UserMission();
        $testUserMission[1]->setUserID($testUser[1]->getID());
        $testUserMission[1]->setMissionID($testMission[1]->getID());
        $testUserMission[1]->create();

        // Select and check a single
        $selectedSingle = UserMission::getByUserID($testUser[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserMission::class, $selectedSingle[0]);
        $this->assertEquals($testUserMission[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserMission[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserMission[0]->getMissionID(), $selectedSingle[0]->getMissionID());

        // Clean up
        foreach($testUserMission as $userMission) {
            $userMission->delete();
        }
        foreach($testMission as $mission) {
            $mission->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testGetByMessageID() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('testMission');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('testMission2');
        $testMission[1]->create();

        // Create a test user mission
        $testUserMission = [];
        $testUserMission[0] = new UserMission();
        $testUserMission[0]->setUserID($testUser[0]->getID());
        $testUserMission[0]->setMissionID($testMission[0]->getID());
        $testUserMission[0]->create();

        $testUserMission[1] = new UserMission();
        $testUserMission[1]->setUserID($testUser[1]->getID());
        $testUserMission[1]->setMissionID($testMission[1]->getID());
        $testUserMission[1]->create();

        // Select and check a single
        $selectedSingle = UserMission::getByMissionID($testMission[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserMission::class, $selectedSingle[0]);
        $this->assertEquals($testUserMission[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserMission[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserMission[0]->getMissionID(), $selectedSingle[0]->getMissionID());

        // Clean up
        foreach($testUserMission as $userMission) {
            $userMission->delete();
        }
        foreach($testMission as $mission) {
            $mission->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testSetUserID() {
        //TODO: Implement
    }

    public function testInvalidUserSetUserID() {
        //TODO: Implement
    }

    public function testSetMissionID() {
        //TODO: Implement
    }

    public function testInvalidMissionSetMissionID() {
        //TODO: Implement
    }
}
