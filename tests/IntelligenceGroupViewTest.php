<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/IntelligenceGroupView.php';
require '../classes/Intelligence.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Group;
use AMC\Classes\Intelligence;
use AMC\Classes\IntelligenceGroupView;
use AMC\Classes\Database;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class IntelligenceGroupViewTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Create and test null constructor
        $intelligenceGroupView = new IntelligenceGroupView();
        $this->assertNull($intelligenceGroupView->getID());
        $this->assertNull($intelligenceGroupView->getGroupID());
        $this->assertNull($intelligenceGroupView->getIntelligenceID());

        // Create and check non null constructor
        $intelligenceGroupView = new IntelligenceGroupView(1, 2, 3);
        $this->assertEquals(1, $intelligenceGroupView->getID());
        $this->assertEquals(2, $intelligenceGroupView->getGroupID());
        $this->assertEquals(3, $intelligenceGroupView->getIntelligenceID());
    }

    public function testCreate() {
        // Create a test intelligence
        $testIntelligence = new Intelligence();
        $testIntelligence->setBody('random timestamp');
        $testIntelligence->create();

        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('TestGroup');
        $testGroup->create();

        // Create a test intelligence group view
        $testIntelligenceGroupView = new IntelligenceGroupView();
        $testIntelligenceGroupView->setIntelligenceID($testIntelligence->getID());
        $testIntelligenceGroupView->setGroupID($testGroup->getID());
        $testIntelligenceGroupView->create();

        // Check the id is a int
        $this->assertInternalType('int', $testIntelligenceGroupView->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `IntelligenceGroupViewID`,`IntelligenceID`,`GroupID` FROM `IntelligenceGroupViews` WHERE `IntelligenceGroupViewID`=?");
        $stmt->bind_param('i', $testIntelligenceGroupView->getID());
        $stmt->execute();
        $stmt->bind_result($intelligenceGroupViewID, $intelligenceID, $groupID);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);
        $stmt->fetch();

        $this->assertEquals($testIntelligenceGroupView->getID(), $intelligenceGroupViewID);
        $this->assertEquals($testIntelligenceGroupView->getIntelligenceID(), $intelligenceID);
        $this->assertEquals($testIntelligenceGroupView->getGroupID(), $groupID);

        $stmt->close();

        // Clean up
        $testIntelligenceGroupView->delete();
        $testIntelligence->delete();
        $testGroup->delete();
    }

    public function testBlankCreate() {
        // Create the object
        $testIntelligenceGroupView = new IntelligenceGroupView();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testIntelligenceGroupView->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Intelligence Group View.', $e->getMessage());
        }
    }

    public function testUpdate() {
        // Create a test intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setBody('random timestamp');
        $testIntelligence[0]->create();

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setBody('random timestamp2');
        $testIntelligence[1]->create();

        // Create a test group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('TestGroup');
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('TestGroup');
        $testGroup[1]->create();

        // Create a test intelligence group view
        $testIntelligenceGroupView = new IntelligenceGroupView();
        $testIntelligenceGroupView->setIntelligenceID($testIntelligence[0]->getID());
        $testIntelligenceGroupView->setGroupID($testGroup[0]->getID());
        $testIntelligenceGroupView->create();

        // Now update it
        $testIntelligenceGroupView->setIntelligenceID($testIntelligence[1]->getID());
        $testIntelligenceGroupView->setGroupID($testGroup[1]->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `IntelligenceGroupViewID`,`IntelligenceID`,`GroupID` FROM `IntelligenceGroupViews` WHERE `IntelligenceGroupViewID`=?");
        $stmt->bind_param('i', $testIntelligenceGroupView->getID());
        $stmt->execute();
        $stmt->bind_result($intelligenceGroupViewID, $intelligenceID, $groupID);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);
        $stmt->fetch();

        $this->assertEquals($testIntelligenceGroupView->getID(), $intelligenceGroupViewID);
        $this->assertEquals($testIntelligenceGroupView->getIntelligenceID(), $intelligenceID);
        $this->assertEquals($testIntelligenceGroupView->getGroupID(), $groupID);

        $stmt->close();

        // Clean up
        $testIntelligenceGroupView->delete();
        foreach($testIntelligence as $intelligence) {
            $intelligence->delete();
        }
        foreach ($testGroup as $group) {
            $group->delete();
        }
    }

    public function testBlankUpdate() {
        // Create the object
        $testIntelligenceGroupView = new IntelligenceGroupView();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testIntelligenceGroupView->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Intelligence Group View.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test intelligence
        $testIntelligence = new Intelligence();
        $testIntelligence->setBody('random timestamp');
        $testIntelligence->create();

        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('TestGroup');
        $testGroup->create();

        // Create a test intelligence group view
        $testIntelligenceGroupView = new IntelligenceGroupView();
        $testIntelligenceGroupView->setIntelligenceID($testIntelligence->getID());
        $testIntelligenceGroupView->setGroupID($testGroup->getID());
        $testIntelligenceGroupView->create();

        // Store the id
        $id = $testIntelligenceGroupView->getID();

        // Delete it
        $testIntelligenceGroupView->delete();

        // Check id is now null
        $this->assertNull($testIntelligenceGroupView->getID());

        // Check its gone
        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `IntelligenceGroupViewID`,`IntelligenceID`,`GroupID` FROM `IntelligenceGroupViews` WHERE `IntelligenceGroupViewID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testIntelligence->delete();
        $testGroup->delete();
    }

    public function testSelectWithInput() {
        // Create a test intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setBody('random timestamp');
        $testIntelligence[0]->create();

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setBody('random timestamp2');
        $testIntelligence[1]->create();

        // Create a test group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('TestGroup');
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('TestGroup');
        $testGroup[1]->create();

        // Create a test intelligence group view
        $testIntelligenceGroupView = [];
        $testIntelligenceGroupView[0] = new IntelligenceGroupView();
        $testIntelligenceGroupView[0]->setIntelligenceID($testIntelligence[0]->getID());
        $testIntelligenceGroupView[0]->setGroupID($testGroup[0]->getID());
        $testIntelligenceGroupView[0]->create();

        $testIntelligenceGroupView[1] = new IntelligenceGroupView();
        $testIntelligenceGroupView[1]->setIntelligenceID($testIntelligence[1]->getID());
        $testIntelligenceGroupView[1]->setGroupID($testGroup[1]->getID());
        $testIntelligenceGroupView[1]->create();

        $testIntelligenceGroupView[2] = new IntelligenceGroupView();
        $testIntelligenceGroupView[2]->setIntelligenceID($testIntelligence[0]->getID());
        $testIntelligenceGroupView[2]->setGroupID($testGroup[1]->getID());
        $testIntelligenceGroupView[2]->create();

        // Select a single
        $selectedSingle = IntelligenceGroupView::select(array($testIntelligenceGroupView[0]->getID()));

        // Check it
        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(IntelligenceGroupView::class, $selectedSingle[0]);
        $this->assertEquals($testIntelligenceGroupView[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testIntelligenceGroupView[0]->getIntelligenceID(), $selectedSingle[0]->getIntelligenceID());
        $this->assertEquals($testIntelligenceGroupView[0]->getGroupID(), $selectedSingle[0]->getGroupID());

        // Select multiple and check
        $selectedMultiple = IntelligenceGroupView::select(array($testIntelligenceGroupView[1]->getID(), $testIntelligenceGroupView[1]->getID()));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(IntelligenceGroupView::class, $selectedMultiple[0]);
        $this->assertInstanceOf(IntelligenceGroupView::class, $selectedMultiple[1]);

        if($testIntelligenceGroupView[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testIntelligenceGroupView[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testIntelligenceGroupView[1]->getIntelligenceID(), $selectedMultiple[$i]->getIntelligenceID());
        $this->assertEquals($testIntelligenceGroupView[1]->getGroupID(), $selectedMultiple[$i]->getGroupID());

        $this->assertEquals($testIntelligenceGroupView[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testIntelligenceGroupView[2]->getIntelligenceID(), $selectedMultiple[$j]->getIntelligenceID());
        $this->assertEquals($testIntelligenceGroupView[2]->getGroupID(), $selectedMultiple[$j]->getGroupID());

        // Clean up
        foreach($testIntelligenceGroupView as $groupView) {
            $groupView->delete();
        }
        foreach($testIntelligence as $intelligence) {
            $intelligence->delete();
        }
        foreach($testGroup as $group) {
            $group->delete();
        }
    }

    public function testSelectAll() {
        // Create a test intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setBody('random timestamp');
        $testIntelligence[0]->create();

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setBody('random timestamp2');
        $testIntelligence[1]->create();

        // Create a test group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('TestGroup');
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('TestGroup');
        $testGroup[1]->create();

        // Create a test intelligence group view
        $testIntelligenceGroupView = [];
        $testIntelligenceGroupView[0] = new IntelligenceGroupView();
        $testIntelligenceGroupView[0]->setIntelligenceID($testIntelligence[0]->getID());
        $testIntelligenceGroupView[0]->setGroupID($testGroup[0]->getID());
        $testIntelligenceGroupView[0]->create();

        $testIntelligenceGroupView[1] = new IntelligenceGroupView();
        $testIntelligenceGroupView[1]->setIntelligenceID($testIntelligence[1]->getID());
        $testIntelligenceGroupView[1]->setGroupID($testGroup[1]->getID());
        $testIntelligenceGroupView[1]->create();

        // Select multiple and check
        $selectedMultiple = IntelligenceGroupView::select(array());
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(IntelligenceGroupView::class, $selectedMultiple[0]);
        $this->assertInstanceOf(IntelligenceGroupView::class, $selectedMultiple[1]);

        if($testIntelligenceGroupView[0]->getID() == $selectedMultiple[1]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testIntelligenceGroupView[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testIntelligenceGroupView[0]->getIntelligenceID(), $selectedMultiple[$i]->getIntelligenceID());
        $this->assertEquals($testIntelligenceGroupView[0]->getGroupID(), $selectedMultiple[$i]->getGroupID());

        $this->assertEquals($testIntelligenceGroupView[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testIntelligenceGroupView[1]->getIntelligenceID(), $selectedMultiple[$j]->getIntelligenceID());
        $this->assertEquals($testIntelligenceGroupView[1]->getGroupID(), $selectedMultiple[$j]->getGroupID());

        // Clean up
        foreach($testIntelligenceGroupView as $groupView) {
            $groupView->delete();
        }
        foreach($testIntelligence as $intelligence) {
            $intelligence->delete();
        }
        foreach($testGroup as $group) {
            $group->delete();
        }
    }

    public function testEql() {
        $testIntelligenceGroupView = [];

        $testIntelligenceGroupView[0] = new IntelligenceGroupView();
        $testIntelligenceGroupView[0]->setIntelligenceID(0);
        $testIntelligenceGroupView[0]->setGroupID(1);

        $testIntelligenceGroupView[1] = new IntelligenceGroupView();
        $testIntelligenceGroupView[1]->setIntelligenceID(0);
        $testIntelligenceGroupView[1]->setGroupID(1);

        $testIntelligenceGroupView[2] = new IntelligenceGroupView();
        $testIntelligenceGroupView[2]->setIntelligenceID(2);
        $testIntelligenceGroupView[2]->setGroupID(3);

        // Check same object is eql
        $this->assertTrue($testIntelligenceGroupView[0]->eql($testIntelligenceGroupView[0]));

        // Check same details are eql
        $this->assertTrue($testIntelligenceGroupView[0]->eql($testIntelligenceGroupView[1]));

        // Check different arent equal
        $this->assertFalse($testIntelligenceGroupView[0]->eql($testIntelligenceGroupView[2]));
    }

    public function testGetByIntelligenceID() {
        // Create a test intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setBody('random timestamp');
        $testIntelligence[0]->create();

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setBody('random timestamp2');
        $testIntelligence[1]->create();

        // Create a test group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('TestGroup');
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('TestGroup');
        $testGroup[1]->create();

        // Create a test intelligence group view
        $testIntelligenceGroupView = [];
        $testIntelligenceGroupView[0] = new IntelligenceGroupView();
        $testIntelligenceGroupView[0]->setIntelligenceID($testIntelligence[0]->getID());
        $testIntelligenceGroupView[0]->setGroupID($testGroup[0]->getID());
        $testIntelligenceGroupView[0]->create();

        $testIntelligenceGroupView[1] = new IntelligenceGroupView();
        $testIntelligenceGroupView[1]->setIntelligenceID($testIntelligence[1]->getID());
        $testIntelligenceGroupView[1]->setGroupID($testGroup[1]->getID());
        $testIntelligenceGroupView[1]->create();

        // Select by group id
        $selected = IntelligenceGroupView::getByIntelligenceID($testIntelligence[0]->getID());

        $this->assertTrue(\is_array($selected));
        $this->assertEquals(1, \count($selected));
        $this->assertInstanceOf(IntelligenceGroupView::class, $selected[0]);
        $this->assertEquals($testIntelligenceGroupView[0]->getID(), $selected[0]->getID());
        $this->assertEquals($testIntelligenceGroupView[0]->getIntelligenceID(), $selected[0]->getIntelligenceID());
        $this->assertEquals($testIntelligenceGroupView[0]->getGroupID(), $selected[0]->getGroupID());

        // Clean up
        foreach($testIntelligenceGroupView as $groupView) {
            $groupView->delete();
        }
        foreach($testIntelligence as $intelligence) {
            $intelligence->delete();
        }
        foreach($testGroup as $group) {
            $group->delete();
        }
    }

    public function testGetByGroupID() {
        // Create a test intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setBody('random timestamp');
        $testIntelligence[0]->create();

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setBody('random timestamp2');
        $testIntelligence[1]->create();

        // Create a test group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('TestGroup');
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('TestGroup');
        $testGroup[1]->create();

        // Create a test intelligence group view
        $testIntelligenceGroupView = [];
        $testIntelligenceGroupView[0] = new IntelligenceGroupView();
        $testIntelligenceGroupView[0]->setIntelligenceID($testIntelligence[0]->getID());
        $testIntelligenceGroupView[0]->setGroupID($testGroup[0]->getID());
        $testIntelligenceGroupView[0]->create();

        $testIntelligenceGroupView[1] = new IntelligenceGroupView();
        $testIntelligenceGroupView[1]->setIntelligenceID($testIntelligence[1]->getID());
        $testIntelligenceGroupView[1]->setGroupID($testGroup[1]->getID());
        $testIntelligenceGroupView[1]->create();

        // Select by group id
        $selected = IntelligenceGroupView::getByGroupID($testGroup[0]->getID());

        $this->assertTrue(\is_array($selected));
        $this->assertEquals(1, \count($selected));
        $this->assertInstanceOf(IntelligenceGroupView::class, $selected[0]);
        $this->assertEquals($testIntelligenceGroupView[0]->getID(), $selected[0]->getID());
        $this->assertEquals($testIntelligenceGroupView[0]->getIntelligenceID(), $selected[0]->getIntelligenceID());
        $this->assertEquals($testIntelligenceGroupView[0]->getGroupID(), $selected[0]->getGroupID());

        // Clean up
        foreach($testIntelligenceGroupView as $groupView) {
            $groupView->delete();
        }
        foreach($testIntelligence as $intelligence) {
            $intelligence->delete();
        }
        foreach($testGroup as $group) {
            $group->delete();
        }
    }

    public function testSetGroupID() {
        //TODO: Implement
    }

    public function testInvalidGroupSetGroupID() {
        //TODO: Implement
    }

    public function testSetIntelligenceID() {
        //TODO: Implement
    }

    public function testInvalidIntelligenceSetIntelligenceID() {
        //TODO: Implement
    }

}
