<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Privilege.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Privilege;
use AMC\Classes\Database;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class PrivilegeTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('jirbj.co.uk', 'testingDB', 'testingDB', 'testingdb');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check null construct
        $privilege = new Privilege();

        $this->assertTrue($privilege->eql(new Privilege()));
        $this->assertNull($privilege->getID());
        $this->assertNull($privilege->getName());

        // Check null constructor
        $privilege = new Privilege(1, 'test');

        $this->assertFalse($privilege->eql(new Privilege()));
        $this->assertEquals(1, $privilege->getID());
        $this->assertEquals('test', $privilege->getName());
    }

    public function testCreate() {
        // Create a test Privilege
        $testPrivilege = new Privilege();
        $testPrivilege->setName('test');
        $testPrivilege->create();

        // Now check id is an int
        $this->assertInternalType('int', $testPrivilege->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `PrivilegeID`,`PrivilegeName` FROM `Privileges` WHERE `PrivilegeID`=?");
        $stmt->bind_param('i', $testPrivilege->getID());
        $stmt->execute();
        $stmt->bind_result($privID, $name);

        // Check there is 1 result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testPrivilege->getID(), $privID);
        $this->assertEquals($testPrivilege->getName(), $name);

        $stmt->close();

        // Clean up
        $testPrivilege->delete();
    }

    public function testBlankCreate() {
        // Create a privilege
        $privilege = new Privilege();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        $privilege->create();
    }

    public function testUpdate() {
        // Create a test Privilege
        $testPrivilege = new Privilege();
        $testPrivilege->setName('test');
        $testPrivilege->create();

        // Update it
        $testPrivilege->setName('test2');
        $testPrivilege->update();

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `PrivilegeID`,`PrivilegeName` FROM `Privileges` WHERE `PrivilegeID`=?");
        $stmt->bind_param('i', $testPrivilege->getID());
        $stmt->execute();
        $stmt->bind_result($privID, $name);

        // Check there is 1 result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testPrivilege->getID(), $privID);
        $this->assertEquals($testPrivilege->getName(), $name);

        $stmt->close();

        // Clean up
        $testPrivilege->delete();
    }

    public function testBlankUpdate() {
        // Create a privilege
        $privilege = new Privilege();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        $privilege->update();
    }

    public function testDelete() {
        // Create a test Privilege
        $testPrivilege = new Privilege();
        $testPrivilege->setName('test');
        $testPrivilege->create();

        // Store id
        $id = $testPrivilege->getID();

        // Now delete it
        $testPrivilege->delete();

        // Check id is now null
        $this->assertNull($testPrivilege->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `PrivilegeID`,`PrivilegeName` FROM `Privileges` WHERE `PrivilegeID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check there is 1 result
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testPrivilege->delete();
    }

    public function testSelectWithInput() {
        // Create a test Privilege
        $testPrivilege = [];
        $testPrivilege[0] = new Privilege();
        $testPrivilege[0]->setName('test');
        $testPrivilege[0]->create();

        $testPrivilege[1] = new Privilege();
        $testPrivilege[1]->setName('test2');
        $testPrivilege[1]->create();

        $testPrivilege[2] = new Privilege();
        $testPrivilege[2]->setName('test3');
        $testPrivilege[2]->create();

        // Select and check a single
        $selectedSingle = Privilege::select(array($testPrivilege[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(Privilege::class, $selectedSingle[0]);

        $this->assertEquals($testPrivilege[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testPrivilege[0]->getName(), $selectedSingle[0]->getName());

        // Select and check multiple
        $selectedMultiple = Privilege::select(array($testPrivilege[1]->getID(), $testPrivilege[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(Privilege::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Privilege::class, $selectedMultiple[1]);

        if($testPrivilege[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testPrivilege[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testPrivilege[1]->getName(), $selectedMultiple[$i]->getName());

        $this->assertEquals($testPrivilege[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testPrivilege[2]->getName(), $selectedMultiple[$j]->getName());

        // Clean up
        foreach($testPrivilege as $priv) {
            $priv->delete();
        }
    }

    public function testSelectAll() {
        // Create a test Privilege
        $testPrivilege = [];
        $testPrivilege[0] = new Privilege();
        $testPrivilege[0]->setName('test');
        $testPrivilege[0]->create();

        $testPrivilege[1] = new Privilege();
        $testPrivilege[1]->setName('test2');
        $testPrivilege[1]->create();

        // Select and check multiple
        $selectedMultiple = Privilege::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(Privilege::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Privilege::class, $selectedMultiple[1]);

        if($testPrivilege[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testPrivilege[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testPrivilege[0]->getName(), $selectedMultiple[$i]->getName());

        $this->assertEquals($testPrivilege[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testPrivilege[1]->getName(), $selectedMultiple[$j]->getName());

        // Clean up
        foreach($testPrivilege as $priv) {
            $priv->delete();
        }
    }

    public function testEql() {
        // Create a test Privilege
        $testPrivilege = [];
        $testPrivilege[0] = new Privilege();
        $testPrivilege[0]->setName('test');

        $testPrivilege[1] = new Privilege();
        $testPrivilege[1]->setName('test');

        $testPrivilege[2] = new Privilege();
        $testPrivilege[2]->setName('test2');

        // Check same object is eql
        $this->assertTrue($testPrivilege[0]->eql($testPrivilege[0]));

        // Check same details are eql
        $this->assertTrue($testPrivilege[0]->eql($testPrivilege[1]));

        // Check different arent equal
        $this->assertFalse($testPrivilege[0]->eql($testPrivilege[2]));
    }

    public function testGetByName() {
        // Create a test Privilege
        $testPrivilege = [];
        $testPrivilege[0] = new Privilege();
        $testPrivilege[0]->setName('test');
        $testPrivilege[0]->create();

        $testPrivilege[1] = new Privilege();
        $testPrivilege[1]->setName('test2');
        $testPrivilege[1]->create();

        // Now get "test"
        $selected = Privilege::getByName('test');

        $this->assertTrue(\is_array($selected));
        $this->assertEquals(1, \count($selected));
        $this->assertInstanceOf(Privilege::class, $selected[0]);
        $this->assertEquals($testPrivilege[0]->getID(), $selected->getID());
        $this->assertEquals($testPrivilege[0]->getName(), $selected->getName());

        // Clean up
        foreach($testPrivilege as $priv) {
            $priv->delete();
        }
    }

}
