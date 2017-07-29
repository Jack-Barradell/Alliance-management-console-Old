<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Mission.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Mission;
use AMC\Classes\Database;
use AMC\Exceptions\BlankObjectException;
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
        $mission->create();
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
        $mission->update();
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
        $testMission[0]->setTitle('test');
        $testMission[0]->setDescription('testDesc');
        $testMission[0]->setStatus('Completed');

        $testMission[1] = new Mission();
        $testMission[1]->setTitle('test');
        $testMission[1]->setDescription('testDesc');
        $testMission[1]->setStatus('Completed');

        $testMission[2] = new Mission();
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

}
