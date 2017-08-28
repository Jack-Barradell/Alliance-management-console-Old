<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/News.php';
require '../classes/NewsComment.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Database;
use AMC\Classes\News;
use AMC\Classes\NewsComment;
use AMC\Classes\User;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class NewsCommentTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('jirbj.co.uk', 'testingDB', 'testingDB', 'testingdb');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check null constructor
        $testNewsComment = new NewsComment();

        $this->assertNull($testNewsComment->getID());
        $this->assertNull($testNewsComment->getNewsID());
        $this->assertNull($testNewsComment->getUserID());
        $this->assertNull($testNewsComment->getBody());
        $this->assertNull($testNewsComment->getTimestamp());

        // Check null constructor
        $testNewsComment = new NewsComment(1, 2, 3, 'body', 123);

        $this->assertEquals(1, $testNewsComment->getID());
        $this->assertEquals(2, $testNewsComment->getNewsID());
        $this->assertEquals(3, $testNewsComment->getUserID());
        $this->assertEquals('body', $testNewsComment->getBody());
        $this->assertEquals(123, $testNewsComment->getTimestamp());
    }

    public function testCreate() {
        // Create test news
        $testNews = new News();
        $testNews->setTeaser('testTeaser');
        $testNews->create();

        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testName');
        $testUser->create();

        // Create a test news comment
        $testNewsComment = new NewsComment();
        $testNewsComment->setNewsID($testNews->getID());
        $testNewsComment->setUserID($testUser->getID());
        $testNewsComment->setBody('testBody');
        $testNewsComment->setTimestamp(123);
        $testNewsComment->create();

        // Check id is now and int
        $this->assertInternalType('int', $testNewsComment->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `NewsCommentID`,`NewsID`,`UserID`,`NewsCommentBody`,`NewsCommentTimestamp` FROM `News_Comments` WHERE `NewsCommentID`=?");
        $stmt->bind_param('i', $testNewsComment->getID());
        $stmt->execute();
        $stmt->bind_result($newsCommentID, $newsID, $userID, $body, $timestamp);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testNewsComment->getID(), $newsCommentID);
        $this->assertEquals($testNewsComment->getNewsID(), $newsID);
        $this->assertEquals($testNewsComment->getUserID(), $userID);
        $this->assertEquals($testNewsComment->getBody(), $body);
        $this->assertEquals($testNewsComment->getTimestamp(), $timestamp);

        $stmt->close();

        // Clean up
        $testNewsComment->delete();
        $testUser->delete();
        $testNews->delete();
    }

    public function testBlankCreate() {
        // Create a test news comment
        $testNewsComment = new NewsComment();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testNewsComment->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank News Comment.', $e->getMessage());
        }
    }

    public function testUpdate() {
        // Create test news
        $testNews = [];
        $testNews[0] = new News();
        $testNews[0]->setTeaser('testTeaser');
        $testNews[0]->create();

        $testNews[1] = new News();
        $testNews[1]->setTeaser('testTeaser2');
        $testNews[1]->create();

        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testName');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testName');
        $testUser[1]->create();

        // Create a test news comment
        $testNewsComment = new NewsComment();
        $testNewsComment->setNewsID($testNews[0]->getID());
        $testNewsComment->setUserID($testUser[0]->getID());
        $testNewsComment->setBody('testBody');
        $testNewsComment->setTimestamp(123);
        $testNewsComment->create();

        // Now update it
        $testNewsComment->setNewsID($testNews[1]->getID());
        $testNewsComment->setUserID($testUser[1]->getID());
        $testNewsComment->setBody('testBody');
        $testNewsComment->setTimestamp(123);
        $testNewsComment->update();

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `NewsCommentID`,`NewsID`,`UserID`,`NewsCommentBody`,`NewsCommentTimestamp` FROM `News_Comments` WHERE `NewsCommentID`=?");
        $stmt->bind_param('i', $testNewsComment->getID());
        $stmt->execute();
        $stmt->bind_result($newsCommentID, $newsID, $userID, $body, $timestamp);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testNewsComment->getID(), $newsCommentID);
        $this->assertEquals($testNewsComment->getNewsID(), $newsID);
        $this->assertEquals($testNewsComment->getUserID(), $userID);
        $this->assertEquals($testNewsComment->getBody(), $body);
        $this->assertEquals($testNewsComment->getTimestamp(), $timestamp);

        $stmt->close();

        // Clean up
        $testNewsComment->delete();
        foreach($testUser as $user) {
            $user->delete();
        }
        foreach($testNews as $news) {
            $news->delete();
        }
    }

    public function testBlankUpdate() {
        // Create a test news comment
        $testNewsComment = new NewsComment();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testNewsComment->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank News Comment.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create test news
        $testNews = new News();
        $testNews->setTeaser('testTeaser');
        $testNews->create();

        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testName');
        $testUser->create();

        // Create a test news comment
        $testNewsComment = new NewsComment();
        $testNewsComment->setNewsID($testNews->getID());
        $testNewsComment->setUserID($testUser->getID());
        $testNewsComment->setBody('testBody');
        $testNewsComment->setTimestamp(123);
        $testNewsComment->create();

        // Save the id
        $id = $testNewsComment->getID();

        // Now delete it
        $testNewsComment->delete();

        // Check id is null
        $this->assertNull($testNewsComment->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `NewsCommentID`,`NewsID`,`UserID`,`NewsCommentBody`,`NewsCommentTimestamp` FROM `News_Comments` WHERE `NewsCommentID`=?");
        $stmt->bind_param('i', $testNewsComment->getID());
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testUser->delete();
        $testNews->delete();
    }

    public function testSelectWithInput() {
        // Create test news
        $testNews = [];
        $testNews[0] = new News();
        $testNews[0]->setTeaser('testTeaser');
        $testNews[0]->create();

        $testNews[1] = new News();
        $testNews[1]->setTeaser('testTeaser2');
        $testNews[1]->create();

        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testName');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testName');
        $testUser[1]->create();

        // Create a test news comment
        $testNewsComment = [];
        $testNewsComment[0] = new NewsComment();
        $testNewsComment[0]->setNewsID($testNews[0]->getID());
        $testNewsComment[0]->setUserID($testUser[0]->getID());
        $testNewsComment[0]->setBody('testBody');
        $testNewsComment[0]->setTimestamp(123);
        $testNewsComment[0]->create();

        $testNewsComment[1] = new NewsComment();
        $testNewsComment[1]->setNewsID($testNews[1]->getID());
        $testNewsComment[1]->setUserID($testUser[1]->getID());
        $testNewsComment[1]->setBody('testBody');
        $testNewsComment[1]->setTimestamp(123);
        $testNewsComment[1]->create();

        $testNewsComment[2] = new NewsComment();
        $testNewsComment[2]->setNewsID($testNews[0]->getID());
        $testNewsComment[2]->setUserID($testUser[1]->getID());
        $testNewsComment[2]->setBody('testBody');
        $testNewsComment[2]->setTimestamp(123);
        $testNewsComment[2]->create();

        // Select and check a single
        $selectedSingle = NewsComment::select(array($testNewsComment[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(NewsComment::class, $selectedSingle[0]);
        $this->assertEquals($testNewsComment[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testNewsComment[0]->getNewsID(), $selectedSingle[0]->getNewsID());
        $this->assertEquals($testNewsComment[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testNewsComment[0]->getBody(), $selectedSingle[0]->getBody());
        $this->assertEquals($testNewsComment[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Select and check multiple
        $selectedMultiple = NewsComment::select(array($testNewsComment[1]->getID(), $testNewsComment[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(NewsComment::class, $selectedMultiple[0]);
        $this->assertInstanceOf(NewsComment::class, $selectedMultiple[1]);

        if($testNewsComment[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testNewsComment[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testNewsComment[1]->getNewsID(), $selectedMultiple[$i]->getNewsID());
        $this->assertEquals($testNewsComment[1]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testNewsComment[1]->getBody(), $selectedMultiple[$i]->getBody());
        $this->assertEquals($testNewsComment[1]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());

        $this->assertEquals($testNewsComment[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testNewsComment[2]->getNewsID(), $selectedMultiple[$j]->getNewsID());
        $this->assertEquals($testNewsComment[2]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testNewsComment[2]->getBody(), $selectedMultiple[$j]->getBody());
        $this->assertEquals($testNewsComment[2]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());

        // Clean up
        foreach($testNewsComment as $newsComment) {
            $newsComment->delete();
        }
        foreach($testNews as $news) {
            $news->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testSelectAll() {
        // Create test news
        $testNews = [];
        $testNews[0] = new News();
        $testNews[0]->setTeaser('testTeaser');
        $testNews[0]->create();

        $testNews[1] = new News();
        $testNews[1]->setTeaser('testTeaser2');
        $testNews[1]->create();

        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testName');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testName');
        $testUser[1]->create();

        // Create a test news comment
        $testNewsComment = [];
        $testNewsComment[0] = new NewsComment();
        $testNewsComment[0]->setNewsID($testNews[0]->getID());
        $testNewsComment[0]->setUserID($testUser[0]->getID());
        $testNewsComment[0]->setBody('testBody');
        $testNewsComment[0]->setTimestamp(123);
        $testNewsComment[0]->create();

        $testNewsComment[1] = new NewsComment();
        $testNewsComment[1]->setNewsID($testNews[1]->getID());
        $testNewsComment[1]->setUserID($testUser[1]->getID());
        $testNewsComment[1]->setBody('testBody');
        $testNewsComment[1]->setTimestamp(123);
        $testNewsComment[1]->create();

        // Select and check multiple
        $selectedMultiple = NewsComment::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(NewsComment::class, $selectedMultiple[0]);
        $this->assertInstanceOf(NewsComment::class, $selectedMultiple[1]);

        if($testNewsComment[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testNewsComment[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testNewsComment[0]->getNewsID(), $selectedMultiple[$i]->getNewsID());
        $this->assertEquals($testNewsComment[0]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testNewsComment[0]->getBody(), $selectedMultiple[$i]->getBody());
        $this->assertEquals($testNewsComment[0]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());

        $this->assertEquals($testNewsComment[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testNewsComment[1]->getNewsID(), $selectedMultiple[$j]->getNewsID());
        $this->assertEquals($testNewsComment[1]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testNewsComment[1]->getBody(), $selectedMultiple[$j]->getBody());
        $this->assertEquals($testNewsComment[1]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());

        // Clean up
        foreach($testNewsComment as $newsComment) {
            $newsComment->delete();
        }
        foreach($testNews as $news) {
            $news->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testEql() {
        // Create a test news comment
        $testNewsComment = [];
        $testNewsComment[0] = new NewsComment();
        $testNewsComment[0]->setNewsID(1);
        $testNewsComment[0]->setUserID(2);
        $testNewsComment[0]->setBody('testBody');
        $testNewsComment[0]->setTimestamp(123);

        $testNewsComment[1] = new NewsComment();
        $testNewsComment[1]->setNewsID(1);
        $testNewsComment[1]->setUserID(2);
        $testNewsComment[1]->setBody('testBody');
        $testNewsComment[1]->setTimestamp(123);

        $testNewsComment[2] = new NewsComment();
        $testNewsComment[2]->setNewsID(1);
        $testNewsComment[2]->setUserID(2);
        $testNewsComment[2]->setBody('testBody');
        $testNewsComment[2]->setTimestamp(123);

        // Check same object is eql
        $this->assertTrue($testNewsComment[0]->eql($testNewsComment[0]));

        // Check same details are eql
        $this->assertTrue($testNewsComment[0]->eql($testNewsComment[0]));

        // Check different arent equal
        $this->assertFalse($testNewsComment[0]->eql($testNewsComment[0]));
    }

    public function testGetByNewsID() {
        // Create test news
        $testNews = [];
        $testNews[0] = new News();
        $testNews[0]->setTeaser('testTeaser');
        $testNews[0]->create();

        $testNews[1] = new News();
        $testNews[1]->setTeaser('testTeaser2');
        $testNews[1]->create();

        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testName');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testName');
        $testUser[1]->create();

        // Create a test news comment
        $testNewsComment = [];
        $testNewsComment[0] = new NewsComment();
        $testNewsComment[0]->setNewsID($testNews[0]->getID());
        $testNewsComment[0]->setUserID($testUser[0]->getID());
        $testNewsComment[0]->setBody('testBody');
        $testNewsComment[0]->setTimestamp(123);
        $testNewsComment[0]->create();

        $testNewsComment[1] = new NewsComment();
        $testNewsComment[1]->setNewsID($testNews[1]->getID());
        $testNewsComment[1]->setUserID($testUser[1]->getID());
        $testNewsComment[1]->setBody('testBody');
        $testNewsComment[1]->setTimestamp(123);
        $testNewsComment[1]->create();

        // Select and check a single
        $selectedSingle = NewsComment::getByNewsID($testNews[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(NewsComment::class, $selectedSingle[0]);
        $this->assertEquals($testNewsComment[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testNewsComment[0]->getNewsID(), $selectedSingle[0]->getNewsID());
        $this->assertEquals($testNewsComment[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testNewsComment[0]->getBody(), $selectedSingle[0]->getBody());
        $this->assertEquals($testNewsComment[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Clean up
        foreach($testNewsComment as $newsComment) {
            $newsComment->delete();
        }
        foreach($testNews as $news) {
            $news->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testGetByUserID() {
        // Create test news
        $testNews = [];
        $testNews[0] = new News();
        $testNews[0]->setTeaser('testTeaser');
        $testNews[0]->create();

        $testNews[1] = new News();
        $testNews[1]->setTeaser('testTeaser2');
        $testNews[1]->create();

        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testName');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testName');
        $testUser[1]->create();

        // Create a test news comment
        $testNewsComment = [];
        $testNewsComment[0] = new NewsComment();
        $testNewsComment[0]->setNewsID($testNews[0]->getID());
        $testNewsComment[0]->setUserID($testUser[0]->getID());
        $testNewsComment[0]->setBody('testBody');
        $testNewsComment[0]->setTimestamp(123);
        $testNewsComment[0]->create();

        $testNewsComment[1] = new NewsComment();
        $testNewsComment[1]->setNewsID($testNews[1]->getID());
        $testNewsComment[1]->setUserID($testUser[1]->getID());
        $testNewsComment[1]->setBody('testBody');
        $testNewsComment[1]->setTimestamp(123);
        $testNewsComment[1]->create();

        // Select and check a single
        $selectedSingle = NewsComment::getByUserID($testUser[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(NewsComment::class, $selectedSingle[0]);
        $this->assertEquals($testNewsComment[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testNewsComment[0]->getNewsID(), $selectedSingle[0]->getNewsID());
        $this->assertEquals($testNewsComment[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testNewsComment[0]->getBody(), $selectedSingle[0]->getBody());
        $this->assertEquals($testNewsComment[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Clean up
        foreach($testNewsComment as $newsComment) {
            $newsComment->delete();
        }
        foreach($testNews as $news) {
            $news->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

}
