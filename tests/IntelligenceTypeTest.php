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
use PHPUnit\Framework\TestCase;

class IntelligenceTypeTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('jirbj.co.uk', 'testingDB', 'testingDB', 'testingdb');
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
        //TODO: Implement
    }

    public function testSelectAll() {
        //TODO: Implement
    }

    public function testEql() {
        //TODO: Implement
    }

}
