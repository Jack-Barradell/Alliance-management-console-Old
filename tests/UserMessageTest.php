<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Message.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Message;
use AMC\Classes\User;
use AMC\Classes\UserMessage;
use AMC\Classes\Database;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class UserMessageTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check then null constructor
        $testUserMessage = new UserMessage();

        $this->assertNull($testUserMessage->getID());
        $this->assertNull($testUserMessage->getUserID());
        $this->assertNull($testUserMessage->getMessageID());
        $this->assertNull($testUserMessage->getAcknowledged());
        $this->assertNull($testUserMessage->getHideInInbox());

        // Check the non null constructor
        $testUserMessage = new UserMessage(1, 2, 3, true, false);

        $this->assertEquals(1, $testUserMessage->getID());
        $this->assertEquals(2, $testUserMessage->getUserID());
        $this->assertEquals(3, $testUserMessage->getMessageID());
        $this->assertTrue($testUserMessage->getAcknowledged());
        $this->assertTrue($testUserMessage->getHideInInbox());
    }

    public function testCreate() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test Message
        $testMessage = new Message();
        $testMessage->setSubject('testMessage');
        $testMessage->create();

        // Now create a test user message
        $testUserMessage = new UserMessage();
        $testUserMessage->setUserID($testUser->getID());
        $testUserMessage->setMessageID($testMessage->getID());
        $testUserMessage->setAcknowledged(true);
        $testUserMessage->setHideInInbox(false);
        $testUserMessage->create();

        // Check id is now an int
        $this->assertInternalType('int', $testUserMessage->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `UserMessageID`,`UserID`,`MessageID`,`UserMessageAcknowledged`,`UserMessageHideInInbox` FROM `User_Messages` WHERE `UserMessageID`=?");
        $stmt->bind_param('i', $testUserMessage->getID());
        $stmt->execute();
        $stmt->bind_result($userMessageID, $userID, $messageID, $acknowledged, $hideInInbox);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        if($acknowledged == 1) {
            $acknowledged = true;
        }
        else if($acknowledged == 0) {
            $acknowledged = false;
        }
        if($hideInInbox == 1) {
            $hideInInbox = true;
        }
        else if($hideInInbox == 0) {
            $hideInInbox = false;
        }

        $this->assertEquals($testUserMessage->getID(), $userMessageID);
        $this->assertEquals($testUserMessage->getUserID(), $userID);
        $this->assertEquals($testUserMessage->getMessageID(), $messageID);
        $this->assertEquals($testUserMessage->getAcknowledged(), $acknowledged);
        $this->assertEquals($testUserMessage->getHideInInbox(), $hideInInbox);

        $stmt->close();

        // Clean up
        $testUserMessage->delete();
        $testUser->delete();
        $testMessage->delete();
    }

    public function testBlankCreate() {
        // Create a test user message
        $testUserMessage = new UserMessage();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testUserMessage->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank User Object.', $e->getMessage());
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

        // Create a test Message
        $testMessage = [];
        $testMessage[0] = new Message();
        $testMessage[0]->setSubject('testMessage');
        $testMessage[0]->create();

        $testMessage[1] = new Message();
        $testMessage[1]->setSubject('testMessage2');
        $testMessage[1]->create();

        // Now create a test user message
        $testUserMessage = new UserMessage();
        $testUserMessage->setUserID($testUser[0]->getID());
        $testUserMessage->setMessageID($testMessage[0]->getID());
        $testUserMessage->setAcknowledged(true);
        $testUserMessage->setHideInInbox(false);
        $testUserMessage->create();

        // Update it
        $testUserMessage->setUserID($testUser[1]->getID());
        $testUserMessage->setMessageID($testMessage[1]->getID());
        $testUserMessage->setAcknowledged(false);
        $testUserMessage->setHideInInbox(true);
        $testUserMessage->update();

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `UserMessageID`,`UserID`,`MessageID`,`UserMessageAcknowledged`,`UserMessageHideInInbox` FROM `User_Messages` WHERE `UserMessageID`=?");
        $stmt->bind_param('i', $testUserMessage->getID());
        $stmt->execute();
        $stmt->bind_result($userMessageID, $userID, $messageID, $acknowledged, $hideInInbox);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        if($acknowledged == 1) {
            $acknowledged = true;
        }
        else if($acknowledged == 0) {
            $acknowledged = false;
        }
        if($hideInInbox == 1) {
            $hideInInbox = true;
        }
        else if($hideInInbox == 0) {
            $hideInInbox = false;
        }

        $this->assertEquals($testUserMessage->getID(), $userMessageID);
        $this->assertEquals($testUserMessage->getUserID(), $userID);
        $this->assertEquals($testUserMessage->getMessageID(), $messageID);
        $this->assertEquals($testUserMessage->getAcknowledged(), $acknowledged);
        $this->assertEquals($testUserMessage->getHideInInbox(), $hideInInbox);

        $stmt->close();

        // Clean up
        $testUserMessage->delete();
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testMessage as $message) {
            $message->delete();
        }
    }

    public function testBlankUpdate() {
        // Create a test user message
        $testUserMessage = new UserMessage();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testUserMessage->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank User Object.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test Message
        $testMessage = new Message();
        $testMessage->setSubject('testMessage');
        $testMessage->create();

        // Now create a test user message
        $testUserMessage = new UserMessage();
        $testUserMessage->setUserID($testUser->getID());
        $testUserMessage->setMessageID($testMessage->getID());
        $testUserMessage->setAcknowledged(true);
        $testUserMessage->setHideInInbox(false);
        $testUserMessage->create();

        // Save the id
        $id = $testUserMessage->getID();

        // Now delete it
        $testUserMessage->delete();

        // Check id is now null
        $this->assertNull($testUserMessage->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `UserMessageID`,`UserID`,`MessageID`,`UserMessageAcknowledged`,`UserMessageHideInInbox` FROM `User_Messages` WHERE `UserMessageID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testUser->delete();
        $testMessage->delete();
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

        // Create a test Message
        $testMessage = [];
        $testMessage[0] = new Message();
        $testMessage[0]->setSubject('testMessage');
        $testMessage[0]->create();

        $testMessage[1] = new Message();
        $testMessage[1]->setSubject('testMessage2');
        $testMessage[1]->create();

        // Now create a test user message
        $testUserMessage = [];
        $testUserMessage[0] = new UserMessage();
        $testUserMessage[0]->setUserID($testUser[0]->getID());
        $testUserMessage[0]->setMessageID($testMessage[0]->getID());
        $testUserMessage[0]->setAcknowledged(true);
        $testUserMessage[0]->setHideInInbox(false);
        $testUserMessage[0]->create();

        $testUserMessage[1] = new UserMessage();
        $testUserMessage[1]->setUserID($testUser[1]->getID());
        $testUserMessage[1]->setMessageID($testMessage[1]->getID());
        $testUserMessage[1]->setAcknowledged(false);
        $testUserMessage[1]->setHideInInbox(true);
        $testUserMessage[1]->create();

        $testUserMessage[2] = new UserMessage();
        $testUserMessage[2]->setUserID($testUser[0]->getID());
        $testUserMessage[2]->setMessageID($testMessage[1]->getID());
        $testUserMessage[2]->setAcknowledged(true);
        $testUserMessage[2]->setHideInInbox(true);
        $testUserMessage[2]->create();

        // Select and check a single
        $selectedSingle = UserMessage::select(array($testUserMessage[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserMessage::class, $selectedSingle[0]);
        $this->assertEquals($testUserMessage[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserMessage[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserMessage[0]->getMessageID(), $selectedSingle[0]->getMessageID());
        $this->assertEquals($testUserMessage[0]->getAcknowledged(), $selectedSingle[0]->getAcknowledged());
        $this->assertEquals($testUserMessage[0]->getHideInInbox(), $selectedSingle[0]->getHideInInbox());

        // Select and check multiple
        $selectedMultiple = UserMessage::select(array($testUserMessage[1]->getID(), $testUserMessage[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(UserMessage::class, $selectedMultiple[0]);
        $this->assertInstanceOf(UserMessage::class, $selectedMultiple[1]);

        if($testUserMessage[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testUserMessage[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testUserMessage[1]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testUserMessage[1]->getMessageID(), $selectedMultiple[$i]->getMessageID());
        $this->assertEquals($testUserMessage[1]->getAcknowledged(), $selectedMultiple[$i]->getAcknowledged());
        $this->assertEquals($testUserMessage[1]->getHideInInbox(), $selectedMultiple[$i]->getHideInInbox());

        $this->assertEquals($testUserMessage[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testUserMessage[2]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testUserMessage[2]->getMessageID(), $selectedMultiple[$j]->getMessageID());
        $this->assertEquals($testUserMessage[2]->getAcknowledged(), $selectedMultiple[$j]->getAcknowledged());
        $this->assertEquals($testUserMessage[2]->getHideInInbox(), $selectedMultiple[$j]->getHideInInbox());

        // Clean up
        foreach($testUserMessage as $userMessage) {
            $userMessage->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testMessage as $message) {
            $message->delete();
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

        // Create a test Message
        $testMessage = [];
        $testMessage[0] = new Message();
        $testMessage[0]->setSubject('testMessage');
        $testMessage[0]->create();

        $testMessage[1] = new Message();
        $testMessage[1]->setSubject('testMessage2');
        $testMessage[1]->create();

        // Now create a test user message
        $testUserMessage = [];
        $testUserMessage[0] = new UserMessage();
        $testUserMessage[0]->setUserID($testUser[0]->getID());
        $testUserMessage[0]->setMessageID($testMessage[0]->getID());
        $testUserMessage[0]->setAcknowledged(true);
        $testUserMessage[0]->setHideInInbox(false);
        $testUserMessage[0]->create();

        $testUserMessage[1] = new UserMessage();
        $testUserMessage[1]->setUserID($testUser[1]->getID());
        $testUserMessage[1]->setMessageID($testMessage[1]->getID());
        $testUserMessage[1]->setAcknowledged(false);
        $testUserMessage[1]->setHideInInbox(true);
        $testUserMessage[1]->create();

        // Select and check multiple
        $selectedMultiple = UserMessage::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(UserMessage::class, $selectedMultiple[0]);
        $this->assertInstanceOf(UserMessage::class, $selectedMultiple[1]);

        if($testUserMessage[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testUserMessage[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testUserMessage[0]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testUserMessage[0]->getMessageID(), $selectedMultiple[$i]->getMessageID());
        $this->assertEquals($testUserMessage[0]->getAcknowledged(), $selectedMultiple[$i]->getAcknowledged());
        $this->assertEquals($testUserMessage[0]->getHideInInbox(), $selectedMultiple[$i]->getHideInInbox());

        $this->assertEquals($testUserMessage[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testUserMessage[1]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testUserMessage[1]->getMessageID(), $selectedMultiple[$j]->getMessageID());
        $this->assertEquals($testUserMessage[1]->getAcknowledged(), $selectedMultiple[$j]->getAcknowledged());
        $this->assertEquals($testUserMessage[1]->getHideInInbox(), $selectedMultiple[$j]->getHideInInbox());

        // Clean up
        foreach($testUserMessage as $userMessage) {
            $userMessage->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testMessage as $message) {
            $message->delete();
        }
    }

    public function testEql() {
        // Create a test user message
        $testUserMessage = [];
        $testUserMessage[0] = new UserMessage();
        $testUserMessage[0]->setUserID(1);
        $testUserMessage[0]->setMessageID(2);
        $testUserMessage[0]->setAcknowledged(true);
        $testUserMessage[0]->setHideInInbox(false);

        $testUserMessage[1] = new UserMessage();
        $testUserMessage[1]->setUserID(1);
        $testUserMessage[1]->setMessageID(2);
        $testUserMessage[1]->setAcknowledged(true);
        $testUserMessage[1]->setHideInInbox(false);

        $testUserMessage[2] = new UserMessage();
        $testUserMessage[2]->setUserID(3);
        $testUserMessage[2]->setMessageID(4);
        $testUserMessage[2]->setAcknowledged(false);
        $testUserMessage[2]->setHideInInbox(true);

        // Check same object is eql
        $this->assertTrue($testUserMessage[0]->eql($testUserMessage[0]));

        // Check same details are eql
        $this->assertTrue($testUserMessage[0]->eql($testUserMessage[0]));

        // Check different arent equal
        $this->assertFalse($testUserMessage[0]->eql($testUserMessage[0]));
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

        // Create a test Message
        $testMessage = [];
        $testMessage[0] = new Message();
        $testMessage[0]->setSubject('testMessage');
        $testMessage[0]->create();

        $testMessage[1] = new Message();
        $testMessage[1]->setSubject('testMessage2');
        $testMessage[1]->create();

        // Now create a test user message
        $testUserMessage = [];
        $testUserMessage[0] = new UserMessage();
        $testUserMessage[0]->setUserID($testUser[0]->getID());
        $testUserMessage[0]->setMessageID($testMessage[0]->getID());
        $testUserMessage[0]->setAcknowledged(true);
        $testUserMessage[0]->setHideInInbox(false);
        $testUserMessage[0]->create();

        $testUserMessage[1] = new UserMessage();
        $testUserMessage[1]->setUserID($testUser[1]->getID());
        $testUserMessage[1]->setMessageID($testMessage[1]->getID());
        $testUserMessage[1]->setAcknowledged(false);
        $testUserMessage[1]->setHideInInbox(true);
        $testUserMessage[1]->create();

        // Select and check a single
        $selectedSingle = UserMessage::getByUserID($testUser[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserMessage::class, $selectedSingle[0]);
        $this->assertEquals($testUserMessage[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserMessage[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserMessage[0]->getMessageID(), $selectedSingle[0]->getMessageID());
        $this->assertEquals($testUserMessage[0]->getAcknowledged(), $selectedSingle[0]->getAcknowledged());
        $this->assertEquals($testUserMessage[0]->getHideInInbox(), $selectedSingle[0]->getHideInInbox());

        // Clean up
        foreach($testUserMessage as $userMessage) {
            $userMessage->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testMessage as $message) {
            $message->delete();
        }
    }

    public function testGetByMessageID() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test Message
        $testMessage = [];
        $testMessage[0] = new Message();
        $testMessage[0]->setSubject('testMessage');
        $testMessage[0]->create();

        $testMessage[1] = new Message();
        $testMessage[1]->setSubject('testMessage2');
        $testMessage[1]->create();

        // Now create a test user message
        $testUserMessage = [];
        $testUserMessage[0] = new UserMessage();
        $testUserMessage[0]->setUserID($testUser[0]->getID());
        $testUserMessage[0]->setMessageID($testMessage[0]->getID());
        $testUserMessage[0]->setAcknowledged(true);
        $testUserMessage[0]->setHideInInbox(false);
        $testUserMessage[0]->create();

        $testUserMessage[1] = new UserMessage();
        $testUserMessage[1]->setUserID($testUser[1]->getID());
        $testUserMessage[1]->setMessageID($testMessage[1]->getID());
        $testUserMessage[1]->setAcknowledged(false);
        $testUserMessage[1]->setHideInInbox(true);
        $testUserMessage[1]->create();

        // Select and check a single
        $selectedSingle = UserMessage::getByMessageID($testMessage[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(UserMessage::class, $selectedSingle[0]);
        $this->assertEquals($testUserMessage[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testUserMessage[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testUserMessage[0]->getMessageID(), $selectedSingle[0]->getMessageID());
        $this->assertEquals($testUserMessage[0]->getAcknowledged(), $selectedSingle[0]->getAcknowledged());
        $this->assertEquals($testUserMessage[0]->getHideInInbox(), $selectedSingle[0]->getHideInInbox());

        // Clean up
        foreach($testUserMessage as $userMessage) {
            $userMessage->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testMessage as $message) {
            $message->delete();
        }
    }

    public function testSetUserID() {
        //TODO: Implement
    }

    public function testInvalidUserSetUserID() {
        //TODO: Implement
    }

    public function testSetMessageID() {
        //TODO: Implement
    }

    public function testInvalidMessageSetMessageID() {
        //TODO: Implement
    }

}
