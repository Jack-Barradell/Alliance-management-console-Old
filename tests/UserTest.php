<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/User.php';
require '../classes/Faction.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Faction;
use AMC\Classes\User;
use AMC\Classes\Database;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('jirbj.co.uk', 'testingDB', 'testingDB', 'testingdb');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check null constructor
        $user = new User();

        $this->assertTrue($user->eql(new User()));
        $this->assertNull($user->getID());
        $this->assertNull($user->getUsername());
        $this->assertNull($user->getPasswordHash());
        $this->assertNull($user->getEmail());
        $this->assertNull($user->getBanned());
        $this->assertNull($user->getActivated());
        $this->assertNull($user->getLastLogin());
        $this->assertNull($user->getSystemAccount());
        $this->assertNull($user->getFactionID());

        // Create and test non null constructor
        $user = new User(1, 'username', 'password', 'email@test.com', false, true, 123, false, 2);

        $this->assertFalse($user->eql(new User()));
        $this->assertEquals(1, $user->getID());
        $this->assertEquals('username', $user->getUsername());
        $this->assertEquals('password', $user->getPasswordHash());
        $this->assertEquals('email@test.com', $user->getEmail());
        $this->assertFalse($user->getBanned());
        $this->assertTrue($user->getActivated());
        $this->assertEquals(123, $user->getLastLogin());
        $this->assertFalse($user->getSystemAccount());
        $this->assertEquals(2, $user->getFactionID());
    }

    public function testCreate() {
        // Create a test faction
        $testFaction = new Faction();
        $testFaction->setName('test');
        $testFaction->create();

        // Create a test user
        $testUser = new User();
        $testUser->setUsername('username');
        $testUser->setPasswordHash('hashed');
        $testUser->setEmail('test@email.com');
        $testUser->setBanned(false);
        $testUser->setActivated(true);
        $testUser->setLastLogin(123);
        $testUser->setSystemAccount(false);
        $testUser->setFactionID($testFaction->getID());
        $testUser->create();

        // Check id is an int
        $this->assertInternalType('int', $testUser->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `UserID`,`Username`,`PasswordHash`,`Email`,`Banned`,`Activated`,`LastLogin`,`SystemAccount`,`FactionID` FROM `Users` WHERE `UserID`=?");
        $stmt->bind_param('i', $testUser->getID());
        $stmt->execute();
        $stmt->bind_result($userID, $username, $passwordHash, $email, $banned, $activated, $lastLogin, $systemAccount, $factionID);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        if($banned == 1) {
            $banned = true;
        }
        else {
            $banned = false;
        }
        if($activated == 1) {
            $activated = true;
        }
        else {
            $activated = false;
        }
        if($systemAccount == 1) {
            $systemAccount = true;
        }
        else {
            $systemAccount = false;
        }

        $this->assertEquals($testUser->getID(), $userID);
        $this->assertEquals($testUser->getUsername(), $username);
        $this->assertEquals($testUser->getPasswordHash(), $passwordHash);
        $this->assertEquals($testUser->getEmail(), $email);
        $this->assertEquals($testUser->getBanned(), $banned);
        $this->assertEquals($testUser->getActivated(), $activated);
        $this->assertEquals($testUser->getLastLogin(), $lastLogin);
        $this->assertEquals($testUser->getSystemAccount(), $systemAccount);
        $this->assertEquals($testUser->getFactionID(), $factionID);

        $stmt->close();

        // Clean up
        $testUser->delete();
        $testFaction->delete();
    }

    public function testBlankCreate() {
        // Create a test user
        $user = new User();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $user->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store blank User.', $e->getMessage());
        }
    }

    public function testUpdate() {
        // Create a test faction
        $testFaction = [];
        $testFaction[0] = new Faction();
        $testFaction[0]->setName('test');
        $testFaction[0]->create();

        $testFaction[1] = new Faction();
        $testFaction[1]->setName('test');
        $testFaction[1]->create();

        // Create a test user
        $testUser = new User();
        $testUser->setUsername('username');
        $testUser->setPasswordHash('hashed');
        $testUser->setEmail('test@email.com');
        $testUser->setBanned(false);
        $testUser->setActivated(true);
        $testUser->setLastLogin(123);
        $testUser->setSystemAccount(false);
        $testUser->setFactionID($testFaction[0]->getID());
        $testUser->create();

        // Now update it
        $testUser->setUsername('username2');
        $testUser->setPasswordHash('hashed2');
        $testUser->setEmail('test@email2.com');
        $testUser->setBanned(true);
        $testUser->setActivated(false);
        $testUser->setLastLogin(12345);
        $testUser->setSystemAccount(true);
        $testUser->setFactionID($testFaction[1]->getID());
        $testUser->update();

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `UserID`,`Username`,`PasswordHash`,`Email`,`Banned`,`Activated`,`LastLogin`,`SystemAccount`,`FactionID` FROM `Users` WHERE `UserID`=?");
        $stmt->bind_param('i', $testUser->getID());
        $stmt->execute();
        $stmt->bind_result($userID, $username, $passwordHash, $email, $banned, $activated, $lastLogin, $systemAccount, $factionID);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        if($banned == 1) {
            $banned = true;
        }
        else {
            $banned = false;
        }
        if($activated == 1) {
            $activated = true;
        }
        else {
            $activated = false;
        }
        if($systemAccount == 1) {
            $systemAccount = true;
        }
        else {
            $systemAccount = false;
        }

        $this->assertEquals($testUser->getID(), $userID);
        $this->assertEquals($testUser->getUsername(), $username);
        $this->assertEquals($testUser->getPasswordHash(), $passwordHash);
        $this->assertEquals($testUser->getEmail(), $email);
        $this->assertEquals($testUser->getBanned(), $banned);
        $this->assertEquals($testUser->getActivated(), $activated);
        $this->assertEquals($testUser->getLastLogin(), $lastLogin);
        $this->assertEquals($testUser->getSystemAccount(), $systemAccount);
        $this->assertEquals($testUser->getFactionID(), $factionID);

        $stmt->close();

        // Clean up
        $testUser->delete();
        foreach($testFaction as $faction) {
            $faction->delete();
        }
    }

    public function testBlankUpdate() {
        // Create a test user
        $user = new User();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $user->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store blank User.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test faction
        $testFaction = new Faction();
        $testFaction->setName('test');
        $testFaction->create();

        // Create a test user
        $testUser = new User();
        $testUser->setUsername('username');
        $testUser->setPasswordHash('hashed');
        $testUser->setEmail('test@email.com');
        $testUser->setBanned(false);
        $testUser->setActivated(true);
        $testUser->setLastLogin(123);
        $testUser->setSystemAccount(false);
        $testUser->setFactionID($testFaction->getID());
        $testUser->create();

        // Store id
        $id = $testUser->getID();

        // Now delete it
        $testUser->delete();

        // Check id is now null
        $this->assertNull($testUser->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `UserID`,`Username`,`PasswordHash`,`Email`,`Banned`,`Activated`,`LastLogin`,`SystemAccount`,`FactionID` FROM `Users` WHERE `UserID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testFaction->delete();
    }

    public function testSelectWithInput() {
        // Create a test faction
        $testFaction = [];
        $testFaction[0] = new Faction();
        $testFaction[0]->setName('test');
        $testFaction[0]->create();

        $testFaction[1] = new Faction();
        $testFaction[1]->setName('test');
        $testFaction[1]->create();

        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('username');
        $testUser[0]->setPasswordHash('hashed');
        $testUser[0]->setEmail('test@email.com');
        $testUser[0]->setBanned(false);
        $testUser[0]->setActivated(true);
        $testUser[0]->setLastLogin(123);
        $testUser[0]->setSystemAccount(false);
        $testUser[0]->setFactionID($testFaction[0]->getID());
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('username2');
        $testUser[1]->setPasswordHash('hashed2');
        $testUser[1]->setEmail('test@email2.com');
        $testUser[1]->setBanned(false);
        $testUser[1]->setActivated(true);
        $testUser[1]->setLastLogin(12345);
        $testUser[1]->setSystemAccount(false);
        $testUser[1]->setFactionID($testFaction[1]->getID());
        $testUser[1]->create();

        $testUser[2] = new User();
        $testUser[2]->setUsername('username3');
        $testUser[2]->setPasswordHash('hashed3');
        $testUser[2]->setEmail('test@email3.com');
        $testUser[2]->setBanned(true);
        $testUser[2]->setActivated(false);
        $testUser[2]->setLastLogin(12345678);
        $testUser[2]->setSystemAccount(true);
        $testUser[2]->setFactionID($testFaction[1]->getID());
        $testUser[2]->create();

        // Get and check a single
        $selectedSingle = User::select(array($testUser[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(User::class, $selectedSingle[0]);

        $this->assertEquals($testUser[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUser[0]->getUsername(), $selectedSingle[0]->getUsername());
        $this->assertEquals($testUser[0]->getPasswordHash(), $selectedSingle[0]->getPasswordHash());
        $this->assertEquals($testUser[0]->getEmail(), $selectedSingle[0]->getEmail());
        $this->assertEquals($testUser[0]->getBanned(), $selectedSingle[0]->getBanned());
        $this->assertEquals($testUser[0]->getActivated(), $selectedSingle[0]->getActivated());
        $this->assertEquals($testUser[0]->getLastLogin(), $selectedSingle[0]->getLastLogin());
        $this->assertEquals($testUser[0]->getSystemAccount(), $selectedSingle[0]->getSystemAccount());
        $this->assertEquals($testUser[0]->getFactionID(), $selectedSingle[0]->getFactionID());

        // Get and check multiple
        $selectedMultiple = User::select(array($testUser[1]->getID(), $testUser[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(User::class, $selectedMultiple[0]);
        $this->assertInstanceOf(User::class, $selectedMultiple[1]);

        if($testUser[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testUser[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testUser[1]->getUsername(), $selectedMultiple[$i]->getUsername());
        $this->assertEquals($testUser[1]->getPasswordHash(), $selectedMultiple[$i]->getPasswordHash());
        $this->assertEquals($testUser[1]->getEmail(), $selectedMultiple[$i]->getEmail());
        $this->assertEquals($testUser[1]->getBanned(), $selectedMultiple[$i]->getBanned());
        $this->assertEquals($testUser[1]->getActivated(), $selectedMultiple[$i]->getActivated());
        $this->assertEquals($testUser[1]->getLastLogin(), $selectedMultiple[$i]->getLastLogin());
        $this->assertEquals($testUser[1]->getSystemAccount(), $selectedMultiple[$i]->getSystemAccount());
        $this->assertEquals($testUser[1]->getFactionID(), $selectedMultiple[$i]->getFactionID());

        $this->assertEquals($testUser[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testUser[2]->getUsername(), $selectedMultiple[$j]->getUsername());
        $this->assertEquals($testUser[2]->getPasswordHash(), $selectedMultiple[$j]->getPasswordHash());
        $this->assertEquals($testUser[2]->getEmail(), $selectedMultiple[$j]->getEmail());
        $this->assertEquals($testUser[2]->getBanned(), $selectedMultiple[$j]->getBanned());
        $this->assertEquals($testUser[2]->getActivated(), $selectedMultiple[$j]->getActivated());
        $this->assertEquals($testUser[2]->getLastLogin(), $selectedMultiple[$j]->getLastLogin());
        $this->assertEquals($testUser[2]->getSystemAccount(), $selectedMultiple[$j]->getSystemAccount());
        $this->assertEquals($testUser[2]->getFactionID(), $selectedMultiple[$j]->getFactionID());

        // Clean up
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testFaction as $faction) {
            $faction->delete();
        }
    }

    public function testSelectAll() {
        // Create a test faction
        $testFaction = [];
        $testFaction[0] = new Faction();
        $testFaction[0]->setName('test');
        $testFaction[0]->create();

        $testFaction[1] = new Faction();
        $testFaction[1]->setName('test');
        $testFaction[1]->create();

        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('username');
        $testUser[0]->setPasswordHash('hashed');
        $testUser[0]->setEmail('test@email.com');
        $testUser[0]->setBanned(false);
        $testUser[0]->setActivated(true);
        $testUser[0]->setLastLogin(123);
        $testUser[0]->setSystemAccount(false);
        $testUser[0]->setFactionID($testFaction[0]->getID());
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('username2');
        $testUser[1]->setPasswordHash('hashed2');
        $testUser[1]->setEmail('test@email2.com');
        $testUser[1]->setBanned(false);
        $testUser[1]->setActivated(true);
        $testUser[1]->setLastLogin(12345);
        $testUser[1]->setSystemAccount(false);
        $testUser[1]->setFactionID($testFaction[1]->getID());
        $testUser[1]->create();

        // Get and check multiple
        $selectedMultiple = User::select(array($testUser[0]->getID(), $testUser[1]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(User::class, $selectedMultiple[0]);
        $this->assertInstanceOf(User::class, $selectedMultiple[1]);

        if($testUser[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testUser[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testUser[0]->getUsername(), $selectedMultiple[$i]->getUsername());
        $this->assertEquals($testUser[0]->getPasswordHash(), $selectedMultiple[$i]->getPasswordHash());
        $this->assertEquals($testUser[0]->getEmail(), $selectedMultiple[$i]->getEmail());
        $this->assertEquals($testUser[0]->getBanned(), $selectedMultiple[$i]->getBanned());
        $this->assertEquals($testUser[0]->getActivated(), $selectedMultiple[$i]->getActivated());
        $this->assertEquals($testUser[0]->getLastLogin(), $selectedMultiple[$i]->getLastLogin());
        $this->assertEquals($testUser[0]->getSystemAccount(), $selectedMultiple[$i]->getSystemAccount());
        $this->assertEquals($testUser[0]->getFactionID(), $selectedMultiple[$i]->getFactionID());

        $this->assertEquals($testUser[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testUser[1]->getUsername(), $selectedMultiple[$j]->getUsername());
        $this->assertEquals($testUser[1]->getPasswordHash(), $selectedMultiple[$j]->getPasswordHash());
        $this->assertEquals($testUser[1]->getEmail(), $selectedMultiple[$j]->getEmail());
        $this->assertEquals($testUser[1]->getBanned(), $selectedMultiple[$j]->getBanned());
        $this->assertEquals($testUser[1]->getActivated(), $selectedMultiple[$j]->getActivated());
        $this->assertEquals($testUser[1]->getLastLogin(), $selectedMultiple[$j]->getLastLogin());
        $this->assertEquals($testUser[1]->getSystemAccount(), $selectedMultiple[$j]->getSystemAccount());
        $this->assertEquals($testUser[1]->getFactionID(), $selectedMultiple[$j]->getFactionID());

        // Clean up
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testFaction as $faction) {
            $faction->delete();
        }
    }

    public function testEql() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setID(1);
        $testUser[0]->setUsername('username');
        $testUser[0]->setPasswordHash('hashed');
        $testUser[0]->setEmail('test@email.com');
        $testUser[0]->setBanned(false);
        $testUser[0]->setActivated(true);
        $testUser[0]->setLastLogin(123);
        $testUser[0]->setSystemAccount(false);
        $testUser[0]->setFactionID(2);

        $testUser[1] = new User();
        $testUser[1]->setID(1);
        $testUser[1]->setUsername('username');
        $testUser[1]->setPasswordHash('hashed');
        $testUser[1]->setEmail('test@email.com');
        $testUser[1]->setBanned(false);
        $testUser[1]->setActivated(true);
        $testUser[1]->setLastLogin(123);
        $testUser[1]->setSystemAccount(false);
        $testUser[1]->setFactionID(2);

        $testUser[2] = new User();
        $testUser[2]->setID(2);
        $testUser[2]->setUsername('username2');
        $testUser[2]->setPasswordHash('hashed2');
        $testUser[2]->setEmail('test@email2.com');
        $testUser[2]->setBanned(true);
        $testUser[2]->setActivated(false);
        $testUser[2]->setLastLogin(12345);
        $testUser[2]->setSystemAccount(true);
        $testUser[2]->setFactionID(10);

        // Check same object is eql
        $this->assertTrue($testUser[0]->eql($testUser[0]));

        // Check same details are eql
        $this->assertTrue($testUser[0]->eql($testUser[1]));

        // Check different arent equal
        $this->assertFalse($testUser[0]->eql($testUser[2]));
    }

    public function testBan() {
        //TODO: Implement
    }

    public function testIncorrectTypeBan() {
        //TODO: Implement
    }

    public function testInvalidUserBan() {
        //TODO: Implement
    }

    public function testUnban() {
        //TODO: Implement
    }

    public function testNullGetUnban() {
        //TODO: Implement
    }

    public function testInvalidUserUnban() {
        //TODO: Implement
    }

    public function testMissingPrerequisiteUnban() {
        //TODO: Implement
    }

    public function testChangePassword() {
        //TODO: Implement
    }

    public function testUserExists() {
        //TODO: Implement
    }

    public function testIncorrectTypeUserExists() {
        //TODO: Implement
    }

    public function testEmailExists() {
        //TODO: Implement
    }

    public function testIncorrectTypeEmailExists() {
        //TODO: Implement
    }

    public function testRegisterAccount() {
        //TODO: Implement
    }

    public function testIncorrectTypeRegisterAccount() {
        //TODO: Implement
    }

    public function testDuplicateEntryRegisterAccount() {
        //TODO: Implement
    }

    public function testLogin() {
        //TODO: Implement
    }

    public function testHasUserPrivilege() {
        //TODO: Implement
    }

    public function testHasPrivilege() {
        //TODO: Implement
    }

    public function testAddToGroup() {
        //TODO: Implement
    }

    public function testRemoveFromGroup() {
        //TODO: Implement
    }

    public function testIsInGroup() {
        //TODO: Implement
    }

    public function testGetGroups() {
        //TODO: Implement
    }

    public function testIssuePrivilege() {
        //TODO: Implement
    }

    public function testRevokePrivilege() {
        //TODO: Implement
    }

    public function testGetPrivileges() {
        //TODO: Implement
    }

    public function testIssueRank() {
        //TODO: Implement
    }

    public function testDuplicateEntryIssueRank() {
        //TODO: Implement
    }

    public function testRevokeRank() {
        //TODO: Implement
    }

    public function testMissingPrerequisiteRevokeRank() {
        //TODO: Implement
    }

    public function testHasRank() {
        //TODO: Implement
    }

    public function testGetRanks() {
        //TODO: Implement
    }

}
