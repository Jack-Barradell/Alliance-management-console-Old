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
use AMC\Classes\Database;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('jirbj.co.uk', 'testingDB', 'testingDB', 'testingdb');
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

        // Check non null constructor
        $message = new Message(1, 1, 'subject', 'body', 123);

        $this->assertFalse($message->eql(new Message()));
        $this->assertEquals(1, $message->getID());
        $this->assertEquals(1, $message->getSenderID());
        $this->assertEquals('subject', $message->getSubject());
        $this->assertEquals('body', $message->getBody());
        $this->assertEquals(123, $message->getTimestamp());
    }

    public function testCreate() {

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

    public function testGetBySenderID() {

    }

}
