<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Notification.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Notification;
use AMC\Classes\User;
use AMC\Classes\UserNotification;
use AMC\Classes\Database;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class UserNotificationTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('jirbj.co.uk', 'testingDB', 'testingDB', 'testingdb');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check null constructor
        $testUserNotification = new UserNotification();

        $this->assertNull($testUserNotification->getID());
        $this->assertNull($testUserNotification->getUserID());
        $this->assertNull($testUserNotification->getNotificationID());
        $this->assertNull($testUserNotification->getAcknowledged());

        // Check the non null constructor
        $testUserNotification = new UserNotification(1,2,3, true);

        $this->assertEquals(1, $testUserNotification->getID());
        $this->assertEquals(2, $testUserNotification->getUserID());
        $this->assertEquals(3, $testUserNotification->getNotificationID());
        $this->assertTrue($testUserNotification->getAcknowledged());
    }

    public function testCreate() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test notification
        $testNotification = new Notification();
        $testNotification->setBody('testNotification');
        $testNotification->create();

        // Create a test user notification
        $testUserNotification = new UserNotification();
        $testUserNotification->setUserID($testUser->getID());
        $testUserNotification->setNotificationID($testNotification->getID());
        $testUserNotification->setAcknowledged(false);
        $testUserNotification->create();

        // Check id is now an int
        $this->assertInternalType('int', $testUserNotification->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `UserNotificationID`,`UserID`,`NotificationID`,`UserNotificationAcknowledged` FROM `User_Notifications` WHERE `UserNotificationID`=?");
        $stmt->bind_param('i', $testUserNotification->getID());
        $stmt->execute();
        $stmt->bind_result($userNotificationID, $userID, $notificationID, $acknowledged);

        // Check theres one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        if($acknowledged == 1) {
            $acknowledged = true;
        }
        else {
            $acknowledged = false;
        }

        $this->assertEquals($testUserNotification->getID(), $userNotificationID);
        $this->assertEquals($testUserNotification->getUserID(), $userID);
        $this->assertEquals($testUserNotification->getNotificationID(), $notificationID);
        $this->assertEquals($testUserNotification->getAcknowledged(), $acknowledged);

        $stmt->close();

        // Clean up
        $testUserNotification->delete();
        $testUser->delete();
        $testNotification->delete();
    }

    public function testBlankCreate() {
        // Create a test user notification
        $testUserNotification = new UserNotification();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testUserNotification->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank User Notification.', $e->getMessage());
        }
    }

    public function testUpdate() {
        // Create a test user
        $testUser  = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test notification
        $testNotification = [];
        $testNotification[0] = new Notification();
        $testNotification[0]->setBody('testNotification');
        $testNotification[0]->create();

        $testNotification[1] = new Notification();
        $testNotification[1]->setBody('testNotification2');
        $testNotification[1]->create();

        // Create a test user notification
        $testUserNotification = new UserNotification();
        $testUserNotification->setUserID($testUser[0]->getID());
        $testUserNotification->setNotificationID($testNotification[0]->getID());
        $testUserNotification->setAcknowledged(false);
        $testUserNotification->create();

        // Now update it
        $testUserNotification->setUserID($testUser[1]->getID());
        $testUserNotification->setNotificationID($testNotification[1]->getID());
        $testUserNotification->setAcknowledged(true);
        $testUserNotification->update();

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `UserNotificationID`,`UserID`,`NotificationID`,`UserNotificationAcknowledged` FROM `User_Notifications` WHERE `UserNotificationID`=?");
        $stmt->bind_param('i', $testUserNotification->getID());
        $stmt->execute();
        $stmt->bind_result($userNotificationID, $userID, $notificationID, $acknowledged);

        // Check theres one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        if($acknowledged == 1) {
            $acknowledged = true;
        }
        else {
            $acknowledged = false;
        }

        $this->assertEquals($testUserNotification->getID(), $userNotificationID);
        $this->assertEquals($testUserNotification->getUserID(), $userID);
        $this->assertEquals($testUserNotification->getNotificationID(), $notificationID);
        $this->assertEquals($testUserNotification->getAcknowledged(), $acknowledged);

        $stmt->close();

        // Clean up
        $testUserNotification->delete();
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testNotification as $notification) {
            $notification->delete();
        }
    }

    public function testBlankUpdate() {
        // Create a test user notification
        $testUserNotification = new UserNotification();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testUserNotification->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank User Notification.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test notification
        $testNotification = new Notification();
        $testNotification->setBody('testNotification');
        $testNotification->create();

        // Create a test user notification
        $testUserNotification = new UserNotification();
        $testUserNotification->setUserID($testUser->getID());
        $testUserNotification->setNotificationID($testNotification->getID());
        $testUserNotification->setAcknowledged(false);
        $testUserNotification->create();

        // Save the id
        $id = $testUserNotification->getID();

        // Now delete it
        $testUserNotification->delete();

        // Check id is null
        $this->assertNull($testUserNotification->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `UserNotificationID`,`UserID`,`NotificationID`,`UserNotificationAcknowledged` FROM `User_Notifications` WHERE `UserNotificationID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Now clean up
        $testUser->delete();
        $testNotification->delete();
    }

    public function testSelectWithInput() {
        // Create a test user
        $testUser  = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test notification
        $testNotification = [];
        $testNotification[0] = new Notification();
        $testNotification[0]->setBody('testNotification');
        $testNotification[0]->create();

        $testNotification[1] = new Notification();
        $testNotification[1]->setBody('testNotification2');
        $testNotification[1]->create();

        // Create a test user notification
        $testUserNotification = [];
        $testUserNotification[0] = new UserNotification();
        $testUserNotification[0]->setUserID($testUser[0]->getID());
        $testUserNotification[0]->setNotificationID($testNotification[0]->getID());
        $testUserNotification[0]->setAcknowledged(false);
        $testUserNotification[0]->create();

        $testUserNotification[1] = new UserNotification();
        $testUserNotification[1]->setUserID($testUser[1]->getID());
        $testUserNotification[1]->setNotificationID($testNotification[1]->getID());
        $testUserNotification[1]->setAcknowledged(false);
        $testUserNotification[1]->create();

        $testUserNotification[2] = new UserNotification();
        $testUserNotification[2]->setUserID($testUser[0]->getID());
        $testUserNotification[2]->setNotificationID($testNotification[1]->getID());
        $testUserNotification[2]->setAcknowledged(true);
        $testUserNotification[2]->create();

        // Select and check a single
        $selectedSingle = UserNotification::select(array($testUserNotification[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserNotification::class, $selectedSingle[0]);
        $this->assertEquals($testUserNotification[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserNotification[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserNotification[0]->getNotificationID(), $selectedSingle[0]->getNotificationID());
        $this->assertEquals($testUserNotification[0]->getAcknowledged(), $selectedSingle[0]->getAcknowledged());

        // Select and check multiple
        $selectedMultiple = UserNotification::select(array($testUserNotification[1]->getID(), $testUserNotification[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(UserNotification::class, $selectedMultiple[0]);
        $this->assertInstanceOf(UserNotification::class, $selectedMultiple[1]);

        if($testUserNotification[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testUserNotification[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testUserNotification[1]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testUserNotification[1]->getNotificationID(), $selectedMultiple[$i]->getNotificationID());
        $this->assertEquals($testUserNotification[1]->getAcknowledged(), $selectedMultiple[$i]->getAcknowledged());

        $this->assertEquals($testUserNotification[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testUserNotification[2]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testUserNotification[2]->getNotificationID(), $selectedMultiple[$j]->getNotificationID());
        $this->assertEquals($testUserNotification[2]->getAcknowledged(), $selectedMultiple[$j]->getAcknowledged());

        // Clean up
        foreach($testUserNotification as $userNotification) {
            $userNotification->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testNotification as $notification) {
            $notification->delete();
        }
    }

    public function testSelectAll() {
        // Create a test user
        $testUser  = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test notification
        $testNotification = [];
        $testNotification[0] = new Notification();
        $testNotification[0]->setBody('testNotification');
        $testNotification[0]->create();

        $testNotification[1] = new Notification();
        $testNotification[1]->setBody('testNotification2');
        $testNotification[1]->create();

        // Create a test user notification
        $testUserNotification = [];
        $testUserNotification[0] = new UserNotification();
        $testUserNotification[0]->setUserID($testUser[0]->getID());
        $testUserNotification[0]->setNotificationID($testNotification[0]->getID());
        $testUserNotification[0]->setAcknowledged(false);
        $testUserNotification[0]->create();

        $testUserNotification[1] = new UserNotification();
        $testUserNotification[1]->setUserID($testUser[1]->getID());
        $testUserNotification[1]->setNotificationID($testNotification[1]->getID());
        $testUserNotification[1]->setAcknowledged(false);
        $testUserNotification[1]->create();

        // Select and check multiple
        $selectedMultiple = UserNotification::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(UserNotification::class, $selectedMultiple[0]);
        $this->assertInstanceOf(UserNotification::class, $selectedMultiple[1]);

        if($testUserNotification[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testUserNotification[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testUserNotification[0]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testUserNotification[0]->getNotificationID(), $selectedMultiple[$i]->getNotificationID());
        $this->assertEquals($testUserNotification[0]->getAcknowledged(), $selectedMultiple[$i]->getAcknowledged());

        $this->assertEquals($testUserNotification[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testUserNotification[1]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testUserNotification[1]->getNotificationID(), $selectedMultiple[$j]->getNotificationID());
        $this->assertEquals($testUserNotification[1]->getAcknowledged(), $selectedMultiple[$j]->getAcknowledged());

        // Clean up
        foreach($testUserNotification as $userNotification) {
            $userNotification->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testNotification as $notification) {
            $notification->delete();
        }
    }

    public function testEql() {
        // Create a test user notification
        $testUserNotification = [];
        $testUserNotification[0] = new UserNotification();
        $testUserNotification[0]->setUserID(1);
        $testUserNotification[0]->setNotificationID(2);
        $testUserNotification[0]->setAcknowledged(false);

        $testUserNotification[1] = new UserNotification();
        $testUserNotification[1]->setUserID(1);
        $testUserNotification[1]->setNotificationID(2);
        $testUserNotification[1]->setAcknowledged(false);

        $testUserNotification[2] = new UserNotification();
        $testUserNotification[2]->setUserID(3);
        $testUserNotification[2]->setNotificationID(4);
        $testUserNotification[2]->setAcknowledged(true);

        // Check same object is eql
        $this->assertTrue($testUserNotification[0]->eql($testUserNotification[0]));

        // Check same details are eql
        $this->assertTrue($testUserNotification[0]->eql($testUserNotification[0]));

        // Check different arent equal
        $this->assertFalse($testUserNotification[0]->eql($testUserNotification[0]));
    }

    public function testGetByUserID() {
        // Create a test user
        $testUser  = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test notification
        $testNotification = [];
        $testNotification[0] = new Notification();
        $testNotification[0]->setBody('testNotification');
        $testNotification[0]->create();

        $testNotification[1] = new Notification();
        $testNotification[1]->setBody('testNotification2');
        $testNotification[1]->create();

        // Create a test user notification
        $testUserNotification = [];
        $testUserNotification[0] = new UserNotification();
        $testUserNotification[0]->setUserID($testUser[0]->getID());
        $testUserNotification[0]->setNotificationID($testNotification[0]->getID());
        $testUserNotification[0]->setAcknowledged(false);
        $testUserNotification[0]->create();

        $testUserNotification[1] = new UserNotification();
        $testUserNotification[1]->setUserID($testUser[1]->getID());
        $testUserNotification[1]->setNotificationID($testNotification[1]->getID());
        $testUserNotification[1]->setAcknowledged(false);
        $testUserNotification[1]->create();

        // Select and check a single
        $selectedSingle = UserNotification::getByUserID($testUser[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserNotification::class, $selectedSingle[0]);
        $this->assertEquals($testUserNotification[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserNotification[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserNotification[0]->getNotificationID(), $selectedSingle[0]->getNotificationID());
        $this->assertEquals($testUserNotification[0]->getAcknowledged(), $selectedSingle[0]->getAcknowledged());

        // Clean up
        foreach($testUserNotification as $userNotification) {
            $userNotification->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testNotification as $notification) {
            $notification->delete();
        }
    }

    public function testGetByNotificationID() {
        // Create a test user
        $testUser  = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test notification
        $testNotification = [];
        $testNotification[0] = new Notification();
        $testNotification[0]->setBody('testNotification');
        $testNotification[0]->create();

        $testNotification[1] = new Notification();
        $testNotification[1]->setBody('testNotification2');
        $testNotification[1]->create();

        // Create a test user notification
        $testUserNotification = [];
        $testUserNotification[0] = new UserNotification();
        $testUserNotification[0]->setUserID($testUser[0]->getID());
        $testUserNotification[0]->setNotificationID($testNotification[0]->getID());
        $testUserNotification[0]->setAcknowledged(false);
        $testUserNotification[0]->create();

        $testUserNotification[1] = new UserNotification();
        $testUserNotification[1]->setUserID($testUser[1]->getID());
        $testUserNotification[1]->setNotificationID($testNotification[1]->getID());
        $testUserNotification[1]->setAcknowledged(false);
        $testUserNotification[1]->create();

        // Select and check a single
        $selectedSingle = UserNotification::getByNotificationID($testNotification[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserNotification::class, $selectedSingle[0]);
        $this->assertEquals($testUserNotification[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserNotification[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserNotification[0]->getNotificationID(), $selectedSingle[0]->getNotificationID());
        $this->assertEquals($testUserNotification[0]->getAcknowledged(), $selectedSingle[0]->getAcknowledged());

        // Clean up
        foreach($testUserNotification as $userNotification) {
            $userNotification->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testNotification as $notification) {
            $notification->delete();
        }
    }

}
