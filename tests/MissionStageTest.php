<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/MissionStage.php';
require '../classes/Mission.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Mission;
use AMC\Classes\MissionStage;
use AMC\Classes\Database;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class MissionStageTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Test null constructor
        $missionStage = new MissionStage();

        $this->assertTrue($missionStage->eql(new MissionStage()));

        $this->assertNull($missionStage->getID());
        $this->assertNull($missionStage->getMissionID());
        $this->assertNull($missionStage->getName());
        $this->assertNull($missionStage->getBody());
        $this->assertNull($missionStage->getStatus());

        // Test the non null constructor
        $missionStage = new MissionStage(1,2, 'test', 'body', 'Completed');

        $this->assertFalse($missionStage->eql(new MissionStage()));

        $this->assertEquals(1, $missionStage->getID());
        $this->assertEquals(2, $missionStage->getMissionID());
        $this->assertEquals('test', $missionStage->getName());
        $this->assertEquals('body', $missionStage->getBody());
        $this->assertEquals('Completed', $missionStage->getStatus());
    }

    public function testCreate() {
        // Create a test mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->create();

        // Create a test mission stage
        $testMissionStage = new MissionStage();
        $testMissionStage->setMissionID($testMission->getID());
        $testMissionStage->setName('test');
        $testMissionStage->setBody('body');
        $testMissionStage->setStatus('Completed');
        $testMissionStage->create();

        // Now check id is an int
        $this->assertInternalType('int', $testMissionStage->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `MissionStageID`,`MissionID`,`MissionStageName`,`MissionStageBody`,`MissionStageStatus` FROM `Mission_Stages` WHERE `MissionStageID`=?");
        $stmt->bind_param('i', $testMissionStage->getID());
        $stmt->execute();
        $stmt->bind_result($missionStageID, $missionID, $name, $body, $status);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testMissionStage->getID(), $missionStageID);
        $this->assertEquals($testMissionStage->getMissionID(), $missionID);
        $this->assertEquals($testMissionStage->getName(), $name);
        $this->assertEquals($testMissionStage->getBody(), $body);
        $this->assertEquals($testMissionStage->getStatus(), $status);

        $stmt->close();

        // Clean up
        $testMissionStage->delete();
        $testMission->delete();
    }

    public function testBlankCreate() {
        // Create blank mission stage
        $missionStage = new MissionStage();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $missionStage->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Mission Stage.', $e->getMessage());
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

        // Create a test mission stage
        $testMissionStage = new MissionStage();
        $testMissionStage->setMissionID($testMission[0]->getID());
        $testMissionStage->setName('test');
        $testMissionStage->setBody('body');
        $testMissionStage->setStatus('Completed');
        $testMissionStage->create();

        // Now update it
        $testMissionStage->setMissionID($testMission[1]->getID());
        $testMissionStage->setName('test2');
        $testMissionStage->setBody('body2');
        $testMissionStage->setStatus('Completed2');
        $testMissionStage->update();

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `MissionStageID`,`MissionID`,`MissionStageName`,`MissionStageBody`,`MissionStageStatus` FROM `Mission_Stages` WHERE `MissionStageID`=?");
        $stmt->bind_param('i', $testMissionStage->getID());
        $stmt->execute();
        $stmt->bind_result($missionStageID, $missionID, $name, $body, $status);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testMissionStage->getID(), $missionStageID);
        $this->assertEquals($testMissionStage->getMissionID(), $missionID);
        $this->assertEquals($testMissionStage->getName(), $name);
        $this->assertEquals($testMissionStage->getBody(), $body);
        $this->assertEquals($testMissionStage->getStatus(), $status);

        $stmt->close();

        // Clean up
        $testMissionStage->delete();
        foreach($testMission as $mission) {
            $mission->delete();
        }
    }

    public function testBlankUpdate() {
        // Create blank mission stage
        $missionStage = new MissionStage();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $missionStage->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Mission Stage.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test mission
        $testMission = new Mission();
        $testMission->setTitle('test');
        $testMission->create();

        // Create a test mission stage
        $testMissionStage = new MissionStage();
        $testMissionStage->setMissionID($testMission->getID());
        $testMissionStage->setName('test');
        $testMissionStage->setBody('body');
        $testMissionStage->setStatus('Completed');
        $testMissionStage->create();

        // Store id
        $id = $testMissionStage->getID();

        // Now delete it
        $testMissionStage->delete();

        // Now check id is null
        $this->assertNull($testMissionStage->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `MissionStageID`,`MissionID`,`MissionStageName`,`MissionStageBody`,`MissionStageStatus` FROM `Mission_Stages` WHERE `MissionStageID`=?");
        $stmt->bind_param('i', $testMissionStage->getID());
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
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

        // Create a test mission stage
        $testMissionStage = [];
        $testMissionStage[0] = new MissionStage();
        $testMissionStage[0]->setMissionID($testMission[0]->getID());
        $testMissionStage[0]->setName('test');
        $testMissionStage[0]->setBody('body');
        $testMissionStage[0]->setStatus('Completed');
        $testMissionStage[0]->create();

        $testMissionStage[1] = new MissionStage();
        $testMissionStage[1]->setMissionID($testMission[0]->getID());
        $testMissionStage[1]->setName('test2');
        $testMissionStage[1]->setBody('body2');
        $testMissionStage[1]->setStatus('Completed2');
        $testMissionStage[1]->create();

        $testMissionStage[2] = new MissionStage();
        $testMissionStage[2]->setMissionID($testMission[1]->getID());
        $testMissionStage[2]->setName('test3');
        $testMissionStage[2]->setBody('body3');
        $testMissionStage[2]->setStatus('Completed3');
        $testMissionStage[2]->create();

        // Now check a single
        $selectedSingle = MissionStage::select(array($testMissionStage[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInternalType(MissionStage::class, $selectedSingle[0]);
        $this->assertEquals($testMissionStage[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testMissionStage[0]->getMissionID(), $selectedSingle[0]->getMissionID());
        $this->assertEquals($testMissionStage[0]->getName(), $selectedSingle[0]->getName());
        $this->assertEquals($testMissionStage[0]->getBody(), $selectedSingle[0]->getBody());
        $this->assertEquals($testMissionStage[0]->getStatus(), $selectedSingle[0]->getStatus());

        // Get and check multiple
        $selectedMultiple = MissionStage::select(array($testMissionStage[1]->getID(), $testMissionStage[2]->getID()));
        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInternalType(MissionStage::class, $selectedMultiple[0]);
        $this->assertInternalType(MissionStage::class, $selectedMultiple[1]);

        if($testMissionStage[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testMissionStage[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testMissionStage[1]->getMissionID(), $selectedMultiple[$i]->getMissionID());
        $this->assertEquals($testMissionStage[1]->getName(), $selectedMultiple[$i]->getName());
        $this->assertEquals($testMissionStage[1]->getBody(), $selectedMultiple[$i]->getBody());
        $this->assertEquals($testMissionStage[1]->getStatus(), $selectedMultiple[$i]->getStatus());

        $this->assertEquals($testMissionStage[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testMissionStage[2]->getMissionID(), $selectedMultiple[$j]->getMissionID());
        $this->assertEquals($testMissionStage[2]->getName(), $selectedMultiple[$j]->getName());
        $this->assertEquals($testMissionStage[2]->getBody(), $selectedMultiple[$j]->getBody());
        $this->assertEquals($testMissionStage[2]->getStatus(), $selectedMultiple[$j]->getStatus());

        // Clean up
        foreach($testMissionStage as $stage) {
            $stage->delete();
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

        // Create a test mission stage
        $testMissionStage = [];
        $testMissionStage[0] = new MissionStage();
        $testMissionStage[0]->setMissionID($testMission[0]->getID());
        $testMissionStage[0]->setName('test');
        $testMissionStage[0]->setBody('body');
        $testMissionStage[0]->setStatus('Completed');
        $testMissionStage[0]->create();

        $testMissionStage[1] = new MissionStage();
        $testMissionStage[1]->setMissionID($testMission[0]->getID());
        $testMissionStage[1]->setName('test2');
        $testMissionStage[1]->setBody('body2');
        $testMissionStage[1]->setStatus('Completed2');
        $testMissionStage[1]->create();

        // Get and check all
        $selectedMultiple = MissionStage::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInternalType(MissionStage::class, $selectedMultiple[0]);
        $this->assertInternalType(MissionStage::class, $selectedMultiple[1]);

        if($testMissionStage[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testMissionStage[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testMissionStage[0]->getMissionID(), $selectedMultiple[$i]->getMissionID());
        $this->assertEquals($testMissionStage[0]->getName(), $selectedMultiple[$i]->getName());
        $this->assertEquals($testMissionStage[0]->getBody(), $selectedMultiple[$i]->getBody());
        $this->assertEquals($testMissionStage[0]->getStatus(), $selectedMultiple[$i]->getStatus());

        $this->assertEquals($testMissionStage[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testMissionStage[1]->getMissionID(), $selectedMultiple[$j]->getMissionID());
        $this->assertEquals($testMissionStage[1]->getName(), $selectedMultiple[$j]->getName());
        $this->assertEquals($testMissionStage[1]->getBody(), $selectedMultiple[$j]->getBody());
        $this->assertEquals($testMissionStage[1]->getStatus(), $selectedMultiple[$j]->getStatus());

        // Clean up
        foreach($testMissionStage as $stage) {
            $stage->delete();
        }
        foreach($testMission as $mission) {
            $mission->delete();
        }
    }

    public function testEql(){
        // Create a test mission stage
        $testMissionStage = [];
        $testMissionStage[0] = new MissionStage();
        $testMissionStage[0]->setID(1);
        $testMissionStage[0]->setMissionID(2);
        $testMissionStage[0]->setName('test');
        $testMissionStage[0]->setBody('body');
        $testMissionStage[0]->setStatus('Completed');

        $testMissionStage[1] = new MissionStage();
        $testMissionStage[1]->setID(1);
        $testMissionStage[1]->setMissionID(2);
        $testMissionStage[1]->setName('test');
        $testMissionStage[1]->setBody('body');
        $testMissionStage[1]->setStatus('Completed');

        $testMissionStage[2] = new MissionStage();
        $testMissionStage[2]->setID(2);
        $testMissionStage[2]->setMissionID(3);
        $testMissionStage[2]->setName('test2');
        $testMissionStage[2]->setBody('body2');
        $testMissionStage[2]->setStatus('Completed2');

        // Check same object is eql
        $this->assertTrue($testMissionStage[0]->eql($testMissionStage[0]));

        // Check same details are eql
        $this->assertTrue($testMissionStage[0]->eql($testMissionStage[1]));

        // Check different arent equal
        $this->assertFalse($testMissionStage[0]->eql($testMissionStage[2]));
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

        // Create a test mission stage
        $testMissionStage = [];
        $testMissionStage[0] = new MissionStage();
        $testMissionStage[0]->setMissionID($testMission[0]->getID());
        $testMissionStage[0]->setName('test');
        $testMissionStage[0]->setBody('body');
        $testMissionStage[0]->setStatus('Completed');
        $testMissionStage[0]->create();

        $testMissionStage[1] = new MissionStage();
        $testMissionStage[1]->setMissionID($testMission[1]->getID());
        $testMissionStage[1]->setName('test2');
        $testMissionStage[1]->setBody('body2');
        $testMissionStage[1]->setStatus('Completed2');
        $testMissionStage[1]->create();

        // Get by mission id
        $selectedSingle = MissionStage::getByMissionID($testMission[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInternalType(MissionStage::class, $selectedSingle[0]);
        $this->assertEquals($testMissionStage[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testMissionStage[0]->getMissionID(), $selectedSingle[0]->getMissionID());
        $this->assertEquals($testMissionStage[0]->getName(), $selectedSingle[0]->getName());
        $this->assertEquals($testMissionStage[0]->getBody(), $selectedSingle[0]->getBody());
        $this->assertEquals($testMissionStage[0]->getStatus(), $selectedSingle[0]->getStatus());

        // Clean up
        foreach($testMissionStage as $stage) {
            $stage->delete();
        }
        foreach($testMission as $mission) {
            $mission->delete();
        }
    }

    public function testSetMissionID() {
        //TODO: Implement
    }

    public function testInvalidMissioNSetMissionID() {
        //TODO: Implement
    }

}
