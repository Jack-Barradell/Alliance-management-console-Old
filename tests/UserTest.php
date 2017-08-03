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
        $user->create();
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
        $user->update();
    }

    public function testDelete() {

    }

    public function testSelectWithInput() {

    }

    public function testSelectAll() {

    }

    public function testEql() {

    }

    public function testBan() {

    }

    public function testIncorrectTypeBan() {

    }

    public function testInvalidUserBan() {

    }

    public function testUnban() {

    }

    public function testNullGetUnban() {

    }

    public function testInvalidUserUnban() {

    }

    public function testMissingPrerequisiteUnban() {

    }

    public function testChangePassword() {

    }

    public function testUserExists() {

    }

    public function testIncorrectTypeUserExists() {

    }

    public function testEmailExists() {

    }

    public function testIncorrectTypeEmailExists() {

    }

    public function testRegisterAccount() {

    }

    public function testIncorrectTypeRegisterAccount() {

    }

    public function testDuplicateEntryRegisterAccount() {

    }

    public function testLogin() {

    }

}
