<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Intelligence.php';
require '../classes/IntelligenceNote.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\IntelligenceNote;
use AMC\Classes\Database;
use AMC\Classes\User;
use AMC\Classes\Intelligence;
use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\InvalidIntelligenceException;
use AMC\Exceptions\InvalidUserException;
use PHPUnit\Framework\TestCase;

class IntelligenceNoteTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check null constructor
        $intelligenceNote = new IntelligenceNote();
        $this->assertNull($intelligenceNote->getID());
        $this->assertNull($intelligenceNote->getUserID());
        $this->assertNull($intelligenceNote->getIntelligenceID());
        $this->assertNull($intelligenceNote->getBody());
        $this->assertNull($intelligenceNote->getTimestamp());

        // Check non null constructor
        $intelligenceNote = new IntelligenceNote(1, 2, 3, 'body', 123);
        $this->assertEquals(1, $intelligenceNote->getID());
        $this->assertEquals(2, $intelligenceNote->getIntelligenceID());
        $this->assertEquals(3, $intelligenceNote->getUserID());
        $this->assertEquals('body', $intelligenceNote->getBody());
        $this->assertEquals(123, $intelligenceNote->getTimestamp());
    }

    public function testCreate() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create test Intelligence
        $testIntelligence = new Intelligence();
        $testIntelligence->setAuthorID($testUser->getID());
        $testIntelligence->setSubject('testSubject');
        $testIntelligence->create();

        // Create test Intelligence Note
        $testIntelligenceNote = new IntelligenceNote();
        $testIntelligenceNote->setUserID($testUser->getID());
        $testIntelligenceNote->setIntelligenceID($testIntelligence->getID());
        $testIntelligenceNote->setBody('testBody');
        $testIntelligenceNote->setTimestamp(123);
        $testIntelligenceNote->create();

        // Check id is now an int
        $this->assertInternalType('int', $testIntelligenceNote->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `IntelligenceNoteID`,`IntelligenceID`,`UserID`,`IntelligenceNoteBody`,`IntelligenceNoteTimestamp` FROM `Intelligence_Notes` WHERE `IntelligenceNoteID`=?");
        $stmt->bind_param('i', $testIntelligenceNote->getID());
        $stmt->execute();
        $stmt->bind_result($intelligenceNoteID, $intelligenceID, $userID, $body, $timestamp);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        // Check it
        $this->assertEquals($testIntelligenceNote->getID(), $intelligenceNoteID);
        $this->assertEquals($testIntelligenceNote->getUserID(), $userID);
        $this->assertEquals($testIntelligenceNote->getIntelligenceID(), $intelligenceID);
        $this->assertEquals($testIntelligenceNote->getBody(), $body);
        $this->assertEquals($testIntelligenceNote->getTimestamp(), $timestamp);

        $stmt->close();

        // Clean up
        $testIntelligenceNote->delete();
        $testIntelligence->delete();
        $testUser->delete();
    }

    public function testBlankCreate() {
        // Create a test intelligence note
        $testIntelligenceNote = new IntelligenceNote();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testIntelligenceNote->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Intelligence Note.', $e->getMessage());
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

        // Create test Intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setAuthorID($testUser[0]->getID());
        $testIntelligence[0]->setSubject('testSubject');
        $testIntelligence[0]->create();

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setAuthorID($testUser[0]->getID());
        $testIntelligence[1]->setSubject('testSubject2');
        $testIntelligence[1]->create();

        // Create test Intelligence Note
        $testIntelligenceNote = new IntelligenceNote();
        $testIntelligenceNote->setUserID($testUser[0]->getID());
        $testIntelligenceNote->setIntelligenceID($testIntelligence[0]->getID());
        $testIntelligenceNote->setBody('testBody');
        $testIntelligenceNote->setTimestamp(123);
        $testIntelligenceNote->create();

        // Now update it
        $testIntelligenceNote->setUserID($testUser[1]->getID());
        $testIntelligenceNote->setIntelligenceID($testIntelligence[1]->getID());
        $testIntelligenceNote->setBody('testBody2');
        $testIntelligenceNote->setTimestamp(12345);
        $testIntelligenceNote->update();

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `IntelligenceNoteID`,`IntelligenceID`,`UserID`,`IntelligenceNoteBody`,`IntelligenceNoteTimestamp` FROM `Intelligence_Notes` WHERE `IntelligenceNoteID`=?");
        $stmt->bind_param('i', $testIntelligenceNote->getID());
        $stmt->execute();
        $stmt->bind_result($intelligenceNoteID, $intelligenceID, $userID, $body, $timestamp);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        // Check it
        $this->assertEquals($testIntelligenceNote->getID(), $intelligenceNoteID);
        $this->assertEquals($testIntelligenceNote->getUserID(), $userID);
        $this->assertEquals($testIntelligenceNote->getIntelligenceID(), $intelligenceID);
        $this->assertEquals($testIntelligenceNote->getBody(), $body);
        $this->assertEquals($testIntelligenceNote->getTimestamp(), $timestamp);

        $stmt->close();

        // Clean up
        $testIntelligenceNote->delete();
        foreach($testIntelligence as $intelligence) {
            $intelligence->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testBlankUpdate() {
        // Create a test intelligence note
        $testIntelligenceNote = new IntelligenceNote();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testIntelligenceNote->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Intelligence Note.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create test Intelligence
        $testIntelligence = new Intelligence();
        $testIntelligence->setAuthorID($testUser->getID());
        $testIntelligence->setSubject('testSubject');
        $testIntelligence->create();

        // Create test Intelligence Note
        $testIntelligenceNote = new IntelligenceNote();
        $testIntelligenceNote->setUserID($testUser->getID());
        $testIntelligenceNote->setIntelligenceID($testIntelligence->getID());
        $testIntelligenceNote->setBody('testBody');
        $testIntelligenceNote->setTimestamp(123);
        $testIntelligenceNote->create();

        // Store the id
        $id = $testIntelligenceNote->getID();

        // Now delete it
        $testIntelligenceNote->delete();

        // Check id is now null
        $this->assertNull($testIntelligenceNote->getID());

        // Check its gone
        $stmt = $this->_connection->prepare("SELECT `IntelligenceNoteID`,`IntelligenceID`,`UserID`,`IntelligenceNoteBody`,`IntelligenceNoteTimestamp` FROM `Intelligence_Notes` WHERE `IntelligenceNoteID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testIntelligence->delete();
        $testUser->delete();
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

        // Create test Intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setAuthorID($testUser[0]->getID());
        $testIntelligence[0]->setSubject('testSubject');
        $testIntelligence[0]->create();

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setAuthorID($testUser[0]->getID());
        $testIntelligence[1]->setSubject('testSubject2');
        $testIntelligence[1]->create();

        // Create test Intelligence Note
        $testIntelligenceNote[0] = [];
        $testIntelligenceNote[0] = new IntelligenceNote();
        $testIntelligenceNote[0]->setUserID($testUser[0]->getID());
        $testIntelligenceNote[0]->setIntelligenceID($testIntelligence[0]->getID());
        $testIntelligenceNote[0]->setBody('testBody');
        $testIntelligenceNote[0]->setTimestamp(123);
        $testIntelligenceNote[0]->create();

        $testIntelligenceNote[1] = new IntelligenceNote();
        $testIntelligenceNote[1]->setUserID($testUser[1]->getID());
        $testIntelligenceNote[1]->setIntelligenceID($testIntelligence[1]->getID());
        $testIntelligenceNote[1]->setBody('testBody2');
        $testIntelligenceNote[1]->setTimestamp(12345);
        $testIntelligenceNote[1]->create();

        $testIntelligenceNote[2] = new IntelligenceNote();
        $testIntelligenceNote[2]->setUserID($testUser[0]->getID());
        $testIntelligenceNote[2]->setIntelligenceID($testIntelligence[1]->getID());
        $testIntelligenceNote[2]->setBody('testBody3');
        $testIntelligenceNote[2]->setTimestamp(12345678);
        $testIntelligenceNote[2]->create();

        // Select and check a single
        $selectedSingle = IntelligenceNote::select(array($testIntelligenceNote[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(IntelligenceNote::class, $selectedSingle[0]);
        $this->assertEquals($testIntelligenceNote[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testIntelligenceNote[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testIntelligenceNote[0]->getIntelligenceID(), $selectedSingle[0]->getIntelligenceID());
        $this->assertEquals($testIntelligenceNote[0]->getBody(), $selectedSingle[0]->getBody());
        $this->assertEquals($testIntelligenceNote[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Select and check multiple
        $selectedMultiple = IntelligenceNote::select(array($testIntelligenceNote[1]->getID(), $testIntelligenceNote[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(IntelligenceNote::class, $selectedMultiple[0]);
        $this->assertInstanceOf(IntelligenceNote::class, $selectedMultiple[1]);

        if($testIntelligenceNote[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testIntelligenceNote[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testIntelligenceNote[1]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testIntelligenceNote[1]->getIntelligenceID(), $selectedMultiple[$i]->getIntelligenceID());
        $this->assertEquals($testIntelligenceNote[1]->getBody(), $selectedMultiple[$i]->getBody());
        $this->assertEquals($testIntelligenceNote[1]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());

        $this->assertEquals($testIntelligenceNote[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testIntelligenceNote[2]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testIntelligenceNote[2]->getIntelligenceID(), $selectedMultiple[$j]->getIntelligenceID());
        $this->assertEquals($testIntelligenceNote[2]->getBody(), $selectedMultiple[$j]->getBody());
        $this->assertEquals($testIntelligenceNote[2]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());

        // Clean up
        foreach($testIntelligenceNote as $intelligenceNote) {
            $intelligenceNote->delete();
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

        // Create test Intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setAuthorID($testUser[0]->getID());
        $testIntelligence[0]->setSubject('testSubject');
        $testIntelligence[0]->create();

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setAuthorID($testUser[0]->getID());
        $testIntelligence[1]->setSubject('testSubject2');
        $testIntelligence[1]->create();

        // Create test Intelligence Note
        $testIntelligenceNote[0] = [];
        $testIntelligenceNote[0] = new IntelligenceNote();
        $testIntelligenceNote[0]->setUserID($testUser[0]->getID());
        $testIntelligenceNote[0]->setIntelligenceID($testIntelligence[0]->getID());
        $testIntelligenceNote[0]->setBody('testBody');
        $testIntelligenceNote[0]->setTimestamp(123);
        $testIntelligenceNote[0]->create();

        $testIntelligenceNote[1] = new IntelligenceNote();
        $testIntelligenceNote[1]->setUserID($testUser[1]->getID());
        $testIntelligenceNote[1]->setIntelligenceID($testIntelligence[1]->getID());
        $testIntelligenceNote[1]->setBody('testBody2');
        $testIntelligenceNote[1]->setTimestamp(12345);
        $testIntelligenceNote[1]->create();

        // Select and check multiple
        $selectedMultiple = IntelligenceNote::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(IntelligenceNote::class, $selectedMultiple[0]);
        $this->assertInstanceOf(IntelligenceNote::class, $selectedMultiple[1]);

        if($testIntelligenceNote[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testIntelligenceNote[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testIntelligenceNote[0]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testIntelligenceNote[0]->getIntelligenceID(), $selectedMultiple[$i]->getIntelligenceID());
        $this->assertEquals($testIntelligenceNote[0]->getBody(), $selectedMultiple[$i]->getBody());
        $this->assertEquals($testIntelligenceNote[0]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());

        $this->assertEquals($testIntelligenceNote[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testIntelligenceNote[1]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testIntelligenceNote[1]->getIntelligenceID(), $selectedMultiple[$j]->getIntelligenceID());
        $this->assertEquals($testIntelligenceNote[1]->getBody(), $selectedMultiple[$j]->getBody());
        $this->assertEquals($testIntelligenceNote[1]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());

        // Clean up
        foreach($testIntelligenceNote as $intelligenceNote) {
            $intelligenceNote->delete();
        }
        foreach($testIntelligence as $intelligence) {
            $intelligence->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testEql() {
        // Create test Intelligence Note
        $testIntelligenceNote[0] = [];
        $testIntelligenceNote[0] = new IntelligenceNote();
        $testIntelligenceNote[0]->setUserID(1);
        $testIntelligenceNote[0]->setIntelligenceID(2);
        $testIntelligenceNote[0]->setBody('testBody');
        $testIntelligenceNote[0]->setTimestamp(123);

        $testIntelligenceNote[1] = new IntelligenceNote();
        $testIntelligenceNote[1]->setUserID(1);
        $testIntelligenceNote[1]->setIntelligenceID(2);
        $testIntelligenceNote[1]->setBody('testBody');
        $testIntelligenceNote[1]->setTimestamp(123);

        $testIntelligenceNote[2] = new IntelligenceNote();
        $testIntelligenceNote[2]->setUserID(3);
        $testIntelligenceNote[2]->setIntelligenceID(4);
        $testIntelligenceNote[2]->setBody('testBody2');
        $testIntelligenceNote[2]->setTimestamp(12345);

        // Check same object is eql
        $this->assertTrue($testIntelligenceNote[0]->eql($testIntelligenceNote[0]));

        // Check same details are eql
        $this->assertTrue($testIntelligenceNote[0]->eql($testIntelligenceNote[0]));

        // Check different arent equal
        $this->assertFalse($testIntelligenceNote[0]->eql($testIntelligenceNote[0]));
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

        // Create test Intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setAuthorID($testUser[0]->getID());
        $testIntelligence[0]->setSubject('testSubject');
        $testIntelligence[0]->create();

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setAuthorID($testUser[0]->getID());
        $testIntelligence[1]->setSubject('testSubject2');
        $testIntelligence[1]->create();

        // Create test Intelligence Note
        $testIntelligenceNote[0] = [];
        $testIntelligenceNote[0] = new IntelligenceNote();
        $testIntelligenceNote[0]->setUserID($testUser[0]->getID());
        $testIntelligenceNote[0]->setIntelligenceID($testIntelligence[0]->getID());
        $testIntelligenceNote[0]->setBody('testBody');
        $testIntelligenceNote[0]->setTimestamp(123);
        $testIntelligenceNote[0]->create();

        $testIntelligenceNote[1] = new IntelligenceNote();
        $testIntelligenceNote[1]->setUserID($testUser[1]->getID());
        $testIntelligenceNote[1]->setIntelligenceID($testIntelligence[1]->getID());
        $testIntelligenceNote[1]->setBody('testBody2');
        $testIntelligenceNote[1]->setTimestamp(12345);
        $testIntelligenceNote[1]->create();

        // Select and check a single
        $selectedSingle = IntelligenceNote::getByUserID($testUser[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(IntelligenceNote::class, $selectedSingle[0]);
        $this->assertEquals($testIntelligenceNote[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testIntelligenceNote[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testIntelligenceNote[0]->getIntelligenceID(), $selectedSingle[0]->getIntelligenceID());
        $this->assertEquals($testIntelligenceNote[0]->getBody(), $selectedSingle[0]->getBody());
        $this->assertEquals($testIntelligenceNote[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Clean up
        foreach($testIntelligenceNote as $intelligenceNote) {
            $intelligenceNote->delete();
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

        // Create test Intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setAuthorID($testUser[0]->getID());
        $testIntelligence[0]->setSubject('testSubject');
        $testIntelligence[0]->create();

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setAuthorID($testUser[0]->getID());
        $testIntelligence[1]->setSubject('testSubject2');
        $testIntelligence[1]->create();

        // Create test Intelligence Note
        $testIntelligenceNote[0] = [];
        $testIntelligenceNote[0] = new IntelligenceNote();
        $testIntelligenceNote[0]->setUserID($testUser[0]->getID());
        $testIntelligenceNote[0]->setIntelligenceID($testIntelligence[0]->getID());
        $testIntelligenceNote[0]->setBody('testBody');
        $testIntelligenceNote[0]->setTimestamp(123);
        $testIntelligenceNote[0]->create();

        $testIntelligenceNote[1] = new IntelligenceNote();
        $testIntelligenceNote[1]->setUserID($testUser[1]->getID());
        $testIntelligenceNote[1]->setIntelligenceID($testIntelligence[1]->getID());
        $testIntelligenceNote[1]->setBody('testBody2');
        $testIntelligenceNote[1]->setTimestamp(12345);
        $testIntelligenceNote[1]->create();

        // Select and check a single
        $selectedSingle = IntelligenceNote::getByIntelligenceID($testIntelligence[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(IntelligenceNote::class, $selectedSingle[0]);
        $this->assertEquals($testIntelligenceNote[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testIntelligenceNote[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testIntelligenceNote[0]->getIntelligenceID(), $selectedSingle[0]->getIntelligenceID());
        $this->assertEquals($testIntelligenceNote[0]->getBody(), $selectedSingle[0]->getBody());
        $this->assertEquals($testIntelligenceNote[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Clean up
        foreach($testIntelligenceNote as $intelligenceNote) {
            $intelligenceNote->delete();
        }
        foreach($testIntelligence as $intelligence) {
            $intelligence->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testSetIntelligenceID() {
        // Create test intelligence
        $testIntelligence = new Intelligence();
        $testIntelligence->setSubject('test');
        $testIntelligence->create();

        // Create a test intelligence note
        $testIntelligenceNote = new IntelligenceNote();

        // Try and set the id
        try {
            $testIntelligenceNote->setIntelligenceID($testIntelligence->getID(), true);
            $this->assertEquals($testIntelligence->getID(), $testIntelligenceNote->getIntelligenceID());
        } finally {
            $testIntelligence->delete();
        }
    }

    public function testInvalidIntelligenceSetIntelligenceID() {
        // Get max intelligence id and add one to it
        $stmt = Database::getConnection()->prepare("SELECT `IntelligenceID` FROM `Intelligence` ORDER BY `IntelligenceID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($intelligenceID);
        if($stmt->fetch()) {
            $useID = $intelligenceID + 1;
        }
        else {
            $useID = 1;
        }
        $stmt->close();

        // Create test intelligence note
        $testIntelNote = new IntelligenceNote();

        // Set expected exception
        $this->expectException(InvalidIntelligenceException::class);

        // Trigger it
        try {
            $testIntelNote->setIntelligenceID($useID, true);
        } catch (InvalidIntelligenceException $e) {
            $this->assertEquals('There is no intelligence with id ' . $useID, $e->getMessage());
        }
    }

    public function testSetUserID() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('test');
        $testUser->create();

        // Create a test intel note
        $testIntelNote = new IntelligenceNote();

        // Try and set the id
        try {
            $testIntelNote->setUserID($testUser->getID(), true);
            $this->assertEquals($testUser->getID(), $testIntelNote->getID());
        } finally {
            $testUser->delete();
        }
    }


    public function testInvalidUserSetUserID() {
        // Find max user id and add one to it
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

        // Create intel note
        $testIntelNote = new IntelligenceNote();

        // Set expected exception
        $this->expectException(InvalidUserException::class);

        // Trigger it
        try {
            $testIntelNote->setUserID($useID, true);
        } catch (InvalidUserException $e) {
            $this->assertEquals('No user exists with id ' .  $useID, $e->getMessage());
        }
    }

}
