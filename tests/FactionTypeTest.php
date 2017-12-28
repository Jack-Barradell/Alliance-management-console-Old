<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/FactionType.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\FactionType;
use AMC\Classes\Database;
use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\IncorrectTypeException;
use PHPUnit\Framework\TestCase;

class FactionTypeTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check with a null constructor
        $factionType = new FactionType();
        $this->assertTrue($factionType->eql(new FactionType()));
        $this->assertNull($factionType->getID());
        $this->assertNull($factionType->getName());

        // Try a non null constructor
        $factionType = new FactionType(1, 'test');
        $this->assertFalse($factionType->eql(new FactionType()));
        $this->assertEquals(1, $factionType->getID());
        $this->assertEquals('test', $factionType->getName());
    }

    public function testCreate() {
        // make a test faction type
        $testFactionType = new FactionType();
        $testFactionType->setName('test');
        $testFactionType->create();

        // Check id is now an int
        $this->assertInternalType('int', $testFactionType->getID());

        // Pull it
        $stmt = $this->_connection->prepare("SELECT `FactionTypeID`,`FactionTypeName` FROM `Faction_Types` WHERE `FactionTypeID`=?");
        $stmt->bind_param('i', $testFactionType->getID());
        $stmt->execute();
        $stmt->bind_param($factionTypeID, $name);

        // Check only one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetc();

        $this->assertEquals($testFactionType->getID(), $factionTypeID);
        $this->assertEquals($testFactionType->getName(), $name);

        $stmt->close();

        // Clean up
        $testFactionType->delete();
    }

    public function testBlankCreate() {
        // Create a faction type
        $factionType = new FactionType();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $factionType->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store blank Faction Type.', $e->getMessage());
        }
    }

    public function testUpdate() {
        // make a test faction type
        $testFactionType = new FactionType();
        $testFactionType->setName('test');
        $testFactionType->create();

        // Now update it
        $testFactionType->setName('test2');
        $testFactionType->update();

        // Pull it
        $stmt = $this->_connection->prepare("SELECT `FactionTypeID`,`FactionTypeName` FROM `Faction_Types` WHERE `FactionTypeID`=?");
        $stmt->bind_param('i', $testFactionType->getID());
        $stmt->execute();
        $stmt->bind_param($factionTypeID, $name);

        // Check only one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetc();

        $this->assertEquals($testFactionType->getID(), $factionTypeID);
        $this->assertEquals($testFactionType->getName(), $name);

        $stmt->close();

        // Clean up
        $testFactionType->delete();
    }

    public function testBlankUpdate() {
        // Create a faction type
        $factionType = new FactionType();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $factionType->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store blank Faction Type.', $e->getMessage());
        }
    }

    public function testDelete() {
        // make a test faction type
        $testFactionType = new FactionType();
        $testFactionType->setName('test');
        $testFactionType->create();

        // Save the id
        $id = $testFactionType->getID();

        // Now delete it
        $testFactionType->delete();

        // Check id is now null
        $this->assertNull($testFactionType->getID());

        // Now check its gone
        $stmt = $this->_connection->prepare("SELECT `FactionTypeID` FROM `Faction_Types` WHERE `FactionTypeID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($factionTypeID);

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();
    }

    public function testSelectWithInput() {
        // make a test faction type
        $testFactionType = [];
        $testFactionType[0] = new FactionType();
        $testFactionType[0]->setName('test');
        $testFactionType[0]->create();

        $testFactionType[1] = new FactionType();
        $testFactionType[1]->setName('test2');
        $testFactionType[1]->create();

        $testFactionType[2] = new FactionType();
        $testFactionType[2]->setName('test3');
        $testFactionType[2]->create();

        // Now select and check a single
        $selectedSingle = FactionType::select(array($testFactionType[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle[0]));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(FactionType::class, $selectedSingle[0]);
        $this->assertEquals($testFactionType[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testFactionType[0]->getName(), $selectedSingle[0]->getName());

        // Now select and check a multiple
        $selectedMultiple = FactionType::select(array($testFactionType[1]->getID(), $testFactionType[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(FactionType::class, $selectedMultiple[0]);
        $this->assertInstanceOf(FactionType::class, $selectedMultiple[1]);

        if($testFactionType[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testFactionType[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testFactionType[1]->getName(), $selectedMultiple[$i]->getName());

        $this->assertEquals($testFactionType[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testFactionType[2]->getName(), $selectedMultiple[$j]->getName());

        // Clean up
        foreach($testFactionType as $type) {
            $type->delete();
        }
    }

    public function testSelectAll() {
        // make a test faction type
        $testFactionType = [];
        $testFactionType[0] = new FactionType();
        $testFactionType[0]->setName('test');
        $testFactionType[0]->create();

        $testFactionType[1] = new FactionType();
        $testFactionType[1]->setName('test2');
        $testFactionType[1]->create();

        // Now select and check a multiple
        $selectedMultiple = FactionType::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(FactionType::class, $selectedMultiple[0]);
        $this->assertInstanceOf(FactionType::class, $selectedMultiple[1]);

        if($testFactionType[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testFactionType[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testFactionType[0]->getName(), $selectedMultiple[$i]->getName());

        $this->assertEquals($testFactionType[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testFactionType[1]->getName(), $selectedMultiple[$j]->getName());

        // Clean up
        foreach($testFactionType as $type) {
            $type->delete();
        }
    }

    public function testEql() {
        // make a test faction type
        $testFactionType = [];
        $testFactionType[0] = new FactionType();
        $testFactionType[0]->setID(1);
        $testFactionType[0]->setName('test');

        $testFactionType[0] = new FactionType();
        $testFactionType[0]->setID(1);
        $testFactionType[0]->setName('test');

        $testFactionType[0] = new FactionType();
        $testFactionType[0]->setID(2);
        $testFactionType[0]->setName('test2');

        // Check same object is eql
        $this->assertTrue($testFactionType[0]->eql($testFactionType[0]));

        // Check same details are eql
        $this->assertTrue($testFactionType[0]->eql($testFactionType[1]));

        // Check different arent equal
        $this->assertFalse($testFactionType[0]->eql($testFactionType[2]));
    }

    public function testFactionTypeExists() {
        // Create a test faction type
        $testFactionType = new FactionType();
        $testFactionType->setName('test');
        $testFactionType->create();

        // Check newly created type exists
        $this->assertTrue(FactionType::factionTypeExists($testFactionType->getID()));

        // Check the next used id doesnt exist
        $this->assertFalse(FactionType::factionTypeExists($testFactionType->getID()+1));

        // Clean up
        $testFactionType->delete();
    }

    public function testIncorrectTypeFactionTypeExists() {
        // Set expected exception
        $this->expectException(IncorrectTypeException::class);

        // Trigger the exception
        try {
            FactionType::factionTypeExists('string');
        } catch(IncorrectTypeException $e) {
            $this->assertEquals('Faction type exists must be passed an int, was given string', $e->getMessage());
        }
    }
}
