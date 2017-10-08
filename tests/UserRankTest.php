<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Rank.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Rank;
use AMC\Classes\User;
use AMC\Classes\UserRank;
use AMC\Classes\Database;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class UserRankTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check null constructor
        $testUserRank = new UserRank();

        $this->assertNull($testUserRank->getID());
        $this->assertNull($testUserRank->getUserID());
        $this->assertNull($testUserRank->getRankID());

        // Check non null constructor
        $testUserRank = new UserRank(1,2,3);

        $this->assertEquals(1, $testUserRank->getID());
        $this->assertEquals(2, $testUserRank->getUserID());
        $this->assertEquals(3, $testUserRank->getRankID());
    }

    public function testCreate() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test rank
        $testRank = new Rank();
        $testRank->setName('testRank');
        $testRank->create();

        // Create a test user rank
        $testUserRank = new UserRank();
        $testUserRank->setUserID($testUser->getID());
        $testUserRank->setRankID($testRank->getID());
        $testUserRank->create();

        // Check id is now an int
        $this->assertInternalType('int', $testUserRank->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `UserRankID`,`UserID`,`RankID` FROM `User_Ranks` WHERE `UserRankID`=?");
        $stmt->bind_result('i', $testUserRank->getID());
        $stmt->execute();
        $stmt->bind_result($userRankID, $userID, $rankID);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testUserRank->getID(), $userRankID);
        $this->assertEquals($testUserRank->getUserID(), $userID);
        $this->assertEquals($testUserRank->getRankID(), $rankID);

        $stmt->close();

        $testUserRank->delete();
        $testUser->delete();
        $testRank->delete();
    }

    public function testBlankCreate() {
        // Create a test user rank
        $testUserRank = new UserRank();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testUserRank->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank User Rank.', $e->getMessage());
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

        // Create a test rank
        $testRank = [];
        $testRank[0] = new Rank();
        $testRank[0]->setName('testRank');
        $testRank[0]->create();

        $testRank[1] = new Rank();
        $testRank[1]->setName('testRank2');
        $testRank[1]->create();

        // Create a test user rank
        $testUserRank = new UserRank();
        $testUserRank->setUserID($testUser[0]->getID());
        $testUserRank->setRankID($testRank[0]->getID());
        $testUserRank->create();

        // Now update it
        $testUserRank->setUserID($testUser[1]->getID());
        $testUserRank->setRankID($testRank[1]->getID());
        $testUserRank->update();

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `UserRankID`,`UserID`,`RankID` FROM `User_Ranks` WHERE `UserRankID`=?");
        $stmt->bind_result('i', $testUserRank->getID());
        $stmt->execute();
        $stmt->bind_result($userRankID, $userID, $rankID);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testUserRank->getID(), $userRankID);
        $this->assertEquals($testUserRank->getUserID(), $userID);
        $this->assertEquals($testUserRank->getRankID(), $rankID);

        $stmt->close();

        $testUserRank->delete();
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testRank as $rank) {
            $rank->delete();
        }
    }

    public function testBlankUpdate() {
        // Create a test user rank
        $testUserRank = new UserRank();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testUserRank->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank User Rank.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test rank
        $testRank = new Rank();
        $testRank->setName('testRank');
        $testRank->create();

        // Create a test user rank
        $testUserRank = new UserRank();
        $testUserRank->setUserID($testUser->getID());
        $testUserRank->setRankID($testRank->getID());
        $testUserRank->create();

        // Save the id
        $id = $testUserRank->getID();

        // Delete it
        $testUserRank->delete();

        // Check id is null
        $this->assertNull($testUserRank->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `UserRankID`,`UserID`,`RankID` FROM `User_Ranks` WHERE `UserRankID`=?");
        $stmt->bind_result('i', $id);
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testUser->delete();
        $testRank->delete();
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

        // Create a test rank
        $testRank = [];
        $testRank[0] = new Rank();
        $testRank[0]->setName('testRank');
        $testRank[0]->create();

        $testRank[1] = new Rank();
        $testRank[1]->setName('testRank2');
        $testRank[1]->create();

        // Create a test user rank
        $testUserRank = [];
        $testUserRank[0] = new UserRank();
        $testUserRank[0]->setUserID($testUser[0]->getID());
        $testUserRank[0]->setRankID($testRank[0]->getID());
        $testUserRank[0]->create();

        $testUserRank[1] = new UserRank();
        $testUserRank[1]->setUserID($testUser[1]->getID());
        $testUserRank[1]->setRankID($testRank[1]->getID());
        $testUserRank[1]->create();

        $testUserRank[2] = new UserRank();
        $testUserRank[2]->setUserID($testUser[0]->getID());
        $testUserRank[2]->setRankID($testRank[1]->getID());
        $testUserRank[2]->create();

        // Select and check a single
        $selectedSingle = UserRank::select(array($testUserRank[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserRank::class, $selectedSingle[0]);
        $this->assertEquals($testUserRank[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserRank[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserRank[0]->getRankID(), $selectedSingle[0]->getRankID());

        // Select and check
        $selectedMultiple = UserRank::select(array($testUserRank[1]->getID(), $testUserRank[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(UserRank::class, $selectedMultiple[0]);
        $this->assertInstanceOf(UserRank::class, $selectedMultiple[1]);

        if($testUserRank[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testUserRank[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testUserRank[1]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testUserRank[1]->getRankID(), $selectedMultiple[$i]->getRankID());

        $this->assertEquals($testUserRank[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testUserRank[2]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testUserRank[2]->getRankID(), $selectedMultiple[$j]->getRankID());

        // Clean up
        foreach($testUserRank as $userRank) {
            $userRank->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testRank as $rank) {
            $rank->delete();
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

        // Create a test rank
        $testRank = [];
        $testRank[0] = new Rank();
        $testRank[0]->setName('testRank');
        $testRank[0]->create();

        $testRank[1] = new Rank();
        $testRank[1]->setName('testRank2');
        $testRank[1]->create();

        // Create a test user rank
        $testUserRank = [];
        $testUserRank[0] = new UserRank();
        $testUserRank[0]->setUserID($testUser[0]->getID());
        $testUserRank[0]->setRankID($testRank[0]->getID());
        $testUserRank[0]->create();

        $testUserRank[1] = new UserRank();
        $testUserRank[1]->setUserID($testUser[1]->getID());
        $testUserRank[1]->setRankID($testRank[1]->getID());
        $testUserRank[1]->create();

        // Select and check
        $selectedMultiple = UserRank::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(UserRank::class, $selectedMultiple[0]);
        $this->assertInstanceOf(UserRank::class, $selectedMultiple[1]);

        if($testUserRank[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testUserRank[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testUserRank[0]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testUserRank[0]->getRankID(), $selectedMultiple[$i]->getRankID());

        $this->assertEquals($testUserRank[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testUserRank[1]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testUserRank[1]->getRankID(), $selectedMultiple[$j]->getRankID());

        // Clean up
        foreach($testUserRank as $userRank) {
            $userRank->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testRank as $rank) {
            $rank->delete();
        }
    }

    public function testEql() {
        // Create a test user rank
        $testUserRank = [];
        $testUserRank[0] = new UserRank();
        $testUserRank[0]->setUserID(1);
        $testUserRank[0]->setRankID(2);

        $testUserRank[1] = new UserRank();
        $testUserRank[1]->setUserID(1);
        $testUserRank[1]->setRankID(2);

        $testUserRank[2] = new UserRank();
        $testUserRank[2]->setUserID(3);
        $testUserRank[2]->setRankID(4);

        // Check same object is eql
        $this->assertTrue($testUserRank[0]->eql($testUserRank[0]));

        // Check same details are eql
        $this->assertTrue($testUserRank[0]->eql($testUserRank[0]));

        // Check different arent equal
        $this->assertFalse($testUserRank[0]->eql($testUserRank[0]));
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

        // Create a test rank
        $testRank = [];
        $testRank[0] = new Rank();
        $testRank[0]->setName('testRank');
        $testRank[0]->create();

        $testRank[1] = new Rank();
        $testRank[1]->setName('testRank2');
        $testRank[1]->create();

        // Create a test user rank
        $testUserRank = [];
        $testUserRank[0] = new UserRank();
        $testUserRank[0]->setUserID($testUser[0]->getID());
        $testUserRank[0]->setRankID($testRank[0]->getID());
        $testUserRank[0]->create();

        $testUserRank[1] = new UserRank();
        $testUserRank[1]->setUserID($testUser[1]->getID());
        $testUserRank[1]->setRankID($testRank[1]->getID());
        $testUserRank[1]->create();

        // Select and check a single
        $selectedSingle = UserRank::getByUserID($testUser[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserRank::class, $selectedSingle[0]);
        $this->assertEquals($testUserRank[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserRank[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserRank[0]->getRankID(), $selectedSingle[0]->getRankID());

        // Clean up
        foreach($testUserRank as $userRank) {
            $userRank->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testRank as $rank) {
            $rank->delete();
        }
    }

    public function testGetByRankID() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test rank
        $testRank = [];
        $testRank[0] = new Rank();
        $testRank[0]->setName('testRank');
        $testRank[0]->create();

        $testRank[1] = new Rank();
        $testRank[1]->setName('testRank2');
        $testRank[1]->create();

        // Create a test user rank
        $testUserRank = [];
        $testUserRank[0] = new UserRank();
        $testUserRank[0]->setUserID($testUser[0]->getID());
        $testUserRank[0]->setRankID($testRank[0]->getID());
        $testUserRank[0]->create();

        $testUserRank[1] = new UserRank();
        $testUserRank[1]->setUserID($testUser[1]->getID());
        $testUserRank[1]->setRankID($testRank[1]->getID());
        $testUserRank[1]->create();

        // Select and check a single
        $selectedSingle = UserRank::getByRankID($testRank[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserRank::class, $selectedSingle[0]);
        $this->assertEquals($testUserRank[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserRank[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserRank[0]->getRankID(), $selectedSingle[0]->getRankID());

        // Clean up
        foreach($testUserRank as $userRank) {
            $userRank->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testRank as $rank) {
            $rank->delete();
        }
    }

    public function testSetUserID() {
        //TODO: Implement
    }

    public function testInvalidUserSetUserID() {
        //TODO: Implement
    }

    public function testSetRankID() {
        //TODO: Implement
    }

    public function testInvalidRankSetRankID() {
        //TODO: Implement
    }

}
