<?php
//TODO: Add role to tests
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/MissionGroupView.php';
require '../classes/Mission.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Mission;
use AMC\Classes\MissionUserView;
use AMC\Classes\Database;
use AMC\Classes\User;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class MissionUserViewTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Create and test null constructor
        $missionUserView = new MissionUserView();

        $this->assertTrue($missionUserView->eql(new MissionUserView()));
        $this->assertNull($missionUserView->getID());
        $this->assertNull($missionUserView->getUserID());
        $this->assertNull($missionUserView->getMissionID());

        // Create and test non null constructor
        $missionUserView = new MissionUserView(1, 2, 3);

        $this->assertFalse($missionUserView->getID());
        $this->assertEquals(1, $missionUserView->getID());
        $this->assertEquals(2, $missionUserView->getUserID());
        $this->assertEquals(3, $missionUserView->getMissionID());
    }

    public function testCreate() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('test');
        $testUser->create();

        // Create a test mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->create();

        // Create a test Mission user view
        $testMissionUserView = new MissionUserView();
        $testMissionUserView->setUserID($testUser->getID());
        $testMissionUserView->setMissionID($testMission->getID());
        $testMissionUserView->create();

        // Check id is now an int
        $this->assertInternalType('int', $testMissionUserView->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `MissionUserViewID`,`UserID`,`MissionID` FROM `Mission_User_Views` WHERE `MissionUserViewID`=?");
        $stmt->bind_param('i', $testMissionUserView->getID());
        $stmt->execute();
        $stmt->bind_result($missionUserViewID, $userID, $missionID);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testMissionUserView->getID(), $missionUserViewID);
        $this->assertEquals($testMissionUserView->getUserID(), $userID);
        $this->assertEquals($testMissionUserView->getMissionID(), $missionID);

        $stmt->close();

        // Clean up
        $testMissionUserView->delete();
        $testUser->delete();
        $testMission->delete();
    }

    public function testBlankCreate() {
        // Create a blank mission user view
        $missionUserView = new MissionUserView();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $missionUserView->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Mission User View.', $e->getMessage());
        }
    }

    public function testUpdate() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('test');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('test2');
        $testUser[1]->create();

        // Create a test mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('test');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('test2');
        $testMission[1]->create();

        // Create a test Mission user view
        $testMissionUserView = new MissionUserView();
        $testMissionUserView->setUserID($testUser[0]->getID());
        $testMissionUserView->setMissionID($testMission[0]->getID());
        $testMissionUserView->create();

        // Now update it
        $testMissionUserView->setUserID($testUser[1]->getID());
        $testMissionUserView->setMissionID($testMission[1]->getID());
        $testMissionUserView->update();

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `MissionUserViewID`,`UserID`,`MissionID` FROM `Mission_User_Views` WHERE `MissionUserViewID`=?");
        $stmt->bind_param('i', $testMissionUserView->getID());
        $stmt->execute();
        $stmt->bind_result($missionUserViewID, $userID, $missionID);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testMissionUserView->getID(), $missionUserViewID);
        $this->assertEquals($testMissionUserView->getUserID(), $userID);
        $this->assertEquals($testMissionUserView->getMissionID(), $missionID);

        $stmt->close();

        // Clean up
        $testMissionUserView->delete();
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testMission as $mission) {
            $mission->delete();
        }
    }

    public function testBlankUpdate() {
        // Create a blank mission user view
        $missionUserView = new MissionUserView();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $missionUserView->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Mission User View.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('test');
        $testUser->create();

        // Create a test mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->create();

        // Create a test Mission user view
        $testMissionUserView = new MissionUserView();
        $testMissionUserView->setUserID($testUser->getID());
        $testMissionUserView->setMissionID($testMission->getID());
        $testMissionUserView->create();

        // Store id
        $id = $testMissionUserView->getID();

        // Now delete it
        $testMissionUserView->delete();

        // Check id is null
        $this->assertNull($testMissionUserView->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `MissionUserViewID`,`UserID`,`MissionID` FROM `Mission_User_Views` WHERE `MissionUserViewID`=?");
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
        $testUser[0]->setUsername('test');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('test2');
        $testUser[1]->create();

        // Create a test mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('test');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('test2');
        $testMission[1]->create();

        // Create a test Mission user view
        $testMissionUserView = [];
        $testMissionUserView[0] = new MissionUserView();
        $testMissionUserView[0]->setUserID($testUser[0]->getID());
        $testMissionUserView[0]->setMissionID($testMission[0]->getID());
        $testMissionUserView[0]->create();

        $testMissionUserView[1] = new MissionUserView();
        $testMissionUserView[1]->setUserID($testUser[1]->getID());
        $testMissionUserView[1]->setMissionID($testMission[1]->getID());
        $testMissionUserView[1]->create();

        $testMissionUserView[2] = new MissionUserView();
        $testMissionUserView[2]->setUserID($testUser[0]->getID());
        $testMissionUserView[2]->setMissionID($testMission[0]->getID());
        $testMissionUserView[2]->create();

        // Now select and check a single
        $selectedSingle = MissionUserView::select(array($testMissionUserView[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInternalType(MissionUserView::class, $selectedSingle[0]);

        $this->assertEquals($testMissionUserView[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testMissionUserView[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testMissionUserView[0]->getMissionID(), $selectedSingle[0]->getMissionID());

        // Now get and check multiple
        $selectedMultiple = MissionUserView::select(array($testMissionUserView[1]->getID(), $testMissionUserView[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInternalType(MissionUserView::class, $selectedMultiple[0]);
        $this->assertInternalType(MissionUserView::class, $selectedMultiple[0]);

        if($testMissionUserView[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testMissionUserView[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testMissionUserView[1]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testMissionUserView[1]->getMissionID(), $selectedMultiple[$i]->getMissionID());

        $this->assertEquals($testMissionUserView[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testMissionUserView[2]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testMissionUserView[2]->getMissionID(), $selectedMultiple[$j]->getMissionID());

        // Clean up
        foreach ($testMissionUserView as $missionUserView) {
            $missionUserView->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testMission as $mission) {
            $mission->delete();
        }
    }

    public function testSelectAll() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('test');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('test2');
        $testUser[1]->create();

        // Create a test mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('test');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('test2');
        $testMission[1]->create();

        // Create a test Mission user view
        $testMissionUserView = [];
        $testMissionUserView[0] = new MissionUserView();
        $testMissionUserView[0]->setUserID($testUser[0]->getID());
        $testMissionUserView[0]->setMissionID($testMission[0]->getID());
        $testMissionUserView[0]->create();

        $testMissionUserView[1] = new MissionUserView();
        $testMissionUserView[1]->setUserID($testUser[1]->getID());
        $testMissionUserView[1]->setMissionID($testMission[1]->getID());
        $testMissionUserView[1]->create();

        // Now get and check multiple
        $selectedMultiple = MissionUserView::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInternalType(MissionUserView::class, $selectedMultiple[0]);
        $this->assertInternalType(MissionUserView::class, $selectedMultiple[0]);

        if($testMissionUserView[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testMissionUserView[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testMissionUserView[0]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testMissionUserView[0]->getMissionID(), $selectedMultiple[$i]->getMissionID());

        $this->assertEquals($testMissionUserView[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testMissionUserView[1]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testMissionUserView[1]->getMissionID(), $selectedMultiple[$j]->getMissionID());

        // Clean up
        foreach ($testMissionUserView as $missionUserView) {
            $missionUserView->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testMission as $mission) {
            $mission->delete();
        }
    }

    public function testEql() {
        // Create a test Mission user view
        $testMissionUserView = [];
        $testMissionUserView[0] = new MissionUserView();
        $testMissionUserView[0]->setUserID(1);
        $testMissionUserView[0]->setMissionID(2);

        $testMissionUserView[1] = new MissionUserView();
        $testMissionUserView[1]->setUserID(1);
        $testMissionUserView[1]->setMissionID(2);

        $testMissionUserView[2] = new MissionUserView();
        $testMissionUserView[2]->setUserID(3);
        $testMissionUserView[2]->setMissionID(4);

        // Check same object is eql
        $this->assertTrue($testMissionUserView[0]->eql($testMissionUserView[0]));

        // Check same details are eql
        $this->assertTrue($testMissionUserView[0]->eql($testMissionUserView[1]));

        // Check different arent equal
        $this->assertFalse($testMissionUserView[0]->eql($testMissionUserView[2]));
    }

    public function testGetByUserID() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('test');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('test2');
        $testUser[1]->create();

        // Create a test mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('test');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('test2');
        $testMission[1]->create();

        // Create a test Mission user view
        $testMissionUserView = [];
        $testMissionUserView[0] = new MissionUserView();
        $testMissionUserView[0]->setUserID($testUser[0]->getID());
        $testMissionUserView[0]->setMissionID($testMission[0]->getID());
        $testMissionUserView[0]->create();

        $testMissionUserView[1] = new MissionUserView();
        $testMissionUserView[1]->setUserID($testUser[1]->getID());
        $testMissionUserView[1]->setMissionID($testMission[1]->getID());
        $testMissionUserView[1]->create();

        // Now pull by user id
        $selectedSingle = MissionUserView::getByUserID($testUser[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInternalType(MissionUserView::class, $selectedSingle[0]);

        $this->assertEquals($testMissionUserView[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testMissionUserView[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testMissionUserView[0]->getMissionID(), $selectedSingle[0]->getMissionID());

        // Clean up
        foreach ($testMissionUserView as $missionUserView) {
            $missionUserView->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testMission as $mission) {
            $mission->delete();
        }
    }

    public function testGetByMissionID() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('test');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('test2');
        $testUser[1]->create();

        // Create a test mission
        $testMission = [];
        $testMission[0] = new Mission();
        $testMission[0]->setTitle('test');
        $testMission[0]->create();

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('test2');
        $testMission[1]->create();

        // Create a test Mission user view
        $testMissionUserView = [];
        $testMissionUserView[0] = new MissionUserView();
        $testMissionUserView[0]->setUserID($testUser[0]->getID());
        $testMissionUserView[0]->setMissionID($testMission[0]->getID());
        $testMissionUserView[0]->create();

        $testMissionUserView[1] = new MissionUserView();
        $testMissionUserView[1]->setUserID($testUser[1]->getID());
        $testMissionUserView[1]->setMissionID($testMission[1]->getID());
        $testMissionUserView[1]->create();

        // Now pull by user id
        $selectedSingle = MissionUserView::getByMissionID($testMission[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInternalType(MissionUserView::class, $selectedSingle[0]);

        $this->assertEquals($testMissionUserView[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testMissionUserView[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testMissionUserView[0]->getMissionID(), $selectedSingle[0]->getMissionID());

        // Clean up
        foreach ($testMissionUserView as $missionUserView) {
            $missionUserView->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testMission as $mission) {
            $mission->delete();
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
