<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Group.php';
require '../classes/Privilege.php';
require '../classes/GroupPrivilege.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Group;
use AMC\Classes\GroupPrivilege;
use AMC\Classes\Database;
use AMC\Classes\Privilege;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class GroupPrivilegeTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('jirbj.co.uk', 'testingDB', 'testingDB', 'testingdb');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Create and test a null
        $groupPriv = new GroupPrivilege();

        $this->assertTrue($groupPriv->eql(new GroupPrivilege()));
        $this->assertNull($groupPriv->getID());
        $this->assertNull($groupPriv->getGroupID());
        $this->assertNull($groupPriv->getPrivilegeID());

        // Create and test a non null constructor
        $groupPriv = new GroupPrivilege(1,2, 3);
        $this->assertFalse($groupPriv->eql(new GroupPrivilege()));
        $this->assertEquals(1, $groupPriv->getID());
        $this->assertEquals(2, $groupPriv->getGroupID());
        $this->assertEquals(3, $groupPriv->getPrivilegeID());
    }

    public function testCreate() {
        // Make a test group
        $testGroup = new Group();
        $testGroup->setName('group');
        $testGroup->setHidden(false);
        $testGroup->create();

        // Make a test privilege
        $testPriv = new Privilege();
        $testPriv->setName('priv');
        $testPriv->create();

        // Make a test group priv
        $testGroupPriv = new GroupPrivilege();

        $testGroupPriv->setGroupID($testGroup->getID());
        $testGroupPriv->setPrivilegeID($testPriv->getID());
        $testGroupPriv->create();

        // Check the id is now a int
        $this->assertInternalType('int', $testGroupPriv->getID());

        // Now pull from the db
        $stmt = $this->_connection->prepare("SELECT `GroupPrivilegeID`,`GroupID`,`PrivilegeID` FROM `Group_Privileges` WHERE `GroupPrivilegeID`=?");
        $stmt->bind_param('i', $testGroupPriv->getID());
        $stmt->execute();
        $stmt->bind_result($groupPrivID, $groupID, $privID);

        // Check there is only one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();
        // Now check them
        $this->assertEquals($testGroupPriv->getID(), $groupPrivID);
        $this->assertEquals($testGroupPriv->getGroupID(), $groupID);
        $this->assertEquals($testGroupPriv->getPrivilegeID(), $privID);

        // Clean up
        $testGroupPriv->delete();
        $testGroup->delete();
        $testPriv->delete();
    }

    public function testBlankCreate() {
        // Make a group priv
        $groupPriv = new GroupPrivilege();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Now trigger the exception
        try {
            $groupPriv->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Group Privilege.', $e->getMessage());
        }
    }

    public function testUpdate() {
        // Make a test group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('group');
        $testGroup[0]->setHidden(false);
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('group2');
        $testGroup[1]->setHidden(false);
        $testGroup[1]->create();

        // Make a test privilege
        $testPriv = [];
        $testPriv[0] = new Privilege();
        $testPriv[0]->setName('priv');
        $testPriv[0]->create();

        $testPriv[1] = new Privilege();
        $testPriv[1]->setName('priv2');
        $testPriv[1]->create();

        // Make a test group priv
        $testGroupPriv = new GroupPrivilege();

        $testGroupPriv->setGroupID($testGroup[0]->getID());
        $testGroupPriv->setPrivilegeID($testPriv[0]->getID());
        $testGroupPriv->create();

        // Now update it
        $testGroupPriv->setGroupID($testGroup[1]->getID());
        $testGroupPriv->setPrivilegeID($testPriv[1]->getID());
        $testGroupPriv->update();

        // Now pull from the db
        $stmt = $this->_connection->prepare("SELECT `GroupPrivilegeID`,`GroupID`,`PrivilegeID` FROM `Group_Privileges` WHERE `GroupPrivilegeID`=?");
        $stmt->bind_param('i', $testGroupPriv->getID());
        $stmt->execute();
        $stmt->bind_result($groupPrivID, $groupID, $privID);

        // Check there is only one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();
        // Now check them
        $this->assertEquals($testGroupPriv->getID(), $groupPrivID);
        $this->assertEquals($testGroupPriv->getGroupID(), $groupID);
        $this->assertEquals($testGroupPriv->getPrivilegeID(), $privID);

        // Clean up
        $testGroupPriv->delete();
        foreach($testGroup as $group) {
            $group->delete();
        }
        foreach($testPriv as $priv) {
            $priv->delete();
        }
    }

    public function testBlankUpdate() {
        // Make a group priv
        $groupPriv = new GroupPrivilege();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Now trigger the exception
        try {
            $groupPriv->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Group Privilege.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Make a test group
        $testGroup = new Group();
        $testGroup->setName('group');
        $testGroup->setHidden(false);
        $testGroup->create();

        // Make a test privilege
        $testPriv = new Privilege();
        $testPriv->setName('priv');
        $testPriv->create();

        // Make a test group priv
        $testGroupPriv = new GroupPrivilege();

        $testGroupPriv->setGroupID($testGroup->getID());
        $testGroupPriv->setPrivilegeID($testPriv->getID());
        $testGroupPriv->create();

        // Save the id
        $id = $testGroupPriv->getID();

        // Now delete it
        $testGroupPriv->delete();

        // Check id is null
        $this->assertNull($testGroupPriv->getID());

        // Check its gone
        $stmt = $this->_connection->prepare("SELECT `GroupPrivilegeID` FROM `Group_Privileges` WHERE `GroupPrivilegeID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testGroup->delete();
        $testPriv->delete();
    }

    public function testSelectWithInput() {
        // Make a test group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('group');
        $testGroup[0]->setHidden(false);
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('group2');
        $testGroup[1]->setHidden(false);
        $testGroup[1]->create();

        // Make a test privilege
        $testPriv = [];
        $testPriv[0] = new Privilege();
        $testPriv[0]->setName('priv');
        $testPriv[0]->create();

        $testPriv[1] = new Privilege();
        $testPriv[1]->setName('priv2');
        $testPriv[1]->create();

        // Make a test group priv
        $testGroupPriv = [];
        $testGroupPriv[0] = new GroupPrivilege();
        $testGroupPriv[0]->setGroupID($testGroup[0]->getID());
        $testGroupPriv[0]->setPrivilegeID($testPriv[0]->getID());
        $testGroupPriv[0]->create();

        $testGroupPriv[1] = new GroupPrivilege();
        $testGroupPriv[1]->setGroupID($testGroup[1]->getID());
        $testGroupPriv[1]->setPrivilegeID($testPriv[1]->getID());
        $testGroupPriv[1]->create();

        $testGroupPriv[2] = new GroupPrivilege();
        $testGroupPriv[2]->setGroupID($testGroup[0]->getID());
        $testGroupPriv[2]->setPrivilegeID($testPriv[1]->getID());
        $testGroupPriv[2]->create();

        // Get a single and check it
        $selectedSingle = GroupPrivilege::select(array($testGroupPriv[0]->getID()));

        $this->assertInstanceOf(GroupPrivilege::class, $selectedSingle);
        $this->assertEquals($testGroupPriv[0]->getID(), $selectedSingle->getID());
        $this->assertEquals($testGroupPriv[0]->getGroupID(), $selectedSingle->getGroupID());
        $this->assertEquals($testGroupPriv[0]->getPrivilegeID(), $selectedSingle->getPrivilegeID());

        // Now get multiple and check
        $selectedMultiple = GroupPrivilege::select(array($testGroupPriv[1]->getID(), $testGroupPriv[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(GroupPrivilege::class, $selectedMultiple[0]);
        $this->assertInstanceOf(GroupPrivilege::class, $selectedMultiple[1]);

        if($testGroupPriv[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testGroupPriv[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testGroupPriv[1]->getGroupID(), $selectedMultiple[$i]->getGroupID());
        $this->assertEquals($testGroupPriv[1]->getPrivilegeID(), $selectedMultiple[$i]->getPrivilegeID());

        $this->assertEquals($testGroupPriv[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testGroupPriv[2]->getGroupID(), $selectedMultiple[$j]->getGroupID());
        $this->assertEquals($testGroupPriv[2]->getPrivilegeID(), $selectedMultiple[$j]->getPrivilegeID());

        // Clean up
        foreach($testGroupPriv as $groupPriv) {
            $groupPriv->delete();
        }
        foreach($testGroup as $group) {
            $group->delete();
        }
        foreach($testPriv as $priv) {
            $priv->delete();
        }
    }

    public function testSelectAll() {
        // Make a test group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('group');
        $testGroup[0]->setHidden(false);
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('group2');
        $testGroup[1]->setHidden(false);
        $testGroup[1]->create();

        // Make a test privilege
        $testPriv = [];
        $testPriv[0] = new Privilege();
        $testPriv[0]->setName('priv');
        $testPriv[0]->create();

        $testPriv[1] = new Privilege();
        $testPriv[1]->setName('priv2');
        $testPriv[1]->create();

        // Make a test group priv
        $testGroupPriv = [];
        $testGroupPriv[0] = new GroupPrivilege();
        $testGroupPriv[0]->setGroupID($testGroup[0]->getID());
        $testGroupPriv[0]->setPrivilegeID($testPriv[0]->getID());
        $testGroupPriv[0]->create();

        $testGroupPriv[1] = new GroupPrivilege();
        $testGroupPriv[1]->setGroupID($testGroup[1]->getID());
        $testGroupPriv[1]->setPrivilegeID($testPriv[1]->getID());
        $testGroupPriv[1]->create();

        // Now get multiple and check
        $selectedMultiple = GroupPrivilege::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(GroupPrivilege::class, $selectedMultiple[0]);
        $this->assertInstanceOf(GroupPrivilege::class, $selectedMultiple[1]);

        if($testGroupPriv[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testGroupPriv[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testGroupPriv[0]->getGroupID(), $selectedMultiple[$i]->getGroupID());
        $this->assertEquals($testGroupPriv[0]->getPrivilegeID(), $selectedMultiple[$i]->getPrivilegeID());

        $this->assertEquals($testGroupPriv[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testGroupPriv[1]->getGroupID(), $selectedMultiple[$j]->getGroupID());
        $this->assertEquals($testGroupPriv[1]->getPrivilegeID(), $selectedMultiple[$j]->getPrivilegeID());

        // Clean up
        foreach($testGroupPriv as $groupPriv) {
            $groupPriv->delete();
        }
        foreach($testGroup as $group) {
            $group->delete();
        }
        foreach($testPriv as $priv) {
            $priv->delete();
        }
    }

    public function testEql() {
        // Make a test group priv
        $testGroupPriv = [];
        $testGroupPriv[0] = new GroupPrivilege();
        $testGroupPriv[0]->setID(1);
        $testGroupPriv[0]->setGroupID(1);
        $testGroupPriv[0]->setPrivilegeID(1);

        $testGroupPriv[1] = new GroupPrivilege();
        $testGroupPriv[1]->setID(1);
        $testGroupPriv[1]->setGroupID(1);
        $testGroupPriv[1]->setPrivilegeID(1);

        $testGroupPriv[2] = new GroupPrivilege();
        $testGroupPriv[2]->setID(2);
        $testGroupPriv[2]->setGroupID(2);
        $testGroupPriv[2]->setPrivilegeID(2);

        // Check same object is eql
        $this->assertTrue($testGroupPriv[0]->eql($testGroupPriv[0]));

        // Check same details are eql
        $this->assertTrue($testGroupPriv[0]->eql($testGroupPriv[1]));

        // Check different arent equal
        $this->assertFalse($testGroupPriv[0]->eql($testGroupPriv[2]));
    }

    public function testGetByGroupID() {
        // Make a test group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('group');
        $testGroup[0]->setHidden(false);
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('group2');
        $testGroup[1]->setHidden(false);
        $testGroup[1]->create();

        // Make a test privilege
        $testPriv = [];
        $testPriv[0] = new Privilege();
        $testPriv[0]->setName('priv');
        $testPriv[0]->create();

        $testPriv[1] = new Privilege();
        $testPriv[1]->setName('priv2');
        $testPriv[1]->create();

        // Make a test group priv
        $testGroupPriv = [];
        $testGroupPriv[0] = new GroupPrivilege();
        $testGroupPriv[0]->setGroupID($testGroup[0]->getID());
        $testGroupPriv[0]->setPrivilegeID($testPriv[0]->getID());
        $testGroupPriv[0]->create();

        $testGroupPriv[1] = new GroupPrivilege();
        $testGroupPriv[1]->setGroupID($testGroup[1]->getID());
        $testGroupPriv[1]->setPrivilegeID($testPriv[1]->getID());
        $testGroupPriv[1]->create();

        // Get by group id and check it
        $selected = GroupPrivilege::getByGroupID($testGroup[0]->getID());

        $this->assertTrue(\is_array($selected));
        $this->assertEquals(1, $selected);
        $this->assertInstanceOf(GroupPrivilege::class, $selected[0]);

        $this->assertEquals($testGroupPriv[0]->getID(), $selected[0]->getID());
        $this->assertEquals($testGroupPriv[0]->getGroupID(), $selected[0]->getGroupID());
        $this->assertEquals($testGroupPriv[0]->getPrivilegeID(), $selected[0]->getPrivilegeID());

        // Clean up
        foreach($testGroupPriv as $groupPriv) {
            $groupPriv->delete();
        }
        foreach($testGroup as $group) {
            $group->delete();
        }
        foreach($testPriv as $priv) {
            $priv->delete();
        }
    }

    public function testGetByPrivilegeID() {
        // Make a test group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('group');
        $testGroup[0]->setHidden(false);
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('group2');
        $testGroup[1]->setHidden(false);
        $testGroup[1]->create();

        // Make a test privilege
        $testPriv = [];
        $testPriv[0] = new Privilege();
        $testPriv[0]->setName('priv');
        $testPriv[0]->create();

        $testPriv[1] = new Privilege();
        $testPriv[1]->setName('priv2');
        $testPriv[1]->create();

        // Make a test group priv
        $testGroupPriv = [];
        $testGroupPriv[0] = new GroupPrivilege();
        $testGroupPriv[0]->setGroupID($testGroup[0]->getID());
        $testGroupPriv[0]->setPrivilegeID($testPriv[0]->getID());
        $testGroupPriv[0]->create();

        $testGroupPriv[1] = new GroupPrivilege();
        $testGroupPriv[1]->setGroupID($testGroup[1]->getID());
        $testGroupPriv[1]->setPrivilegeID($testPriv[1]->getID());
        $testGroupPriv[1]->create();

        // Get by priv id and check it
        $selected = GroupPrivilege::getByPrivilegeID($testPriv[0]->getID());

        $this->assertTrue(\is_array($selected));
        $this->assertEquals(1, $selected);
        $this->assertInstanceOf(GroupPrivilege::class, $selected[0]);

        $this->assertEquals($testGroupPriv[0]->getID(), $selected[0]->getID());
        $this->assertEquals($testGroupPriv[0]->getGroupID(), $selected[0]->getGroupID());
        $this->assertEquals($testGroupPriv[0]->getPrivilegeID(), $selected[0]->getPrivilegeID());

        // Clean up
        foreach($testGroupPriv as $groupPriv) {
            $groupPriv->delete();
        }
        foreach($testGroup as $group) {
            $group->delete();
        }
        foreach($testPriv as $priv) {
            $priv->delete();
        }
    }

}
