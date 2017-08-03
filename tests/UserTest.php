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

    }

    public function testBlankCreate() {

    }

    public function testUpdate() {

    }

    public function testBlankUpdate() {

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
