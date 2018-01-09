<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Award.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Award;
use AMC\Classes\User;
use AMC\Classes\UserAward;
use AMC\Classes\Database;
use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\InvalidAwardException;
use AMC\Exceptions\InvalidUserException;
use PHPUnit\Framework\TestCase;

class UserAwardTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check null constructor
        $userAward = new UserAward();

        $this->assertNull($userAward->getID());
        $this->assertNull($userAward->getUserID());
        $this->assertNull($userAward->getIssuerID());
        $this->assertNull($userAward->getAwardID());
        $this->assertNull($userAward->getTimestamp());

        // Check non null constructor
        $userAward = new UserAward(1,2,3,4,123);

        $this->assertEquals(1, $userAward->getID());
        $this->assertEquals(2, $userAward->getUserID());
        $this->assertEquals(3, $userAward->getIssuerID());
        $this->assertEquals(4, $userAward->getAwardID());
        $this->assertEquals(123, $userAward->getTimestamp());
    }

    public function testCreate() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test issuer
        $testIssuer = new User();
        $testIssuer->setUsername('testIssuer');
        $testIssuer->create();

        // Create a test award
        $testAward = new Award();
        $testAward->setName('testAward');
        $testAward->create();

        // Create a test user award
        $testUserAward = new UserAward();
        $testUserAward->setUserID($testUser->getID());
        $testUserAward->setIssuerID($testIssuer->getID());
        $testUserAward->setAwardID($testAward->getID());
        $testUserAward->setTimestamp(123);
        $testUserAward->create();

        // Check id is now an int
        $this->assertInternalType('int', $testUserAward->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `UserAwardID`,`UserID`,`IssuerID`,`AwardID`,`UserAwardTimestamp` FROM `User_Awards` WHERE `UserAwardID`=?");
        $stmt->bind_param('i', $testUserAward->getID());
        $stmt->execute();
        $stmt->bind_result($userAwardID, $userID, $issuerID, $awardID, $timestamp);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $this->assertEquals($testUserAward->getID(), $userAwardID);
        $this->assertEquals($testUserAward->getUserID(), $userID);
        $this->assertEquals($testUserAward->getIssuerID(), $issuerID);
        $this->assertEquals($testUserAward->getAwardID(), $awardID);
        $this->assertEquals($testUserAward->getTimestamp(), $timestamp);

        $stmt->close();

        // Clean up
        $testUserAward->delete();
        $testAward->delete();
        $testIssuer->delete();
        $testUser->delete();
    }

    public function testBlankCreate() {
        // Create a test user award
        $testUserAward = new UserAward();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testUserAward->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store blank User Award.', $e->getMessage());
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

        // Create a test issuer
        $testIssuer = [];
        $testIssuer[0] = new User();
        $testIssuer[0]->setUsername('testIssuer');
        $testIssuer[0]->create();

        $testIssuer[1] = new User();
        $testIssuer[1]->setUsername('testIssuer2');
        $testIssuer[1]->create();

        // Create a test award
        $testAward = [];
        $testAward[0] = new Award();
        $testAward[0]->setName('testAward');
        $testAward[0]->create();

        $testAward[1] = new Award();
        $testAward[1]->setName('testAward2');
        $testAward[1]->create();

        // Create a test user award
        $testUserAward = new UserAward();
        $testUserAward->setUserID($testUser[0]->getID());
        $testUserAward->setIssuerID($testIssuer[0]->getID());
        $testUserAward->setAwardID($testAward[0]->getID());
        $testUserAward->setTimestamp(123);
        $testUserAward->create();

        // Now update it
        $testUserAward->setUserID($testUser[1]->getID());
        $testUserAward->setIssuerID($testIssuer[1]->getID());
        $testUserAward->setAwardID($testAward[1]->getID());
        $testUserAward->setTimestamp(12345);
        $testUserAward->update();

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `UserAwardID`,`UserID`,`IssuerID`,`AwardID`,`UserAwardTimestamp` FROM `User_Awards` WHERE `UserAwardID`=?");
        $stmt->bind_param('i', $testUserAward->getID());
        $stmt->execute();
        $stmt->bind_result($userAwardID, $userID, $issuerID, $awardID, $timestamp);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $this->assertEquals($testUserAward->getID(), $userAwardID);
        $this->assertEquals($testUserAward->getUserID(), $userID);
        $this->assertEquals($testUserAward->getIssuerID(), $issuerID);
        $this->assertEquals($testUserAward->getAwardID(), $awardID);
        $this->assertEquals($testUserAward->getTimestamp(), $timestamp);

        $stmt->close();

        // Clean up
        $testUserAward->delete();
        foreach($testAward as $award) {
            $award->delete();
        }
        foreach($testIssuer as $issuer) {
            $issuer->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testBlankUpdate() {
        // Create a test user award
        $testUserAward = new UserAward();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testUserAward->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store blank User Award.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test issuer
        $testIssuer = new User();
        $testIssuer->setUsername('testIssuer');
        $testIssuer->create();

        // Create a test award
        $testAward = new Award();
        $testAward->setName('testAward');
        $testAward->create();

        // Create a test user award
        $testUserAward = new UserAward();
        $testUserAward->setUserID($testUser->getID());
        $testUserAward->setIssuerID($testIssuer->getID());
        $testUserAward->setAwardID($testAward->getID());
        $testUserAward->setTimestamp(123);
        $testUserAward->create();

        // Store the id
        $id = $testUserAward->getID();

        // Now delete it
        $testUserAward->delete();

        // Check id is now null
        $this->assertNull($testUserAward->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `UserAwardID`,`UserID`,`IssuerID`,`AwardID`,`UserAwardTimestamp` FROM `User_Awards` WHERE `UserAwardID`=?");
        $stmt->bind_param('i', $testUserAward->getID());
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testAward->delete();
        $testIssuer->delete();
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

        // Create a test issuer
        $testIssuer = [];
        $testIssuer[0] = new User();
        $testIssuer[0]->setUsername('testIssuer');
        $testIssuer[0]->create();

        $testIssuer[1] = new User();
        $testIssuer[1]->setUsername('testIssuer2');
        $testIssuer[1]->create();

        // Create a test award
        $testAward = [];
        $testAward[0] = new Award();
        $testAward[0]->setName('testAward');
        $testAward[0]->create();

        $testAward[1] = new Award();
        $testAward[1]->setName('testAward2');
        $testAward[1]->create();

        // Create a test user award
        $testUserAward = [];
        $testUserAward[0] = new UserAward();
        $testUserAward[0]->setUserID($testUser[0]->getID());
        $testUserAward[0]->setIssuerID($testIssuer[0]->getID());
        $testUserAward[0]->setAwardID($testAward[0]->getID());
        $testUserAward[0]->setTimestamp(123);
        $testUserAward[0]->create();

        $testUserAward[1] = new UserAward();
        $testUserAward[1]->setUserID($testUser[1]->getID());
        $testUserAward[1]->setIssuerID($testIssuer[1]->getID());
        $testUserAward[1]->setAwardID($testAward[1]->getID());
        $testUserAward[1]->setTimestamp(12345);
        $testUserAward[1]->create();

        $testUserAward[2] = new UserAward();
        $testUserAward[2]->setUserID($testUser[0]->getID());
        $testUserAward[2]->setIssuerID($testIssuer[1]->getID());
        $testUserAward[2]->setAwardID($testAward[1]->getID());
        $testUserAward[2]->setTimestamp(1234567);
        $testUserAward[2]->create();

        // Get and check a single
        $selectedSingle = UserAward::select(array($testUserAward[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserAward::class, $selectedSingle[0]);
        $this->assertEquals($testUserAward[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserAward[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserAward[0]->getIssuerID(), $selectedSingle[0]->getIssuerID());
        $this->assertEquals($testUserAward[0]->getAwardID(), $selectedSingle[0]->getAwardID());
        $this->assertEquals($testUserAward[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Get and check multiple
        $selectedMultiple = UserAward::select(array($testUserAward[1]->getID(), $testUserAward[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(UserAward::class, $selectedMultiple[0]);
        $this->assertInstanceOf(UserAward::class, $selectedMultiple[1]);

        if($testUserAward[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testUserAward[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testUserAward[1]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testUserAward[1]->getIssuerID(), $selectedMultiple[$i]->getIssuerID());
        $this->assertEquals($testUserAward[1]->getAwardID(), $selectedMultiple[$i]->getAwardID());
        $this->assertEquals($testUserAward[1]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());

        $this->assertEquals($testUserAward[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testUserAward[2]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testUserAward[2]->getIssuerID(), $selectedMultiple[$j]->getIssuerID());
        $this->assertEquals($testUserAward[2]->getAwardID(), $selectedMultiple[$j]->getAwardID());
        $this->assertEquals($testUserAward[2]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());

        // Clean up
        foreach($testUserAward as $userAward) {
            $userAward->delete();
        }
        foreach($testAward as $award) {
            $award->delete();
        }
        foreach($testIssuer as $issuer) {
            $issuer->delete();
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

        // Create a test issuer
        $testIssuer = [];
        $testIssuer[0] = new User();
        $testIssuer[0]->setUsername('testIssuer');
        $testIssuer[0]->create();

        $testIssuer[1] = new User();
        $testIssuer[1]->setUsername('testIssuer2');
        $testIssuer[1]->create();

        // Create a test award
        $testAward = [];
        $testAward[0] = new Award();
        $testAward[0]->setName('testAward');
        $testAward[0]->create();

        $testAward[1] = new Award();
        $testAward[1]->setName('testAward2');
        $testAward[1]->create();

        // Create a test user award
        $testUserAward = [];
        $testUserAward[0] = new UserAward();
        $testUserAward[0]->setUserID($testUser[0]->getID());
        $testUserAward[0]->setIssuerID($testIssuer[0]->getID());
        $testUserAward[0]->setAwardID($testAward[0]->getID());
        $testUserAward[0]->setTimestamp(123);
        $testUserAward[0]->create();

        $testUserAward[1] = new UserAward();
        $testUserAward[1]->setUserID($testUser[1]->getID());
        $testUserAward[1]->setIssuerID($testIssuer[1]->getID());
        $testUserAward[1]->setAwardID($testAward[1]->getID());
        $testUserAward[1]->setTimestamp(12345);
        $testUserAward[1]->create();

        // Get and check multiple
        $selectedMultiple = UserAward::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(UserAward::class, $selectedMultiple[0]);
        $this->assertInstanceOf(UserAward::class, $selectedMultiple[1]);

        if($testUserAward[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testUserAward[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testUserAward[0]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testUserAward[0]->getIssuerID(), $selectedMultiple[$i]->getIssuerID());
        $this->assertEquals($testUserAward[0]->getAwardID(), $selectedMultiple[$i]->getAwardID());
        $this->assertEquals($testUserAward[0]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());

        $this->assertEquals($testUserAward[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testUserAward[1]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testUserAward[1]->getIssuerID(), $selectedMultiple[$j]->getIssuerID());
        $this->assertEquals($testUserAward[1]->getAwardID(), $selectedMultiple[$j]->getAwardID());
        $this->assertEquals($testUserAward[1]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());

        // Clean up
        foreach($testUserAward as $userAward) {
            $userAward->delete();
        }
        foreach($testAward as $award) {
            $award->delete();
        }
        foreach($testIssuer as $issuer) {
            $issuer->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testEql() {
        // Create a test user award
        $testUserAward = [];
        $testUserAward[0] = new UserAward();
        $testUserAward[0]->setUserID(1);
        $testUserAward[0]->setIssuerID(2);
        $testUserAward[0]->setAwardID(2);
        $testUserAward[0]->setTimestamp(123);

        $testUserAward[1] = new UserAward();
        $testUserAward[1]->setUserID(1);
        $testUserAward[1]->setIssuerID(2);
        $testUserAward[1]->setAwardID(2);
        $testUserAward[1]->setTimestamp(123);

        $testUserAward[2] = new UserAward();
        $testUserAward[2]->setUserID(4);
        $testUserAward[2]->setIssuerID(5);
        $testUserAward[2]->setAwardID(6);
        $testUserAward[2]->setTimestamp(123456);

        // Check same object is eql
        $this->assertTrue($testUserAward[0]->eql($testUserAward[0]));

        // Check same details are eql
        $this->assertTrue($testUserAward[0]->eql($testUserAward[0]));

        // Check different arent equal
        $this->assertFalse($testUserAward[0]->eql($testUserAward[0]));
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

        // Create a test issuer
        $testIssuer = [];
        $testIssuer[0] = new User();
        $testIssuer[0]->setUsername('testIssuer');
        $testIssuer[0]->create();

        $testIssuer[1] = new User();
        $testIssuer[1]->setUsername('testIssuer2');
        $testIssuer[1]->create();

        // Create a test award
        $testAward = [];
        $testAward[0] = new Award();
        $testAward[0]->setName('testAward');
        $testAward[0]->create();

        $testAward[1] = new Award();
        $testAward[1]->setName('testAward2');
        $testAward[1]->create();

        // Create a test user award
        $testUserAward = [];
        $testUserAward[0] = new UserAward();
        $testUserAward[0]->setUserID($testUser[0]->getID());
        $testUserAward[0]->setIssuerID($testIssuer[0]->getID());
        $testUserAward[0]->setAwardID($testAward[0]->getID());
        $testUserAward[0]->setTimestamp(123);
        $testUserAward[0]->create();

        $testUserAward[1] = new UserAward();
        $testUserAward[1]->setUserID($testUser[1]->getID());
        $testUserAward[1]->setIssuerID($testIssuer[1]->getID());
        $testUserAward[1]->setAwardID($testAward[1]->getID());
        $testUserAward[1]->setTimestamp(12345);
        $testUserAward[1]->create();

        // Get and check a single
        $selectedSingle = UserAward::getByUserID($testUser[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserAward::class, $selectedSingle[0]);
        $this->assertEquals($testUserAward[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserAward[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserAward[0]->getIssuerID(), $selectedSingle[0]->getIssuerID());
        $this->assertEquals($testUserAward[0]->getAwardID(), $selectedSingle[0]->getAwardID());
        $this->assertEquals($testUserAward[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Clean up
        foreach($testUserAward as $userAward) {
            $userAward->delete();
        }
        foreach($testAward as $award) {
            $award->delete();
        }
        foreach($testIssuer as $issuer) {
            $issuer->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testGetByIssuerID() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test issuer
        $testIssuer = [];
        $testIssuer[0] = new User();
        $testIssuer[0]->setUsername('testIssuer');
        $testIssuer[0]->create();

        $testIssuer[1] = new User();
        $testIssuer[1]->setUsername('testIssuer2');
        $testIssuer[1]->create();

        // Create a test award
        $testAward = [];
        $testAward[0] = new Award();
        $testAward[0]->setName('testAward');
        $testAward[0]->create();

        $testAward[1] = new Award();
        $testAward[1]->setName('testAward2');
        $testAward[1]->create();

        // Create a test user award
        $testUserAward = [];
        $testUserAward[0] = new UserAward();
        $testUserAward[0]->setUserID($testUser[0]->getID());
        $testUserAward[0]->setIssuerID($testIssuer[0]->getID());
        $testUserAward[0]->setAwardID($testAward[0]->getID());
        $testUserAward[0]->setTimestamp(123);
        $testUserAward[0]->create();

        $testUserAward[1] = new UserAward();
        $testUserAward[1]->setUserID($testUser[1]->getID());
        $testUserAward[1]->setIssuerID($testIssuer[1]->getID());
        $testUserAward[1]->setAwardID($testAward[1]->getID());
        $testUserAward[1]->setTimestamp(12345);
        $testUserAward[1]->create();

        // Get and check a single
        $selectedSingle = UserAward::getByIssuerID($testIssuer[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserAward::class, $selectedSingle[0]);
        $this->assertEquals($testUserAward[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserAward[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserAward[0]->getIssuerID(), $selectedSingle[0]->getIssuerID());
        $this->assertEquals($testUserAward[0]->getAwardID(), $selectedSingle[0]->getAwardID());
        $this->assertEquals($testUserAward[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Clean up
        foreach($testUserAward as $userAward) {
            $userAward->delete();
        }
        foreach($testAward as $award) {
            $award->delete();
        }
        foreach($testIssuer as $issuer) {
            $issuer->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testGetByAwardID() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test issuer
        $testIssuer = [];
        $testIssuer[0] = new User();
        $testIssuer[0]->setUsername('testIssuer');
        $testIssuer[0]->create();

        $testIssuer[1] = new User();
        $testIssuer[1]->setUsername('testIssuer2');
        $testIssuer[1]->create();

        // Create a test award
        $testAward = [];
        $testAward[0] = new Award();
        $testAward[0]->setName('testAward');
        $testAward[0]->create();

        $testAward[1] = new Award();
        $testAward[1]->setName('testAward2');
        $testAward[1]->create();

        // Create a test user award
        $testUserAward = [];
        $testUserAward[0] = new UserAward();
        $testUserAward[0]->setUserID($testUser[0]->getID());
        $testUserAward[0]->setIssuerID($testIssuer[0]->getID());
        $testUserAward[0]->setAwardID($testAward[0]->getID());
        $testUserAward[0]->setTimestamp(123);
        $testUserAward[0]->create();

        $testUserAward[1] = new UserAward();
        $testUserAward[1]->setUserID($testUser[1]->getID());
        $testUserAward[1]->setIssuerID($testIssuer[1]->getID());
        $testUserAward[1]->setAwardID($testAward[1]->getID());
        $testUserAward[1]->setTimestamp(12345);
        $testUserAward[1]->create();

        // Get and check a single
        $selectedSingle = UserAward::getByAwardID($testAward[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserAward::class, $selectedSingle[0]);
        $this->assertEquals($testUserAward[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserAward[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserAward[0]->getIssuerID(), $selectedSingle[0]->getIssuerID());
        $this->assertEquals($testUserAward[0]->getAwardID(), $selectedSingle[0]->getAwardID());
        $this->assertEquals($testUserAward[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Clean up
        foreach($testUserAward as $userAward) {
            $userAward->delete();
        }
        foreach($testAward as $award) {
            $award->delete();
        }
        foreach($testIssuer as $issuer) {
            $issuer->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testSetUserID() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('test');
        $testUser->create();

        // Create a test user award
        $testUserAward = new UserAward();

        // Try and set the id
        try {
            $testUserAward->setUserID($testUser->getID(), true);
            $this->assertEquals($testUser->getID(), $testUserAward->getUserID());
        } finally {
            $testUser->delete();
        }
    }

    public function testInvalidUserSetUserID() {
        // Get max user id and add one to it
        $stmt = Database::getConnection()->prepare("SELECT `UserID` FROM `Users` ORDER BY `UserID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($userID);
        if($stmt->fetch()) {
            $useID = $userID + 1;
        }
        else {
            $useID = 1;
        }
        $stmt->fetch();

        // Create test user award
        $testUserAward = new UserAward();

        // Set set expected exception
        $this->expectException(InvalidUserException::class);

        // Trigger it
        try {
            $testUserAward->setUserID($useID);
        } catch(InvalidUserException $e) {
            $this->assertEquals('No user exists with id ' . $useID, $e->getMessage());
        }
    }

    public function testSetIssuerID() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('test');
        $testUser->create();

        // Create a test user award
        $testUserAward = new UserAward();

        // Try and set id
        try {
            $testUserAward->setIssuerID($testUser->getID(), true);
            $this->assertEquals($testUser->getID(), $testUserAward->getIssuerID());
        } finally {
            $testUser->delete();
        }
    }

    public function testInvalidUserSetIssuerID() {
        // Get max user id and add one to it
        $stmt = Database::getConnection()->prepare("SELECT `UserID` FROM `Users` ORDER BY `UserID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($userID);
        if($stmt->fetch()) {
            $useID = $userID + 1;
        }
        else {
            $useID = 1;
        }
        $stmt->fetch();

        // Create test user award
        $testUserAward = new UserAward();

        // Set expected exception
        $this->expectException(InvalidUserException::class);

        // Trigger the exception
        try {
            $testUserAward->setIssuerID($useID, true);
        } catch(InvalidUserException $e) {
            $this->assertEquals('No user exists with id ' . $useID, $e->getMessage());
        }
    }

    public function testSetAwardID() {
        // Create a test award
        $testAward = new Award();
        $testAward->setName('test');
        $testAward->create();

        // Create a test award
        $testUserAward = new UserAward();

        // Try and set the id
        try {
            $testUserAward->setAwardID($testAward->getID(), true);
            $this->assertEquals($testAward->getID(), $testUserAward->getAwardID());
        } finally {
            $testAward->delete();
        }
    }

    public function testInvalidAwardSetAwardID() {
        // Get max award id and add one
        $stmt = Database::getConnection()->prepare("SELECT `AwardID` FROM `Awards` ORDER BY `AwardID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($awardID);
        if($stmt->fetch()) {
            $useID = $awardID + 1;
        }
        else {
            $useID = 1;
        }
        $stmt->close();

        // Create test user award
        $testUserAward = new UserAward();

        // Set expected exception
        $this->expectException(InvalidAwardException::class);

        // Trigger it
        try {
            $testUserAward->setAwardID($useID, true);
        } catch(InvalidAwardException $e) {
            $this->assertEquals('No award exists with id ' . $useID, $e->getMessage());
        }
    }

}
