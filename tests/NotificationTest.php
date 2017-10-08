<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Notification.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Group;
use AMC\Classes\Notification;
use AMC\Classes\Database;
use AMC\Classes\User;
use AMC\Classes\UserNotification;
use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\InvalidGroupException;
use AMC\Exceptions\InvalidUserException;
use PHPUnit\Framework\TestCase;

class NotificationTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Create and test null constructor
        $notification = new Notification();

        $this->assertTrue($notification->eql(new Notification()));
        $this->assertNull($notification->getID());
        $this->assertNull($notification->getBody());
        $this->assertNull($notification->getTimestamp());

        // Create and test non null constructor
        $notification = new Notification(1, 'note', 123);

        $this->assertFalse($notification->eql(new  Notification()));
        $this->assertEquals(1, $notification->getID());
        $this->assertEquals('note', $notification->getBody());
        $this->assertEquals(123, $notification->getTimestamp());
    }

    public function testCreate() {
        // Create a test notification
        $testNotification = new Notification();
        $testNotification->setBody('test');
        $testNotification->setTimestamp(123);
        $testNotification->create();

        // Check id is now an int
        $this->assertInternalType('int', $testNotification->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `NotificationID`,`NotificationBody`,`NotificationTimestamp` FROM `Notifications` WHERE `NotificationID`=?");
        $stmt->bind_param('i', $testNotification->getID());
        $stmt->execute();
        $stmt->bind_result($notificationID, $body, $timestamp);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        // Now compare
        $stmt->fetch();

        $this->assertEquals($testNotification->getID(), $notificationID);
        $this->assertEquals($testNotification->getBody(), $body);
        $this->assertEquals($testNotification->getTimestamp(), $timestamp);

        $stmt->close();

        // Clean up
        $testNotification->delete();
    }

    public function testBlankCreate() {
        // Create a notification
        $notification = new Notification();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $notification->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store blank Notification.', $e->getMessage());
        }
    }

    public function testUpdate() {
        // Create a test notification
        $testNotification = new Notification();
        $testNotification->setBody('test');
        $testNotification->setTimestamp(123);
        $testNotification->create();

        // Now update it
        $testNotification->setBody('test2');
        $testNotification->setTimestamp(123456);
        $testNotification->create();

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `NotificationID`,`NotificationBody`,`NotificationTimestamp` FROM `Notifications` WHERE `NotificationID`=?");
        $stmt->bind_param('i', $testNotification->getID());
        $stmt->execute();
        $stmt->bind_result($notificationID, $body, $timestamp);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        // Now compare
        $stmt->fetch();

        $this->assertEquals($testNotification->getID(), $notificationID);
        $this->assertEquals($testNotification->getBody(), $body);
        $this->assertEquals($testNotification->getTimestamp(), $timestamp);

        $stmt->close();

        // Clean up
        $testNotification->delete();
    }

    public function testBlankUpdate() {
        // Create a notification
        $notification = new Notification();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $notification->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store blank Notification.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test notification
        $testNotification = new Notification();
        $testNotification->setBody('test');
        $testNotification->setTimestamp(123);
        $testNotification->create();

        // Store the id
        $id = $testNotification->getID();

        // Now delete it
        $testNotification->delete();

        // Check id is null now
        $this->assertNull($testNotification->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `NotificationID`,`NotificationBody`,`NotificationTimestamp` FROM `Notifications` WHERE `NotificationID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testNotification->delete();
    }

    public function testSelectWithInput() {
        // Create a test notification
        $testNotification = [];
        $testNotification[0] = new Notification();
        $testNotification[0]->setBody('test');
        $testNotification[0]->setTimestamp(123);
        $testNotification[0]->create();

        $testNotification[1] = new Notification();
        $testNotification[1]->setBody('test2');
        $testNotification[1]->setTimestamp(12345);
        $testNotification[1]->create();

        $testNotification[2] = new Notification();
        $testNotification[2]->setBody('test3');
        $testNotification[2]->setTimestamp(12345678);
        $testNotification[2]->create();

        // Now pull and check a single
        $selectedSingle = Notification::select(array($testNotification[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(Notification::class, $selectedSingle[0]);

        $this->assertEquals($testNotification[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testNotification[0]->getBody(), $selectedSingle[0]->getBody());
        $this->assertEquals($testNotification[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Now pull and check multiple
        $selectedMultiple = Notification::select(array($testNotification[1]->getID(), $testNotification[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(1, \count($selectedMultiple));
        $this->assertInstanceOf(Notification::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Notification::class, $selectedMultiple[1]);

        if($testNotification[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testNotification[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testNotification[1]->getBody(), $selectedMultiple[$i]->getBody());
        $this->assertEquals($testNotification[1]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());

        $this->assertEquals($testNotification[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testNotification[2]->getBody(), $selectedMultiple[$j]->getBody());
        $this->assertEquals($testNotification[2]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());

        // Clean up
        foreach ($testNotification as $notification) {
            $notification->delete();
        }
    }

    public function testSelectAll() {
        // Create a test notification
        $testNotification = [];
        $testNotification[0] = new Notification();
        $testNotification[0]->setBody('test');
        $testNotification[0]->setTimestamp(123);
        $testNotification[0]->create();

        $testNotification[1] = new Notification();
        $testNotification[1]->setBody('test2');
        $testNotification[1]->setTimestamp(12345);
        $testNotification[1]->create();

        // Now pull and check multiple
        $selectedMultiple = Notification::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(1, \count($selectedMultiple));
        $this->assertInstanceOf(Notification::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Notification::class, $selectedMultiple[1]);

        if($testNotification[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testNotification[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testNotification[0]->getBody(), $selectedMultiple[$i]->getBody());
        $this->assertEquals($testNotification[0]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());

        $this->assertEquals($testNotification[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testNotification[1]->getBody(), $selectedMultiple[$j]->getBody());
        $this->assertEquals($testNotification[1]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());

        // Clean up
        foreach ($testNotification as $notification) {
            $notification->delete();
        }
    }

    public function testEql() {
        // Create a test notification
        $testNotification = [];
        $testNotification[0] = new Notification();
        $testNotification[0]->setBody('test');
        $testNotification[0]->setTimestamp(123);

        $testNotification[1] = new Notification();
        $testNotification[1]->setBody('test');
        $testNotification[1]->setTimestamp(123);

        $testNotification[2] = new Notification();
        $testNotification[2]->setBody('test2');
        $testNotification[2]->setTimestamp(12345);

        // Check same object is eql
        $this->assertTrue($testNotification[0]->eql($testNotification[0]));

        // Check same details are eql
        $this->assertTrue($testNotification[0]->eql($testNotification[1]));

        // Check different arent equal
        $this->assertFalse($testNotification[0]->eql($testNotification[2]));
    }

    public function testIssueToUser() {
        // Create a user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test notification
        $testNotification = [];
        $testNotification[0] = new Notification();
        $testNotification[0]->setBody('test');
        $testNotification[0]->setTimestamp(123);
        $testNotification[0]->create();

        // Note this one is not committed
        $testNotification[1] = new Notification();
        $testNotification[1]->setBody('test');
        $testNotification[1]->setTimestamp(123);

        // Now issue to the user
        $testNotification[0]->issueToUser($testUser->getID(), false);

        // Pull and check
        $userNotification = [];
        $userNotification[0] = UserNotification::getByNotificationID($testNotification[0]->getID());

        $this->assertTrue(\is_array($userNotification[0]));
        $this->assertEquals(1, \count($userNotification[0]));
        $this->assertInstanceOf(UserNotification::class, $userNotification[0][0]);
        $this->assertEquals($testNotification[0]->getID(), $userNotification[0][0]->getNotificationID());
        $this->assertEquals($testUser->getID(), $userNotification[0][0]->getUserID());

        // Now issue the other notification, but allow it to commit
        $testNotification[1]->issueToUser($testUser->getID(), true);

        $userNotification[1] = UserNotification::getByNotificationID($testNotification[1]->getID());

        // Now check the id for notification 1 is an int
        $this->assertInternalType('int', $testNotification[1]->getID());
        $this->assertTrue(\is_array($userNotification[1]));
        $this->assertEquals(1, \count($userNotification[1]));
        $this->assertInstanceOf(UserNotification::class, $userNotification[1][0]);
        $this->assertEquals($testNotification[1]->getID(), $userNotification[1][0]->getNotificationID());
        $this->assertEquals($testUser->getID(), $userNotification[1][0]->getUserID());

        // Clean up
        foreach($userNotification as $elem) {
            $elem->delete();
        }
        foreach($testNotification as $notification) {
            $notification->delete();
        }
        $testUser->delete();
    }

    public function testInvalidUserIssueToUser() {
        // Find the largest user id
        $stmt = $this->_connection->prepare("SELECT `UserID` FROM `Users` ORDER BY `UserID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($userID);
        $stmt->fetch();
        $largestID = $userID;
        $largestID++;

        // Create a test notification
        $testNotification = new Notification();
        $testNotification->setBody('test');
        $testNotification->setTimestamp(123);
        $testNotification->create();

        // Set expected exception
        $this->expectException(InvalidUserException::class);

        // Trigger it
        try {
            $testNotification->issueToUser($largestID);
        } catch(InvalidUserException $e) {
            $this->assertEquals('User with id ' . $largestID . ' does not exist.', $e->getMessage());
        } finally {
            $testNotification->delete();
        }
    }

    public function testIssueToGroup() {
        // Create test users
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('testGroup');
        $testGroup->create();

        // Create a test notification
        $testNotification = new Notification();
        $testNotification->setBody('test');
        $testNotification->setTimestamp(123);
        $testNotification->create();

        // Add users to group
        $testUser[0]->addToGroup($testGroup->getID());
        $testUser[1]->addToGroup($testGroup->getID());

        // Now issue the notification to the group
        $testNotification->issueToGroup($testGroup->getID());

        // Now pull and check
        $userNotifications = UserNotification::getByNotificationID($testNotification->getID());

        $this->assertTrue(\is_array($userNotifications));
        $this->assertEquals(2, \count($userNotifications));
        $this->assertInstanceOf(UserNotification::class, $userNotifications[0]);
        $this->assertInstanceOf(UserNotification::class, $userNotifications[1]);

        if($testUser[0]->getID() == $userNotifications[0]->getUserID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testUser[0]->getID(), $userNotifications[$i]->getUserID());
        $this->assertEquals($testNotification->getID(), $userNotifications[$i]->getNotificationID());

        $this->assertEquals($testNotification->getID(), $userNotifications[$j]->getNotificationID());
        $this->assertEquals($testUser[1]->getID(), $userNotifications[$j]->getUserID());

        // Clean up
        foreach($userNotifications as $userNotification) {
            $userNotification->delete();
        }
        $testNotification->delete();
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testInvalidUserIssueToGroup() {
        // Find the largest group id
        $stmt = $this->_connection->prepare("SELECT `GroupID` FROM `Group` ORDER BY `GroupID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($groupID);
        $stmt->fetch();
        $largestID = $groupID;
        $largestID++;

        // Create a test notification
        $testNotification = new Notification();
        $testNotification->setBody('test');
        $testNotification->setTimestamp(123);
        $testNotification->create();

        // Set the expected exception
        $this->expectException(InvalidGroupException::class);

        // Trigger it
        try {
            $testNotification->issueToGroup($largestID);
        } catch(InvalidGroupException $e) {
            $this->assertEquals('Group with id ' . $largestID . ' does not exist.', $e->getMessage());
        } finally {
            $testNotification->delete();
        }
    }

    public function testNotificationExists() {
        //TODO: Implement
    }

    public function testIncorrectTypeNotificationExists() {
        //TODO: Implement
    }

}
