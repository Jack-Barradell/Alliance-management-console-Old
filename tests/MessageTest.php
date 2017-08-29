<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Message.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Database;
use AMC\Classes\Group;
use AMC\Classes\Message;
use AMC\Classes\User;
use AMC\Classes\UserMessage;
use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\InvalidGroupException;
use AMC\Exceptions\InvalidUserException;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check the null constructor
        $message = new Message();

        $this->assertTrue($message->eql(new Message()));
        $this->assertNull($message->getID());
        $this->assertNull($message->getSenderID());
        $this->assertNull($message->getSubject());
        $this->assertNull($message->getBody());
        $this->assertNull($message->getTimestamp());
        $this->assertNull($message->getHideInSentBox());

        // Check non null constructor
        $message = new Message(1, 1, 'subject', 'body', 123, false);

        $this->assertFalse($message->eql(new Message()));
        $this->assertEquals(1, $message->getID());
        $this->assertEquals(1, $message->getSenderID());
        $this->assertEquals('subject', $message->getSubject());
        $this->assertEquals('body', $message->getBody());
        $this->assertEquals(123, $message->getTimestamp());
        $this->assertFalse($message->getHideInSentBox());
    }

    public function testCreate() {
        // Create a test sender
        $testSender = new User();
        $testSender->setUsername('testUser');
        $testSender->create();

        // Create a test message
        $testMessage = new Message();
        $testMessage->setSenderID($testSender->getID());
        $testMessage->setSubject('testSub');
        $testMessage->setBody('test');
        $testMessage->setTimestamp(123);
        $testMessage->setHideInSentBox(false);
        $testMessage->create();

        // Check id is now an int
        $this->assertInternalType('int', $testMessage->getID());

        // Now pull it
        $stmt = $this->_connection->prepare("SELECT `MessageID`,`SenderID`,`MessageSubject`,`MessageBody`,`MessageTimestamp`,`MessageHideInSentBox` FROM `Messages` WHERE `MessageID`=?");
        $stmt->bind_param('i', $testMessage->getID());
        $stmt->execute();
        $stmt->bind_result($messageID, $senderID, $subject, $body, $timestamp, $hideInSentBox);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        if($hideInSentBox == 1) {
            $hideInSentBox = true;
        }
        else if($hideInSentBox == 0){
            $hideInSentBox = false;
        }

        // Check the results
        $this->assertEquals($testMessage->getID(), $messageID);
        $this->assertEquals($testMessage->getSenderID(), $senderID);
        $this->assertEquals($testMessage->getSubject(), $subject);
        $this->assertEquals($testMessage->getBody(), $body);
        $this->assertEquals($testMessage->getTimestamp(), $timestamp);
        $this->assertEquals($testMessage->getHideInSentBox(), $hideInSentBox);

        $stmt->close();

        // Clean up
        $testMessage->delete();
        $testSender->delete();
    }

    public function testBlankCreate() {
        // Create a blank message
        $message = new Message();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $message->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Message.', $e->getMessage());
        }
    }

    public function testUpdate() {
        // Create a test sender
        $testSender = [];
        $testSender[0] = new User();
        $testSender[0]->setUsername('testUser');
        $testSender[0]->create();

        $testSender[1] = new User();
        $testSender[1]->setUsername('testUser2');
        $testSender[1]->create();

        // Create a test message
        $testMessage = new Message();
        $testMessage->setSenderID($testSender[0]->getID());
        $testMessage->setSubject('testSub');
        $testMessage->setBody('test');
        $testMessage->setTimestamp(123);
        $testMessage->setHideInSentBox(false);
        $testMessage->create();

        // Now update it
        $testMessage->setSenderID($testSender[1]->getID());
        $testMessage->setSubject('testSub2');
        $testMessage->setBody('test2');
        $testMessage->setTimestamp(12345);
        $testMessage->setHideInSentBox(true);
        $testMessage->update();

        // Now pull it
        $stmt = $this->_connection->prepare("SELECT `MessageID`,`SenderID`,`MessageSubject`,`MessageBody`,`MessageTimestamp`,`MessageHideInSentBox` FROM `Messages` WHERE `MessageID`=?");
        $stmt->bind_param('i', $testMessage->getID());
        $stmt->execute();
        $stmt->bind_result($messageID, $senderID, $subject, $body, $timestamp, $hideInSentBox);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        if($hideInSentBox == 1) {
            $hideInSentBox = true;
        }
        else if($hideInSentBox == 0) {
            $hideInSentBox = false;
        }

        // Check the results
        $this->assertEquals($testMessage->getID(), $messageID);
        $this->assertEquals($testMessage->getSenderID(), $senderID);
        $this->assertEquals($testMessage->getSubject(), $subject);
        $this->assertEquals($testMessage->getBody(), $body);
        $this->assertEquals($testMessage->getTimestamp(), $timestamp);
        $this->assertEquals($testMessage->getHideInSentBox(), $hideInSentBox);

        $stmt->close();

        // Clean up
        $testMessage->delete();
        foreach($testSender as $sender) {
            $sender->delete();
        }
    }

    public function testBlankUpdate() {
        // Create a blank message
        $message = new Message();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $message->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Message.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test sender
        $testSender = new User();
        $testSender->setUsername('testUser');
        $testSender->create();

        // Create a test message
        $testMessage = new Message();
        $testMessage->setSenderID($testSender->getID());
        $testMessage->setSubject('testSub');
        $testMessage->setBody('test');
        $testMessage->setTimestamp(123);
        $testMessage->setHideInSentBox(false);
        $testMessage->create();

        // Store the id
        $id = $testMessage->getID();

        // Now delete it
        $testMessage->delete();

        // Now check id is null
        $this->assertNull($testMessage->getID());

        // Now check its gone
        // Now pull it
        $stmt = $this->_connection->prepare("SELECT `MessageID`,`SenderID`,`MessageSubject`,`MessageBody`,`MessageTimestamp`,`HideInSentBox` FROM `Messages` WHERE `MessageID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testMessage->delete();
        $testSender->delete();
    }

    public function testSelectWithInput() {
        // Create a test sender
        $testSender = [];
        $testSender[0] = new User();
        $testSender[0]->setUsername('testUser');
        $testSender[0]->create();

        $testSender[1] = new User();
        $testSender[1]->setUsername('testUser2');
        $testSender[1]->create();

        // Create a test message
        $testMessage = [];
        $testMessage[0] = new Message();
        $testMessage[0]->setSenderID($testSender[0]->getID());
        $testMessage[0]->setSubject('testSub');
        $testMessage[0]->setBody('test');
        $testMessage[0]->setTimestamp(123);
        $testMessage[0]->setHideInSentBox(false);
        $testMessage[0]->create();

        $testMessage[1] = new Message();
        $testMessage[1]->setSenderID($testSender[0]->getID());
        $testMessage[1]->setSubject('testSub2');
        $testMessage[1]->setBody('test2');
        $testMessage[1]->setTimestamp(12345);
        $testMessage[1]->setHideInSentBox(true);
        $testMessage[1]->create();

        $testMessage[2] = new Message();
        $testMessage[2]->setSenderID($testSender[1]->getID());
        $testMessage[2]->setSubject('testSub3');
        $testMessage[2]->setBody('test3');
        $testMessage[2]->setTimestamp(12345678);
        $testMessage[2]->setHideInSentBox(false);
        $testMessage[2]->create();

        // Check and pull a single
        $selectedSingle = Message::select(array($testMessage[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(Message::class, $selectedSingle[0]);
        $this->assertEquals($testMessage[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testMessage[0]->getSenderID(), $selectedSingle[0]->getSenderID());
        $this->assertEquals($testMessage[0]->getSubject(), $selectedSingle[0]->getSubject());
        $this->assertEquals($testMessage[0]->getBody(), $selectedSingle[0]->getBody());
        $this->assertEquals($testMessage[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());
        $this->assertEquals($testMessage[0]->getHideInSentBox(), $selectedSingle[0]->getHideInSentBox());

        // Check and pull multiple
        $selectedMultiple = Message::select(array($testMessage[1]->getID(), $testMessage[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(Message::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Message::class, $selectedMultiple[1]);

        if($testMessage[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testMessage[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testMessage[1]->getSenderID(), $selectedMultiple[$i]->getSenderID());
        $this->assertEquals($testMessage[1]->getSubject(), $selectedMultiple[$i]->getSubject());
        $this->assertEquals($testMessage[1]->getBody(), $selectedMultiple[$i]->getBody());
        $this->assertEquals($testMessage[1]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());
        $this->assertEquals($testMessage[1]->getHideInSentBox(), $selectedMultiple[$i]->getHideInSentBox());

        $this->assertEquals($testMessage[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testMessage[2]->getSenderID(), $selectedMultiple[$j]->getSenderID());
        $this->assertEquals($testMessage[2]->getSubject(), $selectedMultiple[$j]->getSubject());
        $this->assertEquals($testMessage[2]->getBody(), $selectedMultiple[$j]->getBody());
        $this->assertEquals($testMessage[2]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());
        $this->assertEquals($testMessage[2]->getHideInSentBox(), $selectedMultiple[$j]->getHideInSentBox());

        // Clean up
        foreach($testMessage as $message) {
            $message->delete();
        }
        foreach($testSender as $sender) {
            $sender->delete();
        }
    }

    public function testSelectAll() {
        // Create a test sender
        $testSender = [];
        $testSender[0] = new User();
        $testSender[0]->setUsername('testUser');
        $testSender[0]->create();

        $testSender[1] = new User();
        $testSender[1]->setUsername('testUser2');
        $testSender[1]->create();

        // Create a test message
        $testMessage = [];
        $testMessage[0] = new Message();
        $testMessage[0]->setSenderID($testSender[0]->getID());
        $testMessage[0]->setSubject('testSub');
        $testMessage[0]->setBody('test');
        $testMessage[0]->setTimestamp(123);
        $testMessage[0]->setHideInSentBox(false);
        $testMessage[0]->create();

        $testMessage[1] = new Message();
        $testMessage[1]->setSenderID($testSender[1]->getID());
        $testMessage[1]->setSubject('testSub2');
        $testMessage[1]->setBody('test2');
        $testMessage[1]->setTimestamp(12345);
        $testMessage[1]->setHideInSentBox(true);
        $testMessage[1]->create();

        // Check and pull multiple
        $selectedMultiple = Message::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(Message::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Message::class, $selectedMultiple[1]);

        if($testMessage[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testMessage[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testMessage[0]->getSenderID(), $selectedMultiple[$i]->getSenderID());
        $this->assertEquals($testMessage[0]->getSubject(), $selectedMultiple[$i]->getSubject());
        $this->assertEquals($testMessage[0]->getBody(), $selectedMultiple[$i]->getBody());
        $this->assertEquals($testMessage[0]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());
        $this->assertEquals($testMessage[0]->getHideInSentBox(), $selectedMultiple[$i]->getHideInSentBox());

        $this->assertEquals($testMessage[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testMessage[1]->getSenderID(), $selectedMultiple[$j]->getSenderID());
        $this->assertEquals($testMessage[1]->getSubject(), $selectedMultiple[$j]->getSubject());
        $this->assertEquals($testMessage[1]->getBody(), $selectedMultiple[$j]->getBody());
        $this->assertEquals($testMessage[1]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());
        $this->assertEquals($testMessage[1]->getHideInSentBox(), $selectedMultiple[$j]->getHideInSentBox());

        // Clean up
        foreach($testMessage as $message) {
            $message->delete();
        }
        foreach($testSender as $sender) {
            $sender->delete();
        }
    }

    public function testEql() {
        // Create a test message
        $testMessage = [];
        $testMessage[0] = new Message();
        $testMessage[0]->setID(1);
        $testMessage[0]->setSenderID(1);
        $testMessage[0]->setSubject('testSub');
        $testMessage[0]->setBody('test');
        $testMessage[0]->setTimestamp(123);
        $testMessage[0]->setHideInSentBox(false);

        $testMessage[1] = new Message();
        $testMessage[1]->setID(1);
        $testMessage[1]->setSenderID(1);
        $testMessage[1]->setSubject('testSub');
        $testMessage[1]->setBody('test');
        $testMessage[1]->setTimestamp(123);
        $testMessage[1]->setHideInSentBox(false);

        $testMessage[2] = new Message();
        $testMessage[2]->setID(2);
        $testMessage[2]->setSenderID(2);
        $testMessage[2]->setSubject('testSub2');
        $testMessage[2]->setBody('test2');
        $testMessage[2]->setTimestamp(12345);
        $testMessage[2]->setHideInSentBox(true);

        // Check same object is eql
        $this->assertTrue($testMessage[0]->eql($testMessage[0]));

        // Check same details are eql
        $this->assertTrue($testMessage[0]->eql($testMessage[1]));

        // Check different arent equal
        $this->assertFalse($testMessage[0]->eql($testMessage[2]));
    }

    public function testGetBySenderID() {
        // Create a test sender
        $testSender = [];
        $testSender[0] = new User();
        $testSender[0]->setUsername('testUser');
        $testSender[0]->create();

        $testSender[1] = new User();
        $testSender[1]->setUsername('testUser2');
        $testSender[1]->create();

        // Create a test message
        $testMessage = [];
        $testMessage[0] = new Message();
        $testMessage[0]->setSenderID($testSender[0]->getID());
        $testMessage[0]->setSubject('testSub');
        $testMessage[0]->setBody('test');
        $testMessage[0]->setTimestamp(123);
        $testMessage[0]->setHideInSentBox(false);
        $testMessage[0]->create();

        $testMessage[1] = new Message();
        $testMessage[1]->setSenderID($testSender[1]->getID());
        $testMessage[1]->setSubject('testSub2');
        $testMessage[1]->setBody('test2');
        $testMessage[1]->setTimestamp(12345);
        $testMessage[1]->setHideInSentBox(true);
        $testMessage[1]->create();

        // Select and check for a sender id
        $selected = Message::getBySenderID($testSender[0]->getID());

        $this->assertTrue(\is_array($selected));
        $this->assertEquals(1, \count($selected));
        $this->assertInstanceOf(Message::class, $selected[0]);

        $this->assertEquals($testMessage[0]->getID(), $selected[0]->getID());
        $this->assertEquals($testMessage[0]->getSenderID(), $selected[0]->getSenderID());
        $this->assertEquals($testMessage[0]->getSubject(), $selected[0]->getSubject());
        $this->assertEquals($testMessage[0]->getBody(), $selected[0]->getBody());
        $this->assertEquals($testMessage[0]->getTimestamp(), $selected[0]->getTimestamp());

        // Clean up
        foreach($testMessage as $message) {
            $message->delete();
        }
        foreach($testSender as $sender) {
            $sender->delete();
        }
    }

    public function testSendToUser() {
        // Create a test sender
        $testSender = new User();
        $testSender->setUsername('testSender');
        $testSender->create();

        // Create a test receiver
        $testReceiver = new User();
        $testReceiver->setUsername('testReceiver');
        $testReceiver->create();

        // Build a test message
        $testMessage = new Message();
        $testMessage->setSenderID($testSender->getID());
        $testMessage->setSubject('testTimestamp');
        $testMessage->setBody('testBody');
        $testMessage->setTimestamp(123);
        $testMessage->setHideInSentBox(false);
        $testMessage->sendToUser($testReceiver->getID());

        // Now get and pull the user message
        $userMessage = UserMessage::getByMessageID($testMessage->getID());

        $this->assertTrue(\is_array($userMessage));
        $this->assertEquals(1, \count($userMessage));
        $this->assertInstanceOf(UserMessage::class, $userMessage[0]);
        $this->assertEquals($testMessage->getID(), $userMessage[0]->getMessageID());
        $this->assertEquals($testReceiver->getID(), $userMessage[0]->getUserID());
        $this->assertFalse($userMessage[0]->getAcknowledged());

        // Clean up
        foreach($userMessage as $elem) {
            $elem->delete();
        }
        $testMessage->delete();
        $testReceiver->delete();
        $testSender->delete();
    }

    public function testInvalidUserSendToUser() {
        // Create a test sender
        $testSender = new User();
        $testSender->setUsername('testSender');
        $testSender->create();

        // Now find the largest user id
        $stmt = $this->_connection->prepare("SELECT `UserID` FROM `Users` ORDER BY `UserID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($userID);
        $stmt->fetch();
        $largestID = $userID;
        $largestID++;

        // Build the message
        $testMessage = new Message();
        $testMessage->setSenderID($testSender->getID());
        $testMessage->setSubject('testSubject');
        $testMessage->setBody('testBody');
        $testMessage->setTimestamp(123);
        $testMessage->setHideInSentBox(false);

        // Set the expected exception
        $this->expectException(InvalidUserException::class);

        // Trigger it
        try {
            $testMessage->sendToUser($largestID);
        } catch(InvalidUserException $e) {
            $this->assertEquals('There is no user with id ' . $largestID, $e->getMessage());
        } finally {
            $testSender->delete();
        }
    }

    public function testSendToGroup() {
        // Create a test sender
        $testSender = new User();
        $testSender->setUsername('testSender');
        $testSender->create();

        // Create test group members
        $testMembers = [];

        $testMembers[0] = new User();
        $testMembers[0]->setUsername('testMember');
        $testMembers[0]->create();

        $testMembers[1] = new User();
        $testMembers[1]->setUsername('testMember2');
        $testMembers[1]->create();

        // Create a test group
        $testGroup = new Group();
        $testGroup->setName('testGroup');
        $testGroup->setHidden(false);
        $testGroup->create();

        // Build the message
        $testMessage = new Message();
        $testMessage->setSenderID($testSender->getID());
        $testMessage->setSubject('testSubject');
        $testMessage->setBody('testBody');
        $testMessage->setTimestamp(123);
        $testMessage->setHideInSentBox(false);

        // Add users to group
        $testMembers[0]->addToGroup($testGroup->getID());
        $testMembers[1]->addToGroup($testGroup->getID());

        // Now check each user of the group has the message
        $userMessages = UserMessage::getByMessageID($testMessage->getID());

        $this->assertTrue(\is_array($userMessages));
        $this->assertEquals(2, \count($userMessages));
        $this->assertInstanceOf(UserMessage::class, $userMessages[0]);
        $this->assertInstanceOf(UserMessage::class, $userMessages[1]);
        $this->assertEquals($testMessage->getID(), $userMessages[0]->getMessageID());
        $this->assertEquals($testMessage->getID(), $userMessages[1]->getMessageID());

        if($testMembers[0]->getID() == $userMessages[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testMembers[0]->getID(), $userMessages[$i]->getUserID());
        $this->assertEquals($testMembers[1]->getID(), $userMessages[$j]->getUserID());

        foreach($userMessages as $userMessage) {
            $userMessage->delete();
        }
        $testMessage->delete();
        foreach($testMembers as $testMember) {
            $testMember->removeFromGroup($testGroup->getID());
            $testMember->delete();
        }
        $testSender->delete();
        $testGroup->delete();
    }

    public function testInvalidGroupSendToGroup() {
        // Create a test sender
        $testSender = new User();
        $testSender->setUsername('testSender');
        $testSender->create();

        // Now find the largest group id
        $stmt = $this->_connection->prepare("SELECT `GroupID` FROM `Groups` ORDER BY `GroupID` DESC LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($groupID);
        $stmt->fetch();
        $largestID = $groupID;
        $largestID++;

        // Build the message
        $testMessage = new Message();
        $testMessage->setSenderID($testSender->getID());
        $testMessage->setSubject('testSubject');
        $testMessage->setBody('testBody');
        $testMessage->setTimestamp(123);
        $testMessage->setHideInSentBox(false);

        // Set expected exception
        $this->expectException(InvalidGroupException::class);

        // Trigger it
        try {
            $testMessage->sendToGroup($largestID);
        } catch(InvalidGroupException $e) {
            $this->assertEquals('There is no group with id ' . $largestID, $e->getMessage());
        } finally {
            $testSender->delete();
        }
    }

    public function testDeleteFromSentBox() {
        // Create a test sender
        $testSender = new User();
        $testSender->setUsername('testSender');
        $testSender->create();

        // Build the message
        $testMessage = new Message();
        $testMessage->setSenderID($testSender->getID());
        $testMessage->setSubject('testSubject');
        $testMessage->setBody('testBody');
        $testMessage->setTimestamp(123);
        $testMessage->setHideInSentBox(false);
        $testMessage->commit();

        // Run the method
        $testMessage->deleteFromSentBox();

        // Check it
        $this->assertTrue($testMessage->getHideInSentBox());

        $stmt = $this->_connection->prepare("SELECT `HideInSentBox` FROM `Messages` WHERE `MessageID`=?");
        $stmt->bind_param('i', $testMessage->getID());
        $stmt->execute();
        $stmt->bind_result($hideInSentBox);
        $stmt->fetch();

        if($hideInSentBox == 1) {
            $hideInSentBox = true;
        }
        else {
            $hideInSentBox = false;
        }

        $this->assertTrue($hideInSentBox);
        $stmt->close();

        // Clean up
        $testMessage->delete();
        $testSender->delete();
    }

}
