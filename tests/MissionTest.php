<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Mission.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Database;
use AMC\Classes\Group;
use AMC\Classes\Mission;
use AMC\Classes\MissionGroupView;
use AMC\Classes\MissionUserView;
use AMC\Classes\User;
use AMC\Classes\UserMission;
use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\DuplicateEntryException;
use AMC\Exceptions\InvalidGroupException;
use AMC\Exceptions\InvalidUserException;
use AMC\Exceptions\MissingPrerequisiteException;
use PHPUnit\Framework\TestCase;

class MissionTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('jirbj.co.uk', 'testingDB', 'testingDB', 'testingdb');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Create and test null constructor
        $mission = new Mission();

        $this->assertTrue($mission->eql(new Mission()));
        $this->assertNull($mission->getID());
        $this->assertNull($mission->getTitle());
        $this->assertNull($mission->getDescription());
        $this->assertNull($mission->getStatus());

        // Create and test non null constructor
        $mission = new Mission(1, 'test', 'description', 'Completed');

        $this->assertFalse($mission->eql(new Mission()));
        $this->assertEquals(1, $mission->getID());
        $this->assertEquals('test', $mission->getTitle());
        $this->assertEquals('description', $mission->getDescription());
        $this->assertEquals('Completed', $mission->getStatus());
    }

    public function testCreate() {
        // Create a test mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->setDescription('testDesc');
        $testMission->setStatus('Completed');
        $testMission->create();

        // Check id is a number now
        $this->assertInternalType('int', $testMission->getID());

        // Pull from id
        $stmt = $this->_connection->prepare("SELECT `MissionID`,`MissionTitle`,`MissionDescription`,`MissionStatus` FROM `Missions` WHERE `MissionID`=?");
        $stmt->bind_param('i', $testMission->getID());
        $stmt->execute();
        $stmt->bind_param($missionID, $title, $description, $status);

        // Check that there is one result
        $this->assertEquals(1, $stmt->num_rows);

        // Check it
        $this->assertEquals($testMission->getID(), $missionID);
        $this->assertEquals($testMission->getTitle(), $testMission);
        $this->assertEquals($testMission->getDescription(),$description);
        $this->assertEquals($testMission->getStatus(), $status);

        $stmt->close();

        // Clean up
        $testMission->delete();
    }

    public function testBlankCreate() {
        // Create a blank mission
        $mission = new Mission();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $mission->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Mission.', $e->getMessage());
        }
    }

    public function testUpdate() {
        // Create a test mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->setDescription('testDesc');
        $testMission->setStatus('Completed');
        $testMission->create();

        // Now update it
        $testMission->setTitle('test2');
        $testMission->setDescription('testDesc2');
        $testMission->setStatus('Completed2');
        $testMission->update();

        // Pull from id
        $stmt = $this->_connection->prepare("SELECT `MissionID`,`MissionTitle`,`MissionDescription`,`MissionStatus` FROM `Missions` WHERE `MissionID`=?");
        $stmt->bind_param('i', $testMission->getID());
        $stmt->execute();
        $stmt->bind_param($missionID, $title, $description, $status);

        // Check that there is one result
        $this->assertEquals(1, $stmt->num_rows);

        // Check it
        $this->assertEquals($testMission->getID(), $missionID);
        $this->assertEquals($testMission->getTitle(), $testMission);
        $this->assertEquals($testMission->getDescription(),$description);
        $this->assertEquals($testMission->getStatus(), $status);

        $stmt->close();

        // Clean up
        $testMission->delete();
    }

    public function testBlankUpdate() {
        // Create a blank mission
        $mission = new Mission();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $mission->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Mission.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->setDescription('testDesc');
        $testMission->setStatus('Completed');
        $testMission->create();

        // store the id
        $id = $testMission->getID();

        // Now delete it
        $testMission->delete();

        // Check id is now null
        $this->assertNull($testMission->getID());

        // Pull from id
        $stmt = $this->_connection->prepare("SELECT `MissionID`,`MissionTitle`,`MissionDescription`,`MissionStatus` FROM `Missions` WHERE `MissionID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check that there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();
    }

    public function testSelectWithInput() {
        // Create a test mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('test');
        $testMission[0]->setDescription('testDesc');
        $testMission[0]->setStatus('Completed');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('test2');
        $testMission[1]->setDescription('testDesc2');
        $testMission[1]->setStatus('Completed2');
        $testMission[1]->create();

        $testMission[2] = new Mission();
        $testMission[2]->setTitle('test3');
        $testMission[2]->setDescription('testDesc3');
        $testMission[2]->setStatus('Completed3');
        $testMission[2]->create();

        // Now get and check a single
        $selectedSingle = Mission::select(array($testMission[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(Mission::class, $selectedSingle[0]);
        $this->assertEquals($testMission[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testMission[0]->getTitle(), $selectedSingle[0]->getTitle());
        $this->assertEquals($testMission[0]->getDescription(), $selectedSingle[0]->getDescription());
        $this->assertEquals($testMission[0]->getStatus(), $selectedSingle[0]->getStatus());

        // Now get and check multiple
        $selectedMultiple = Mission::select(array($testMission[1]->getID(), $testMission[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(Mission::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Mission::class, $selectedMultiple[1]);

        if($testMission[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testMission[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testMission[1]->getTitle(), $selectedMultiple[$i]->getTitle());
        $this->assertEquals($testMission[1]->getDescription(), $selectedMultiple[$i]->getDescription());
        $this->assertEquals($testMission[1]->getStatus(), $selectedMultiple[$i]->getStatus());

        $this->assertEquals($testMission[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testMission[2]->getTitle(), $selectedMultiple[$j]->getTitle());
        $this->assertEquals($testMission[2]->getDescription(), $selectedMultiple[$j]->getDescription());
        $this->assertEquals($testMission[2]->getStatus(), $selectedMultiple[$j]->getStatus());

        // Clean up
        foreach($testMission as $mission) {
            $mission->delete();
        }
    }

    public function testSelectAll() {
        // Create a test mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('test');
        $testMission[0]->setDescription('testDesc');
        $testMission[0]->setStatus('Completed');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('test2');
        $testMission[1]->setDescription('testDesc2');
        $testMission[1]->setStatus('Completed2');
        $testMission[1]->create();

        // Now get and check multiple
        $selectedMultiple = Mission::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(Mission::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Mission::class, $selectedMultiple[1]);

        if($testMission[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testMission[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testMission[0]->getTitle(), $selectedMultiple[$i]->getTitle());
        $this->assertEquals($testMission[0]->getDescription(), $selectedMultiple[$i]->getDescription());
        $this->assertEquals($testMission[0]->getStatus(), $selectedMultiple[$i]->getStatus());

        $this->assertEquals($testMission[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testMission[1]->getTitle(), $selectedMultiple[$j]->getTitle());
        $this->assertEquals($testMission[1]->getDescription(), $selectedMultiple[$j]->getDescription());
        $this->assertEquals($testMission[1]->getStatus(), $selectedMultiple[$j]->getStatus());

        // Clean up
        foreach($testMission as $mission) {
            $mission->delete();
        }
    }

    public function testEql() {
        // Create a test mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setID(1);
        $testMission[0]->setTitle('test');
        $testMission[0]->setDescription('testDesc');
        $testMission[0]->setStatus('Completed');

        $testMission[1] = new Mission();
        $testMission[1]->setID(1);
        $testMission[1]->setTitle('test');
        $testMission[1]->setDescription('testDesc');
        $testMission[1]->setStatus('Completed');

        $testMission[2] = new Mission();
        $testMission[2]->setID(2);
        $testMission[2]->setTitle('test2');
        $testMission[2]->setDescription('testDesc2');
        $testMission[2]->setStatus('Completed2');

        // Check same object is eql
        $this->assertTrue($testMission[0]->eql($testMission[0]));

        // Check same details are eql
        $this->assertTrue($testMission[0]->eql($testMission[1]));

        // Check different arent equal
        $this->assertFalse($testMission[0]->eql($testMission[2]));
    }

    public function testGetByStatus() {
        // Create a test mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('test');
        $testMission[0]->setDescription('testDesc');
        $testMission[0]->setStatus('Completed');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('test2');
        $testMission[1]->setDescription('testDesc2');
        $testMission[1]->setStatus('Completed2');
        $testMission[1]->create();

        // Now select
        $selected = Mission::getByStatus('Completed');

        $this->assertTrue(\is_array($selected));
        $this->assertEquals(1, \count($selected));
        $this->assertInstanceOf(Mission::class, $selected[0]);
        $this->assertEquals($testMission[0]->getID(), $selected[0]->getID());
        $this->assertEquals($testMission[0]->getTitle(), $selected[0]->getTitle());
        $this->assertEquals($testMission[0]->getDescription(), $selected[0]->getDescription());
        $this->assertEquals($testMission[0]->getStatus(), $selected[0]->getStatus());

        // Clean up
        foreach($testMission as $mission) {
            $mission->delete();
        }
    }

    public function testIssueToUser() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->setDescription('testDesc');
        $testMission->setStatus('Started');
        $testMission->create();

        // Issue it to the user
        $testMission->issueToUser($testUser->getID());

        // Check
        $userMissions = UserMission::getByMissionID($testMission->getID());

        $this->assertTrue(\is_array($userMissions));
        $this->assertEquals(1, \count($userMissions));
        $this->assertInternalType(UserMission::class, $userMissions[0]);
        $this->assertEquals($testUser->getID(), $userMissions[0]->getUserID());
        $this->assertEquals($testMission->getID(), $userMissions[0]->getMissionID());

        // Clean up
        foreach($userMissions as $userMission) {
            $userMission->delete();
        }
        $testMission->delete();
        $testUser->delete();
    }

    public function testDuplicateEntryIssueToUser() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->setDescription('testDesc');
        $testMission->setStatus('Started');
        $testMission->create();

        // Issue it to the user
        $testMission->issueToUser($testUser->getID());

        // Set expected exception
        $this->expectException(DuplicateEntryException::class);

        // Trigger it
        try {
            $testMission->issueToUser($testUser->getID());
        } catch(DuplicateEntryException $e) {
            $this->assertEquals('Attempted to assign user with id ' . $testUser->getID() . ' to mission mission with id ' . $testMission->getID() . ' but they were already issued.', $e->getMessage());
        } finally {
            $testMission->removeFromUser($testUser->getID());
            $testMission->delete();
            $testUser->delete();
        }
    }

    public function testInvalidUserIssueToUser() {
        // Find the largest user id
        $stmt = $this->_connection->prepare("SELECT `UserID` FROM `Users` ORDER BY `UserID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($userID);
        $stmt->fetch();
        $largestID = $userID;
        $largestID++;

        // Create a mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->setDescription('testDesc');
        $testMission->setStatus('Started');
        $testMission->create();

        // Set the expected exception
        $this->expectException(InvalidUserException::class);

        // Trigger it
        try {
            $testMission->issueToUser($largestID);
        } catch(InvalidUserException $e) {
            $this->assertEquals('There is no user with user id ' . $largestID, $e->getMessage());
        } finally {
            $testMission->delete();
        }
    }

    public function testRemoveFromUser() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->setDescription('testDesc');
        $testMission->setStatus('Started');
        $testMission->create();

        // Issue it to the user
        $testMission->issueToUser($testUser->getID());

        // Check the user has the mission
        $this->assertTrue($testMission->userIsAssigned($testUser->getID()));

        // Now remove them
        $testMission->removeFromUser($testUser->getID());

        // Check they are no longer on the mission
        $this->assertFalse($testMission->userIsAssigned($testUser->getID()));

        $userMissions = UserMission::getByMissionID($testMission->getID());
        $this->assertNull($userMissions);

        // Clean up
        $testMission->delete();
        $testUser->delete();
    }

    public function testMissingPrerequisiteRemoveFromUser() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->setDescription('testDesc');
        $testMission->setStatus('Started');
        $testMission->create();

        // Set the expected exception
        $this->expectException(MissingPrerequisiteException::class);

        // Trigger it
        try {
            $testMission->removeFromUser($testUser->getID());
        } catch(MissingPrerequisiteException $e) {
            $this->assertEquals('Tried to unassign user with id ' . $testUser->delete() . ' from mission with id ' . $testMission->delete() . ' but they were not assigned.', $e->getMessage());
        } finally {
            $testMission->delete();
            $testUser->delete();
        }
    }

    public function testUserIsAssigned() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('test');
        $testMission[0]->setDescription('testDesc');
        $testMission[0]->setStatus('Started');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('test2');
        $testMission[1]->setDescription('testDesc2');
        $testMission[1]->setStatus('Started2');
        $testMission[1]->create();

        // Check when no missions are assigned it returns false
        $this->assertFalse($testMission[0]->userIsAssigned($testUser->getID()));
        $this->assertFalse($testMission[1]->userIsAssigned($testUser->getID()));

        // Issue one mission to the user
        $testMission[0]->issueToUser($testUser->getID());

        // Check mission 0 now returns true and mission 1 returns false
        $this->assertTrue($testMission[0]->userIsAssigned($testUser->getID()));
        $this->assertFalse($testMission[1]->userIsAssigned($testUser->getID()));

        // Issue the other mission
        $this->assertTrue($testMission[0]->userIsAssigned($testUser->getID()));
        $this->assertTrue($testMission[1]->userIsAssigned($testUser->getID()));

        // Clean up
        foreach($testMission as $mission) {
            $mission->delete();
        }
        $testUser->delete();
    }

    public function testShowToUser() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->setDescription('testDesc');
        $testMission->setStatus('Started');
        $testMission->create();

        // Issue it to the user
        $testMission->showToUser($testUser->getID());

        // Check
        $missionUserViews = MissionUserView::getByMissionID($testMission->getID());

        $this->assertTrue(\is_array($missionUserViews));
        $this->assertEquals(1, \count($missionUserViews));
        $this->assertInstanceOf(MissionUserView::class, $missionUserViews[0]);
        $this->assertEquals($testUser->getID(), $missionUserViews[0]->getUserID());
        $this->assertEquals($testMission->getID(), $missionUserViews[0]->getMissionID());

        foreach($missionUserViews as $missionUserView) {
            $missionUserView->delete();
        }
        $testMission->delete();
        $testUser->delete();
    }

    public function testDuplicateEntryShowToUser() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->setDescription('testDesc');
        $testMission->setStatus('Started');
        $testMission->create();

        // Show the mission
        $testMission->showToUser($testUser->getID());

        // Set expected exception
        $this->expectException(DuplicateEntryException::class);

        // Trigger it
        try {
            $testMission->showToUser($testUser->getID());
        } catch(DuplicateEntryException $e) {
            $this->assertEquals('User with id ' . $testUser->getID() . ' was given access to mission with id ' . $testMission->getID() . ' when they already had it.', $e->getMessage());
        } finally {
            $testMission->hideFromUser($testUser->getID());
            $testMission->delete();
            $testUser->delete();
        }
    }

    public function testInvalidUserShowToUser() {
        // Find the largest user id
        $stmt = $this->_connection->prepare("SELECT `UserID` FROM `Users` ORDER BY `UserID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($userID);
        $stmt->fetch();
        $largestID = $userID;
        $largestID++;

        // Create a mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->setDescription('testDesc');
        $testMission->setStatus('Started');
        $testMission->create();

        // Set the expected exception
        $this->expectException(InvalidUserException::class);

        // Trigger it
        try {
            $testMission->showToUser($largestID);
        } catch(InvalidUserException $e) {
            $this->assertEquals('There is no user with user id ' . $largestID, $e->getMessage());
        } finally {
            $testMission->delete();
        }
    }

    public function testHideFromUser() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->setDescription('testDesc');
        $testMission->setStatus('Started');
        $testMission->create();

        // Issue it to the user
        $testMission->showToUser($testUser->getID());

        // Check user now has it
        $this->assertTrue($testMission->userCanSee($testUser->getID()));

        // Now revoke it
        $testMission->hideFromUser($testUser->getID());

        // Now pull to check
        $missionUserView = MissionUserView::getByMissionID($testMission->getID());
        $this->assertNull($missionUserView);

        // Clean up
        $testMission->delete();
        $testUser->delete();
    }

    public function testMissingPrerequisiteHideFromUser() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->setDescription('testDesc');
        $testMission->setStatus('Started');
        $testMission->create();

        // Set expected exception
        $this->expectException(MissingPrerequisiteException::class);

        // Trigger it
        try {
            $testMission->hideFromUser($testUser->getID());
        } catch(MissingPrerequisiteException $e) {
            $this->assertEquals('User with id ' . $testUser->getID() . ' was removed from the access list of mission with id ' . $testMission->getID() . ' but they did not have access.', $e->getMessage());
        } finally {
            $testMission->delete();
            $testUser->delete();
        }
    }

    public function testUserCanSee() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('test');
        $testMission[0]->setDescription('testDesc');
        $testMission[0]->setStatus('Started');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('test2');
        $testMission[1]->setDescription('testDesc2');
        $testMission[1]->setStatus('Started2');
        $testMission[1]->create();

        $testMission[2] = new Mission();
        $testMission[2]->setTitle('test3');
        $testMission[2]->setDescription('testDesc3');
        $testMission[2]->setStatus('Started3');
        $testMission[2]->create();

        // Check the user cannot see either mission (so has no visible missions)
        $this->assertFalse($testMission[0]->userCanSee($testUser->getID()));
        $this->assertFalse($testMission[1]->userCanSee($testUser->getID()));
        $this->assertFalse($testMission[2]->userCanSee($testUser->getID()));

        // Let the user see mission 0
        $testMission[0]->showToUser($testUser->getID());

        // Check user can see mission 0
        $this->assertTrue($testMission[0]->userCanSee($testUser->getID()));
        $this->assertFalse($testMission[1]->userCanSee($testUser->getID()));
        $this->assertFalse($testMission[2]->userCanSee($testUser->getID()));

        // Let user see mission 1
        $testMission[1]->showToUser($testUser->getID());

        // Check it can see 2 missions
        $this->assertTrue($testMission[0]->userCanSee($testUser->getID()));
        $this->assertTrue($testMission[1]->userCanSee($testUser->getID()));
        $this->assertFalse($testMission[2]->userCanSee($testUser->getID()));

        // Check a user assigned to a mission can see it
        $testMission[2]->issueToUser($testUser->getID());

        // Check user can see all missions
        $this->assertTrue($testMission[0]->userCanSee($testUser->getID()));
        $this->assertTrue($testMission[1]->userCanSee($testUser->getID()));
        $this->assertTrue($testMission[2]->userCanSee($testUser->getID()));

        // Clean up
        $testMission[0]->hideFromUser($testUser->getID());
        $testMission[1]->hideFromUser($testUser->getID());
        $testMission[2]->removeFromUser($testUser->getID());
        foreach($testMission as $mission) {
            $mission->delete();
        }
        $testUser->delete();
    }

    public function testShowToGroup() {
        // Create a mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->setDescription('testDesc');
        $testMission->setStatus('Started');
        $testMission->create();

        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('testGroup');
        $testGroup->setHidden(false);
        $testGroup->create();

        // Give the group access
        $testMission->showToGroup($testGroup->getID());

        // Pull the mission group view
        $missionGroupView = MissionGroupView::getByMissionID($testMission->getID());

        // Check it
        $this->assertTrue(\is_array($missionGroupView));
        $this->assertEquals(1, \count($missionGroupView));
        $this->assertInstanceOf(MissionGroupView::class, $missionGroupView[0]);
        $this->assertEquals($testMission->getID(), $missionGroupView[0]->getMissionID());
        $this->assertEquals($testGroup->getID(), $missionGroupView[0]->getGroupID());

        // Clean up
        $testMission->hideFromGroup($testGroup->getID());
        $testGroup->delete();
        $testMission->delete();
    }

    public function testDuplicateEntryShowToGroup() {
        // Create a mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->setDescription('testDesc');
        $testMission->setStatus('Started');
        $testMission->create();

        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('testGroup');
        $testGroup->setHidden(false);
        $testGroup->create();

        // Give the group access
        $testMission->showToGroup($testGroup->getID());

        // Set expected exception
        $this->expectException(DuplicateEntryException::class);

        // Trigger it
        try {
            $testMission->showToGroup($testGroup->getID());
        } catch(DuplicateEntryException $e) {
            $this->assertEquals('Group with id ' . $testGroup->getID() . ' was given access to mission with id ' . $testMission->getID() . ' but they are already had it.', $e->getMessage());
        } finally {
            $testMission->hideFromGroup($testGroup->getID());
            $testGroup->delete();
            $testMission->delete();
        }
    }

    public function testInvalidGroupShowToGroup() {
        // Find the largest group id
        $stmt = $this->_connection->prepare("SELECT `GroupID` FROM `Group` ORDER BY `GroupID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($groupID);
        $stmt->fetch();
        $largestID = $groupID;
        $largestID++;

        // Create a mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->setDescription('testDesc');
        $testMission->setStatus('Started');
        $testMission->create();

        // Set expected exception
        $this->expectException(InvalidGroupException::class);

        // Trigger it
        try {
            $testMission->showToGroup($largestID);
        } catch(InvalidGroupException $e) {
            $this->assertEquals('There is no group with group id ' . $largestID, $e->getMessage());
        } finally {
            $testMission->delete();
        }
    }

    public function testHideFromGroup() {
        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('testGroup');
        $testGroup->setHidden(false);
        $testGroup->create();

        // Create a mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->setDescription('testDesc');
        $testMission->setStatus('Started');
        $testMission->create();

        // Issue it the group
        $testMission->showToGroup($testGroup->getID());

        // Check group now has it
        $this->assertTrue($testMission->groupCanSee($testGroup->getID()));

        // Now revoke it
        $testMission->hideFromGroup($testGroup->getID());

        // Now pull and check
        $missionGroupView = MissionGroupView::getByMissionID($testGroup->getID());
        $this->assertNull($missionGroupView);

        // Clean up
        $testMission->delete();
        $testGroup->delete();
    }

    public function testMissingPrerequisiteHideFromGroup() {
        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('testGroup');
        $testGroup->setHidden(false);
        $testGroup->create();

        // Create a mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->setDescription('testDesc');
        $testMission->setStatus('Started');
        $testMission->create();

        // Set the expected exception
        $this->expectException(MissingPrerequisiteException::class);

        // Trigger it
        try {
            $testMission->hideFromGroup($testGroup->getID());
        } catch(MissingPrerequisiteException $e) {
            $this->assertEquals('Group with id ' . $testGroup->getID() . ' was removed from the access list of mission with id ' . $testMission->getID() . ' but they did not have access.', $e->getMessage());
        } finally {
            $testMission->delete();
            $testGroup->delete();
        }
    }

    public function testGroupCanSee() {
        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('testGroup');
        $testGroup->setHidden(false);
        $testGroup->create();

        // Create a mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('test');
        $testMission[0]->setDescription('testDesc');
        $testMission[0]->setStatus('Started');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('test2');
        $testMission[1]->setDescription('testDesc2');
        $testMission[1]->setStatus('Started2');
        $testMission[1]->create();

        // Check the group cannot see either mission (so has no visible missions)
        $this->assertFalse($testMission[0]->groupCanSee($testGroup->getID()));
        $this->assertFalse($testMission[1]->groupCanSee($testGroup->getID()));

        // Let the group see mission 0
        $testMission[0]->showToGroup($testGroup->getID());

        // Check group can see mission 0
        $this->assertTrue($testMission[0]->groupCanSee($testGroup->getID()));
        $this->assertFalse($testMission[1]->groupCanSee($testGroup->getID()));

        // Let user see mission 1
        $testMission[1]->showToGroup($testGroup->getID());

        // Check group can see both mission
        $this->assertTrue($testMission[0]->groupCanSee($testGroup->getID()));
        $this->assertTrue($testMission[1]->groupCanSee($testGroup->getID()));

        // Clean up
        $testMission[0]->hideFromGroup($testGroup->getID());
        $testMission[1]->hideFromGroup($testGroup->getID());
        foreach($testMission as $mission) {
            $mission->delete();
        }
        $testGroup->delete();
    }

    public function testUserHasAccess() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('testGroup');
        $testGroup->setHidden(false);
        $testGroup->create();

        // Create a mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('test');
        $testMission[0]->setDescription('testDesc');
        $testMission[0]->setStatus('Started');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('test2');
        $testMission[1]->setDescription('testDesc2');
        $testMission[1]->setStatus('Started2');
        $testMission[1]->create();

        $testMission[2] = new Mission();
        $testMission[2]->setTitle('test3');
        $testMission[2]->setDescription('testDesc3');
        $testMission[2]->setStatus('Started3');
        $testMission[2]->create();

        // Add the user to the group
        $testUser->addToGroup($testGroup->getID());

        // Check the user cannot access either mission
        $this->assertFalse($testMission[0]->userHasAccess($testUser->getID()));
        $this->assertFalse($testMission[1]->userHasAccess($testUser->getID()));
        $this->assertFalse($testMission[2]->userHasAccess($testUser->getID()));

        // Give the user access to mission 0
        $testMission[0]->issueToUser($testUser->getID());

        // Check the user can see mission 0
        $this->assertTrue($testMission[0]->userHasAccess($testUser->getID()));
        $this->assertFalse($testMission[1]->userHasAccess($testUser->getID()));
        $this->assertFalse($testMission[2]->userHasAccess($testUser->getID()));

        // Give the user access to mission
        $testMission[1]->showToUser($testUser->getID());

        // Check the user can see mission 1
        $this->assertTrue($testMission[0]->userHasAccess($testUser->getID()));
        $this->assertTrue($testMission[1]->userHasAccess($testUser->getID()));
        $this->assertFalse($testMission[2]->userHasAccess($testUser->getID()));

        // Give the group access to mission 2
        $testMission[2]->showToGroup($testGroup->getID());

        // Check the user can see mission 2
        $this->assertTrue($testMission[0]->userHasAccess($testUser->getID()));
        $this->assertTrue($testMission[1]->userHasAccess($testUser->getID()));
        $this->assertTrue($testMission[2]->userHasAccess($testUser->getID()));

        // Clean up
        $testMission[0]->removeFromUser($testUser->getID());
        $testMission[1]->hideFromUser($testUser->getID());
        $testMission[2]->hideFromGroup($testGroup->getID());
        $testUser->removeFromGroup($testGroup->getID());
        foreach($testMission as $mission) {
            $mission->delete();
        }
        $testGroup->delete();
        $testUser->delete();
    }

}
