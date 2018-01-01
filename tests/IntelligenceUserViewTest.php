<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/IntelligenceUserView.php';
require '../classes/Intelligence.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Database;
use AMC\Classes\Intelligence;
use AMC\Classes\IntelligenceUserView;
use AMC\Classes\User;
use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\InvalidIntelligenceException;
use AMC\Exceptions\InvalidUserException;
use PHPUnit\Framework\TestCase;

class IntelligenceUserViewTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check null constructor
        $testIntelligenceUserView = new IntelligenceUserView();

        $this->assertNull($testIntelligenceUserView->getID());
        $this->assertNull($testIntelligenceUserView->getUserID());
        $this->assertNull($testIntelligenceUserView->getIntelligenceID());

        // Check the non null constructor
        $testIntelligenceUserView = new IntelligenceUserView(1, 2, 3);

        $this->assertEquals(1, $testIntelligenceUserView->getID());
        $this->assertEquals(2, $testIntelligenceUserView->getUserID());
        $this->assertEquals(3, $testIntelligenceUserView->getIntelligenceID());
    }

    public function testCreate() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create test intelligence
        $testIntelligence = new Intelligence();
        $testIntelligence->setSubject('testIntelligence');
        $testIntelligence->create();

        // Create test intelligence user view
        $testIntelligenceUserView = new IntelligenceUserView();
        $testIntelligenceUserView->setUserID($testUser->getID());
        $testIntelligenceUserView->setIntelligenceID($testIntelligence->getID());
        $testIntelligenceUserView->create();

        // Check id is now an int
        $this->assertInternalType('int', $testIntelligenceUserView->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `IntelligenceUserViewID`,`UserID`,`IntelligenceID` FROM `Intelligence_User_Views` WHERE `IntelligenceUserViewID`=?");
        $stmt->bind_param('i', $testIntelligenceUserView->getID());
        $stmt->execute();
        $stmt->bind_result($intelligenceUserViewID, $userID, $intelligenceID);

        // Check there is only one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testIntelligenceUserView->getID(), $intelligenceUserViewID);
        $this->assertEquals($testIntelligenceUserView->getUserID(), $userID);
        $this->assertEquals($testIntelligenceUserView->getIntelligenceID(), $intelligenceID);

        $stmt->close();

        // Clean up
        $testIntelligenceUserView->delete();
        $testUser->delete();
        $testIntelligence->delete();
    }

    public function testBlankCreate() {
        // Create test intelligence user view
        $testIntelligenceUserView = new IntelligenceUserView();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testIntelligenceUserView->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Intelligence User View.', $e->getMessage());
        }
    }

    public function testUpdate() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create test intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setSubject('testIntelligence');
        $testIntelligence[0]->create();

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setSubject('testIntelligence2');
        $testIntelligence[1]->create();


        // Create test intelligence user view
        $testIntelligenceUserView = new IntelligenceUserView();
        $testIntelligenceUserView->setUserID($testUser[0]->getID());
        $testIntelligenceUserView->setIntelligenceID($testIntelligence[0]->getID());
        $testIntelligenceUserView->create();

        // Now update it
        $testIntelligenceUserView->setUserID($testUser[1]->getID());
        $testIntelligenceUserView->setIntelligenceID($testIntelligence[1]->getID());
        $testIntelligenceUserView->update();

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `IntelligenceUserViewID`,`UserID`,`IntelligenceID` FROM `Intelligence_User_Views` WHERE `IntelligenceUserViewID`=?");
        $stmt->bind_param('i', $testIntelligenceUserView->getID());
        $stmt->execute();
        $stmt->bind_result($intelligenceUserViewID, $userID, $intelligenceID);

        // Check there is only one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testIntelligenceUserView->getID(), $intelligenceUserViewID);
        $this->assertEquals($testIntelligenceUserView->getUserID(), $userID);
        $this->assertEquals($testIntelligenceUserView->getIntelligenceID(), $intelligenceID);

        $stmt->close();

        // Clean up
        $testIntelligenceUserView->delete();
        $testUser->delete();
        $testIntelligence->delete();
    }

    public function testBlankUpdate() {
        // Create test intelligence user view
        $testIntelligenceUserView = new IntelligenceUserView();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testIntelligenceUserView->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Intelligence User View.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create test intelligence
        $testIntelligence = new Intelligence();
        $testIntelligence->setSubject('testIntelligence');
        $testIntelligence->create();

        // Create test intelligence user view
        $testIntelligenceUserView = new IntelligenceUserView();
        $testIntelligenceUserView->setUserID($testUser->getID());
        $testIntelligenceUserView->setIntelligenceID($testIntelligence->getID());
        $testIntelligenceUserView->create();

        // Store id
        $id = $testIntelligenceUserView->getID();

        // Now delete it
        $testIntelligenceUserView->delete();

        // Check id is null
        $this->assertNull($testIntelligenceUserView->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `IntelligenceUserViewID`,`UserID`,`IntelligenceID` FROM `Intelligence_User_Views` WHERE `IntelligenceUserViewID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testUser->delete();
        $testIntelligence->delete();
    }

    public function testSelectWithInput() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create test intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setSubject('testIntelligence');
        $testIntelligence[0]->create();

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setSubject('testIntelligence2');
        $testIntelligence[1]->create();

        // Create test intelligence user view
        $testIntelligenceUserView = [];
        $testIntelligenceUserView[0] = new IntelligenceUserView();
        $testIntelligenceUserView[0]->setUserID($testUser[0]->getID());
        $testIntelligenceUserView[0]->setIntelligenceID($testIntelligence[0]->getID());
        $testIntelligenceUserView[0]->create();

        $testIntelligenceUserView[1] = new IntelligenceUserView();
        $testIntelligenceUserView[1]->setUserID($testUser[1]->getID());
        $testIntelligenceUserView[1]->setIntelligenceID($testIntelligence[1]->getID());
        $testIntelligenceUserView[1]->create();

        $testIntelligenceUserView[2] = new IntelligenceUserView();
        $testIntelligenceUserView[2]->setUserID($testUser[0]->getID());
        $testIntelligenceUserView[2]->setIntelligenceID($testIntelligence[1]->getID());
        $testIntelligenceUserView[2]->create();

        // Select and check a single
        $selectedSingle = IntelligenceUserView::select(array($testIntelligenceUserView[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInternalType(IntelligenceUserView::class, $selectedSingle[0]);
        $this->assertEquals($testIntelligenceUserView[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testIntelligenceUserView[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testIntelligenceUserView[0]->getIntelligenceID(), $selectedSingle[0]->getIntelligenceID());

        // Select and check multiple
        $selectedMultiple = IntelligenceUserView::select(array($testIntelligenceUserView[1]->getID(), $testIntelligenceUserView[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInternalType(IntelligenceUserView::class, $selectedMultiple[0]);
        $this->assertInternalType(IntelligenceUserView::class, $selectedMultiple[1]);

        if($testIntelligenceUserView[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testIntelligenceUserView[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testIntelligenceUserView[1]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testIntelligenceUserView[1]->getIntelligenceID(), $selectedMultiple[$i]->getIntelligenceID());

        $this->assertEquals($testIntelligenceUserView[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testIntelligenceUserView[2]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testIntelligenceUserView[2]->getIntelligenceID(), $selectedMultiple[$j]->getIntelligenceID());

        // Clean up
        foreach($testIntelligenceUserView as $intelligenceUserView) {
            $intelligenceUserView->delete();
        }
        foreach($testIntelligence as $intelligence) {
            $intelligence->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testSelectAll() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create test intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setSubject('testIntelligence');
        $testIntelligence[0]->create();

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setSubject('testIntelligence2');
        $testIntelligence[1]->create();

        // Create test intelligence user view
        $testIntelligenceUserView = [];
        $testIntelligenceUserView[0] = new IntelligenceUserView();
        $testIntelligenceUserView[0]->setUserID($testUser[0]->getID());
        $testIntelligenceUserView[0]->setIntelligenceID($testIntelligence[0]->getID());
        $testIntelligenceUserView[0]->create();

        $testIntelligenceUserView[1] = new IntelligenceUserView();
        $testIntelligenceUserView[1]->setUserID($testUser[1]->getID());
        $testIntelligenceUserView[1]->setIntelligenceID($testIntelligence[1]->getID());
        $testIntelligenceUserView[1]->create();

        // Select and check multiple
        $selectedMultiple = IntelligenceUserView::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInternalType(IntelligenceUserView::class, $selectedMultiple[0]);
        $this->assertInternalType(IntelligenceUserView::class, $selectedMultiple[1]);

        if($testIntelligenceUserView[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testIntelligenceUserView[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testIntelligenceUserView[0]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testIntelligenceUserView[0]->getIntelligenceID(), $selectedMultiple[$i]->getIntelligenceID());

        $this->assertEquals($testIntelligenceUserView[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testIntelligenceUserView[1]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testIntelligenceUserView[1]->getIntelligenceID(), $selectedMultiple[$j]->getIntelligenceID());

        // Clean up
        foreach($testIntelligenceUserView as $intelligenceUserView) {
            $intelligenceUserView->delete();
        }
        foreach($testIntelligence as $intelligence) {
            $intelligence->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testEql() {
        // Create test intelligence user view
        $testIntelligenceUserView = [];
        $testIntelligenceUserView[0] = new IntelligenceUserView();
        $testIntelligenceUserView[0]->setUserID(1);
        $testIntelligenceUserView[0]->setIntelligenceID(2);

        $testIntelligenceUserView[1] = new IntelligenceUserView();
        $testIntelligenceUserView[1]->setUserID(1);
        $testIntelligenceUserView[1]->setIntelligenceID(2);

        $testIntelligenceUserView[2] = new IntelligenceUserView();
        $testIntelligenceUserView[2]->setUserID(3);
        $testIntelligenceUserView[2]->setIntelligenceID(4);

        // Check same object is eql
        $this->assertTrue($testIntelligenceUserView[0]->eql($testIntelligenceUserView[0]));

        // Check same details are eql
        $this->assertTrue($testIntelligenceUserView[0]->eql($testIntelligenceUserView[0]));

        // Check different arent equal
        $this->assertFalse($testIntelligenceUserView[0]->eql($testIntelligenceUserView[0]));
    }

    public function testGetByUserID() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create test intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setSubject('testIntelligence');
        $testIntelligence[0]->create();

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setSubject('testIntelligence2');
        $testIntelligence[1]->create();

        // Create test intelligence user view
        $testIntelligenceUserView = [];
        $testIntelligenceUserView[0] = new IntelligenceUserView();
        $testIntelligenceUserView[0]->setUserID($testUser[0]->getID());
        $testIntelligenceUserView[0]->setIntelligenceID($testIntelligence[0]->getID());
        $testIntelligenceUserView[0]->create();

        $testIntelligenceUserView[1] = new IntelligenceUserView();
        $testIntelligenceUserView[1]->setUserID($testUser[1]->getID());
        $testIntelligenceUserView[1]->setIntelligenceID($testIntelligence[1]->getID());
        $testIntelligenceUserView[1]->create();

        // Select and check a single
        $selectedSingle = IntelligenceUserView::getByUserID($testUser[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInternalType(IntelligenceUserView::class, $selectedSingle[0]);
        $this->assertEquals($testIntelligenceUserView[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testIntelligenceUserView[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testIntelligenceUserView[0]->getIntelligenceID(), $selectedSingle[0]->getIntelligenceID());

        // Clean up
        foreach($testIntelligenceUserView as $intelligenceUserView) {
            $intelligenceUserView->delete();
        }
        foreach($testIntelligence as $intelligence) {
            $intelligence->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testGetByIntelligenceID() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create test intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setSubject('testIntelligence');
        $testIntelligence[0]->create();

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setSubject('testIntelligence2');
        $testIntelligence[1]->create();

        // Create test intelligence user view
        $testIntelligenceUserView = [];
        $testIntelligenceUserView[0] = new IntelligenceUserView();
        $testIntelligenceUserView[0]->setUserID($testUser[0]->getID());
        $testIntelligenceUserView[0]->setIntelligenceID($testIntelligence[0]->getID());
        $testIntelligenceUserView[0]->create();

        $testIntelligenceUserView[1] = new IntelligenceUserView();
        $testIntelligenceUserView[1]->setUserID($testUser[1]->getID());
        $testIntelligenceUserView[1]->setIntelligenceID($testIntelligence[1]->getID());
        $testIntelligenceUserView[1]->create();

        // Select and check a single
        $selectedSingle = IntelligenceUserView::getByIntelligenceID($testIntelligence[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInternalType(IntelligenceUserView::class, $selectedSingle[0]);
        $this->assertEquals($testIntelligenceUserView[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testIntelligenceUserView[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testIntelligenceUserView[0]->getIntelligenceID(), $selectedSingle[0]->getIntelligenceID());

        // Clean up
        foreach($testIntelligenceUserView as $intelligenceUserView) {
            $intelligenceUserView->delete();
        }
        foreach($testIntelligence as $intelligence) {
            $intelligence->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testSetUserID() {
        // Create test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create test intelligence user view
        $intelligenceUserView = new IntelligenceUserView();

        try {
            $intelligenceUserView->setUserID($testUser->getID(), true);
            $this->assertEquals($testUser->getID(), $intelligenceUserView->getUserID());
        } finally {

            // Clean up
            $testUser->delete();
        }
    }

    public function testInvalidUserSetUserID() {
        // get max user id and add one to it
        $stmt = Database::getConnection()->prepare("SELECT `UserID` FROM `Users` ORDER BY `UserID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($userID);
        if($stmt->fetch()) {
            $useID = $userID + 1;
        }
        else {
            $useID = 1;
        }
        $stmt->close();

        // Create an intelligence user view
        $testIntelView = new IntelligenceUserView();

        // Set expected exception
        $this->expectException(InvalidUserException::class);

        // Trigger it
        try {
            $testIntelView->setUserID($useID, true);
        } catch (InvalidUserException $e) {
            $this->assertEquals('No user exists with id ' . $useID, $e->getMessage());
        }
    }

    public function testSetIntelligenceID() {
        // Create a test intelligence
        $testIntelligence = new Intelligence();
        $testIntelligence->setSubject('test');
        $testIntelligence->create();

        // Create a test intelligence user view
        $testIntelligenceUserView = new IntelligenceUserView();

        // Try and set id
        try {
            $testIntelligenceUserView->setIntelligenceID($testIntelligence->getID(), true);
            $this->assertEquals($testIntelligence->getID(), $testIntelligenceUserView->getIntelligenceID());
        } finally {
            $testIntelligence->delete();
        }
    }

    public function testInvalidIntelligenceSetIntelligenceID() {
        // Get max intelligence id and add one to it
        $stmt = Database::getConnection()->prepare("SELECT `IntelligenceID` FROM `Intelligence` ORDER BY `IntelligenceID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($intelID);
        if($stmt->fetch()) {
            $useID = $intelID + 1;
        }
        else {
            $useID = 1;
        }
        $stmt->close();

        // FCreate test intel user view
        $testIntelView = new IntelligenceUserView();

        // Set expected exception
        $this->expectException(InvalidIntelligenceException::class);

        // Trigger it
        try {
            $testIntelView->setIntelligenceID($useID, true);
        } catch (InvalidIntelligenceException $e) {
            $this->assertEquals('No intelligence exists with id ' . $useID, $e->getMessage());
        }
    }

}
