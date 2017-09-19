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
use AMC\Classes\Privilege;
use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\DuplicateEntryException;
use AMC\Exceptions\IncorrectTypeException;
use AMC\Exceptions\MissingPrerequisiteException;
use AMC\Exceptions\NullGetException;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
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
        else if($hidden == 0) {
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
        try {
            $group->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Group.', $e->getMessage());
        }
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
        else if($hidden == 0) {
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
        try {
            $group->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Group.', $e->getMessage());
        }
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
        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(Group::class, $selectedSingle[0]);
        $this->assertEquals($testGroup[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testGroup[0]->getName(), $selectedSingle[0]->getName());
        $this->assertEquals($testGroup[0]->getHidden(), $selectedSingle[0]->getHidden());

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
        $testGroup[0]->setID(1);
        $testGroup[0]->setName('test');
        $testGroup[0]->setHidden(false);

        $testGroup[1] = new Group();
        $testGroup[1]->setID(1);
        $testGroup[1]->setName('test');
        $testGroup[1]->setHidden(false);

        $testGroup[2] = new Group();
        $testGroup[2]->setID(2);
        $testGroup[2]->setName('test2');
        $testGroup[2]->setHidden(true);

        // Check same object is eql
        $this->assertTrue($testGroup[0]->eql($testGroup[0]));

        // Check same details are eql
        $this->assertTrue($testGroup[0]->eql($testGroup[1]));

        // Check different arent equal
        $this->assertFalse($testGroup[0]->eql($testGroup[2]));
    }

    public function testIssuePrivilege() {
        // Create a test privilege
        $testPrivilege = new Privilege();
        $testPrivilege->setName('testPriv');
        $testPrivilege->commit();

        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('testGroup');
        $testGroup->setHidden(false);
        $testGroup->create();

        $this->assertFalse($testGroup->hasGroupPrivilege($testPrivilege->getID()));

        $testGroup->issuePrivilege($testPrivilege->getID());

        $this->assertTrue($testGroup->hasGroupPrivilege($testPrivilege->getID()));

        // Clean up
        $testGroup->revokePrivilege($testPrivilege->getID());
        $testGroup->delete();
        $testPrivilege->delete();
    }

    public function testDuplicatedEntryIssuePrivilege() {
        // Create a test privilege
        $testPrivilege = new Privilege();
        $testPrivilege->setName('testPriv');
        $testPrivilege->commit();

        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('testGroup');
        $testGroup->setHidden(false);
        $testGroup->create();

        $testGroup->issuePrivilege($testPrivilege->getID());

        // Set expected exception
        $this->expectException(DuplicateEntryException::class);

        // Trigger the exception
        try {
            $testGroup->issuePrivilege($testPrivilege->getID());
        } catch(DuplicateEntryException $e) {
            $this->assertEquals('Group with id ' . $testGroup->getID(). ' was issued privilege with id ' . $testPrivilege->getID() . ' but they already have it.', $e->getMessage());
        } finally {
            // Clean up
            $testGroup->revokePrivilege($testPrivilege->getID());
            $testGroup->delete();
            $testPrivilege->delete();
        }
    }

    public function testInvalidPrivilegeIssuePrivilege() {
        //TODO: Implement
    }

    public function testRevokePrivilege() {
        $testPrivilege = new Privilege();
        $testPrivilege->setName('testPriv');
        $testPrivilege->commit();

        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('testGroup');
        $testGroup->setHidden(false);
        $testGroup->create();

        $testGroup->issuePrivilege($testPrivilege->getID());

        // Check it now has the priv
        $this->assertTrue($testGroup->hasGroupPrivilege($testPrivilege->getID()));

        // Now revoke the priv
        $testGroup->revokePrivilege($testPrivilege->getID());

        // Check they no longer have the priv
        $this->assertFalse($testGroup->hasGroupPrivilege($testPrivilege->getID()));

        // Clean up
        $testGroup->delete();
        $testPrivilege->delete();
    }

    public function testMissingPerquisiteRevokePrivilege() {
        $testPrivilege = new Privilege();
        $testPrivilege->setName('testPriv');
        $testPrivilege->commit();

        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('testGroup');
        $testGroup->setHidden(false);
        $testGroup->create();

        // Set expected exception
        $this->expectException(MissingPrerequisiteException::class);

        // Trigger it
        try {
            $testGroup->revokePrivilege($testPrivilege->getID());
        } catch(MissingPrerequisiteException $e) {
            $this->assertEquals('Tried to remove privilege with id ' . $testPrivilege->getID() . ' from group with id ' . $testGroup->getID() . ' but they did not have it.', $e->getMessage());
        } finally {
            // Clean up
            $testGroup->delete();
            $testPrivilege->delete();
        }
    }

    public function testInvalidPrivilegeRevokePrivilege() {
        //TODO: Implement
    }

    public function testHasGroupPrivilege() {
        $testPrivilege = [];
        $testPrivilege[0] = new Privilege();
        $testPrivilege[0]->setName('testPriv');
        $testPrivilege[0]->commit();

        $testPrivilege[1] = new Privilege();
        $testPrivilege[1]->setName('testPriv2');
        $testPrivilege[1]->commit();

        // Create a test group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('testGroup');
        $testGroup[0]->setHidden(false);
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('testGroup2');
        $testGroup[1]->setHidden(false);
        $testGroup[1]->create();

        // Now check it
        $this->assertFalse($testGroup[0]->hasGroupPrivilege($testPrivilege[0]->getID()));
        $this->assertFalse($testGroup[1]->hasGroupPrivilege($testPrivilege[0]->getID()));

        $this->assertFalse($testGroup[0]->hasGroupPrivilege($testPrivilege[1]->getID()));
        $this->assertFalse($testGroup[1]->hasGroupPrivilege($testPrivilege[1]->getID()));

        // Issue the priv to one
        $testGroup[0]->issuePrivilege($testPrivilege[0]->getID());

        $this->assertTrue($testGroup[0]->hasGroupPrivilege($testPrivilege[0]->getID()));
        $this->assertFalse($testGroup[1]->hasGroupPrivilege($testPrivilege[0]->getID()));

        $this->assertFalse($testGroup[0]->hasGroupPrivilege($testPrivilege[1]->getID()));
        $this->assertFalse($testGroup[1]->hasGroupPrivilege($testPrivilege[1]->getID()));

        // Issue priv to the other
        $testGroup[1]->issuePrivilege($testPrivilege[1]->getID());

        $this->assertTrue($testGroup[0]->hasGroupPrivilege($testPrivilege[0]->getID()));
        $this->assertFalse($testGroup[1]->hasGroupPrivilege($testPrivilege[0]->getID()));

        $this->assertFalse($testGroup[0]->hasGroupPrivilege($testPrivilege[1]->getID()));
        $this->assertTrue($testGroup[1]->hasGroupPrivilege($testPrivilege[1]->getID()));

        // Clean up
        $testGroup[0]->revokePrivilege($testPrivilege[0]->getID());
        $testGroup[1]->revokePrivilege($testPrivilege[1]->getID());
        foreach($testGroup as $group) {
            $group->delete();
        }
        foreach($testPrivilege as $priv) {
            $priv->delete();
        }
    }

    public function testInvalidPrivilegeHasGroupPrivilege() {
        //TODO: Implement
    }

    public function testGetPrivileges() {
        // Make a test group
        $testGroup = new Group();
        $testGroup->setName('TestGroup');
        $testGroup->setHidden(false);
        $testGroup->create();

        // Create test privs
        $testPrivilege = [];

        $testPrivilege[0] = new Privilege();
        $testPrivilege[0]->setName('TestPriv');
        $testPrivilege[0]->create();

        $testPrivilege[1] = new Privilege();
        $testPrivilege[1]->setName('TestPriv2');
        $testPrivilege[1]->create();

        // Check the group doesnt return privs
        $this->assertNull($testGroup->getPrivileges());

        // Issue the first privilege
        $testGroup->issuePrivilege($testPrivilege[0]->getID());

        // Check it
        $privs = $testGroup->getPrivileges();

        $this->assertTrue(\is_array($privs));
        $this->assertEquals(1, \count($privs));
        $this->assertInstanceOf(Privilege::class, $privs[0]);
        $this->assertEquals($testPrivilege[0]->getID(), $privs[0]->getID());
        $this->assertEquals($testPrivilege[0]->getName(), $privs[0]->getName());

        // Issue the second privilege
        $testGroup->issuePrivilege($testPrivilege[1]->getID());

        // Check it
        $privs = $testGroup->getPrivileges();

        $this->assertTrue(\is_array($privs));
        $this->assertEquals(2, \count($privs));
        $this->assertInstanceOf(Privilege::class, $privs[0]);
        $this->assertInstanceOf(Privilege::class, $privs[1]);

        if($testPrivilege[0]->getID() == $privs[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testPrivilege[0]->getID(), $privs[$i]->getID());
        $this->assertEquals($testPrivilege[0]->getName(), $privs[$i]->getName());

        $this->assertEquals($testPrivilege[1]->getID(), $privs[$j]->getID());
        $this->assertEquals($testPrivilege[1]->getName(), $privs[$j]->getName());

        // Clean up
        $testGroup->revokePrivilege($testPrivilege[0]->getID());
        $testGroup->revokePrivilege($testPrivilege[1]->getID());
        $testGroup->delete();
        foreach($testPrivilege as $priv) {
            $priv->delete();
        }
    }

    public function testGroupExists() {
        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('TestGroup');
        $testGroup->setHidden(false);
        $testGroup->create();

        // Check there is no group with an id bigger than the new one
        $this->assertFalse(Group::groupExists($testGroup->getID() + 1, false));
        $this->assertFalse(Group::groupExists($testGroup->getID() + 1, true));

        // Check group that doesnt exist
        $this->assertFalse(Group::groupExists('TestGroup2', false));
        $this->assertFalse(Group::groupExists('TestGroup2', true));

        // Check the actual group shows up
        $this->assertTrue(Group::groupExists($testGroup->getID(), false));
        $this->assertEquals($testGroup->getName(), Group::groupExists($testGroup->getID(), true));

        $this->assertTrue(Group::groupExists('TestGroup2', false));
        $this->assertEquals($testGroup->getID(), Group::groupExists('TestGroup2', true));

        // Clean up
        $testGroup->delete();
    }

    public function testIncorrectTypeGroupExists() {
        // Set expected exception
        $this->expectException(IncorrectTypeException::class);

        // Trigger it
        try {
            Group::groupExists(false);
        } catch(IncorrectTypeException $e) {
            $this->assertEquals('Group exists must be passed an int or string, was given boolean', $e->getMessage());
        }
    }

    public function testSetGroupID() {
        //TODO: Implement
    }

    public function testInvalidGroupSetGroupID() {
        //TODO: Implement
    }

    public function testSetPrivilegeID() {
        //TODO: Implement
    }

    public function testInvalidPrivilegeSetPrivilegeID() {
        //TODO: Implement
    }

}
