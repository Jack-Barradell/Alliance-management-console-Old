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
use AMC\Classes\Notification;
use AMC\Classes\User;
use AMC\Classes\Database;
use AMC\Classes\UserNotification;
use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\IncorrectTypeException;
use AMC\Exceptions\InvalidUserException;
use AMC\Exceptions\MissingPrerequisiteException;
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
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->setActivated(true);
        $testUser->create();

        // TODO ############## FINISH THIS ###############

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
