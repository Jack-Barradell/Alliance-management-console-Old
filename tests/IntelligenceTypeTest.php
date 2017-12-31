<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/IntelligenceType.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\IntelligenceType;
use AMC\Classes\Database;
use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\IncorrectTypeException;
use PHPUnit\Framework\TestCase;

class IntelligenceTypeTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check null constructor
        $intelligenceType = new IntelligenceType();

        $this->assertNull($intelligenceType->getID());
        $this->assertNull($intelligenceType->getName());

        // Check non null constructor
        $intelligenceType = new IntelligenceType(1, 'name');

        $this->assertEquals(1, $intelligenceType->getID());
        $this->assertEquals('name', $intelligenceType->getID());
    }

    public function testCreate() {
        //Create a test intelligence type
        $testIntelligenceType = new IntelligenceType();
        $testIntelligenceType->setName('testName');
        $testIntelligenceType->create();

        // Check id is now an int
        $this->assertInternalType('int', $testIntelligenceType->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `IntelligenceTypeID`,`IntelligenceTypeName` FROM `Intelligence_Types` WHERE `IntelligenceTypeID`=?");
        $stmt->bind_param('i', $testIntelligenceType->getID());
        $stmt->execute();
        $stmt->bind_result($intelligenceTypeID, $name);

        // Check there is only one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testIntelligenceType->getID(), $intelligenceTypeID);
        $this->assertEquals($testIntelligenceType->getName(), $name);

        $stmt->close();

        // Clean up
        $testIntelligenceType->delete();
    }

    public function testBlankCreate() {
        // Create a test intelligence type
        $testIntelligenceType = new IntelligenceType();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testIntelligenceType->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Intelligence Type.', $e->getMessage());
        }
    }

    public function testUpdate() {
        //Create a test intelligence type
        $testIntelligenceType = new IntelligenceType();
        $testIntelligenceType->setName('testName');
        $testIntelligenceType->create();

        // Now update it
        $testIntelligenceType->setName('testName2');
        $testIntelligenceType->update();

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `IntelligenceTypeID`,`IntelligenceTypeName` FROM `Intelligence_Types` WHERE `IntelligenceTypeID`=?");
        $stmt->bind_param('i', $testIntelligenceType->getID());
        $stmt->execute();
        $stmt->bind_result($intelligenceTypeID, $name);

        // Check there is only one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testIntelligenceType->getID(), $intelligenceTypeID);
        $this->assertEquals($testIntelligenceType->getName(), $name);

        $stmt->close();

        // Clean up
        $testIntelligenceType->delete();
    }

    public function testBlankUpdate() {
        // Create a test intelligence type
        $testIntelligenceType = new IntelligenceType();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testIntelligenceType->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Intelligence Type.', $e->getMessage());
        }
    }

    public function testDelete() {
        //Create a test intelligence type
        $testIntelligenceType = new IntelligenceType();
        $testIntelligenceType->setName('testName');
        $testIntelligenceType->create();

        // Save the id
        $id = $testIntelligenceType->getID();

        // Now delete it
        $testIntelligenceType->delete();

        // Check id is null
        $this->assertNull($testIntelligenceType->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `IntelligenceTypeID`,`IntelligenceTypeName` FROM `Intelligence_Types` WHERE `IntelligenceTypeID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();
    }

    public function testSelectWithInput() {
        //Create a test intelligence type
        $testIntelligenceType = [];
        $testIntelligenceType[0] = new IntelligenceType();
        $testIntelligenceType[0]->setName('testName');
        $testIntelligenceType[0]->create();

        $testIntelligenceType[1] = new IntelligenceType();
        $testIntelligenceType[1]->setName('testName2');
        $testIntelligenceType[1]->create();

        $testIntelligenceType[2] = new IntelligenceType();
        $testIntelligenceType[2]->setName('testName3');
        $testIntelligenceType[2]->create();

        // Get and check a single
        $selectedSingle = IntelligenceType::select(array($testIntelligenceType[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(IntelligenceType::class, $selectedSingle[0]);
        $this->assertEquals($testIntelligenceType[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testIntelligenceType[0]->getName(), $selectedSingle[0]->getName());

        // Get and check multiple
        $selectedMultiple = IntelligenceType::select(array($testIntelligenceType[1]->getID(), $testIntelligenceType[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(IntelligenceType::class, $selectedMultiple[0]);
        $this->assertInstanceOf(IntelligenceType::class, $selectedMultiple[1]);

        if($testIntelligenceType[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testIntelligenceType[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testIntelligenceType[1]->getName(), $selectedMultiple[$i]->getName());

        $this->assertEquals($testIntelligenceType[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testIntelligenceType[2]->getName(), $selectedMultiple[$j]->getName());

        // Clean up
        foreach($testIntelligenceType as $intelligenceType) {
            $intelligenceType->delete();
        }
    }

    public function testSelectAll() {
        //Create a test intelligence type
        $testIntelligenceType = [];
        $testIntelligenceType[0] = new IntelligenceType();
        $testIntelligenceType[0]->setName('testName');
        $testIntelligenceType[0]->create();

        $testIntelligenceType[1] = new IntelligenceType();
        $testIntelligenceType[1]->setName('testName2');
        $testIntelligenceType[1]->create();
        // Get and check multiple
        $selectedMultiple = IntelligenceType::select(array($testIntelligenceType[1]->getID(), $testIntelligenceType[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(IntelligenceType::class, $selectedMultiple[0]);
        $this->assertInstanceOf(IntelligenceType::class, $selectedMultiple[1]);

        if($testIntelligenceType[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testIntelligenceType[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testIntelligenceType[0]->getName(), $selectedMultiple[$i]->getName());

        $this->assertEquals($testIntelligenceType[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testIntelligenceType[1]->getName(), $selectedMultiple[$j]->getName());

        // Clean up
        foreach($testIntelligenceType as $intelligenceType) {
            $intelligenceType->delete();
        }
    }

    public function testEql() {
        //Create a test intelligence type
        $testIntelligenceType = [];
        $testIntelligenceType[0] = new IntelligenceType();
        $testIntelligenceType[0]->setName('testName');

        $testIntelligenceType[1] = new IntelligenceType();
        $testIntelligenceType[1]->setName('testName');

        $testIntelligenceType[2] = new IntelligenceType();
        $testIntelligenceType[2]->setName('testName2');

        // Check same object is eql
        $this->assertTrue($testIntelligenceType[0]->eql($testIntelligenceType[0]));

        // Check same details are eql
        $this->assertTrue($testIntelligenceType[0]->eql($testIntelligenceType[0]));

        // Check different arent equal
        $this->assertFalse($testIntelligenceType[0]->eql($testIntelligenceType[0]));
    }

    public function testIntelligenceTypeExists() {
        // Create test intelligence type
        $testIntelligenceType = new IntelligenceType();
        $testIntelligenceType->setName('test');
        $testIntelligenceType->create();

        // Check newly created intelligence type exists
        $this->assertTrue(IntelligenceType::intelligenceTypeExists($testIntelligenceType->getID()));

        // Check next used id doesnt exist
        $this->assertFalse(IntelligenceType::intelligenceTypeExists($testIntelligenceType->getID()+1));

        // Clean up
        $testIntelligenceType->delete();
    }

    public function testIncorrectTypeIntelligenceTypeExists() {
        // Set expected exception
        $this->expectException(IncorrectTypeException::class);

        // Trigger it
        try {
            IntelligenceType::intelligenceTypeExists('string');
        } catch (IncorrectTypeException $e) {
            $this->assertEquals('Intelligence type exists must be passed an int, was given string', $e->getMessage());
        }
    }

}
