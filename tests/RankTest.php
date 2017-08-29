<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Rank.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Rank;
use AMC\Classes\Database;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class RankTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Test the null constructor
        $rank = new Rank();

        $this->assertFalse($rank->eql(new Rank()));
        $this->assertNull($rank->getID());
        $this->assertNull($rank->getName());
        $this->assertNull($rank->getSalary());
        $this->assertNull($rank->getImage());

        // Test non null constructor
        $rank = new Rank(1, 'test', 1000, 'image.png');

        $this->assertFalse($rank->eql(new Rank()));
        $this->assertEquals(1, $rank->getID());
        $this->assertEquals('test', $rank->getName());
        $this->assertEquals(1000, $rank->getSalary());
        $this->assertEquals('image.png', $rank->getImage());
    }

    public function testCreate() {
        // Create a test rank
        $testRank = new Rank();
        $testRank->setName('test');
        $testRank->setSalary(123);
        $testRank->setImage('dir/image.png');
        $testRank->create();

        // Check id is now an int
        $this->assertInternalType('int', $testRank->getID());

        // Now pull
        $stmt = $this->_connection->prepare("SELECT `RankID`,`RankName`,`RankSalary`,`RankImage` FROM `Ranks` WHERE `RankID`=?");
        $stmt->bind_param('i', $testRank->getID());
        $stmt->execute();
        $stmt->bind_result($rankID, $name, $salary, $image);

        // Check one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testRank->getID(), $rankID);
        $this->assertEquals($testRank->getName(), $name);
        $this->assertEquals($testRank->getSalary(), $salary);
        $this->assertEquals($testRank->getImage(), $image);

        $stmt->close();

        // Clean up
        $testRank->delete();
    }

    public function testBlankCreate() {
        // Create the rank
        $rank = new Rank();

        // Set exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $rank->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Rank.', $e->getMessage());
        }
    }

    public function testUpdate() {
        // Create a test rank
        $testRank = new Rank();
        $testRank->setName('test');
        $testRank->setSalary(123);
        $testRank->setImage('dir/image.png');
        $testRank->create();

        // Now update it
        $testRank->setName('test2');
        $testRank->setSalary(12345);
        $testRank->setImage('dir/image2.png');
        $testRank->update();

        // Now pull
        $stmt = $this->_connection->prepare("SELECT `RankID`,`RankName`,`RankSalary`,`RankImage` FROM `Ranks` WHERE `RankID`=?");
        $stmt->bind_param('i', $testRank->getID());
        $stmt->execute();
        $stmt->bind_result($rankID, $name, $salary, $image);

        // Check one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        $this->assertEquals($testRank->getID(), $rankID);
        $this->assertEquals($testRank->getName(), $name);
        $this->assertEquals($testRank->getSalary(), $salary);
        $this->assertEquals($testRank->getImage(), $image);

        $stmt->close();

        // Clean up
        $testRank->delete();
    }

    public function testBlankUpdate() {
        // Create the rank
        $rank = new Rank();

        // Set exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        try {
            $rank->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Rank.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a test rank
        $testRank = new Rank();
        $testRank->setName('test');
        $testRank->setSalary(123);
        $testRank->setImage('dir/image.png');
        $testRank->create();

        // Now store id
        $id = $testRank->getID();

        // Now delete it
        $testRank->delete();

        // Now check id is null
        $this->assertNull($testRank->getID());

        // Now pull
        $stmt = $this->_connection->prepare("SELECT `RankID`,`RankName`,`RankSalary`,`RankImage` FROM `Ranks` WHERE `RankID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check one result
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();
    }

    public function testSelectWithInput() {
        // Create a test rank
        $testRank = [];
        $testRank[0] = new Rank();
        $testRank[0]->setName('test');
        $testRank[0]->setSalary(123);
        $testRank[0]->setImage('dir/image.png');
        $testRank[0]->create();

        $testRank[1] = new Rank();
        $testRank[1]->setName('test2');
        $testRank[1]->setSalary(12345);
        $testRank[1]->setImage('dir/image2.png');
        $testRank[1]->create();

        $testRank[2] = new Rank();
        $testRank[2]->setName('test3');
        $testRank[2]->setSalary(12345678);
        $testRank[2]->setImage('dir/image3.png');
        $testRank[2]->create();

        // Now get and check a single
        $selectedSingle = Rank::select(array($testRank[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(Rank::class, $selectedSingle[0]);

        $this->assertEquals($testRank[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testRank[0]->getName(), $selectedSingle[0]->getName());
        $this->assertEquals($testRank[0]->getSalary(), $selectedSingle[0]->getSalary());
        $this->assertEquals($testRank[0]->getImage(), $selectedSingle[0]->getImage());

        // Now get and check multiple
        $selectedMultiple = Rank::select(array($testRank[1]->getID(), $testRank[2]->getID()));

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(Rank::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Rank::class, $selectedMultiple[1]);

        if($testRank[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testRank[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testRank[1]->getName(), $selectedMultiple[$i]->getName());
        $this->assertEquals($testRank[1]->getSalary(), $selectedMultiple[$i]->getSalary());
        $this->assertEquals($testRank[1]->getImage(), $selectedMultiple[$i]->getImage());

        $this->assertEquals($testRank[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testRank[2]->getName(), $selectedMultiple[$j]->getName());
        $this->assertEquals($testRank[2]->getSalary(), $selectedMultiple[$j]->getSalary());
        $this->assertEquals($testRank[2]->getImage(), $selectedMultiple[$j]->getImage());

        // Clean up
        foreach($testRank as $rank) {
            $rank->delete();
        }
    }


    public function testSelectAll() {
        // Create a test rank
        $testRank = [];
        $testRank[0] = new Rank();
        $testRank[0]->setName('test');
        $testRank[0]->setSalary(123);
        $testRank[0]->setImage('dir/image.png');
        $testRank[0]->create();

        $testRank[1] = new Rank();
        $testRank[1]->setName('test2');
        $testRank[1]->setSalary(12345);
        $testRank[1]->setImage('dir/image2.png');
        $testRank[1]->create();

        // Now get and check multiple
        $selectedMultiple = Rank::select(array());

        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(Rank::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Rank::class, $selectedMultiple[1]);

        if($testRank[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testRank[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testRank[0]->getName(), $selectedMultiple[$i]->getName());
        $this->assertEquals($testRank[0]->getSalary(), $selectedMultiple[$i]->getSalary());
        $this->assertEquals($testRank[0]->getImage(), $selectedMultiple[$i]->getImage());

        $this->assertEquals($testRank[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testRank[1]->getName(), $selectedMultiple[$j]->getName());
        $this->assertEquals($testRank[1]->getSalary(), $selectedMultiple[$j]->getSalary());
        $this->assertEquals($testRank[1]->getImage(), $selectedMultiple[$j]->getImage());

        // Clean up
        foreach($testRank as $rank) {
            $rank->delete();
        }
    }

    public function testEql() {
        // Create a test rank
        $testRank = [];
        $testRank[0] = new Rank();
        $testRank[0]->setName('test');
        $testRank[0]->setSalary(123);
        $testRank[0]->setImage('dir/image.png');

        $testRank[1] = new Rank();
        $testRank[1]->setName('test');
        $testRank[1]->setSalary(123);
        $testRank[1]->setImage('dir/image.png');

        $testRank[2] = new Rank();
        $testRank[2]->setName('test2');
        $testRank[2]->setSalary(12345);
        $testRank[2]->setImage('dir/image2.png');

        // Check same object is eql
        $this->assertTrue($testRank[0]->eql($testRank[0]));

        // Check same details are eql
        $this->assertTrue($testRank[0]->eql($testRank[1]));

        // Check different arent equal
        $this->assertFalse($testRank[0]->eql($testRank[2]));
    }

}
