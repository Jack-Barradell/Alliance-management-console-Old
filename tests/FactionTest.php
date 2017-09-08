<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/FactionType.php';
require '../classes/Faction.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Faction;
use AMC\Classes\Database;
use AMC\Classes\FactionType;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class FactionTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Test a null constructor
        $faction = new Faction();

        $this->assertTrue($faction->eql(new Faction()));
        $this->assertNull($faction->getID());
        $this->assertNull($faction->getFactionTypeID());
        $this->assertNull($faction->getName());

        // Check a valued constructor
        $faction = new Faction(1, 1, 'name');

        $this->assertFalse($faction->eql(new Faction()));
        $this->assertEquals(1, $faction->getID());
        $this->assertEquals(1, $faction->getFactionTypeID());
        $this->assertEquals('name', $faction->getName());
    }

    public function testCreate() {
        // Create a faction type to use
        $testFactionType = new FactionType();
        $testFactionType->setName('testType');
        $testFactionType->create();

        // Create a faction to test
        $testFaction = new Faction();
        $testFaction->setFactionTypeID($testFactionType->getID());
        $testFaction->setName('test');
        $testFaction->create();

        // Check id is now a number
        $this->assertInternalType('int', $testFaction->getID());

        // Now pull it and check it
        $stmt = $this->_connection->prepare("SElECT `FactionID`,`FactionTypeID`,`FactionName` FROM `Factions` WHERE `FactionID`=?");
        $stmt->bind_param('i', $testFaction->getID());
        $stmt->execute();
        $stmt->bind_result($factionID, $typeID, $name);

        // Check there is only one result
        $this->assertEquals(1, $stmt->num_rows);

        // Check its details
        $stmt->fetch();

        $this->assertEquals($testFaction->getID(), $factionID);
        $this->assertEquals($testFaction->getFactionTypeID(), $typeID);
        $this->assertEquals($testFaction->getName(), $name);

        $stmt->close();

        // Clean up
        $testFaction->delete();
        $testFactionType->delete();
    }

    public function testBlankCreate() {
        // Create a faction
        $faction = new Faction();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger the exception
        try {
            $faction->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store blank Faction.', $e->getMessage());
        }
    }

    public function testUpdate() {
        // Create a faction type to use
        $testFactionType = [];
        $testFactionType[0] = new FactionType();
        $testFactionType[0]->setName('testType');
        $testFactionType[0]->create();

        $testFactionType[1] = new FactionType();
        $testFactionType[1]->setName('testType2');
        $testFactionType[1]->create();

        // Create a faction to test
        $testFaction = new Faction();
        $testFaction->setFactionTypeID($testFactionType[0]->getID());
        $testFaction->setName('test');
        $testFaction->create();

        // Update it
        $testFaction->setFactionTypeID($testFactionType[1]->getID());
        $testFaction->setName('updated');
        $testFaction->update();

        // Now pull it and check it
        $stmt = $this->_connection->prepare("SElECT `FactionID`,`FactionTypeID`,`FactionName` FROM `Factions` WHERE `FactionID`=?");
        $stmt->bind_param('i', $testFaction->getID());
        $stmt->execute();
        $stmt->bind_result($factionID, $typeID, $name);

        // Check there is only one result
        $this->assertEquals(1, $stmt->num_rows);

        // Check its details
        $stmt->fetch();

        $this->assertEquals($testFaction->getID(), $factionID);
        $this->assertEquals($testFaction->getFactionTypeID(), $typeID);
        $this->assertEquals($testFaction->getName(), $name);

        $stmt->close();

        // Clean up
        $testFaction->delete();
        foreach($testFactionType as $type) {
            $type->delete();
        }
    }

    public function testBlankUpdate() {
        // Create a faction
        $faction = new Faction();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger the exception
        try {
            $faction->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store blank Faction.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a faction type to use
        $testFactionType = new FactionType();
        $testFactionType->setName('testType');
        $testFactionType->create();

        // Create a faction to test
        $testFaction = new Faction();
        $testFaction->setFactionTypeID($testFactionType->getID());
        $testFaction->setName('test');
        $testFaction->create();

        // Save the id
        $id = $testFaction->getID();

        // Now delete it
        $testFaction->delete();

        // Check id is now null
        $this->assertNull($testFaction->getID());

        // Check its gone
        $stmt = $this->_connection->prepare("SELECT `FactionID` FROM `Factions` WHERE `FactionID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check 0 results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testFactionType->delete();
    }

    public function testSelectWithInput() {
        // Create a faction type to use
        $testFactionType = new FactionType();
        $testFactionType->setName('testType');
        $testFactionType->create();

        // Create a faction to test
        $testFaction = [];
        $testFaction[0] = new Faction();
        $testFaction[0]->setFactionTypeID($testFactionType->getID());
        $testFaction[0]->setName('test');
        $testFaction[0]->create();

        $testFaction[1] = new Faction();
        $testFaction[1]->setFactionTypeID($testFactionType->getID());
        $testFaction[1]->setName('test2');
        $testFaction[1]->create();

        $testFaction[2] = new Faction();
        $testFaction[2]->setFactionTypeID($testFactionType->getID());
        $testFaction[2]->setName('test3');
        $testFaction[2]->create();

        // Get a single faction and check it
        $selectedSingle = Faction::select(array($testFaction[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(Faction::class, $selectedSingle[0]);
        $this->assertEquals($testFaction[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testFaction[0]->getFactionTypeID(), $selectedSingle[0]->getFactionTypeID());
        $this->assertEquals($testFaction[0]->getName(), $selectedSingle[0]->getName());

        // Select multiple and check them
        $selectedMultiple = Faction::select(array($testFaction[1]->getID(), $testFaction[2]->getID()));

        // Check its an array with correct content
        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(Faction::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Faction::class, $selectedMultiple[1]);

        if($testFaction[1]->getID() == $selectedMultiple[0]) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testFaction[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testFaction[1]->getFactionTypeID(), $selectedMultiple[$i]->getFactionTypeID());
        $this->assertEquals($testFaction[1]->getName(), $selectedMultiple[$i]->getName());

        $this->assertEquals($testFaction[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testFaction[2]->getFactionTypeID(), $selectedMultiple[$j]->getFactionTypeID());
        $this->assertEquals($testFaction[2]->getName(), $selectedMultiple[$j]->getName());

        // Clean up
        foreach($testFaction as $faction) {
            $faction->delete();
        }
        $testFactionType->delete();
    }

    public function testSelectAll() {
        // Create a faction type to use
        $testFactionType = new FactionType();
        $testFactionType->setName('testType');
        $testFactionType->create();

        // Create a faction to test
        $testFaction = [];
        $testFaction[0] = new Faction();
        $testFaction[0]->setFactionTypeID($testFactionType->getID());
        $testFaction[0]->setName('test');
        $testFaction[0]->create();

        $testFaction[1] = new Faction();
        $testFaction[1]->setFactionTypeID($testFactionType->getID());
        $testFaction[1]->setName('test2');
        $testFaction[1]->create();

        // Select multiple and check them
        $selectedMultiple = Faction::select(array());

        // Check its an array with correct content
        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(Faction::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Faction::class, $selectedMultiple[1]);

        if($testFaction[0]->getID() == $selectedMultiple[0]) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testFaction[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testFaction[0]->getFactionTypeID(), $selectedMultiple[$i]->getFactionTypeID());
        $this->assertEquals($testFaction[0]->getName(), $selectedMultiple[$i]->getName());

        $this->assertEquals($testFaction[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testFaction[1]->getFactionTypeID(), $selectedMultiple[$j]->getFactionTypeID());
        $this->assertEquals($testFaction[1]->getName(), $selectedMultiple[$j]->getName());

        // Clean up
        foreach($testFaction as $faction) {
            $faction->delete();
        }
        $testFactionType->delete();
    }

    public function testEql() {
        // Create a faction to test
        $testFaction = [];
        $testFaction[0] = new Faction();
        $testFaction[0]->setID(1);
        $testFaction[0]->setFactionTypeID(1);
        $testFaction[0]->setName('test');

        $testFaction[1] = new Faction();
        $testFaction[1]->setID(1);
        $testFaction[1]->setFactionTypeID(1);
        $testFaction[1]->setName('test');

        $testFaction[2] = new Faction();
        $testFaction[2]->setID(2);
        $testFaction[2]->setFactionTypeID(2);
        $testFaction[2]->setName('test2');

        // Check same object is eql
        $this->assertTrue($testFaction[0]->eql($testFaction[0]));

        // Check same details are eql
        $this->assertTrue($testFaction[0]->eql($testFaction[1]));

        // Check different arent equal
        $this->assertFalse($testFaction[0]->eql($testFaction[2]));
    }

    public function testGetByFactionTypeID() {
        // Create a faction type to use
        $testFactionType = [];
        $testFactionType[0] = new FactionType();
        $testFactionType[0]->setName('testType');
        $testFactionType[0]->create();

        $testFactionType[1] = new FactionType();
        $testFactionType[1]->setName('testType2');
        $testFactionType[1]->create();

        // Create a faction to test
        $testFaction = [];
        $testFaction[0] = new Faction();
        $testFaction[0]->setFactionTypeID($testFactionType[0]->getID());
        $testFaction[0]->setName('test');
        $testFaction[0]->create();

        $testFaction[1] = new Faction();
        $testFaction[1]->setFactionTypeID($testFactionType[1]->getID());
        $testFaction[1]->setName('test2');
        $testFaction[1]->create();

        // Select by a faction type id
        $selectedSingle = Faction::getByFactionTypeID($testFactionType[0]->getID());

        // Check it
        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(Faction::class, $selectedSingle[0]);
        $this->assertEquals($testFaction[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testFaction[0]->getFactionTypeID(), $selectedSingle[0]->getFactionTypeID());
        $this->assertEquals($testFaction[0]->getName(), $selectedSingle[0]->getName());

        // Clean up
        foreach($testFaction as $faction) {
            $faction->delete();
        }
        foreach($testFactionType as $type) {
            $type->delete();
        }
    }

    public function testSetFactionTypeID() {
        //TODO: Implement
    }

    public function testInvalidFactionTypeSetFactionTypeID() {
        //TODO: Implement
    }

}
