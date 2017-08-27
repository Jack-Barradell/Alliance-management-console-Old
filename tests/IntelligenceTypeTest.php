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
        //TODO: Implement
    }

    public function testBlankCreate() {
        //TODO: Implement
    }

    public function testUpdate() {
        //TODO: Implement
    }

    public function testBlankUpdate() {
        //TODO: Implement
    }

    public function testDelete() {
        //TODO: Implement
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
