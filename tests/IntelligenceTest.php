<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Intelligence.php';
require '../classes/IntelligenceType.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Intelligence;
use AMC\Classes\Database;
use AMC\Classes\IntelligenceType;
use AMC\Classes\User;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class IntelligenceTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('jirbj.co.uk', 'testingDB', 'testingDB', 'testingdb');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check null constructor
        $intelligence = new Intelligence();

        $this->assertNull($intelligence->getID());
        $this->assertNull($intelligence->getAuthorID());
        $this->assertNull($intelligence->getIntelligenceTypeID());
        $this->assertNull($intelligence->getSubject());
        $this->assertNull($intelligence->getBody());
        $this->assertNull($intelligence->getTimestamp());
        $this->assertNull($intelligence->getPublic());

        // Check non null constructor
        $intelligence = new Intelligence(1, 2, 3, 'subject', 'body', 123, false);

        $this->assertEquals(1, $intelligence->getID());
        $this->assertEquals(2, $intelligence->getAuthorID());
        $this->assertEquals(3, $intelligence->getIntelligenceTypeID());
        $this->assertEquals('subject', $intelligence->getSubject());
        $this->assertEquals('body', $intelligence->getBody());
        $this->assertEquals(123, $intelligence->getTimestamp());
        $this->assertFalse($intelligence->getPublic());
    }

    public function testCreate() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test intelligence type
        $testIntelligenceType = new IntelligenceType();
        $testIntelligenceType->setName('testIntelligenceType');
        $testIntelligenceType->create();

        // Create a test intelligence
        $testIntelligence = new Intelligence();
        $testIntelligence->setAuthorID($testUser->getID());
        $testIntelligence->setIntelligenceTypeID($testIntelligenceType->getID());
        $testIntelligence->setSubject('testSubject');
        $testIntelligence->setBody('testBody');
        $testIntelligence->setTimestamp(123);
        $testIntelligence->setPublic(false);
        $testIntelligence->create();

        // Check id is now an int
        $this->assertInternalType('int', $testIntelligence->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `IntelligenceID`,`AuthorID`,`IntelligenceTypeID`,`IntelligenceSubject`,`IntelligenceBody`,`IntelligenceTimestamp`,`IntelligencePublic` FROM `Intelligence` WHERE `IntelligenceID`=?");
        $stmt->bind_param('i', $testIntelligence->getID());
        $stmt->execute();
        $stmt->bind_result($intelligenceID, $authorID, $intelligenceTypeID, $subject, $body, $timestamp, $public);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        if($public == 1) {
            $public = true;
        }
        else if($public == 0) {
            $public = false;
        }

        $this->assertEquals($testIntelligence->getID(), $intelligenceID);
        $this->assertEquals($testIntelligence->getAuthorID(), $authorID);
        $this->assertEquals($testIntelligence->getIntelligenceTypeID(), $intelligenceTypeID);
        $this->assertEquals($testIntelligence->getSubject(), $subject);
        $this->assertEquals($testIntelligence->getBody(), $body);
        $this->assertEquals($testIntelligence->getTimestamp(), $timestamp);
        $this->assertEquals($testIntelligence->getPublic(), $public);

        $stmt->close();

        // Clean up
        $testIntelligence->delete();
        $testUser->delete();
        $testIntelligenceType->delete();
    }

    public function testBlankCreate() {
        // Create test intelligence
        $testIntelligence = new Intelligence();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testIntelligence->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store blank Intelligence.', $e->getMessage());
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

        // Create a test intelligence type
        $testIntelligenceType = [];
        $testIntelligenceType[0] = new IntelligenceType();
        $testIntelligenceType[0]->setName('testIntelligenceType');
        $testIntelligenceType[0]->create();

        $testIntelligenceType[1] = new IntelligenceType();
        $testIntelligenceType[1]->setName('testIntelligenceType2');
        $testIntelligenceType[1]->create();

        // Create a test intelligence
        $testIntelligence = new Intelligence();
        $testIntelligence->setAuthorID($testUser[0]->getID());
        $testIntelligence->setIntelligenceTypeID($testIntelligenceType[0]->getID());
        $testIntelligence->setSubject('testSubject');
        $testIntelligence->setBody('testBody');
        $testIntelligence->setTimestamp(123);
        $testIntelligence->setPublic(false);
        $testIntelligence->create();

        // Now update it
        $testIntelligence->setAuthorID($testUser[1]->getID());
        $testIntelligence->setIntelligenceTypeID($testIntelligenceType[1]->getID());
        $testIntelligence->setSubject('testSubject2');
        $testIntelligence->setBody('testBody2');
        $testIntelligence->setTimestamp(12345);
        $testIntelligence->setPublic(true);
        $testIntelligence->create();

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `IntelligenceID`,`AuthorID`,`IntelligenceTypeID`,`IntelligenceSubject`,`IntelligenceBody`,`IntelligenceTimestamp`,`IntelligencePublic` FROM `Intelligence` WHERE `IntelligenceID`=?");
        $stmt->bind_param('i', $testIntelligence->getID());
        $stmt->execute();
        $stmt->bind_result($intelligenceID, $authorID, $intelligenceTypeID, $subject, $body, $timestamp, $public);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        if($public == 1) {
            $public = true;
        }
        else if($public == 0) {
            $public = false;
        }

        $this->assertEquals($testIntelligence->getID(), $intelligenceID);
        $this->assertEquals($testIntelligence->getAuthorID(), $authorID);
        $this->assertEquals($testIntelligence->getIntelligenceTypeID(), $intelligenceTypeID);
        $this->assertEquals($testIntelligence->getSubject(), $subject);
        $this->assertEquals($testIntelligence->getBody(), $body);
        $this->assertEquals($testIntelligence->getTimestamp(), $timestamp);
        $this->assertEquals($testIntelligence->getPublic(), $public);

        $stmt->close();

        // Clean up
        $testIntelligence->delete();
        $testUser->delete();
        $testIntelligenceType->delete();
    }

    public function testBlankUpdate() {
        // Create test intelligence
        $testIntelligence = new Intelligence();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testIntelligence->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store blank Intelligence.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test intelligence type
        $testIntelligenceType = new IntelligenceType();
        $testIntelligenceType->setName('testIntelligenceType');
        $testIntelligenceType->create();

        // Create a test intelligence
        $testIntelligence = new Intelligence();
        $testIntelligence->setAuthorID($testUser->getID());
        $testIntelligence->setIntelligenceTypeID($testIntelligenceType->getID());
        $testIntelligence->setSubject('testSubject');
        $testIntelligence->setBody('testBody');
        $testIntelligence->setTimestamp(123);
        $testIntelligence->setPublic(false);
        $testIntelligence->create();

        // Store the id
        $id = $testIntelligence->getID();

        // Now delete it
        $testIntelligence->delete();

        // Check id is null
        $this->assertNull($testIntelligence->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `IntelligenceID`,`AuthorID`,`IntelligenceTypeID`,`IntelligenceSubject`,`IntelligenceBody`,`IntelligenceTimestamp`,`IntelligencePublic` FROM `Intelligence` WHERE `IntelligenceID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testUser->delete();
        $testIntelligenceType->delete();
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

        // Create a test intelligence type
        $testIntelligenceType = [];
        $testIntelligenceType[0] = new IntelligenceType();
        $testIntelligenceType[0]->setName('testIntelligenceType');
        $testIntelligenceType[0]->create();

        $testIntelligenceType[1] = new IntelligenceType();
        $testIntelligenceType[1]->setName('testIntelligenceType2');
        $testIntelligenceType[1]->create();

        // Create a test intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setAuthorID($testUser[0]->getID());
        $testIntelligence[0]->setIntelligenceTypeID($testIntelligenceType[0]->getID());
        $testIntelligence[0]->setSubject('testSubject');
        $testIntelligence[0]->setBody('testBody');
        $testIntelligence[0]->setTimestamp(123);
        $testIntelligence[0]->setPublic(false);
        $testIntelligence[0]->create();

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setAuthorID($testUser[1]->getID());
        $testIntelligence[1]->setIntelligenceTypeID($testIntelligenceType[1]->getID());
        $testIntelligence[1]->setSubject('testSubject2');
        $testIntelligence[1]->setBody('testBody2');
        $testIntelligence[1]->setTimestamp(12345);
        $testIntelligence[1]->setPublic(false);
        $testIntelligence[1]->create();

        $testIntelligence[2] = new Intelligence();
        $testIntelligence[2]->setAuthorID($testUser[0]->getID());
        $testIntelligence[2]->setIntelligenceTypeID($testIntelligenceType[1]->getID());
        $testIntelligence[2]->setSubject('testSubject3');
        $testIntelligence[2]->setBody('testBody3');
        $testIntelligence[2]->setTimestamp(12345678);
        $testIntelligence[2]->setPublic(true);
        $testIntelligence[2]->create();

        // Get and check a single
        $selectedSingle = Intelligence::select(array($testIntelligence[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(Intelligence::class, $selectedSingle[0]);
        $this->assertEquals($testIntelligence[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testIntelligence[0]->getAuthorID(), $selectedSingle[0]->getAuthorID());
        $this->assertEquals($testIntelligence[0]->getIntelligenceTypeID(), $selectedSingle[0]->getIntelligenceTypeID());
        $this->assertEquals($testIntelligence[0]->getSubject(), $selectedSingle[0]->getSubject());
        $this->assertEquals($testIntelligence[0]->getBody(), $selectedSingle[0]->getBody());
        $this->assertEquals($testIntelligence[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());
        $this->assertEquals($testIntelligence[0]->getPublic(), $selectedSingle[0]->getPublic());

        // Get and check multiple
        $selectedMultiple = Intelligence::select(array($testIntelligence[1]->getID(), $testIntelligence[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(1, \count($selectedMultiple));
        $this->assertInstanceOf(Intelligence::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Intelligence::class, $selectedMultiple[0]);

        if($testIntelligence[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testIntelligence[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testIntelligence[1]->getAuthorID(), $selectedMultiple[$i]->getAuthorID());
        $this->assertEquals($testIntelligence[1]->getIntelligenceTypeID(), $selectedMultiple[$i]->getIntelligenceTypeID());
        $this->assertEquals($testIntelligence[1]->getSubject(), $selectedMultiple[$i]->getSubject());
        $this->assertEquals($testIntelligence[1]->getBody(), $selectedMultiple[$i]->getBody());
        $this->assertEquals($testIntelligence[1]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());
        $this->assertEquals($testIntelligence[1]->getPublic(), $selectedMultiple[$i]->getPublic());

        $this->assertEquals($testIntelligence[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testIntelligence[2]->getAuthorID(), $selectedMultiple[$j]->getAuthorID());
        $this->assertEquals($testIntelligence[2]->getIntelligenceTypeID(), $selectedMultiple[$j]->getIntelligenceTypeID());
        $this->assertEquals($testIntelligence[2]->getSubject(), $selectedMultiple[$j]->getSubject());
        $this->assertEquals($testIntelligence[2]->getBody(), $selectedMultiple[$j]->getBody());
        $this->assertEquals($testIntelligence[2]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());
        $this->assertEquals($testIntelligence[2]->getPublic(), $selectedMultiple[$j]->getPublic());

        // Clean up
        foreach($testIntelligence as $intelligence) {
            $intelligence->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testIntelligenceType as $intelligenceType) {
            $intelligenceType->delete();
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

        // Create a test intelligence type
        $testIntelligenceType = [];
        $testIntelligenceType[0] = new IntelligenceType();
        $testIntelligenceType[0]->setName('testIntelligenceType');
        $testIntelligenceType[0]->create();

        $testIntelligenceType[1] = new IntelligenceType();
        $testIntelligenceType[1]->setName('testIntelligenceType2');
        $testIntelligenceType[1]->create();

        // Create a test intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setAuthorID($testUser[0]->getID());
        $testIntelligence[0]->setIntelligenceTypeID($testIntelligenceType[0]->getID());
        $testIntelligence[0]->setSubject('testSubject');
        $testIntelligence[0]->setBody('testBody');
        $testIntelligence[0]->setTimestamp(123);
        $testIntelligence[0]->setPublic(false);
        $testIntelligence[0]->create();

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setAuthorID($testUser[1]->getID());
        $testIntelligence[1]->setIntelligenceTypeID($testIntelligenceType[1]->getID());
        $testIntelligence[1]->setSubject('testSubject2');
        $testIntelligence[1]->setBody('testBody2');
        $testIntelligence[1]->setTimestamp(12345);
        $testIntelligence[1]->setPublic(false);
        $testIntelligence[1]->create();

        // Get and check multiple
        $selectedMultiple = Intelligence::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(1, \count($selectedMultiple));
        $this->assertInstanceOf(Intelligence::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Intelligence::class, $selectedMultiple[0]);

        if($testIntelligence[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testIntelligence[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testIntelligence[0]->getAuthorID(), $selectedMultiple[$i]->getAuthorID());
        $this->assertEquals($testIntelligence[0]->getIntelligenceTypeID(), $selectedMultiple[$i]->getIntelligenceTypeID());
        $this->assertEquals($testIntelligence[0]->getSubject(), $selectedMultiple[$i]->getSubject());
        $this->assertEquals($testIntelligence[0]->getBody(), $selectedMultiple[$i]->getBody());
        $this->assertEquals($testIntelligence[0]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());
        $this->assertEquals($testIntelligence[0]->getPublic(), $selectedMultiple[$i]->getPublic());

        $this->assertEquals($testIntelligence[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testIntelligence[1]->getAuthorID(), $selectedMultiple[$j]->getAuthorID());
        $this->assertEquals($testIntelligence[1]->getIntelligenceTypeID(), $selectedMultiple[$j]->getIntelligenceTypeID());
        $this->assertEquals($testIntelligence[1]->getSubject(), $selectedMultiple[$j]->getSubject());
        $this->assertEquals($testIntelligence[1]->getBody(), $selectedMultiple[$j]->getBody());
        $this->assertEquals($testIntelligence[1]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());
        $this->assertEquals($testIntelligence[1]->getPublic(), $selectedMultiple[$j]->getPublic());

        // Clean up
        foreach($testIntelligence as $intelligence) {
            $intelligence->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testIntelligenceType as $intelligenceType) {
            $intelligenceType->delete();
        }
    }

    public function testEql() {
        // Create test intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setAuthorID(1);
        $testIntelligence[0]->setIntelligenceTypeID(2);
        $testIntelligence[0]->setSubject('testSubject');
        $testIntelligence[0]->setBody('testBody');
        $testIntelligence[0]->setTimestamp(123);
        $testIntelligence[0]->setPublic(false);

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setAuthorID(1);
        $testIntelligence[1]->setIntelligenceTypeID(2);
        $testIntelligence[1]->setSubject('testSubject');
        $testIntelligence[1]->setBody('testBody');
        $testIntelligence[1]->setTimestamp(123);
        $testIntelligence[1]->setPublic(false);

        $testIntelligence[2] = new Intelligence();
        $testIntelligence[2]->setAuthorID(3);
        $testIntelligence[2]->setIntelligenceTypeID(4);
        $testIntelligence[2]->setSubject('testSubject2');
        $testIntelligence[2]->setBody('testBody2');
        $testIntelligence[2]->setTimestamp(12345);
        $testIntelligence[2]->setPublic(true);

        // Check same object is eql
        $this->assertTrue($testIntelligence[0]->eql($testIntelligence[0]));

        // Check same details are eql
        $this->assertTrue($testIntelligence[0]->eql($testIntelligence[0]));

        // Check different arent equal
        $this->assertFalse($testIntelligence[0]->eql($testIntelligence[0]));
    }

    public function testGetByAuthorID() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test intelligence type
        $testIntelligenceType = [];
        $testIntelligenceType[0] = new IntelligenceType();
        $testIntelligenceType[0]->setName('testIntelligenceType');
        $testIntelligenceType[0]->create();

        $testIntelligenceType[1] = new IntelligenceType();
        $testIntelligenceType[1]->setName('testIntelligenceType2');
        $testIntelligenceType[1]->create();

        // Create a test intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setAuthorID($testUser[0]->getID());
        $testIntelligence[0]->setIntelligenceTypeID($testIntelligenceType[0]->getID());
        $testIntelligence[0]->setSubject('testSubject');
        $testIntelligence[0]->setBody('testBody');
        $testIntelligence[0]->setTimestamp(123);
        $testIntelligence[0]->setPublic(false);
        $testIntelligence[0]->create();

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setAuthorID($testUser[1]->getID());
        $testIntelligence[1]->setIntelligenceTypeID($testIntelligenceType[1]->getID());
        $testIntelligence[1]->setSubject('testSubject2');
        $testIntelligence[1]->setBody('testBody2');
        $testIntelligence[1]->setTimestamp(12345);
        $testIntelligence[1]->setPublic(false);
        $testIntelligence[1]->create();

        // Get and check a single
        $selectedSingle = Intelligence::getByAuthorID($testUser[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(Intelligence::class, $selectedSingle[0]);
        $this->assertEquals($testIntelligence[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testIntelligence[0]->getAuthorID(), $selectedSingle[0]->getAuthorID());
        $this->assertEquals($testIntelligence[0]->getIntelligenceTypeID(), $selectedSingle[0]->getIntelligenceTypeID());
        $this->assertEquals($testIntelligence[0]->getSubject(), $selectedSingle[0]->getSubject());
        $this->assertEquals($testIntelligence[0]->getBody(), $selectedSingle[0]->getBody());
        $this->assertEquals($testIntelligence[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());
        $this->assertEquals($testIntelligence[0]->getPublic(), $selectedSingle[0]->getPublic());

        // Clean up
        foreach($testIntelligence as $intelligence) {
            $intelligence->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testIntelligenceType as $intelligenceType) {
            $intelligenceType->delete();
        }
    }

    public function testGetByIntelligenceTypeID() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test intelligence type
        $testIntelligenceType = [];
        $testIntelligenceType[0] = new IntelligenceType();
        $testIntelligenceType[0]->setName('testIntelligenceType');
        $testIntelligenceType[0]->create();

        $testIntelligenceType[1] = new IntelligenceType();
        $testIntelligenceType[1]->setName('testIntelligenceType2');
        $testIntelligenceType[1]->create();

        // Create a test intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setAuthorID($testUser[0]->getID());
        $testIntelligence[0]->setIntelligenceTypeID($testIntelligenceType[0]->getID());
        $testIntelligence[0]->setSubject('testSubject');
        $testIntelligence[0]->setBody('testBody');
        $testIntelligence[0]->setTimestamp(123);
        $testIntelligence[0]->setPublic(false);
        $testIntelligence[0]->create();

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setAuthorID($testUser[1]->getID());
        $testIntelligence[1]->setIntelligenceTypeID($testIntelligenceType[1]->getID());
        $testIntelligence[1]->setSubject('testSubject2');
        $testIntelligence[1]->setBody('testBody2');
        $testIntelligence[1]->setTimestamp(12345);
        $testIntelligence[1]->setPublic(false);
        $testIntelligence[1]->create();

        // Get and check a single
        $selectedSingle = Intelligence::getByIntelligenceTypeID($testIntelligenceType[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(Intelligence::class, $selectedSingle[0]);
        $this->assertEquals($testIntelligence[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testIntelligence[0]->getAuthorID(), $selectedSingle[0]->getAuthorID());
        $this->assertEquals($testIntelligence[0]->getIntelligenceTypeID(), $selectedSingle[0]->getIntelligenceTypeID());
        $this->assertEquals($testIntelligence[0]->getSubject(), $selectedSingle[0]->getSubject());
        $this->assertEquals($testIntelligence[0]->getBody(), $selectedSingle[0]->getBody());
        $this->assertEquals($testIntelligence[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());
        $this->assertEquals($testIntelligence[0]->getPublic(), $selectedSingle[0]->getPublic());

        // Clean up
        foreach($testIntelligence as $intelligence) {
            $intelligence->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testIntelligenceType as $intelligenceType) {
            $intelligenceType->delete();
        }
    }

    public function testGetByPublic() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test intelligence type
        $testIntelligenceType = [];
        $testIntelligenceType[0] = new IntelligenceType();
        $testIntelligenceType[0]->setName('testIntelligenceType');
        $testIntelligenceType[0]->create();

        $testIntelligenceType[1] = new IntelligenceType();
        $testIntelligenceType[1]->setName('testIntelligenceType2');
        $testIntelligenceType[1]->create();

        // Create a test intelligence
        $testIntelligence = [];
        $testIntelligence[0] = new Intelligence();
        $testIntelligence[0]->setAuthorID($testUser[0]->getID());
        $testIntelligence[0]->setIntelligenceTypeID($testIntelligenceType[0]->getID());
        $testIntelligence[0]->setSubject('testSubject');
        $testIntelligence[0]->setBody('testBody');
        $testIntelligence[0]->setTimestamp(123);
        $testIntelligence[0]->setPublic(false);
        $testIntelligence[0]->create();

        $testIntelligence[1] = new Intelligence();
        $testIntelligence[1]->setAuthorID($testUser[1]->getID());
        $testIntelligence[1]->setIntelligenceTypeID($testIntelligenceType[1]->getID());
        $testIntelligence[1]->setSubject('testSubject2');
        $testIntelligence[1]->setBody('testBody2');
        $testIntelligence[1]->setTimestamp(12345);
        $testIntelligence[1]->setPublic(true);
        $testIntelligence[1]->create();

        // Get and check a single
        $selectedSingle = Intelligence::getByPublic(false);

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(Intelligence::class, $selectedSingle[0]);
        $this->assertEquals($testIntelligence[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testIntelligence[0]->getAuthorID(), $selectedSingle[0]->getAuthorID());
        $this->assertEquals($testIntelligence[0]->getIntelligenceTypeID(), $selectedSingle[0]->getIntelligenceTypeID());
        $this->assertEquals($testIntelligence[0]->getSubject(), $selectedSingle[0]->getSubject());
        $this->assertEquals($testIntelligence[0]->getBody(), $selectedSingle[0]->getBody());
        $this->assertEquals($testIntelligence[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());
        $this->assertEquals($testIntelligence[0]->getPublic(), $selectedSingle[0]->getPublic());

        // Clean up
        foreach($testIntelligence as $intelligence) {
            $intelligence->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testIntelligenceType as $intelligenceType) {
            $intelligenceType->delete();
        }
    }

}
