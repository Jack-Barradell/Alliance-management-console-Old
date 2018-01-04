<?php
//TODO: Add role to tests
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/MissionGroupView.php';
require '../classes/Mission.php';
require '../classes/Group.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Group;
use AMC\Classes\Mission;
use AMC\Classes\MissionGroupView;
use AMC\Classes\Database;
use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\InvalidGroupException;
use AMC\Exceptions\InvalidMissionException;
use PHPUnit\Framework\TestCase;

class MissionGroupViewTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Create and test a null constructor
        $missionGroupView = new MissionGroupView();

        $this->assertTrue($missionGroupView->eql(new MissionGroupView()));
        $this->assertNull($missionGroupView->getID());
        $this->assertNull($missionGroupView->getGroupID());
        $this->assertNull($missionGroupView->getMissionID());

        // Create and test non null
        $missionGroupView = new MissionGroupView(1 , 2, 3);

        $this->assertEquals(1, $missionGroupView->getID());
        $this->assertEquals(2, $missionGroupView->getGroupID());
        $this->assertEquals(3, $missionGroupView->getMissionID());
    }

    public function testCreate() {
        // Create a test mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->create();

        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('testGroup');
        $testGroup->create();

        // Create a test mission group view
        $testMissionGroupView = new MissionGroupView();
        $testMissionGroupView->setGroupID($testGroup->getID());
        $testMissionGroupView->setMissionID($testMission->getID());
        $testMissionGroupView->create();

        // Check id is now an int
        $this->assertInternalType('int', $testMissionGroupView->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `MissionGroupViewID`,`GroupID`,`MissionID` FROM `Mission_Group_Views` WHERE `MissionGroupViewID`=?");
        $stmt->bind_param('i', $testMissionGroupView->getID());
        $stmt->execute();
        $stmt->bind_result($missionGroupViewID, $groupID, $missionID);

        // check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testMissionGroupView->getID(), $missionGroupViewID);
        $this->assertEquals($testMissionGroupView->getGroupID(), $groupID);
        $this->assertEquals($testMissionGroupView->getMissionID(), $missionID);

        $stmt->close();

        // Clean up
        $testMissionGroupView->delete();
        $testGroup->delete();
        $testMission->delete();
    }

    public function testBlankCreate() {
        // Create a blank
        $missionGroupView = new MissionGroupView();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $missionGroupView->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Mission Group View.', $e->getMessage());
        }
    }

    public function testUpdate() {
        // Create a test mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('test');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('test2');
        $testMission[1]->create();

        // Create a test group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('testGroup');
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('testGroup2');
        $testGroup[1]->create();

        // Create a test mission group view
        $testMissionGroupView = new MissionGroupView();
        $testMissionGroupView->setGroupID($testGroup[0]->getID());
        $testMissionGroupView->setMissionID($testMission[0]->getID());
        $testMissionGroupView->create();

        // Now update it
        $testMissionGroupView->setGroupID($testGroup[1]->getID());
        $testMissionGroupView->setMissionID($testMission[1]->getID());
        $testMissionGroupView->create();

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `MissionGroupViewID`,`GroupID`,`MissionID` FROM `Mission_Group_Views` WHERE `MissionGroupViewID`=?");
        $stmt->bind_param('i', $testMissionGroupView->getID());
        $stmt->execute();
        $stmt->bind_result($missionGroupViewID, $groupID, $missionID);

        // check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testMissionGroupView->getID(), $missionGroupViewID);
        $this->assertEquals($testMissionGroupView->getGroupID(), $groupID);
        $this->assertEquals($testMissionGroupView->getMissionID(), $missionID);

        $stmt->close();

        // Clean up
        $testMissionGroupView->delete();
        foreach($testGroup as $group) {
            $group->delete();
        }
        foreach($testMission as $mission) {
            $mission->delete();
        }
    }

    public function testBlankUpdate() {
        // Create a blank
        $missionGroupView = new MissionGroupView();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $missionGroupView->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Mission Group View.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->create();

        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('testGroup');
        $testGroup->create();

        // Create a test mission group view
        $testMissionGroupView = new MissionGroupView();
        $testMissionGroupView->setGroupID($testGroup->getID());
        $testMissionGroupView->setMissionID($testMission->getID());
        $testMissionGroupView->create();

        // Save the id
        $id = $testMissionGroupView->getID();

        // Now delete it
        $testMissionGroupView->delete();

        // Check id is now null
        $this->assertNull($testMissionGroupView->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `MissionGroupViewID`,`GroupID`,`MissionID` FROM `Mission_Group_Views` WHERE `MissionGroupViewID`=?");
        $stmt->bind_param('i', $testMissionGroupView->getID());
        $stmt->execute();
        $stmt->bind_result($missionGroupViewID, $groupID, $missionID);

        // check there are no results
        $this->assertEquals(0, $stmt->num_rows);
        $stmt->close();

        // Clean up
        $testGroup->delete();
        $testMission->delete();
    }

    public function testSelectWithInput() {
        // Create a test mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('test');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('test2');
        $testMission[1]->create();

        // Create a test group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('testGroup');
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('testGroup2');
        $testGroup[1]->create();

        // Create a test mission group view
        $testMissionGroupView = [];
        $testMissionGroupView[0] = new MissionGroupView();
        $testMissionGroupView[0]->setGroupID($testGroup[0]->getID());
        $testMissionGroupView[0]->setMissionID($testMission[0]->getID());
        $testMissionGroupView[0]->create();

        $testMissionGroupView[1] = new MissionGroupView();
        $testMissionGroupView[1]->setGroupID($testGroup[1]->getID());
        $testMissionGroupView[1]->setMissionID($testMission[1]->getID());
        $testMissionGroupView[1]->create();

        $testMissionGroupView[2] = new MissionGroupView();
        $testMissionGroupView[2]->setGroupID($testGroup[0]->getID());
        $testMissionGroupView[2]->setMissionID($testMission[1]->getID());
        $testMissionGroupView[2]->create();

        // Now select and check a single
        $selectedSingle = MissionGroupView::select(array($testMissionGroupView[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(MissionGroupView::class, $selectedSingle[0]);
        $this->assertEquals($testMissionGroupView[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testMissionGroupView[0]->getGroupID(), $selectedSingle[0]->getGroupID());
        $this->assertEquals($testMissionGroupView[0]->getMissionID(), $selectedSingle[0]->getMissionID());

        // Select and check multiple
        $selectedMultiple = MissionGroupView::select(array($testMissionGroupView[1]->getID(), $testMissionGroupView[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(MissionGroupView::class, $selectedMultiple[0]);
        $this->assertInstanceOf(MissionGroupView::class, $selectedMultiple[1]);

        if($testMissionGroupView[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testMissionGroupView[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testMissionGroupView[1]->getGroupID(), $selectedMultiple[$i]->getGroupID());
        $this->assertEquals($testMissionGroupView[1]->getMissionID(), $selectedMultiple[$i]->getMissionID());

        $this->assertEquals($testMissionGroupView[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testMissionGroupView[2]->getGroupID(), $selectedMultiple[$j]->getGroupID());
        $this->assertEquals($testMissionGroupView[2]->getMissionID(), $selectedMultiple[$j]->getMissionID());

        // Clean up
        foreach($testMissionGroupView as $view) {
            $view->delete();
        }
        foreach($testGroup as $group) {
            $group->delete();
        }
        foreach($testMission as $mission) {
            $mission->delete();
        }
    }

    public function testSelectAll() {
        // Create a test mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('test');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('test2');
        $testMission[1]->create();

        // Create a test group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('testGroup');
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('testGroup2');
        $testGroup[1]->create();

        // Create a test mission group view
        $testMissionGroupView = [];
        $testMissionGroupView[0] = new MissionGroupView();
        $testMissionGroupView[0]->setGroupID($testGroup[0]->getID());
        $testMissionGroupView[0]->setMissionID($testMission[0]->getID());
        $testMissionGroupView[0]->create();

        $testMissionGroupView[1] = new MissionGroupView();
        $testMissionGroupView[1]->setGroupID($testGroup[1]->getID());
        $testMissionGroupView[1]->setMissionID($testMission[1]->getID());
        $testMissionGroupView[1]->create();

        // Select and check multiple
        $selectedMultiple = MissionGroupView::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(MissionGroupView::class, $selectedMultiple[0]);
        $this->assertInstanceOf(MissionGroupView::class, $selectedMultiple[1]);

        if($testMissionGroupView[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testMissionGroupView[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testMissionGroupView[0]->getGroupID(), $selectedMultiple[$i]->getGroupID());
        $this->assertEquals($testMissionGroupView[0]->getMissionID(), $selectedMultiple[$i]->getMissionID());

        $this->assertEquals($testMissionGroupView[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testMissionGroupView[1]->getGroupID(), $selectedMultiple[$j]->getGroupID());
        $this->assertEquals($testMissionGroupView[1]->getMissionID(), $selectedMultiple[$j]->getMissionID());

        // Clean up
        foreach($testMissionGroupView as $view) {
            $view->delete();
        }
        foreach($testGroup as $group) {
            $group->delete();
        }
        foreach($testMission as $mission) {
            $mission->delete();
        }
    }

    public function testEql() {
        // Create a test mission group view
        $testMissionGroupView = [];
        $testMissionGroupView[0] = new MissionGroupView();
        $testMissionGroupView[0]->setID(1);
        $testMissionGroupView[0]->setGroupID(1);
        $testMissionGroupView[0]->setMissionID(2);

        $testMissionGroupView[1] = new MissionGroupView();
        $testMissionGroupView[1]->setID(1);
        $testMissionGroupView[1]->setGroupID(1);
        $testMissionGroupView[1]->setMissionID(2);

        $testMissionGroupView[2] = new MissionGroupView();
        $testMissionGroupView[2]->setID(2);
        $testMissionGroupView[2]->setGroupID(3);
        $testMissionGroupView[2]->setMissionID(4);

        // Check same object is eql
        $this->assertTrue($testMissionGroupView[0]->eql($testMissionGroupView[0]));

        // Check same details are eql
        $this->assertTrue($testMissionGroupView[0]->eql($testMissionGroupView[1]));

        // Check different arent equal
        $this->assertFalse($testMissionGroupView[0]->eql($testMissionGroupView[2]));
    }

    public function testGetByMissionID() {
        // Create a test mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('test');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('test2');
        $testMission[1]->create();

        // Create a test group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('testGroup');
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('testGroup2');
        $testGroup[1]->create();

        // Create a test mission group view
        $testMissionGroupView = [];
        $testMissionGroupView[0] = new MissionGroupView();
        $testMissionGroupView[0]->setGroupID($testGroup[0]->getID());
        $testMissionGroupView[0]->setMissionID($testMission[0]->getID());
        $testMissionGroupView[0]->create();

        $testMissionGroupView[1] = new MissionGroupView();
        $testMissionGroupView[1]->setGroupID($testGroup[1]->getID());
        $testMissionGroupView[1]->setMissionID($testMission[1]->getID());
        $testMissionGroupView[1]->create();

        // Now select and check a single
        $selectedSingle = MissionGroupView::getByMissionID($testMission[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(MissionGroupView::class, $selectedSingle[0]);
        $this->assertEquals($testMissionGroupView[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testMissionGroupView[0]->getGroupID(), $selectedSingle[0]->getGroupID());
        $this->assertEquals($testMissionGroupView[0]->getMissionID(), $selectedSingle[0]->getMissionID());
    }

    public function testGetByGroupID() {
        // Create a test mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('test');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('test2');
        $testMission[1]->create();

        // Create a test group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('testGroup');
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('testGroup2');
        $testGroup[1]->create();

        // Create a test mission group view
        $testMissionGroupView = [];
        $testMissionGroupView[0] = new MissionGroupView();
        $testMissionGroupView[0]->setGroupID($testGroup[0]->getID());
        $testMissionGroupView[0]->setMissionID($testMission[0]->getID());
        $testMissionGroupView[0]->create();

        $testMissionGroupView[1] = new MissionGroupView();
        $testMissionGroupView[1]->setGroupID($testGroup[1]->getID());
        $testMissionGroupView[1]->setMissionID($testMission[1]->getID());
        $testMissionGroupView[1]->create();

        // Now select and check a single
        $selectedSingle = MissionGroupView::getByGroupID($testGroup[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(MissionGroupView::class, $selectedSingle[0]);
        $this->assertEquals($testMissionGroupView[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testMissionGroupView[0]->getGroupID(), $selectedSingle[0]->getGroupID());
        $this->assertEquals($testMissionGroupView[0]->getMissionID(), $selectedSingle[0]->getMissionID());
    }

    public function testSetGroupID() {
        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('test');
        $testGroup->create();

        // Create a test mission group view
        $testMissionGroupView = new MissionGroupView();

        // Set the id
        try {
            $testMissionGroupView->setGroupID($testGroup->getID(), true);
            $this->assertEquals($testGroup->getID(), $testMissionGroupView->getGroupID());
        } finally {
            $testGroup->delete();
        }

    }

    public function testInvalidGroupSetGroupID() {
        // Get max group id and add one to it
        $stmt = Database::getConnection()->prepare("SELECT `GroupID` FROM `Groups` ORDER BY `GroupID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($groupID);
        if($stmt->fetch()) {
            $useID = $groupID + 1;
        }
        else {
            $useID = 1;
        }
        $stmt->close();

        // Create test mission group view
        $testMissionGroupView = new MissionGroupView();

        // Set expected exception
        $this->expectException(InvalidGroupException::class);

        // Trigger it
        try {
            $testMissionGroupView->setGroupID($useID, true);
        } catch (InvalidGroupException $e) {
            $this->assertEquals('No group exists with id ' . $useID, $e->getMessage());
        }
    }

    public function testSetMissionID() {
        // Create a test mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->create();

        // Create test mission group view
        $testMissionGroupView = new MissionGroupView();

        // Set the id
        try {
            $testMissionGroupView->setMissionID($testMission->getID(), true);
            $this->assertEquals($testMission->getID(), $testMissionGroupView->getMissionID());
        } finally {
            $testMission->delete();
        }
    }

    public function testInvalidMissionSetMissionID() {
        // Get max mission id and add one to it
        $stmt = Database::getConnection()->prepare("SELECT `MissionID` FROM `Missions` ORDER BY `MissionID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($missionID);
        if($stmt->fetch()) {
            $useID = $missionID + 1;
        }
        else {
            $useID = 1;
        }
        $stmt->close();

        // Create test mission group view
        $testMissionGroupView = new MissionGroupView();

        // Set expected exception
        $this->expectException(InvalidMissionException::class);

        // Trigger it
        try {
            $testMissionGroupView->setMissionID($useID, true);
        } catch (InvalidMissionException $e) {
            $this->assertEquals('There is no mission with id ' . $useID, $e->getMessage());
        }
    }

}
