<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/News.php';
require '../classes/NewsCategory.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Database;
use AMC\Classes\News;
use AMC\Classes\NewsCategory;
use AMC\Classes\User;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class NewsTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check null constructor
        $testNews = new News();

        $this->assertNull($testNews->getID());
        $this->assertNull($testNews->getAuthorID());
        $this->assertNull($testNews->getNewsCategoryID());
        $this->assertNull($testNews->getTitle());
        $this->assertNull($testNews->getThumbnail());
        $this->assertNull($testNews->getTeaser());
        $this->assertNull($testNews->getBody());
        $this->assertNull($testNews->getPublished());
        $this->assertNull($testNews->getMembersOnly());
        $this->assertNull($testNews->getCommentsAllowed());

        // Check non null constructor
        $testNews = new News(1,2,3, 'title', '/file/img.png', 'teaser', 'body', false, true, true);

        $this->assertEquals(1, $testNews->getID());
        $this->assertEquals(2, $testNews->getAuthorID());
        $this->assertEquals(3, $testNews->getNewsCategoryID());
        $this->assertEquals('title', $testNews->getTitle());
        $this->assertEquals('/file/img.png', $testNews->getThumbnail());
        $this->assertEquals('teaser', $testNews->getTeaser());
        $this->assertEquals('body', $testNews->getBody());
        $this->assertFalse($testNews->getPublished());
        $this->assertTrue($testNews->getMembersOnly());
        $this->assertTrue($testNews->getCommentsAllowed());
    }

    public function testCreate() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test news category
        $testNewsCategory = new NewsCategory();
        $testNewsCategory->setName('testCategory');
        $testNewsCategory->create();

        // Create test news
        $testNews = new News();

        $testNews->setAuthorID($testUser->getID());
        $testNews->setNewsCategoryID($testNewsCategory->getID());
        $testNews->setTitle('testTitle');
        $testNews->setThumbnail('/file/img.png');
        $testNews->setTeaser('testTeaser');
        $testNews->setBody('testBody');
        $testNews->setPublished(true);
        $testNews->setMembersOnly(false);
        $testNews->setCommentsAllowed(true);
        $testNews->create();

        // Check id is now an int
        $this->assertInternalType('int', $testNews->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `NewsID`,`AuthorID`,`NewsCategoryID`,`NewsTitle`,`NewsThumbnail`,`NewsTeaser`,`NewsBody`,`NewsPublished`,`NewsMembersOnly`,`NewsCommentsAllowed` FROM `News` WHERE `NewsID`=?");
        $stmt->bind_param('i', $testNews->getID());
        $stmt->execute();
        $stmt->bind_result($newsID, $authorID, $newsCategoryID, $title, $thumbnail, $teaser, $body, $published, $membersOnly, $commentsAllowed);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        if($published == 1) {
            $published = true;
        }
        else if($published == 0) {
            $published = false;
        }
        if($membersOnly == 1) {
            $membersOnly = true;
        }
        else if($membersOnly == 0) {
            $membersOnly = false;
        }
        if($commentsAllowed == 1) {
            $commentsAllowed = true;
        }
        else if($commentsAllowed == 0) {
            $commentsAllowed = false;
        }

        $this->assertEquals($testNews->getID(), $newsID);
        $this->assertEquals($testNews->getAuthorID(), $authorID);
        $this->assertEquals($testNews->getNewsCategoryID(), $newsCategoryID);
        $this->assertEquals($testNews->getTitle(), $title);
        $this->assertEquals($testNews->getThumbnail(), $thumbnail);
        $this->assertEquals($testNews->getTeaser(), $teaser);
        $this->assertEquals($testNews->getBody(), $body);
        $this->assertEquals($testNews->getPublished(), $published);
        $this->assertEquals($testNews->getMembersOnly(), $membersOnly);
        $this->assertEquals($testNews->getCommentsAllowed(), $commentsAllowed);

        $stmt->close();

        // Clean up
        $testNews->delete();
        $testNewsCategory->delete();
        $testUser->delete();
    }

    public function testBlankCreate() {
        // Create test news
        $testNews = new News();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testNews->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store blank News.', $e->getMessage());
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

        // Create a test news category
        $testNewsCategory = [];
        $testNewsCategory[0] = new NewsCategory();
        $testNewsCategory[0]->setName('testCategory');
        $testNewsCategory[0]->create();

        $testNewsCategory[1] = new NewsCategory();
        $testNewsCategory[1]->setName('testCategory2');
        $testNewsCategory[1]->create();

        // Create test news
        $testNews = new News();

        $testNews->setAuthorID($testUser[0]->getID());
        $testNews->setNewsCategoryID($testNewsCategory[0]->getID());
        $testNews->setTitle('testTitle');
        $testNews->setThumbnail('/file/img.png');
        $testNews->setTeaser('testTeaser');
        $testNews->setBody('testBody');
        $testNews->setPublished(true);
        $testNews->setMembersOnly(false);
        $testNews->setCommentsAllowed(true);
        $testNews->create();

        // Now update it
        $testNews->setAuthorID($testUser[1]->getID());
        $testNews->setNewsCategoryID($testNewsCategory[1]->getID());
        $testNews->setTitle('testTitle2');
        $testNews->setThumbnail('/file/img.png2');
        $testNews->setTeaser('testTeaser2');
        $testNews->setBody('testBody2');
        $testNews->setPublished(false);
        $testNews->setMembersOnly(true);
        $testNews->setCommentsAllowed(false);
        $testNews->update();

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `NewsID`,`AuthorID`,`NewsCategoryID`,`NewsTitle`,`NewsThumbnail`,`NewsTeaser`,`NewsBody`,`NewsPublished`,`NewsMembersOnly`,`NewsCommentsAllowed` FROM `News` WHERE `NewsID`=?");
        $stmt->bind_param('i', $testNews->getID());
        $stmt->execute();
        $stmt->bind_result($newsID, $authorID, $newsCategoryID, $title, $thumbnail, $teaser, $body, $published, $membersOnly, $commentsAllowed);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        if($published == 1) {
            $published = true;
        }
        else if($published == 0) {
            $published = false;
        }
        if($membersOnly == 1) {
            $membersOnly = true;
        }
        else if($membersOnly == 0) {
            $membersOnly = false;
        }
        if($commentsAllowed == 1) {
            $commentsAllowed = true;
        }
        else if($commentsAllowed == 0) {
            $commentsAllowed = false;
        }

        $this->assertEquals($testNews->getID(), $newsID);
        $this->assertEquals($testNews->getAuthorID(), $authorID);
        $this->assertEquals($testNews->getNewsCategoryID(), $newsCategoryID);
        $this->assertEquals($testNews->getTitle(), $title);
        $this->assertEquals($testNews->getThumbnail(), $thumbnail);
        $this->assertEquals($testNews->getTeaser(), $teaser);
        $this->assertEquals($testNews->getBody(), $body);
        $this->assertEquals($testNews->getPublished(), $published);
        $this->assertEquals($testNews->getMembersOnly(), $membersOnly);
        $this->assertEquals($testNews->getCommentsAllowed(), $commentsAllowed);

        $stmt->close();

        // Clean up
        $testNews->delete();
        $testNewsCategory->delete();
        $testUser->delete();
    }

    public function testBlankUpdate() {
        // Create test news
        $testNews = new News();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testNews->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store blank News.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create a test news category
        $testNewsCategory = new NewsCategory();
        $testNewsCategory->setName('testCategory');
        $testNewsCategory->create();

        // Create test news
        $testNews = new News();

        $testNews->setAuthorID($testUser->getID());
        $testNews->setNewsCategoryID($testNewsCategory->getID());
        $testNews->setTitle('testTitle');
        $testNews->setThumbnail('/file/img.png');
        $testNews->setTeaser('testTeaser');
        $testNews->setBody('testBody');
        $testNews->setPublished(true);
        $testNews->setMembersOnly(false);
        $testNews->setCommentsAllowed(true);
        $testNews->create();

        // Store the id
        $id = $testNews->getID();

        // Now delete it
        $testNews->delete();

        // Now check id is null
        $this->assertNull($testNews->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `NewsID`,`AuthorID`,`NewsCategoryID`,`NewsTitle`,`NewsThumbnail`,`NewsTeaser`,`NewsBody`,`NewsPublished`,`NewsMembersOnly`,`NewsCommentsAllowed` FROM `News` WHERE `NewsID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testNewsCategory->delete();
        $testUser->delete();
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

        // Create a test news category
        $testNewsCategory = [];
        $testNewsCategory[0] = new NewsCategory();
        $testNewsCategory[0]->setName('testCategory');
        $testNewsCategory[0]->create();

        $testNewsCategory[1] = new NewsCategory();
        $testNewsCategory[1]->setName('testCategory2');
        $testNewsCategory[1]->create();

        // Create test news
        $testNews = [];

        $testNews[0] = new News();
        $testNews[0]->setAuthorID($testUser[0]->getID());
        $testNews[0]->setNewsCategoryID($testNewsCategory[0]->getID());
        $testNews[0]->setTitle('testTitle');
        $testNews[0]->setThumbnail('/file/img.png');
        $testNews[0]->setTeaser('testTeaser');
        $testNews[0]->setBody('testBody');
        $testNews[0]->setPublished(true);
        $testNews[0]->setMembersOnly(false);
        $testNews[0]->setCommentsAllowed(true);
        $testNews[0]->create();

        $testNews[1] = new News();
        $testNews[1]->setAuthorID($testUser[1]->getID());
        $testNews[1]->setNewsCategoryID($testNewsCategory[1]->getID());
        $testNews[1]->setTitle('testTitle2');
        $testNews[1]->setThumbnail('/file/img2.png');
        $testNews[1]->setTeaser('testTeaser2');
        $testNews[1]->setBody('testBody2');
        $testNews[1]->setPublished(true);
        $testNews[1]->setMembersOnly(false);
        $testNews[1]->setCommentsAllowed(true);
        $testNews[1]->create();

        $testNews[2] = new News();
        $testNews[2]->setAuthorID($testUser[0]->getID());
        $testNews[2]->setNewsCategoryID($testNewsCategory[0]->getID());
        $testNews[2]->setTitle('testTitle');
        $testNews[2]->setThumbnail('/file/img.png');
        $testNews[2]->setTeaser('testTeaser');
        $testNews[2]->setBody('testBody');
        $testNews[2]->setPublished(true);
        $testNews[2]->setMembersOnly(false);
        $testNews[2]->setCommentsAllowed(false);
        $testNews[2]->create();

        // Select and check a single
        $selectedSingle = News::select(array($testNews[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(News::class, $selectedSingle[0]);
        $this->assertEquals($testNews[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testNews[0]->getAuthorID(), $selectedSingle[0]->getAuthorID());
        $this->assertEquals($testNews[0]->getNewsCategoryID(), $selectedSingle[0]->getNewsCategoryID());
        $this->assertEquals($testNews[0]->getTitle(), $selectedSingle[0]->getTitle());
        $this->assertEquals($testNews[0]->getThumbnail(), $selectedSingle[0]->getThumbnail());
        $this->assertEquals($testNews[0]->getTeaser(), $selectedSingle[0]->getTeaser());
        $this->assertEquals($testNews[0]->getBody(), $selectedSingle[0]->getBody());
        $this->assertEquals($testNews[0]->getPublished(), $selectedSingle[0]->getPublished());
        $this->assertEquals($testNews[0]->getMembersOnly(), $selectedSingle[0]->getMembersOnly());
        $this->assertEquals($testNews[0]->getCommentsAllowed(), $selectedSingle[0]->getCommentsAllowed());

        // Select and check multiple
        $selectedMultiple = News::select(array($testNews[1]->getID(), $testNews[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(News::class, $selectedMultiple[0]);
        $this->assertInstanceOf(News::class, $selectedMultiple[1]);

        if($testNews[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testNews[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testNews[1]->getAuthorID(), $selectedMultiple[$i]->getAuthorID());
        $this->assertEquals($testNews[1]->getNewsCategoryID(), $selectedMultiple[$i]->getNewsCategoryID());
        $this->assertEquals($testNews[1]->getTitle(), $selectedMultiple[$i]->getTitle());
        $this->assertEquals($testNews[1]->getThumbnail(), $selectedMultiple[$i]->getThumbnail());
        $this->assertEquals($testNews[1]->getTeaser(), $selectedMultiple[$i]->getTeaser());
        $this->assertEquals($testNews[1]->getBody(), $selectedMultiple[$i]->getBody());
        $this->assertEquals($testNews[1]->getPublished(), $selectedMultiple[$i]->getPublished());
        $this->assertEquals($testNews[1]->getMembersOnly(), $selectedMultiple[$i]->getMembersOnly());
        $this->assertEquals($testNews[1]->getCommentsAllowed(), $selectedMultiple[$i]->getCommentsAllowed());

        $this->assertEquals($testNews[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testNews[2]->getAuthorID(), $selectedMultiple[$j]->getAuthorID());
        $this->assertEquals($testNews[2]->getNewsCategoryID(), $selectedMultiple[$j]->getNewsCategoryID());
        $this->assertEquals($testNews[2]->getTitle(), $selectedMultiple[$j]->getTitle());
        $this->assertEquals($testNews[2]->getThumbnail(), $selectedMultiple[$j]->getThumbnail());
        $this->assertEquals($testNews[2]->getTeaser(), $selectedMultiple[$j]->getTeaser());
        $this->assertEquals($testNews[2]->getBody(), $selectedMultiple[$j]->getBody());
        $this->assertEquals($testNews[2]->getPublished(), $selectedMultiple[$j]->getPublished());
        $this->assertEquals($testNews[2]->getMembersOnly(), $selectedMultiple[$j]->getMembersOnly());
        $this->assertEquals($testNews[2]->getCommentsAllowed(), $selectedMultiple[$j]->getCommentsAllowed());

        // Clean up
        foreach($testNews as $news) {
            $news->delete();
        }
        foreach($testNewsCategory as $newsCategory) {
            $newsCategory->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
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

        // Create a test news category
        $testNewsCategory = [];
        $testNewsCategory[0] = new NewsCategory();
        $testNewsCategory[0]->setName('testCategory');
        $testNewsCategory[0]->create();

        $testNewsCategory[1] = new NewsCategory();
        $testNewsCategory[1]->setName('testCategory2');
        $testNewsCategory[1]->create();

        // Create test news
        $testNews = [];

        $testNews[0] = new News();
        $testNews[0]->setAuthorID($testUser[0]->getID());
        $testNews[0]->setNewsCategoryID($testNewsCategory[0]->getID());
        $testNews[0]->setTitle('testTitle');
        $testNews[0]->setThumbnail('/file/img.png');
        $testNews[0]->setTeaser('testTeaser');
        $testNews[0]->setBody('testBody');
        $testNews[0]->setPublished(true);
        $testNews[0]->setMembersOnly(false);
        $testNews[0]->setCommentsAllowed(true);
        $testNews[0]->create();

        $testNews[1] = new News();
        $testNews[1]->setAuthorID($testUser[1]->getID());
        $testNews[1]->setNewsCategoryID($testNewsCategory[1]->getID());
        $testNews[1]->setTitle('testTitle2');
        $testNews[1]->setThumbnail('/file/img2.png');
        $testNews[1]->setTeaser('testTeaser2');
        $testNews[1]->setBody('testBody2');
        $testNews[1]->setPublished(true);
        $testNews[1]->setMembersOnly(false);
        $testNews[1]->setCommentsAllowed(true);
        $testNews[1]->create();

        // Select and check multiple
        $selectedMultiple = News::select(array($testNews[1]->getID(), $testNews[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(News::class, $selectedMultiple[0]);
        $this->assertInstanceOf(News::class, $selectedMultiple[1]);

        if($testNews[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testNews[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testNews[0]->getAuthorID(), $selectedMultiple[$i]->getAuthorID());
        $this->assertEquals($testNews[0]->getNewsCategoryID(), $selectedMultiple[$i]->getNewsCategoryID());
        $this->assertEquals($testNews[0]->getTitle(), $selectedMultiple[$i]->getTitle());
        $this->assertEquals($testNews[0]->getThumbnail(), $selectedMultiple[$i]->getThumbnail());
        $this->assertEquals($testNews[0]->getTeaser(), $selectedMultiple[$i]->getTeaser());
        $this->assertEquals($testNews[0]->getBody(), $selectedMultiple[$i]->getBody());
        $this->assertEquals($testNews[0]->getPublished(), $selectedMultiple[$i]->getPublished());
        $this->assertEquals($testNews[0]->getMembersOnly(), $selectedMultiple[$i]->getMembersOnly());
        $this->assertEquals($testNews[0]->getCommentsAllowed(), $selectedMultiple[$i]->getCommentsAllowed());

        $this->assertEquals($testNews[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testNews[1]->getAuthorID(), $selectedMultiple[$j]->getAuthorID());
        $this->assertEquals($testNews[1]->getNewsCategoryID(), $selectedMultiple[$j]->getNewsCategoryID());
        $this->assertEquals($testNews[1]->getTitle(), $selectedMultiple[$j]->getTitle());
        $this->assertEquals($testNews[1]->getThumbnail(), $selectedMultiple[$j]->getThumbnail());
        $this->assertEquals($testNews[1]->getTeaser(), $selectedMultiple[$j]->getTeaser());
        $this->assertEquals($testNews[1]->getBody(), $selectedMultiple[$j]->getBody());
        $this->assertEquals($testNews[1]->getPublished(), $selectedMultiple[$j]->getPublished());
        $this->assertEquals($testNews[1]->getMembersOnly(), $selectedMultiple[$j]->getMembersOnly());
        $this->assertEquals($testNews[1]->getCommentsAllowed(), $selectedMultiple[$j]->getCommentsAllowed());

        // Clean up
        foreach($testNews as $news) {
            $news->delete();
        }
        foreach($testNewsCategory as $newsCategory) {
            $newsCategory->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testEql() {
        // Create test news
        $testNews = [];

        $testNews[0] = new News();
        $testNews[0]->setAuthorID(1);
        $testNews[0]->setNewsCategoryID(2);
        $testNews[0]->setTitle('testTitle');
        $testNews[0]->setThumbnail('/file/img.png');
        $testNews[0]->setTeaser('testTeaser');
        $testNews[0]->setBody('testBody');
        $testNews[0]->setPublished(true);
        $testNews[0]->setMembersOnly(false);
        $testNews[0]->setCommentsAllowed(true);

        $testNews[0] = new News();
        $testNews[0]->setAuthorID(1);
        $testNews[0]->setNewsCategoryID(2);
        $testNews[0]->setTitle('testTitle');
        $testNews[0]->setThumbnail('/file/img.png');
        $testNews[0]->setTeaser('testTeaser');
        $testNews[0]->setBody('testBody');
        $testNews[0]->setPublished(true);
        $testNews[0]->setMembersOnly(false);
        $testNews[0]->setCommentsAllowed(true);

        $testNews[0] = new News();
        $testNews[0]->setAuthorID(3);
        $testNews[0]->setNewsCategoryID(3);
        $testNews[0]->setTitle('testTitle2');
        $testNews[0]->setThumbnail('/file/img2.png');
        $testNews[0]->setTeaser('testTeaser2');
        $testNews[0]->setBody('testBody2');
        $testNews[0]->setPublished(false);
        $testNews[0]->setMembersOnly(true);
        $testNews[0]->setCommentsAllowed(false);

        // Check same object is eql
        $this->assertTrue($testNews[0]->eql($testNews[0]));

        // Check same details are eql
        $this->assertTrue($testNews[0]->eql($testNews[0]));

        // Check different arent equal
        $this->assertFalse($testNews[0]->eql($testNews[0]));
    }

    public function testGetByAuthorID() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test news category
        $testNewsCategory = [];
        $testNewsCategory[0] = new NewsCategory();
        $testNewsCategory[0]->setName('testCategory');
        $testNewsCategory[0]->create();

        $testNewsCategory[1] = new NewsCategory();
        $testNewsCategory[1]->setName('testCategory2');
        $testNewsCategory[1]->create();

        // Create test news
        $testNews = [];

        $testNews[0] = new News();
        $testNews[0]->setAuthorID($testUser[0]->getID());
        $testNews[0]->setNewsCategoryID($testNewsCategory[0]->getID());
        $testNews[0]->setTitle('testTitle');
        $testNews[0]->setThumbnail('/file/img.png');
        $testNews[0]->setTeaser('testTeaser');
        $testNews[0]->setBody('testBody');
        $testNews[0]->setPublished(true);
        $testNews[0]->setMembersOnly(false);
        $testNews[0]->setCommentsAllowed(true);
        $testNews[0]->create();

        $testNews[1] = new News();
        $testNews[1]->setAuthorID($testUser[1]->getID());
        $testNews[1]->setNewsCategoryID($testNewsCategory[1]->getID());
        $testNews[1]->setTitle('testTitle2');
        $testNews[1]->setThumbnail('/file/img2.png');
        $testNews[1]->setTeaser('testTeaser2');
        $testNews[1]->setBody('testBody2');
        $testNews[1]->setPublished(true);
        $testNews[1]->setMembersOnly(false);
        $testNews[1]->setCommentsAllowed(true);
        $testNews[1]->create();

        // Select and check a single
        $selectedSingle = News::getByAuthorID($testUser[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(News::class, $selectedSingle[0]);
        $this->assertEquals($testNews[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testNews[0]->getAuthorID(), $selectedSingle[0]->getAuthorID());
        $this->assertEquals($testNews[0]->getNewsCategoryID(), $selectedSingle[0]->getNewsCategoryID());
        $this->assertEquals($testNews[0]->getTitle(), $selectedSingle[0]->getTitle());
        $this->assertEquals($testNews[0]->getThumbnail(), $selectedSingle[0]->getThumbnail());
        $this->assertEquals($testNews[0]->getTeaser(), $selectedSingle[0]->getTeaser());
        $this->assertEquals($testNews[0]->getBody(), $selectedSingle[0]->getBody());
        $this->assertEquals($testNews[0]->getPublished(), $selectedSingle[0]->getPublished());
        $this->assertEquals($testNews[0]->getMembersOnly(), $selectedSingle[0]->getMembersOnly());
        $this->assertEquals($testNews[0]->getCommentsAllowed(), $selectedSingle[0]->getCommentsAllowed());

        // Clean up
        foreach($testNews as $news) {
            $news->delete();
        }
        foreach($testNewsCategory as $newsCategory) {
            $newsCategory->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testGetByNewsCategoryID() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create a test news category
        $testNewsCategory = [];
        $testNewsCategory[0] = new NewsCategory();
        $testNewsCategory[0]->setName('testCategory');
        $testNewsCategory[0]->create();

        $testNewsCategory[1] = new NewsCategory();
        $testNewsCategory[1]->setName('testCategory2');
        $testNewsCategory[1]->create();

        // Create test news
        $testNews = [];

        $testNews[0] = new News();
        $testNews[0]->setAuthorID($testUser[0]->getID());
        $testNews[0]->setNewsCategoryID($testNewsCategory[0]->getID());
        $testNews[0]->setTitle('testTitle');
        $testNews[0]->setThumbnail('/file/img.png');
        $testNews[0]->setTeaser('testTeaser');
        $testNews[0]->setBody('testBody');
        $testNews[0]->setPublished(true);
        $testNews[0]->setMembersOnly(false);
        $testNews[0]->setCommentsAllowed(true);
        $testNews[0]->create();

        $testNews[1] = new News();
        $testNews[1]->setAuthorID($testUser[1]->getID());
        $testNews[1]->setNewsCategoryID($testNewsCategory[1]->getID());
        $testNews[1]->setTitle('testTitle2');
        $testNews[1]->setThumbnail('/file/img2.png');
        $testNews[1]->setTeaser('testTeaser2');
        $testNews[1]->setBody('testBody2');
        $testNews[1]->setPublished(true);
        $testNews[1]->setMembersOnly(false);
        $testNews[1]->setCommentsAllowed(true);
        $testNews[1]->create();

        // Select and check a single
        $selectedSingle = News::getByNewsCategoryID($testNewsCategory[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(News::class, $selectedSingle[0]);
        $this->assertEquals($testNews[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testNews[0]->getAuthorID(), $selectedSingle[0]->getAuthorID());
        $this->assertEquals($testNews[0]->getNewsCategoryID(), $selectedSingle[0]->getNewsCategoryID());
        $this->assertEquals($testNews[0]->getTitle(), $selectedSingle[0]->getTitle());
        $this->assertEquals($testNews[0]->getThumbnail(), $selectedSingle[0]->getThumbnail());
        $this->assertEquals($testNews[0]->getTeaser(), $selectedSingle[0]->getTeaser());
        $this->assertEquals($testNews[0]->getBody(), $selectedSingle[0]->getBody());
        $this->assertEquals($testNews[0]->getPublished(), $selectedSingle[0]->getPublished());
        $this->assertEquals($testNews[0]->getMembersOnly(), $selectedSingle[0]->getMembersOnly());
        $this->assertEquals($testNews[0]->getCommentsAllowed(), $selectedSingle[0]->getCommentsAllowed());

        // Clean up
        foreach($testNews as $news) {
            $news->delete();
        }
        foreach($testNewsCategory as $newsCategory) {
            $newsCategory->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testSetUserID() {
        //TODO: Implement
    }

    public function testInvalidUserSetUserID() {
        //TODO: Implement
    }

    public function testSetNewsCategoryID() {
        //TODO: Implement
    }

    public function testInvalidNewsCategorySetNewsCategoryID() {
        //TODO: Implement
    }

}
