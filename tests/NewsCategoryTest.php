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
use AMC\Classes\NewsCategory;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class NewsCategoryTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check null constructor
        $testNewsCategory = new NewsCategory();

        $this->assertNull($testNewsCategory->getID());
        $this->assertNull($testNewsCategory->getName());
        $this->assertNull($testNewsCategory->getImage());

        // Check non null constructor
        $testNewsCategory = new NewsCategory(1, 'name', 'img/file.png');

        $this->assertEquals(1, $testNewsCategory->getID());
        $this->assertEquals('name', $testNewsCategory->getName());
        $this->assertEquals('img/file.png', $testNewsCategory->getImage());
    }

    public function testCreate() {
        // Create a test news category
        $testNewsCategory = new NewsCategory();
        $testNewsCategory->setName('testName');
        $testNewsCategory->setImage('/file/test.png');
        $testNewsCategory->create();

        // Check id is now an int
        $this->assertInternalType('int', $testNewsCategory->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `NewsCategoryID`,`NewsCategoryName`,`NewsCategoryImage` FROM `News_Categories` WHERE `NewsCategoryID`=?");
        $stmt->bind_param('i', $testNewsCategory->getID());
        $stmt->execute();
        $stmt->bind_result($newsCategoryID, $name, $image);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testNewsCategory->getID(), $newsCategoryID);
        $this->assertEquals($testNewsCategory->getName(), $name);
        $this->assertEquals($testNewsCategory->getImage(), $image);

        $stmt->close();

        // Clean up
        $testNewsCategory->delete();
    }

    public function testBlankCreate() {
        // Create a test news category
        $testNewsCategory = new NewsCategory();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testNewsCategory->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank News Category.', $e->getMessage());
        }
    }

    public function testUpdate() {
        // Create a test news category
        $testNewsCategory = new NewsCategory();
        $testNewsCategory->setName('testName');
        $testNewsCategory->setImage('/file/test.png');
        $testNewsCategory->create();

        // Update it
        $testNewsCategory->setName('testName2');
        $testNewsCategory->setImage('/file/test2.png');
        $testNewsCategory->update();

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `NewsCategoryID`,`NewsCategoryName`,`NewsCategoryImage` FROM `News_Categories` WHERE `NewsCategoryID`=?");
        $stmt->bind_param('i', $testNewsCategory->getID());
        $stmt->execute();
        $stmt->bind_result($newsCategoryID, $name, $image);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testNewsCategory->getID(), $newsCategoryID);
        $this->assertEquals($testNewsCategory->getName(), $name);
        $this->assertEquals($testNewsCategory->getImage(), $image);

        $stmt->close();

        // Clean up
        $testNewsCategory->delete();
    }

    public function testBlankUpdate() {
        // Create a test news category
        $testNewsCategory = new NewsCategory();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $testNewsCategory->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank News Category.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test news category
        $testNewsCategory = new NewsCategory();
        $testNewsCategory->setName('testName');
        $testNewsCategory->setImage('/file/test.png');
        $testNewsCategory->create();

        // Store the id
        $id = $testNewsCategory->getID();

        // Now delete it
        $testNewsCategory->delete();

        // Check the id is now null
        $this->assertNull($testNewsCategory->getID());

        // Now pull and check
        $stmt = $this->_connection->prepare("SELECT `NewsCategoryID`,`NewsCategoryName`,`NewsCategoryImage` FROM `News_Categories` WHERE `NewsCategoryID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();
    }

    public function testSelectWithInput() {
        // Create a test news category
        $testNewsCategory = [];
        $testNewsCategory[0] = new NewsCategory();
        $testNewsCategory[0]->setName('testName');
        $testNewsCategory[0]->setImage('/file/test.png');
        $testNewsCategory[0]->create();

        $testNewsCategory[1] = new NewsCategory();
        $testNewsCategory[1]->setName('testName2');
        $testNewsCategory[1]->setImage('/file/test2.png');
        $testNewsCategory[1]->create();

        $testNewsCategory[2] = new NewsCategory();
        $testNewsCategory[2]->setName('testName3');
        $testNewsCategory[2]->setImage('/file/test3.png');
        $testNewsCategory[2]->create();

        // Get and check a single
        $selectedSingle = NewsCategory::select(array($testNewsCategory[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(NewsCategory::class, $selectedSingle[0]);
        $this->assertEquals($testNewsCategory[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testNewsCategory[0]->getName(), $selectedSingle[0]->getName());
        $this->assertEquals($testNewsCategory[0]->getImage(), $selectedSingle[0]->getImage());

        // Get and check multiple
        $selectedMultiple = NewsCategory::select(array($testNewsCategory[1]->getID(), $testNewsCategory[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(NewsCategory::class, $selectedMultiple[0]);
        $this->assertInstanceOf(NewsCategory::class, $selectedMultiple[1]);

        if($testNewsCategory[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testNewsCategory[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testNewsCategory[1]->getName(), $selectedMultiple[$i]->getName());
        $this->assertEquals($testNewsCategory[1]->getImage(), $selectedMultiple[$i]->getImage());

        $this->assertEquals($testNewsCategory[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testNewsCategory[2]->getName(), $selectedMultiple[$j]->getName());
        $this->assertEquals($testNewsCategory[2]->getImage(), $selectedMultiple[$j]->getImage());

        foreach($testNewsCategory as $newsCategory) {
            $newsCategory->delete();
        }
    }

    public function testSelectAll() {
        // Create a test news category
        $testNewsCategory = [];
        $testNewsCategory[0] = new NewsCategory();
        $testNewsCategory[0]->setName('testName');
        $testNewsCategory[0]->setImage('/file/test.png');
        $testNewsCategory[0]->create();

        $testNewsCategory[1] = new NewsCategory();
        $testNewsCategory[1]->setName('testName2');
        $testNewsCategory[1]->setImage('/file/test2.png');
        $testNewsCategory[1]->create();

        // Get and check multiple
        $selectedMultiple = NewsCategory::select(array($testNewsCategory[1]->getID(), $testNewsCategory[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(NewsCategory::class, $selectedMultiple[0]);
        $this->assertInstanceOf(NewsCategory::class, $selectedMultiple[1]);

        if($testNewsCategory[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testNewsCategory[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testNewsCategory[0]->getName(), $selectedMultiple[$i]->getName());
        $this->assertEquals($testNewsCategory[0]->getImage(), $selectedMultiple[$i]->getImage());

        $this->assertEquals($testNewsCategory[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testNewsCategory[1]->getName(), $selectedMultiple[$j]->getName());
        $this->assertEquals($testNewsCategory[1]->getImage(), $selectedMultiple[$j]->getImage());

        foreach($testNewsCategory as $newsCategory) {
            $newsCategory->delete();
        }
    }

    public function testEql() {
        // Create a test news category
        $testNewsCategory = [];
        $testNewsCategory[0] = new NewsCategory();
        $testNewsCategory[0]->setName('testName');
        $testNewsCategory[0]->setImage('/file/test.png');

        $testNewsCategory[1] = new NewsCategory();
        $testNewsCategory[1]->setName('testName');
        $testNewsCategory[1]->setImage('/file/test.png');

        $testNewsCategory[2] = new NewsCategory();
        $testNewsCategory[2]->setName('testName2');
        $testNewsCategory[2]->setImage('/file/test2.png');

        // Check same object is eql
        $this->assertTrue($testNewsCategory[0]->eql($testNewsCategory[0]));

        // Check same details are eql
        $this->assertTrue($testNewsCategory[0]->eql($testNewsCategory[0]));

        // Check different arent equal
        $this->assertFalse($testNewsCategory[0]->eql($testNewsCategory[0]));
    }

    public function testNewsCategoryExists() {
        //TODO: Implement
    }

    public function testIncorrectTypeNewsCategoryExists() {
        //TODO: Implement
    }

}
