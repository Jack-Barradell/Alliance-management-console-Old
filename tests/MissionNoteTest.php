<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/MissionNote.php';
require '../classes/Mission.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Mission;
use AMC\Classes\MissionNote;
use AMC\Classes\Database;
use AMC\Classes\User;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class MissionNoteTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('jirbj.co.uk', 'testingDB', 'testingDB', 'testingdb');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check the null constructor
        $missionNote = new MissionNote();

        $this->assertTrue($missionNote->eql(new MissionNote()));
        $this->assertNull($missionNote->getID());
        $this->assertNull($missionNote->getUserID());
        $this->assertNull($missionNote->getMissionID());
        $this->assertNull($missionNote->getBody());
        $this->assertNull($missionNote->getTimestamp());

        // Check non null constructor
        $missionNote = new MissionNote(1,2,3, 'test', 123);

        $this->assertFalse($missionNote->eql(new MissionNote()));
        $this->assertEquals(1, $missionNote->getID());
        $this->assertEquals(2, $missionNote->getUserID());
        $this->assertEquals(3, $missionNote->getMissionID());
        $this->assertEquals('test', $missionNote->getBody());
        $this->assertEquals(123, $missionNote->getTimestamp());
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

        // Create a test mission note
        $testMissionNote = new MissionNote();
        $testMissionNote->setUserID($testUser->getID());
        $testMissionNote->setMissionID($testMission->getID());
        $testMissionNote->setBody('test');
        $testMissionNote->setTimestamp(123);
        $testMissionNote->create();

        // Check id is now an int
        $this->assertInternalType('int', $testMissionNote->getID());

        // Now pull it and check
        $stmt = $this->_connection->prepare("SELECT `MissionNoteID`,`UserID`,`MissionID`,`MissionNoteBody`,`MissionNoteTimestamp` FROM `Mission_Notes` WHERE `MissionNoteID`=?");
        $stmt->bind_param('i', $testMissionNote->getID());
        $stmt->execute();
        $stmt->bind_result($missionNoteID, $userID, $missionID, $body, $timestamp);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testMissionNote->getID(), $missionNoteID);
        $this->assertEquals($testMissionNote->getUserID(), $userID);
        $this->assertEquals($testMissionNote->getMissionID(), $missionID);
        $this->assertEquals($testMissionNote->getBody(), $body);
        $this->assertEquals($testMissionNote->getTimestamp(), $timestamp);

        $stmt->close();

        // Clean up
        $testMissionNote->delete();
        $testUser->delete();
        $testMission->delete();
    }

    public function testBlankCreate() {
        // Create a blank mission note
        $missionNote = new MissionNote();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $missionNote->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Mission Note.', $e->getMessage());
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

        // Create a test mission note
        $testMissionNote = new MissionNote();
        $testMissionNote->setUserID($testUser[0]->getID());
        $testMissionNote->setMissionID($testMission[0]->getID());
        $testMissionNote->setBody('test');
        $testMissionNote->setTimestamp(123);
        $testMissionNote->create();

        // Now update it
        $testMissionNote->setUserID($testUser[1]->getID());
        $testMissionNote->setMissionID($testMission[1]->getID());
        $testMissionNote->setBody('test2');
        $testMissionNote->setTimestamp(12345);
        $testMissionNote->update();

        // Now pull it and check
        $stmt = $this->_connection->prepare("SELECT `MissionNoteID`,`UserID`,`MissionID`,`MissionNoteBody`,`MissionNoteTimestamp` FROM `Mission_Notes` WHERE `MissionNoteID`=?");
        $stmt->bind_param('i', $testMissionNote->getID());
        $stmt->execute();
        $stmt->bind_result($missionNoteID, $userID, $missionID, $body, $timestamp);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testMissionNote->getID(), $missionNoteID);
        $this->assertEquals($testMissionNote->getUserID(), $userID);
        $this->assertEquals($testMissionNote->getMissionID(), $missionID);
        $this->assertEquals($testMissionNote->getBody(), $body);
        $this->assertEquals($testMissionNote->getTimestamp(), $timestamp);

        $stmt->close();

        // Clean up
        $testMissionNote->delete();
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testMission as $mission) {
            $mission->delete();
        }
    }

    public function testBlankUpdate() {
        // Create a blank mission note
        $missionNote = new MissionNote();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $missionNote->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Mission Note.', $e->getMessage());
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

        // Create a test mission note
        $testMissionNote = new MissionNote();
        $testMissionNote->setUserID($testUser->getID());
        $testMissionNote->setMissionID($testMission->getID());
        $testMissionNote->setBody('test');
        $testMissionNote->setTimestamp(123);
        $testMissionNote->create();

        // Save its id
        $id = $testMissionNote->getID();

        // Now delete it
        $testMissionNote->delete();

        // Check id is now null
        $this->assertNull($testMissionNote->getID());

        // Now pull it and check
        $stmt = $this->_connection->prepare("SELECT `MissionNoteID`,`UserID`,`MissionID`,`MissionNoteBody`,`MissionNoteTimestamp` FROM `Mission_Notes` WHERE `MissionNoteID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($missionNoteID, $userID, $missionID, $body, $timestamp);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testMissionNote->getID(), $missionNoteID);
        $this->assertEquals($testMissionNote->getUserID(), $userID);
        $this->assertEquals($testMissionNote->getMissionID(), $missionID);
        $this->assertEquals($testMissionNote->getBody(), $body);
        $this->assertEquals($testMissionNote->getTimestamp(), $timestamp);

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

        // Create a test mission note
        $testMissionNote = [];
        $testMissionNote[0] = new MissionNote();
        $testMissionNote[0]->setUserID($testUser[0]->getID());
        $testMissionNote[0]->setMissionID($testMission[0]->getID());
        $testMissionNote[0]->setBody('test');
        $testMissionNote[0]->setTimestamp(123);
        $testMissionNote[0]->create();

        $testMissionNote[1] = new MissionNote();
        $testMissionNote[1]->setUserID($testUser[1]->getID());
        $testMissionNote[1]->setMissionID($testMission[1]->getID());
        $testMissionNote[1]->setBody('test2');
        $testMissionNote[1]->setTimestamp(12345);
        $testMissionNote[1]->create();

        $testMissionNote[2] = new MissionNote();
        $testMissionNote[2]->setUserID($testUser[0]->getID());
        $testMissionNote[2]->setMissionID($testMission[1]->getID());
        $testMissionNote[2]->setBody('test3');
        $testMissionNote[2]->setTimestamp(12345678);
        $testMissionNote[2]->create();

        // Get and check a single
        $selectedSingle = MissionNote::select(array($testMissionNote[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(MissionNote::class, $selectedSingle[0]);
        $this->assertEquals($testMissionNote[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testMissionNote[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testMissionNote[0]->getMissionID(), $selectedSingle[0]->getMissionID());
        $this->assertEquals($testMissionNote[0]->getBody(), $selectedSingle[0]->getBody());
        $this->assertEquals($testMissionNote[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Get and check multiple

        $selectedMultiple = MissionNote::select(array($testMissionNote[1]->getID(), $testMissionNote[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(MissionNote::class, $selectedMultiple[0]);
        $this->assertInstanceOf(MissionNote::class, $selectedMultiple[1]);

        if($testMissionNote[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testMissionNote[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testMissionNote[1]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testMissionNote[1]->getMissionID(), $selectedMultiple[$i]->getMissionID());
        $this->assertEquals($testMissionNote[1]->getBody(), $selectedMultiple[$i]->getBody());
        $this->assertEquals($testMissionNote[1]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());

        $this->assertEquals($testMissionNote[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testMissionNote[2]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testMissionNote[2]->getMissionID(), $selectedMultiple[$j]->getMissionID());
        $this->assertEquals($testMissionNote[2]->getBody(), $selectedMultiple[$j]->getBody());
        $this->assertEquals($testMissionNote[2]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());

        // Clean up
        foreach($testMissionNote as $note) {
            $note->delete();
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

        // Create a test mission note
        $testMissionNote = [];
        $testMissionNote[0] = new MissionNote();
        $testMissionNote[0]->setUserID($testUser[0]->getID());
        $testMissionNote[0]->setMissionID($testMission[0]->getID());
        $testMissionNote[0]->setBody('test');
        $testMissionNote[0]->setTimestamp(123);
        $testMissionNote[0]->create();

        $testMissionNote[1] = new MissionNote();
        $testMissionNote[1]->setUserID($testUser[1]->getID());
        $testMissionNote[1]->setMissionID($testMission[1]->getID());
        $testMissionNote[1]->setBody('test2');
        $testMissionNote[1]->setTimestamp(12345);
        $testMissionNote[1]->create();

        $selectedMultiple = MissionNote::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(MissionNote::class, $selectedMultiple[0]);
        $this->assertInstanceOf(MissionNote::class, $selectedMultiple[1]);

        if($testMissionNote[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testMissionNote[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testMissionNote[0]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testMissionNote[0]->getMissionID(), $selectedMultiple[$i]->getMissionID());
        $this->assertEquals($testMissionNote[0]->getBody(), $selectedMultiple[$i]->getBody());
        $this->assertEquals($testMissionNote[0]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());

        $this->assertEquals($testMissionNote[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testMissionNote[1]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testMissionNote[1]->getMissionID(), $selectedMultiple[$j]->getMissionID());
        $this->assertEquals($testMissionNote[1]->getBody(), $selectedMultiple[$j]->getBody());
        $this->assertEquals($testMissionNote[1]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());

        // Clean up
        foreach($testMissionNote as $note) {
            $note->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testMission as $mission) {
            $mission->delete();
        }
    }

    public function testEql() {
        // Create a test mission note
        $testMissionNote = [];
        $testMissionNote[0] = new MissionNote();
        $testMissionNote[0]->setID(1);
        $testMissionNote[0]->setUserID(1);
        $testMissionNote[0]->setMissionID(2);
        $testMissionNote[0]->setBody('test');
        $testMissionNote[0]->setTimestamp(123);

        $testMissionNote[1] = new MissionNote();
        $testMissionNote[1]->setID(1);
        $testMissionNote[1]->setUserID(1);
        $testMissionNote[1]->setMissionID(2);
        $testMissionNote[1]->setBody('test');
        $testMissionNote[1]->setTimestamp(123);

        $testMissionNote[2] = new MissionNote();
        $testMissionNote[2]->setID(2);
        $testMissionNote[2]->setUserID(3);
        $testMissionNote[2]->setMissionID(4);
        $testMissionNote[2]->setBody('test2');
        $testMissionNote[2]->setTimestamp(123456);

        // Check same object is eql
        $this->assertTrue($testMissionNote[0]->eql($testMissionNote[0]));

        // Check same details are eql
        $this->assertTrue($testMissionNote[0]->eql($testMissionNote[1]));

        // Check different arent equal
        $this->assertFalse($testMissionNote[0]->eql($testMissionNote[2]));
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

        // Create a test mission note
        $testMissionNote = [];
        $testMissionNote[0] = new MissionNote();
        $testMissionNote[0]->setUserID($testUser[0]->getID());
        $testMissionNote[0]->setMissionID($testMission[0]->getID());
        $testMissionNote[0]->setBody('test');
        $testMissionNote[0]->setTimestamp(123);
        $testMissionNote[0]->create();

        $testMissionNote[1] = new MissionNote();
        $testMissionNote[1]->setUserID($testUser[1]->getID());
        $testMissionNote[1]->setMissionID($testMission[1]->getID());
        $testMissionNote[1]->setBody('test2');
        $testMissionNote[1]->setTimestamp(12345);
        $testMissionNote[1]->create();

        // Get and check a single
        $selectedSingle = MissionNote::getByUserID($testUser[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(MissionNote::class, $selectedSingle[0]);
        $this->assertEquals($testMissionNote[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testMissionNote[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testMissionNote[0]->getMissionID(), $selectedSingle[0]->getMissionID());
        $this->assertEquals($testMissionNote[0]->getBody(), $selectedSingle[0]->getBody());
        $this->assertEquals($testMissionNote[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Clean up
        foreach($testMissionNote as $note) {
            $note->delete();
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

        // Create a test mission note
        $testMissionNote = [];
        $testMissionNote[0] = new MissionNote();
        $testMissionNote[0]->setUserID($testUser[0]->getID());
        $testMissionNote[0]->setMissionID($testMission[0]->getID());
        $testMissionNote[0]->setBody('test');
        $testMissionNote[0]->setTimestamp(123);
        $testMissionNote[0]->create();

        $testMissionNote[1] = new MissionNote();
        $testMissionNote[1]->setUserID($testUser[1]->getID());
        $testMissionNote[1]->setMissionID($testMission[1]->getID());
        $testMissionNote[1]->setBody('test2');
        $testMissionNote[1]->setTimestamp(12345);
        $testMissionNote[1]->create();

        // Get and check a single
        $selectedSingle = MissionNote::getByMissionID($testMission[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(MissionNote::class, $selectedSingle[0]);
        $this->assertEquals($testMissionNote[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testMissionNote[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testMissionNote[0]->getMissionID(), $selectedSingle[0]->getMissionID());
        $this->assertEquals($testMissionNote[0]->getBody(), $selectedSingle[0]->getBody());
        $this->assertEquals($testMissionNote[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Clean up
        foreach($testMissionNote as $note) {
            $note->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testMission as $mission) {
            $mission->delete();
        }
    }

}
