<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Group.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Group;
use AMC\Classes\Database;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('jirbj.co.uk', 'testingDB', 'testingDB', 'testingdb');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Create and test a null constructor
        $group = new Group();

        $this->assertTrue($group->eql(new Group()));
        $this->assertNull($group->getID());
        $this->assertNull($group->getName());
        $this->assertNull($group->getHidden());

        // Create and test a non null constructor
        $group = new Group(1, 'name', false);

        $this->assertFalse($group->eql(new Group()));
        $this->assertEquals(1, $group->getID());
        $this->assertEquals('name', $group->getName());
        $this->assertFalse($group->getHidden());
    }

    public function testCreate() {

    }

    public function testBlankCreate() {

    }

    public function testUpdate() {

    }

    public function testBlankUpdate() {

    }

    public function testDelete() {

    }

    public function testSelectWithInput() {

    }

    public function testSelectAll() {

    }

    public function testEql() {

    }

}
