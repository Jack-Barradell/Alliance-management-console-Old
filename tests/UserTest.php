<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/User.php';
require '../classes/Faction.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\AdminLog;
use AMC\Classes\Ban;
use AMC\Classes\Faction;
use AMC\Classes\Group;
use AMC\Classes\LoginLog;
use AMC\Classes\Notification;
use AMC\Classes\Privilege;
use AMC\Classes\User;
use AMC\Classes\Database;
use AMC\Classes\UserGroup;
use AMC\Classes\UserNotification;
use AMC\Classes\UserPrivilege;
use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\DuplicateEntryException;
use AMC\Exceptions\IncorrectTypeException;
use AMC\Exceptions\InvalidGroupException;
use AMC\Exceptions\InvalidUserException;
use AMC\Exceptions\MissingPrerequisiteException;
use PharIo\Manifest\Email;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
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
        // Create a test user to be banned
        $testUser = User::registerAccount('testUser', 'testPassword', 'test@email.com');

        // Create an admin to ban them
        $testAdmin = new User();
        $testAdmin->setUsername('testAdmin');
        $testAdmin->setActivated(true);
        $testAdmin->create();

        // Check the user can login before being banned
        $preBanLoginTest = User::login('testUser', 'testPassword');

        $this->assertInstanceOf(User::class, $preBanLoginTest);
        $this->assertEquals($testUser->getID(), $preBanLoginTest->getID());

        // Now issue a ban
        $timeOfBan = \time() + 1000;
        $testUser->ban($testAdmin->getID(), 'reason', $timeOfBan);

        // Check the user can no longer login
        $this->assertNull(User::login('testUser', 'testPassword'));

        // Check a ban was issued
        $ban = Ban::getByUserID($testUser->getID());

        $this->assertTrue(\is_array($ban));
        $this->assertEquals(1, \count($ban));
        $this->assertInstanceOf(Ban::class, $ban[0]);
        $this->assertEquals($testUser->getID(), $ban[0]->getUserID());
        $this->assertEquals($testAdmin->getID(), $ban[0]->getAdminID());
        $this->assertEquals('reason', $ban[0]->getReason());
        $this->assertTrue($ban[0]->getActive());

        $this->assertTrue($testUser->getBanned());

        // Check an admin log was created
        $adminLog = AdminLog::getByAdminID($testAdmin->getID());

        $this->assertTrue(\is_array($adminLog));
        $this->assertEquals(1, \count($adminLog));
        $this->assertInstanceOf(AdminLog::class, $adminLog[0]);
        $this->assertEquals($testAdmin->getID(), $adminLog[0]->getID());
        $this->assertEquals($testAdmin->getUsername() . ' banned user: ' . $testUser->getUsername() . ' with id: ' . $testUser->getID(), $adminLog[0]->getEvent());
        // Note timestamp would not equal due to potential delay in execution time so is not checked here

        // Check a user notification is issued
        $userNotification = UserNotification::getByUserID($testUser->getID());

        $this->assertTrue(\is_array($userNotification));
        $this->assertEquals(1, \count($userNotification));
        $this->assertInstanceOf(UserNotification::class, $userNotification[0]);
        $this->assertEquals($testUser->getID(), $userNotification[0]->getUserID());

        // Check the notification is issued
        $notification = Notification::get($userNotification[0]->getNotificationID());

        $this->assertInternalType(Notification::class, $notification);
        $this->assertEquals($userNotification[0]->getNotificationID(), $notification->getID());
        $this->assertEquals('You were banned by ' . $testAdmin->getUsername() . '. Reason: reason', $notification->getBody());

        // Clean up
        $notification->delete();
        $userNotification[0]->delete();
        $adminLog[0]->delete();
        $ban->delete();
        $testAdmin->delete();
        $testUser->delete();
    }

    public function testIncorrectTypeBan() {
        // Create a test user to be banned
        $testUser = User::registerAccount('testUser', 'testPassword', 'test@email.com');

        // Create an admin to ban them
        $testAdmin = new User();
        $testAdmin->setUsername('testAdmin');
        $testAdmin->create();

        // Set expected exception
        $this->expectException(IncorrectTypeException::class);

        // Trigger it
        try {
            $testUser->ban($testAdmin->getID(), 'reason', 'now');
        } catch(IncorrectTypeException $e) {
            $this->assertEquals('Ban expiry must be an int was given now', $e->getMessage());
        } finally {
            $testUser->delete();
            $testAdmin->delete();
        }
    }

    public function testInvalidUserBan() {
        // Create a test user to be banned
        $testUser = User::registerAccount('testUser', 'testPassword', 'test@email.com');

        // Get largest id for a user
        $stmt = $this->_connection->prepare("SELECT `UserID` FROM `Users` ORDER BY `UserID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($userID);
        $largestID = $userID + 1;

        // Set expected exception
        $this->expectException(InvalidUserException::class);

        // Trigger it
        try {
            $testUser->ban($largestID, 'reason', -1);
        } catch(InvalidUserException $e) {
            $this->assertEquals('No admin with ID' . $largestID, $e->getMessage());
        } finally {
            $testUser->delete();
        }
    }

    public function testUnban() {
        // Create a test user to be banned
        $testUser = User::registerAccount('testUser', 'testPassword', 'test@email.com');

        // Create an admin to ban them
        $testAdmin = new User();
        $testAdmin->setUsername('testAdmin');
        $testAdmin->setActivated(true);
        $testAdmin->create();

        // Check the user can login before being banned
        $preBanLoginTest = User::login('testUser', 'testPassword');

        $this->assertInstanceOf(User::class, $preBanLoginTest);
        $this->assertEquals($testUser->getID(), $preBanLoginTest->getID());

        // Now ban the user
        $testUser->ban($testAdmin->getID(), 'reason', -1);

        $this->assertNull(User::login('testUser', 'testPassword'));

        // Now unban them
        $testUser->unban($testAdmin->getID());

        // Now check they can login
        $postBanLoginTest = User::login('testUser', 'testPassword');

        $this->assertInstanceOf(User::class, $postBanLoginTest);
        $this->assertEquals($testUser->getID(), $postBanLoginTest->getID());

        // Pull created objects
        $adminLogs = AdminLog::getByAdminID($testAdmin->getID());
        $bans = Ban::getByUserID($testUser->getID());
        $userNotifications = UserNotification::getByUserID($testUser->getID());
        $notificationIDs = array($userNotifications[0]->getNotificationID(), $userNotifications[1]->getNotificationID());
        $notifications = Notification::get($notificationIDs);

        // Check the ban is marked as inactive
        $this->assertFalse($bans[0]->getActive());

        // Check there are 2 admin logs
        $this->assertEquals(2, \count($adminLogs));

        // Check there are 2 notifications
        $this->assertEquals(2, \count($notifications));

        // Check there are 2 user notifications
        $this->assertEquals(2, \count($userNotifications));

        // Clean up
        foreach($userNotifications as $userNotification) {
            $userNotification->delete();
        }
        foreach($bans as $ban) {
            $ban->delete();
        }
        foreach($notifications as $notification) {
            $notification->delete();
        }
        foreach($adminLogs as $adminLog) {
            $adminLog->delete();
        }
        $testAdmin->delete();
        $testUser->delete();
    }

    public function testInvalidUserUnban() {
        // Create a test user to be banned
        $testUser = User::registerAccount('testUser', 'testPassword', 'test@email.com');

        // Create an admin to ban them
        $testAdmin = new User();
        $testAdmin->setUsername('testAdmin');
        $testAdmin->setActivated(true);
        $testAdmin->create();

        // Get largest id for a user
        $stmt = $this->_connection->prepare("SELECT `UserID` FROM `Users` ORDER BY `UserID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($userID);
        $largestID = $userID + 1;

        // Ban the user
        $testUser->ban($testAdmin->getID(), 'reason', -1);

        // Set expected exception
        $this->expectException(InvalidUserException::class);

        // Trigger it
        try {
            $testUser->unban($largestID);
        } catch(InvalidUserException $e) {
            $this->assertEquals('No admin with ID: ' . $largestID, $e->getMessage());
        } finally {
            // Clean up
            $userNotifications = UserNotification::getByUserID($testUser);
            $notificationIDs = [];
            foreach($userNotifications as $userNotification) {
                $notificationIDs[] = $userNotification->getNotificationID();
                $userNotification->delete();
            }
            $notifications = Notification::get($notificationIDs);
            foreach($notifications as $notification) {
                $notification->delete();
            }
            $bans = Ban::getByUserID($testUser->getID());
            foreach($bans as $ban) {
                $ban->delete();
            }
            $adminLogs = AdminLog::getByAdminID($testAdmin->getID());
            foreach($adminLogs as $adminLog) {
                $adminLog->delete();
            }
            $testUser->delete();
            $testAdmin->delete();
        }
    }

    public function testMissingPrerequisiteUnban() {
        // Create a test user to be banned
        $testUser = User::registerAccount('testUser', 'testPassword', 'test@email.com');

        // Create an admin to ban them
        $testAdmin = new User();
        $testAdmin->setUsername('testAdmin');
        $testAdmin->setActivated(true);
        $testAdmin->create();

        // Get largest id for a user
        $stmt = $this->_connection->prepare("SELECT `UserID` FROM `Users` ORDER BY `UserID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($userID);
        $largestID = $userID + 1;

        // Set expected exception
        $this->expectException(MissingPrerequisiteException::class);

        // Trigger it
        try {
            $testUser->unban($largestID);
        } catch(MissingPrerequisiteException $e) {
            $this->assertEquals('Tried to unban a non-banned user with ID: ' . $testUser->getID(), $e->getMessage());
        } finally {
            // Clean up
            $userNotifications = UserNotification::getByUserID($testUser);
            $notificationIDs = [];
            foreach($userNotifications as $userNotification) {
                $notificationIDs[] = $userNotification->getNotificationID();
                $userNotification->delete();
            }
            $notifications = Notification::get($notificationIDs);
            foreach($notifications as $notification) {
                $notification->delete();
            }
            $bans = Ban::getByUserID($testUser->getID());
            foreach($bans as $ban) {
                $ban->delete();
            }
            $adminLogs = AdminLog::getByAdminID($testAdmin->getID());
            foreach($adminLogs as $adminLog) {
                $adminLog->delete();
            }
            $testUser->delete();
            $testAdmin->delete();
        }
    }

    public function testChangePassword() {
        // Create a test user
        $testUser = User::registerAccount('testUser', 'testPassword', 'test@email.com');

        $oldHash = $testUser->getPasswordHash();

        // Change the password
        $testUser->changePassword('newPassword');

        // Check the hash has changed
        $this->assertNotEquals($oldHash, $testUser->getPasswordHash());

        // Get the user notification
        $userNotifications = UserNotification::getByUserID($testUser->getID());

        $this->assertTrue(\is_array($userNotifications));
        $this->assertEquals(1, \count($userNotifications));
        $this->assertInstanceOf(UserNotification::class, $userNotifications[0]);
        $this->assertEquals($testUser->getID(), $userNotifications[0]->getID());

        // Get the notification
        $notification = Notification::get($userNotifications[0]->getNotificationID());
        $this->assertEquals('You changed your password.', $notification->getBody());

        // Clean up
        $userNotifications[0]->delete();
        $notification->delete();
        $testUser->delete();
    }

    public function testUserExists() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->setActivated(true);
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->setActivated(false);
        $testUser[1]->create();

        // Get largest id for a user
        $stmt = $this->_connection->prepare("SELECT `UserID` FROM `Users` ORDER BY `UserID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($userID);
        $largestID = $userID + 1;

        // Check no user exists with an id out of range
        $this->assertFalse(User::userExists($largestID, false, false));
        $this->assertFalse(User::userExists($largestID, true, false));
        $this->assertFalse(User::userExists($largestID, true, true));
        $this->assertFalse(User::userExists($largestID, false, true));

        // Check user exists with valid id and shows no matter the filters
        $this->assertTrue(User::userExists($testUser[0]->getID(), false, false));
        $this->assertTrue(User::userExists($testUser[0]->getID(), true, false));
        $this->assertEquals($testUser[0]->getUsername(), User::userExists($testUser[0]->getID(), true, true));
        $this->assertEquals($testUser[0]->getUsername(), User::userExists($testUser[0]->getID(), false, true));

        // Check user exists with valid name even with filters
        $this->assertTrue(User::userExists($testUser[0]->getUsername(), false, false));
        $this->assertTrue(User::userExists($testUser[0]->getUsername(), true, false));
        $this->assertEquals($testUser[0]->getID(), User::userExists($testUser[0]->getUsername(), true, true));
        $this->assertEquals($testUser[0]->getID(), User::userExists($testUser[0]->getUsername(), false, true));

        // Check the disabled user only shows when includeInactive is true when using id
        $this->assertFalse(User::userExists($testUser[1]->getID(), false, false));
        $this->assertTrue(User::userExists($testUser[1]->getID(), true, false));
        $this->assertEquals($testUser[1]->getUsername(), User::userExists($testUser[1]->getID(), true, true));
        $this->assertFalse(User::userExists($testUser[1]->getID(), false, true));

        // Check the disabled user only shows when includeInactive is true when using username
        $this->assertFalse(User::userExists($testUser[1]->getUsername(), false, false));
        $this->assertTrue(User::userExists($testUser[1]->getUsername(), true, false));
        $this->assertEquals($testUser[1]->getID(), User::userExists($testUser[1]->getUsername(), true, true));
        $this->assertFalse(User::userExists($testUser[1]->getUsername(), false, true));

        // Clean up
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testIncorrectTypeUserExists() {
        // Set expected exception
        $this->expectException(IncorrectTypeException::class);

        // Trigger it
        try {
            User::userExists(false);
        } catch(IncorrectTypeException $e) {
            $this->assertEquals('Must provide id (int) or string for name was given boolean', $e->getMessage());
        }
    }

    public function testEmailExists() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->setEmail('test@email.com');
        $testUser[0]->setActivated(true);
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[0]->setEmail('test2@email.com');
        $testUser[1]->setActivated(false);
        $testUser[1]->create();

        // Check an invalid email returns false
        $this->assertFalse(User::emailExists('notAValid@email.com', false, false));
        $this->assertFalse(User::emailExists('notAValid@email.com', true, false));
        $this->assertFalse(User::emailExists('notAValid@email.com', true, true));
        $this->assertFalse(User::emailExists('notAValid@email.com', false, true));

        // Check a valid activated user shows
        $this->assertTrue(User::emailExists($testUser[0]->getEmail(), false, false));
        $this->assertTrue(User::emailExists($testUser[0]->getEmail(), true, false));
        $this->assertEquals($testUser[0]->getID(), User::emailExists($testUser[0]->getEmail(), true, true));
        $this->assertEquals($testUser[0]->getID(), User::emailExists($testUser[0]->getEmail(), false, true));

        // Check an inactive used only shows when IncludeInactive is true
        $this->assertFalse(User::emailExists($testUser[1]->getEmail(), false, false));
        $this->assertTrue(User::emailExists($testUser[1]->getEmail(), true, false));
        $this->assertEquals($testUser[1]->getID(), User::emailExists($testUser[1]->getEmail(), true, true));
        $this->assertFalse(User::emailExists($testUser[1]->getEmail(), false, true));

        // Clean up
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testIncorrectTypeEmailExists() {
        // Set expected exception
        $this->expectException(IncorrectTypeException::class);

        // Trigger it
        try {
            User::emailExists(false);
        } catch(IncorrectTypeException $e) {
            $this->assertEquals('Email must be provided as string was given boolean', $e->getMessage());
        }
    }

    public function testRegisterAccount() {
        // Register a user
        $testUser = User::registerAccount('testUser', 'password', 'test@email.com');

        // Check the user has an integer id
        $this->assertInternalType('int', $testUser->getID());

        // Check the details are correct
        $this->assertEquals('testUser', $testUser->getUsername());
        $this->assertEquals('test@email.com', $testUser->getEmail());
        $this->assertTrue($testUser->getActivated());
        $this->assertFalse($testUser->getBanned());
        $this->assertFalse($testUser->getSystemAccount());
        $this->assertEquals(-1, $testUser->getLastLogin());

        // Check there is now a user notification
        $userNotifications = UserNotification::getByUserID($testUser->getID());

        $this->assertTrue(\is_array($userNotifications));
        $this->assertEquals(1, \count($userNotifications));
        $this->assertInstanceOf(UserNotification::class, $userNotifications[0]);
        $this->assertFalse($userNotifications[0]->getAcknowledged());

        // Check a notification is created
        $notification = Notification::get($userNotifications[0]->getNotificationID());

        $this->assertInstanceOf(Notification::class, $notification[0]);
        $this->assertEquals('Account created.', $notification->getBody());

        // Clean up
        $userNotifications[0]->delete();
        $notification->delete();
        $testUser->delete();
    }

    public function testIncorrectTypeRegisterAccount() {
        // Set expected exception
        $this->expectException(IncorrectTypeException::class);

        // Trigger it for username
        try {
            User::registerAccount(false, 'password', 'test@email.com');
        } catch(IncorrectTypeException $e) {
            $this->assertEquals('Username must be a string and not be blank was given boolean', $e->getMessage());
        }
        try {
            User::registerAccount('', 'password', 'test@email.com');
        } catch(IncorrectTypeException $e) {
            $this->assertEquals('Username must be a string and not be blank was given string', $e->getMessage());
        }
        try {
            User::registerAccount('     ', 'password', 'test@email.com');
        } catch(IncorrectTypeException $e) {
            $this->assertEquals('Username must be a string and not be blank was given string', $e->getMessage());
        }

        // Trigger it for password
        try {
            User::registerAccount('testUser', false, 'test@email.com');
        } catch(IncorrectTypeException $e) {
            $this->assertEquals('Password must be a string and not be blank was given boolean', $e->getMessage());
        }
        try {
            User::registerAccount('testUser', '', 'test@email.com');
        } catch(IncorrectTypeException $e) {
            $this->assertEquals('Password must be a string and not be blank was given string', $e->getMessage());
        }
        try {
            User::registerAccount('testUser', '    ', 'test@email.com');
        } catch(IncorrectTypeException $e) {
            $this->assertEquals('Password must be a string and not be blank was given string', $e->getMessage());
        }

        // Trigger it for email
        try {
            User::registerAccount('testUser', 'password', false);
        } catch(IncorrectTypeException $e) {
            $this->assertEquals('Email must be a string and not be blank was given boolean', $e->getMessage());
        }
        try {
            User::registerAccount('testUser', 'password', '');
        } catch(IncorrectTypeException $e) {
            $this->assertEquals('Email must be a string and not be blank was given string', $e->getMessage());
        }
        try {
            User::registerAccount('testUser', 'password', '    ');
        } catch(IncorrectTypeException $e) {
            $this->assertEquals('Email must be a string and not be blank was given string', $e->getMessage());
        }

        // Trigger it for activated
        try {
            User::registerAccount('testUser', 'password', 'test@email.com', 'string');
        } catch(IncorrectTypeException $e) {
            $this->assertEquals('Activated state must be a boolean was given string', $e->getMessage());
        }

        // Trigger it for system account
        try {
            User::registerAccount('testUser', 'password', 'test@email.com', true, 'string');
        } catch(IncorrectTypeException $e) {
            $this->assertEquals('System account state must be a boolean was given string', $e->getMessage());
        }
    }

    public function testDuplicateEntryRegisterAccount() {
        // Create test users
        $testUser = [];
        $testUser[0] = User::registerAccount('testUser', 'password', 'test@email.com');
        $testUser[1] = User::registerAccount('testUser2', 'password', 'test@email2.com');

        // Set expected exception
        $this->expectException(DuplicateEntryException::class);

        // Trigger it for username
        try {
            User::registerAccount('testUser', 'password', 'test@emailer.com');
        } catch(DuplicateEntryException $e) {
            $this->assertEquals('User with username testUser already exists', $e->getMessage());
        }

        // Trigger it for email
        try {
            User::registerAccount('testUser3', 'password', 'test@email2.com');
        } catch(DuplicateEntryException $e) {
            $this->assertEquals('User with email test@email2.com already exists', $e->getMessage());
        } finally {
            foreach($testUser as $user) {
                $user->delete();
            }
        }
    }

    public function testLogin() {
        // Create a test user
        $testUser = User::registerAccount('testUser', 'password', 'test@email.com');

        // Check logging into an invalid user returns null
        $this->assertNull(User::login('invalidUser', 'randomPassword'));

        // Check the test user can login
        $loggedIn = User::login('testUser', 'password');
        $this->assertInstanceOf(User::class, $loggedIn);
        $this->assertEquals($testUser->getID(), $loggedIn->getID());

        // Check a login log is created
        $loginLog = LoginLog::getByUserID($testUser->getID());
        $this->assertTrue(\is_array($loginLog));
        $this->assertEquals(1, \count($loginLog));
        $this->assertInternalType(LoginLog::class, $loginLog[0]);
        $this->assertEquals('Success', $loginLog[0]->getResult());

        // Clean out the login log
        $loginLog[0]->delete();

        // Check the incorrect password doesn't log in
        $failedLogin = User::login('testUser', 'wrongPassword');
        $this->assertNull($failedLogin);

        // Check a login log is created
        $loginLog = LoginLog::getByUserID($testUser->getID());
        $this->assertTrue(\is_array($loginLog));
        $this->assertEquals(1, \count($loginLog));
        $this->assertInternalType(LoginLog::class, $loginLog[0]);
        $this->assertEquals('Failed', $loginLog[0]->getResult());

        // Delete the login log
        $loginLog[0]->delete();

        // Make the user inactive
        $testUser->setActivated(false);
        $testUser->update();

        // Check the user cant login even with the right password
        $failedLogin = User::login('testUser', 'password');
        $this->assertNull($failedLogin);

        // Now check a failed login log is created
        $loginLog = LoginLog::getByUserID($testUser->getID());
        $this->assertTrue(\is_array($loginLog));
        $this->assertEquals(1, \count($loginLog));
        $this->assertInternalType(LoginLog::class, $loginLog[0]);
        $this->assertEquals('Banned/Inactive', $loginLog[0]->getResult());

        // Delete login log
        $loginLog->delete();

        // Clean up
        $testUser->delete();
    }

    public function testHasUserPrivilege() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->setActivated(true);
        $testUser->create();

        // Create test privilege
        $testPrivilege = new Privilege();
        $testPrivilege->setName('testPriv');
        $testPrivilege->create();

        // Check the user doesn't have the priv
        $this->assertFalse($testUser->hasUserPrivilege($testPrivilege->getID()));

        // Issue the priv
        $testUser->issuePrivilege($testPrivilege->getID());

        // Check the user now has the priv
        $this->assertTrue($testUser->hasUserPrivilege($testPrivilege->getID()));


        // Clean up
        $testUser->revokePrivilege($testPrivilege->getID());
        $testUser->delete();
        $testPrivilege->delete();
    }

    public function testInvalidPrivilegeHasUserPrivilege() {
        //TODO: Implement
    }

    public function testHasPrivilege() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->setActivated(true);
        $testUser->create();

        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('testGroup');
        $testGroup->create();

        // Create test privileges
        $testPrivilege = [];

        $testPrivilege[0] = new Privilege();
        $testPrivilege[0]->setName('testPriv');
        $testPrivilege[0]->create();

        $testPrivilege[1] = new Privilege();
        $testPrivilege[1]->setName('testPriv2');
        $testPrivilege[1]->create();

        // Add the user to the group
        $testUser->addToGroup($testGroup->getID());

        // Check user has neither priv
        $this->assertFalse($testUser->hasPrivilege($testPrivilege[0]->getID()));
        $this->assertFalse($testUser->hasPrivilege($testPrivilege[1]->getID()));

        // Issue the first priv to the user
        $testUser->issuePrivilege($testPrivilege[0]->getID());

        // Check user now has first priv
        $this->assertTrue($testUser->hasPrivilege($testPrivilege[0]->getID()));
        $this->assertFalse($testUser->hasPrivilege($testPrivilege[1]->getID()));

        // Issue the second priv to the group
        $testGroup->issuePrivilege($testPrivilege[1]->getID());

        // Check user has both privs
        $this->assertTrue($testUser->hasPrivilege($testPrivilege[0]->getID()));
        $this->assertTrue($testUser->hasPrivilege($testPrivilege[1]->getID()));

        // Give the user the second priv
        $testUser->issuePrivilege($testPrivilege[1]->getID());

        // Check it doesnt break if the user has the priv 2 ways
        $this->assertTrue($testUser->hasPrivilege($testPrivilege[1]->getID()));

        // Clean up
        $testUser->revokePrivilege($testPrivilege[0]->getID());
        $testUser->revokePrivilege($testPrivilege[1]->getID());
        $testGroup->revokePrivilege($testPrivilege[1]->getID());
        foreach($testPrivilege as $priv) {
            $priv->delete();
        }
        $testUser->removeFromGroup($testGroup->getID());
        $testUser->delete();
        $testGroup->delete();
    }

    public function testInvalidPrivilegeHasPrivilege() {
        //TODO: Implement
    }

    public function testAddToGroup() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->setActivated(true);
        $testUser->create();

        // Create a test group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('testGroup');
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('testGroup2');
        $testGroup[1]->create();

        // Check there are no user groups
        $this->assertNull(UserGroup::getByUserID($testUser->getID()));
        $this->assertNull(UserGroup::getByGroupID($testGroup[0]->getID()));

        // Add the user to the group
        $testUser->addToGroup($testGroup[0]->getID());

        // Check there is now a user group
        $userGroups = UserGroup::getByUserID($testUser->getID());

        $this->assertTrue(\is_array($userGroups));
        $this->assertEquals(1, \count($userGroups));
        $this->assertInstanceOf(UserGroup::class, $userGroups[0]);
        $this->assertEquals($testUser->getID(), $userGroups[0]->getUserID());
        $this->assertEquals($testGroup[0]->getID(), $userGroups[0]->getGroupID());
        $this->assertFalse($userGroups[0]->getAdmin());

        // Remove the user
        $testUser->removeFromGroup($testGroup[0]->getID());

        // Add use to second group this time as admin
        $testUser->addToGroup($testGroup[1]->getID(), true);

        // Check there is now a user group
        $userGroups = UserGroup::getByUserID($testUser->getID());

        $this->assertTrue(\is_array($userGroups));
        $this->assertEquals(1, \count($userGroups));
        $this->assertInstanceOf(UserGroup::class, $userGroups[0]);
        $this->assertEquals($testUser->getID(), $userGroups[0]->getUserID());
        $this->assertEquals($testGroup[1]->getID(), $userGroups[0]->getGroupID());
        $this->assertTrue($userGroups[1]->getAdmin());

        // Clean up
        $testUser->removeFromGroup($testGroup[1]->getID());
        $testUser->delete();
        foreach($testGroup as $group) {
            $group->delete();
        }
    }

    public function testDuplicateEntryAddToGroup() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->setActivated(true);
        $testUser->create();

        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('testGroup');
        $testGroup->create();

        // Add to group
        $testUser->addToGroup($testGroup->getID());

        // Set expected exception
        $this->expectException(DuplicateEntryException::class);

        // Trigger it
        try {
            $testUser->addToGroup($testGroup->getID());
        } catch(DuplicateEntryException $e) {
            $this->assertEquals('User with id ' . $testUser->getID() . ' was added to Group with id ' . $testGroup->getID() . ' but they were already a member.', $e->getMessage());
        } finally {
            $testUser->removeFromGroup($testGroup->getID());
            $testUser->delete();
            $testGroup->delete();
        }
    }

    public function testInvalidGroupAddToGroup() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->setActivated(true);
        $testUser->create();

        // Find an invalid group id
        $stmt = $this->_connection->prepare("SELECT `GroupID` FROM `Groups` ORDER BY `GroupID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($resultID);
        $stmt->fetch();
        $invalidID = $resultID + 1;
        $stmt->close();

        // Set expected exception
        $this->expectException(InvalidGroupException::class);

        // Trigger it
        try {
            $testUser->addToGroup($invalidID);
        } catch(InvalidGroupException $e) {
            $this->assertEquals('No group found with id ' . $invalidID, $e->getMessage());
        } finally {
            $testUser->delete();
        }
    }

    public function testRemoveFromGroup() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->setActivated(true);
        $testUser->create();

        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('testGroup');
        $testGroup->create();

        // Add the user to the group
        $testUser->addToGroup($testGroup->getID());

        // Check there is a user group
        $this->assertTrue(\is_array(UserGroup::getByUserID($testUser->getID())));

        // Now remove the user from the group
        $testUser->removeFromGroup($testGroup->getID());

        // Check there are now no user groups
        $this->assertNull(UserGroup::getByUserID($testUser->getID()));

        // Clean up
        $testUser->delete();
        $testGroup->delete();
    }

    public function testMissingPrerequisiteRemoveFromGroup() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->setActivated(true);
        $testUser->create();

        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('testGroup');
        $testGroup->create();

        // Set expected exception
        $this->expectException(MissingPrerequisiteException::class);

        // Trigger it
        try {
            $testUser->removeFromGroup($testGroup->getID());
        } catch(MissingPrerequisiteException $e) {
            $this->assertEquals('Tried to remove user with ID ' . $testUser->getID() . ' from group with id ' . $testGroup->getID() . ' when they are not a member.', $e->getMessage());
        } finally {
            $testGroup->delete();
            $testUser->delete();
        }
    }

    public function testInvalidGroupRemoveFromGroup() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->setActivated(true);
        $testUser->create();

        // Find an invalid group id
        $stmt = $this->_connection->prepare("SELECT `GroupID` FROM `Groups` ORDER BY `GroupID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($resultID);
        $stmt->fetch();
        $invalidID = $resultID + 1;
        $stmt->close();

        // Set expected exception
        $this->expectException(InvalidGroupException::class);

        // Trigger it
        try {
            $testUser->removeFromGroup($invalidID);
        } catch(InvalidGroupException $e) {
            $this->assertEquals('No group found with id ' . $invalidID, $e->getMessage());
        } finally {
            $testUser->delete();
        }
    }

    public function testIsInGroup() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->setActivated(true);
        $testUser->create();

        // Create a test group
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('testGroup');
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('testGroup2');
        $testGroup[1]->create();

        // Check the user is in neither group
        $this->assertFalse($testUser->isInGroup($testGroup[0]->getID()));
        $this->assertFalse($testUser->isInGroup($testGroup[1]->getID()));

        // Add the user to the first group
        $testUser->addToGroup($testGroup[0]->getID());

        // Check the user is in group one but not group two
        $this->assertTrue($testUser->isInGroup($testGroup[0]->getID()));
        $this->assertFalse($testUser->isInGroup($testGroup[1]->getID()));

        // Add the user to the second group
        $testUser->addToGroup($testGroup[1]->getID());

        // Check the user is now in both groups
        $this->assertTrue($testUser->isInGroup($testGroup[0]->getID()));
        $this->assertTrue($testUser->isInGroup($testGroup[1]->getID()));

        // Remove the user from the first group
        $testUser->removeFromGroup($testGroup[0]->getID());

        // Check the user is only in the second group
        $this->assertFalse($testUser->isInGroup($testGroup[0]->getID()));
        $this->assertTrue($testUser->isInGroup($testGroup[1]->getID()));

        // Remove the user from the second group
        $testUser->removeFromGroup($testGroup[1]->getID());

        // Check the user is in neither group
        $this->assertFalse($testUser->isInGroup($testGroup[0]->getID()));
        $this->assertFalse($testUser->isInGroup($testGroup[1]->getID()));

        // Clean up
        $testUser->delete();
        foreach($testGroup as $group) {
            $group->delete();
        }
    }

    public function testGetGroups() {
        // Create test user
        $testUser = new User();
        $testUser->setUsername('testName');
        $testUser->setActivated(true);
        $testUser->create();

        // Create test groups
        $testGroup = [];
        $testGroup[0] = new Group();
        $testGroup[0]->setName('testGroup');
        $testGroup[0]->create();

        $testGroup[1] = new Group();
        $testGroup[1]->setName('testGroup2');
        $testGroup[1]->create();

        // Check when a user has no groups it returns null
        $this->assertNull($testUser->getGroups());

        // Add the user to group one
        $testUser->addToGroup($testGroup[0]->getID());

        // Check it now returns an array including the group
        $groups = $testUser->getGroups();

        $this->assertTrue(\is_array($groups));
        $this->assertEquals(1, \count($groups));
        $this->assertInternalType(Group::class, $groups[0]);
        $this->assertEquals($testGroup[0]->getID(), $groups[0]->getID());
        $this->assertEquals($testGroup[0]->getName(), $groups[0]->getName());
        $this->assertEquals($testGroup[0]->getHidden(), $groups[0]->getHidden());

        // Now add the user to the second group
        $testUser->addToGroup($testGroup[1]->getID(), true);

        // Check it now returns an array including the groups
        $groups = $testUser->getGroups();

        $this->assertTrue(\is_array($groups));
        $this->assertEquals(2, \count($groups));
        $this->assertInternalType(Group::class, $groups[0]);
        $this->assertInternalType(Group::class, $groups[1]);

        if($testGroup[0]->getID() == $groups[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testGroup[0]->getID(), $groups[$i]->getID());
        $this->assertEquals($testGroup[0]->getName(), $groups[$i]->getName());
        $this->assertEquals($testGroup[0]->getHidden(), $groups[$i]->getHidden());

        $this->assertEquals($testGroup[1]->getID(), $groups[$j]->getID());
        $this->assertEquals($testGroup[1]->getName(), $groups[$j]->getName());
        $this->assertEquals($testGroup[1]->getHidden(), $groups[$j]->getHidden());

        // Clean up
        $testUser->removeFromGroup($testGroup[0]->getID());
        $testUser->removeFromGroup($testGroup[1]->getID());
        foreach($testGroup as $group) {
            $group->delete();
        }
        $testUser->delete();
    }

    public function testIssuePrivilege() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->setActivated(true);
        $testUser->create();

        // Create a test privilege
        $testPrivilege = new Privilege();
        $testPrivilege->setName('testPriv');
        $testPrivilege->create();

        // Check there are no user privileges for the user or the priv
        $this->assertNull(UserPrivilege::getByUserID($testUser->getID()));
        $this->assertNull(UserPrivilege::getByPrivilegeID($testUser->getID()));

        // Now issue the priv to the user
        $testUser->issuePrivilege($testPrivilege->getID());

        // Pull the user privs
        $userPrivs = UserPrivilege::getByUserID($testUser->getID());

        $this->assertTrue(\is_array($userPrivs));
        $this->assertEquals(1, \count($userPrivs));
        $this->assertInstanceOf(UserPrivilege::class, $userPrivs[0]);
        $this->assertEquals($testUser->getID(), $userPrivs[0]->getUserID());
        $this->assertEquals($testPrivilege->getID(), $userPrivs[0]->getPrivilegeID());

        // Clean up
        $testUser->revokePrivilege($testPrivilege->getID());
        $testUser->delete();
        $testPrivilege->delete();
    }

    public function testDuplicateEntryIssuePrivilege() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->setActivated(true);
        $testUser->create();

        // Create a test privilege
        $testPrivilege = new Privilege();
        $testPrivilege->setName('testPriv');
        $testPrivilege->create();

        // Now issue the priv to the user
        $testUser->issuePrivilege($testPrivilege->getID());

        // Set expected exception
        $this->expectException(DuplicateEntryException::class);

        // Trigger it
        try {
            $testUser->issuePrivilege($testPrivilege->getID());
        } catch(DuplicateEntryException $e) {
            $this->assertEquals('User with id ' . $testUser->getID() . ' was issued Privilege with id ' . $testPrivilege->getID() . ' when they already have it.', $e->getMessage());
        } finally {
            $testUser->revokePrivilege($testPrivilege->getID());
            $testUser->delete();
            $testPrivilege->delete();
        }
    }

    public function testInvalidPrivilegeIssuePrivilege() {
        // TODO: Implement
    }

    public function testRevokePrivilege() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->setActivated(true);
        $testUser->create();

        // Create a test privilege
        $testPrivilege = new Privilege();
        $testPrivilege->setName('testPriv');
        $testPrivilege->create();

        // Now issue the priv to the user
        $testUser->issuePrivilege($testPrivilege->getID());

        // Check its issued
        $this->assertTrue(\is_array(UserPrivilege::getByUserID($testUser->getID())));

        // Now revoke the priv
        $testUser->revokePrivilege($testPrivilege->getID());

        // Check there are no user privileges for the user or the priv
        $this->assertNull(UserPrivilege::getByUserID($testUser->getID()));
        $this->assertNull(UserPrivilege::getByPrivilegeID($testUser->getID()));

        // Clean up
        $testUser->delete();
        $testPrivilege->delete();
    }

    public function testMissingPrerequisiteRevokePrivilege() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->setActivated(true);
        $testUser->create();

        // Create a test privilege
        $testPrivilege = new Privilege();
        $testPrivilege->setName('testPriv');
        $testPrivilege->create();

        // Set expected exception
        $this->expectException(MissingPrerequisiteException::class);

        // Trigger it
        try {
            $testUser->revokePrivilege($testPrivilege->getID());
        } catch(MissingPrerequisiteException $e) {
            $this->assertEquals('Tried to revoke privilege with id ' . $testPrivilege->getID() . ' from user with id ' . $testUser->getID() . ' when the user doesnt have it.', $e->getMessage());
        } finally {
            $testUser->delete();
            $testPrivilege->delete();
        }
    }

    public function testInvalidPrivilegeRevokePrivilege() {
        // TODO: Implement
    }

    public function testGetPrivileges() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->setActivated(true);
        $testUser->create();

        // Create a test privilege
        $testPrivilege = [];
        $testPrivilege[0] = new Privilege();
        $testPrivilege[0]->setName('testPriv');
        $testPrivilege[0]->create();

        $testPrivilege[1] = new Privilege();
        $testPrivilege[1]->setName('testPriv2');
        $testPrivilege[1]->create();

        // Check it returns null when there are no privs assigned
        $this->assertNull($testUser->getPrivileges());

        // Now issue the first priv
        $testUser->issuePrivilege($testPrivilege[0]->getID());

        // Check the return
        $privs = $testUser->getPrivileges();

        $this->assertTrue(\is_array($privs));
        $this->assertEquals(1, \count($privs));
        $this->assertInstanceOf(Privilege::class, $privs[0]);
        $this->assertEquals($testPrivilege[0]->getID(), $privs[0]->getID());
        $this->assertEquals($testPrivilege[0]->getName(), $privs[0]->getName());

        // Issue the other priv
        $testUser->issuePrivilege($testPrivilege[1]->getID());

        // Check the return
        $privs = $testUser->getPrivileges();

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
        $testUser->revokePrivilege($testPrivilege[0]->getID());
        $testUser->revokePrivilege($testPrivilege[1]->getID());
        foreach($testPrivilege as $priv) {
            $priv->delete();
        }
        $testUser->delete();
    }

    public function testIssueRank() {
        //TODO: Implement
    }

    public function testDuplicateEntryIssueRank() {
        //TODO: Implement
    }

    public function testInvalidRankIssueRank() {
        //TODO: Implement
    }

    public function testRevokeRank() {
        //TODO: Implement
    }

    public function testMissingPrerequisiteRevokeRank() {
        //TODO: Implement
    }

    public function testInvalidRankRevokeRank() {
        //TODO: Implement
    }

    public function testHasRank() {
        //TODO: Implement
    }

    public function testInvalidRankHasRank() {
        //TODO: Implement
    }

    public function testGetRanks() {
        //TODO: Implement
    }

}
