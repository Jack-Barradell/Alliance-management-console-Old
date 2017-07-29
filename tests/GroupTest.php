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
use AMC\Exceptions\BlankObjectException;
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
        // Create a group
        $testGroup = new Group();
        $testGroup->setName('test');
        $testGroup->setHidden(false);
        $testGroup->create();

        // Now check the id is a int
        $this->assertInternalType('int', $testGroup->getID());

        // Now pull it and check
        $stmt = $this->_connection->prepare("SELECT `GroupID`,`GroupName`,`GroupHidden` FROM `Groups` WHERE `GroupID`=?");
        $stmt->bind_param('i', $testGroup->getID());
        $stmt->execute();
        $stmt->bind_result($groupID, $name, $hidden);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        // Convert the numeric to a boolean
        if($hidden == 1) {
            $hidden = true;
        }
        else {
            $hidden = false;
        }

        $this->assertEquals($testGroup->getID(), $groupID);
        $this->assertEquals($testGroup->getName(), $name);
        $this->assertEquals($testGroup->getHidden(), $hidden);

        $stmt->close();

        // Clean up
        $testGroup->delete();
    }

    public function testBlankCreate() {
        // Create a group
        $group = new Group();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger the exception
        $group->create();
    }

    public function testUpdate() {
        // Create a group
        $testGroup = new Group();
        $testGroup->setName('test');
        $testGroup->setHidden(false);
        $testGroup->create();

        // Now update it
        $testGroup->setName('updated');
        $testGroup->setHidden(true);

        // Now pull it and check
        $stmt = $this->_connection->prepare("SELECT `GroupID`,`GroupName`,`GroupHidden` FROM `Groups` WHERE `GroupID`=?");
        $stmt->bind_param('i', $testGroup->getID());
        $stmt->execute();
        $stmt->bind_result($groupID, $name, $hidden);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        // Convert the numeric to a boolean
        if($hidden == 1) {
            $hidden = true;
        }
        else {
            $hidden = false;
        }

        $this->assertEquals($testGroup->getID(), $groupID);
        $this->assertEquals($testGroup->getName(), $name);
        $this->assertEquals($testGroup->getHidden(), $hidden);

        $stmt->close();

        // Clean up
        $testGroup->delete();
    }

    public function testBlankUpdate() {
        // Create a group
        $group = new Group();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger the exception
        $group->update();
    }

    public function testDelete() {
        // Create a group
        $testGroup = new Group();
        $testGroup->setName('test');
        $testGroup->setHidden(false);
        $testGroup->create();

        // Save the id
        $id = $testGroup->getID();

        // Now delete it
        $testGroup->delete();

        // Check id is null
        $this->assertNull($testGroup->getID());

        // Check its gone
        $stmt = $this->_connection->prepare("SELECT `GroupID` FROM `Groups` WHERE `GroupID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();
    }

    public function testSelectWithInput() {
        // Create a group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('test');
        $testGroup[0]->setHidden(false);
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('test2');
        $testGroup[1]->setHidden(false);
        $testGroup[1]->create();

        $testGroup[2] = new Group();
        $testGroup[2]->setName('test2');
        $testGroup[2]->setHidden(true);
        $testGroup[2]->create();

        // Now select a single and check it
        $selectedSingle = Group::select(array($testGroup[0]->getID()));

        // Check it
        $this->assertInstanceOf(Group::class, $selectedSingle);
        $this->assertEquals($testGroup[0]->getID(), $selectedSingle->getID());
        $this->assertEquals($testGroup[0]->getName(), $selectedSingle->getName());
        $this->assertEquals($testGroup[0]->getHidden(), $selectedSingle->getHidden());

        // Now select multiple and check them
        $selectedMultiple = Group::select(array($testGroup[1]->getID(), $testGroup[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(Group::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Group::class, $selectedMultiple[1]);

        if($testGroup[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testGroup[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testGroup[1]->getName(), $selectedMultiple[$i]->getName());
        $this->assertEquals($testGroup[1]->getHidden(), $selectedMultiple[$i]->getHidden());

        $this->assertEquals($testGroup[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testGroup[2]->getName(), $selectedMultiple[$j]->getName());
        $this->assertEquals($testGroup[2]->getHidden(), $selectedMultiple[$j]->getHidden());

        // Clean up
        foreach($testGroup as $group) {
            $group->delete();
        }
    }

    public function testSelectAll() {
        // Create a group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('test');
        $testGroup[0]->setHidden(false);
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('test2');
        $testGroup[1]->setHidden(false);
        $testGroup[1]->create();

        // Now select multiple and check them
        $selectedMultiple = Group::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(Group::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Group::class, $selectedMultiple[1]);

        if($testGroup[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testGroup[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testGroup[0]->getName(), $selectedMultiple[$i]->getName());
        $this->assertEquals($testGroup[0]->getHidden(), $selectedMultiple[$i]->getHidden());

        $this->assertEquals($testGroup[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testGroup[1]->getName(), $selectedMultiple[$j]->getName());
        $this->assertEquals($testGroup[1]->getHidden(), $selectedMultiple[$j]->getHidden());

        // Clean up
        foreach($testGroup as $group) {
            $group->delete();
        }
    }

    public function testEql() {
        // Create a group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('test');
        $testGroup[0]->setHidden(false);

        $testGroup[1] = new Group();
        $testGroup[1]->setName('test');
        $testGroup[1]->setHidden(false);

        $testGroup[2] = new Group();
        $testGroup[2]->setName('test2');
        $testGroup[2]->setHidden(true);

        // Check same object is eql
        $this->assertTrue($testGroup[0]->eql($testGroup[0]));

        // Check same details are eql
        $this->assertTrue($testGroup[0]->eql($testGroup[1]));

        // Check different arent equal
        $this->assertFalse($testGroup[0]->eql($testGroup[2]));
    }

}
