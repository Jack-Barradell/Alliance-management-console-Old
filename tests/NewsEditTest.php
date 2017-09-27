<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/News.php';
require '../classes/NewsEdit.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Database;
use AMC\Classes\News;
use AMC\Classes\NewsEdit;
use AMC\Classes\User;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class NewsEditTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check null constructor
        $testNewsEdit = new NewsEdit();

        $this->assertNull($testNewsEdit->getID());
        $this->assertNull($testNewsEdit->getEditorID());
        $this->assertNull($testNewsEdit->getNewsID());
        $this->assertNull($testNewsEdit->getTimestamp());

        // Check non null constructor
        $testNewsEdit = new NewsEdit(1,2,3, 123);

        $this->assertEquals(1, $testNewsEdit->getID());
        $this->assertEquals(2, $testNewsEdit->getEditorID());
        $this->assertEquals(3, $testNewsEdit->getNewsID());
        $this->assertEquals(123, $testNewsEdit->getTimestamp());
    }

    public function testCreate() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create test news
        $testNews = new News();
        $testNews->setTeaser('testNews');
        $testNews->create();

        // Create a test news edit
        $testNewsEdit = new NewsEdit();
        $testNewsEdit->setEditorID($testUser->getID());
        $testNewsEdit->setNewsID($testNews->getID());
        $testNewsEdit->setTimestamp(123);
        $testNewsEdit->create();

        // Check id is now an int
        $this->assertInternalType('int', $testNewsEdit->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `NewsEditID`,`EditorID`,`NewsID`,`NewsEditTimestamp` FROM `News_Edits` WHERE `NewsEditID`=?");
        $stmt->bind_param('i', $testNewsEdit->getID());
        $stmt->execute();
        $stmt->bind_result($newsEditID, $editorID, $newsID, $timestamp);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testNewsEdit->getID(), $newsEditID);
        $this->assertEquals($testNewsEdit->getEditorID(), $editorID);
        $this->assertEquals($testNewsEdit->getNewsID(), $newsID);
        $this->assertEquals($testNewsEdit->getTimestamp(), $timestamp);

        $stmt->close();

        // Clean up
        $testNewsEdit->delete();
        $testNews->delete();
        $testUser->delete();
    }

    public function testBlankCreate() {
        // Create a test news edit
        $testNewsEdit = new NewsEdit();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testNewsEdit->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank News Edit.', $e->getMessage());
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

        // Create test news
        $testNews = [];
        $testNews[0] = new News();
        $testNews[0]->setTeaser('testNews');
        $testNews[0]->create();

        $testNews[1] = new News();
        $testNews[1]->setTeaser('testNews2');
        $testNews[1]->create();

        // Create a test news edit
        $testNewsEdit = new NewsEdit();
        $testNewsEdit->setEditorID($testUser[0]->getID());
        $testNewsEdit->setNewsID($testNews[0]->getID());
        $testNewsEdit->setTimestamp(123);
        $testNewsEdit->create();

        // Now update it
        $testNewsEdit->setEditorID($testUser[1]->getID());
        $testNewsEdit->setNewsID($testNews[1]->getID());
        $testNewsEdit->setTimestamp(12345);
        $testNewsEdit->update();

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `NewsEditID`,`EditorID`,`NewsID`,`NewsEditTimestamp` FROM `News_Edits` WHERE `NewsEditID`=?");
        $stmt->bind_param('i', $testNewsEdit->getID());
        $stmt->execute();
        $stmt->bind_result($newsEditID, $editorID, $newsID, $timestamp);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testNewsEdit->getID(), $newsEditID);
        $this->assertEquals($testNewsEdit->getEditorID(), $editorID);
        $this->assertEquals($testNewsEdit->getNewsID(), $newsID);
        $this->assertEquals($testNewsEdit->getTimestamp(), $timestamp);

        $stmt->close();

        // Clean up
        $testNewsEdit->delete();
        foreach($testNews as $news) {
            $news->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testBlankUpdate() {
        // Create a test news edit
        $testNewsEdit = new NewsEdit();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testNewsEdit->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank News Edit.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();

        // Create test news
        $testNews = new News();
        $testNews->setTeaser('testNews');
        $testNews->create();

        // Create a test news edit
        $testNewsEdit = new NewsEdit();
        $testNewsEdit->setEditorID($testUser->getID());
        $testNewsEdit->setNewsID($testNews->getID());
        $testNewsEdit->setTimestamp(123);
        $testNewsEdit->create();

        // Save the id
        $id = $testNewsEdit->getID();

        // Now delete it
        $testNewsEdit->delete();

        // Now check id is null
        $this->assertNull($testNewsEdit->getID());

        // Pull and check
        $stmt = $this->_connection->prepare("SELECT `NewsEditID`,`EditorID`,`NewsID`,`NewsEditTimestamp` FROM `News_Edits` WHERE `NewsEditID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testNews->delete();
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

        // Create test news
        $testNews = [];
        $testNews[0] = new News();
        $testNews[0]->setTeaser('testNews');
        $testNews[0]->create();

        $testNews[1] = new News();
        $testNews[1]->setTeaser('testNews2');
        $testNews[1]->create();

        // Create a test news edit
        $testNewsEdit = [];
        $testNewsEdit[0] = new NewsEdit();
        $testNewsEdit[0]->setEditorID($testUser[0]->getID());
        $testNewsEdit[0]->setNewsID($testNews[0]->getID());
        $testNewsEdit[0]->setTimestamp(123);
        $testNewsEdit[0]->create();

        $testNewsEdit[1] = new NewsEdit();
        $testNewsEdit[1]->setEditorID($testUser[1]->getID());
        $testNewsEdit[1]->setNewsID($testNews[1]->getID());
        $testNewsEdit[1]->setTimestamp(12345);
        $testNewsEdit[1]->create();

        $testNewsEdit[2] = new NewsEdit();
        $testNewsEdit[2]->setEditorID($testUser[0]->getID());
        $testNewsEdit[2]->setNewsID($testNews[1]->getID());
        $testNewsEdit[2]->setTimestamp(12345678);
        $testNewsEdit[2]->create();

        // Select and check a single
        $selectedSingle = NewsEdit::select(array($testNewsEdit[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(NewsEdit::class, $selectedSingle[0]);
        $this->assertEquals($testNewsEdit[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testNewsEdit[0]->getEditorID(), $selectedSingle[0]->getEditorID());
        $this->assertEquals($testNewsEdit[0]->getNewsID(), $selectedSingle[0]->getNewsID());
        $this->assertEquals($testNewsEdit[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Get and check multiple
        $selectedMultiple = NewsEdit::select(array($testNewsEdit[1]->getID(), $testNewsEdit[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(NewsEdit::class, $selectedMultiple[0]);
        $this->assertInstanceOf(NewsEdit::class, $selectedMultiple[1]);

        if($testNewsEdit[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testNewsEdit[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testNewsEdit[1]->getEditorID(), $selectedMultiple[$i]->getEditorID());
        $this->assertEquals($testNewsEdit[1]->getNewsID(), $selectedMultiple[$i]->getNewsID());
        $this->assertEquals($testNewsEdit[1]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());

        $this->assertEquals($testNewsEdit[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testNewsEdit[2]->getEditorID(), $selectedMultiple[$j]->getEditorID());
        $this->assertEquals($testNewsEdit[2]->getNewsID(), $selectedMultiple[$j]->getNewsID());
        $this->assertEquals($testNewsEdit[2]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());

        // Clean up
        foreach($testNewsEdit as $newsEdit) {
            $newsEdit->delete();
        }
        foreach($testNews as $news) {
            $news->delete();
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

        // Create test news
        $testNews = [];
        $testNews[0] = new News();
        $testNews[0]->setTeaser('testNews');
        $testNews[0]->create();

        $testNews[1] = new News();
        $testNews[1]->setTeaser('testNews2');
        $testNews[1]->create();

        // Create a test news edit
        $testNewsEdit = [];
        $testNewsEdit[0] = new NewsEdit();
        $testNewsEdit[0]->setEditorID($testUser[0]->getID());
        $testNewsEdit[0]->setNewsID($testNews[0]->getID());
        $testNewsEdit[0]->setTimestamp(123);
        $testNewsEdit[0]->create();

        $testNewsEdit[1] = new NewsEdit();
        $testNewsEdit[1]->setEditorID($testUser[1]->getID());
        $testNewsEdit[1]->setNewsID($testNews[1]->getID());
        $testNewsEdit[1]->setTimestamp(12345);
        $testNewsEdit[1]->create();

        // Get and check multiple
        $selectedMultiple = NewsEdit::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(NewsEdit::class, $selectedMultiple[0]);
        $this->assertInstanceOf(NewsEdit::class, $selectedMultiple[1]);

        if($testNewsEdit[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testNewsEdit[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testNewsEdit[0]->getEditorID(), $selectedMultiple[$i]->getEditorID());
        $this->assertEquals($testNewsEdit[0]->getNewsID(), $selectedMultiple[$i]->getNewsID());
        $this->assertEquals($testNewsEdit[0]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());

        $this->assertEquals($testNewsEdit[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testNewsEdit[1]->getEditorID(), $selectedMultiple[$j]->getEditorID());
        $this->assertEquals($testNewsEdit[1]->getNewsID(), $selectedMultiple[$j]->getNewsID());
        $this->assertEquals($testNewsEdit[1]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());

        // Clean up
        foreach($testNewsEdit as $newsEdit) {
            $newsEdit->delete();
        }
        foreach($testNews as $news) {
            $news->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testEql() {
        // Create a test news edit
        $testNewsEdit = [];
        $testNewsEdit[0] = new NewsEdit();
        $testNewsEdit[0]->setEditorID(1);
        $testNewsEdit[0]->setNewsID(2);
        $testNewsEdit[0]->setTimestamp(123);

        $testNewsEdit[1] = new NewsEdit();
        $testNewsEdit[1]->setEditorID(1);
        $testNewsEdit[1]->setNewsID(2);
        $testNewsEdit[1]->setTimestamp(123);

        $testNewsEdit[2] = new NewsEdit();
        $testNewsEdit[2]->setEditorID(3);
        $testNewsEdit[2]->setNewsID(4);
        $testNewsEdit[2]->setTimestamp(12345);


        // Check same object is eql
        $this->assertTrue($testNewsEdit[0]->eql($testNewsEdit[0]));

        // Check same details are eql
        $this->assertTrue($testNewsEdit[0]->eql($testNewsEdit[0]));

        // Check different arent equal
        $this->assertFalse($testNewsEdit[0]->eql($testNewsEdit[0]));
    }

    public function testGetByEditorID() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create test news
        $testNews = [];
        $testNews[0] = new News();
        $testNews[0]->setTeaser('testNews');
        $testNews[0]->create();

        $testNews[1] = new News();
        $testNews[1]->setTeaser('testNews2');
        $testNews[1]->create();

        // Create a test news edit
        $testNewsEdit = [];
        $testNewsEdit[0] = new NewsEdit();
        $testNewsEdit[0]->setEditorID($testUser[0]->getID());
        $testNewsEdit[0]->setNewsID($testNews[0]->getID());
        $testNewsEdit[0]->setTimestamp(123);
        $testNewsEdit[0]->create();

        $testNewsEdit[1] = new NewsEdit();
        $testNewsEdit[1]->setEditorID($testUser[1]->getID());
        $testNewsEdit[1]->setNewsID($testNews[1]->getID());
        $testNewsEdit[1]->setTimestamp(12345);
        $testNewsEdit[1]->create();

        // Select and check a single
        $selectedSingle = NewsEdit::getByEditorID($testUser[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(NewsEdit::class, $selectedSingle[0]);
        $this->assertEquals($testNewsEdit[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testNewsEdit[0]->getEditorID(), $selectedSingle[0]->getEditorID());
        $this->assertEquals($testNewsEdit[0]->getNewsID(), $selectedSingle[0]->getNewsID());
        $this->assertEquals($testNewsEdit[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Clean up
        foreach($testNewsEdit as $newsEdit) {
            $newsEdit->delete();
        }
        foreach($testNews as $news) {
            $news->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testGetByNewsID() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser2');
        $testUser[1]->create();

        // Create test news
        $testNews = [];
        $testNews[0] = new News();
        $testNews[0]->setTeaser('testNews');
        $testNews[0]->create();

        $testNews[1] = new News();
        $testNews[1]->setTeaser('testNews2');
        $testNews[1]->create();

        // Create a test news edit
        $testNewsEdit = [];
        $testNewsEdit[0] = new NewsEdit();
        $testNewsEdit[0]->setEditorID($testUser[0]->getID());
        $testNewsEdit[0]->setNewsID($testNews[0]->getID());
        $testNewsEdit[0]->setTimestamp(123);
        $testNewsEdit[0]->create();

        $testNewsEdit[1] = new NewsEdit();
        $testNewsEdit[1]->setEditorID($testUser[1]->getID());
        $testNewsEdit[1]->setNewsID($testNews[1]->getID());
        $testNewsEdit[1]->setTimestamp(12345);
        $testNewsEdit[1]->create();

        // Select and check a single
        $selectedSingle = NewsEdit::getByNewsID($testNews[0]->getID());

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(NewsEdit::class, $selectedSingle[0]);
        $this->assertEquals($testNewsEdit[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testNewsEdit[0]->getEditorID(), $selectedSingle[0]->getEditorID());
        $this->assertEquals($testNewsEdit[0]->getNewsID(), $selectedSingle[0]->getNewsID());
        $this->assertEquals($testNewsEdit[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Clean up
        foreach($testNewsEdit as $newsEdit) {
            $newsEdit->delete();
        }
        foreach($testNews as $news) {
            $news->delete();
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

    public function testSetNewsID() {
        //TODO: Implement
    }

    public function testInvalidNewsSetNewsID() {
        //TODO: Implement
    }

}
