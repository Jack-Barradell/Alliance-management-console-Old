<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Notification.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Notification;
use AMC\Classes\Database;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class NotificationTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('jirbj.co.uk', 'testingDB', 'testingDB', 'testingdb');
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
        $notification->create();
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
        $notification->update();
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
        $stmt->bind_param('i', $testNotification->getID());
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

}
