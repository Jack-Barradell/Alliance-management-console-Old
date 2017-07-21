<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Award.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Database;
use AMC\Exceptions\BlankObjectException;
use AMC\Classes\Award;
use PHPUnit\Framework\TestCase;

class AwardTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('localhost', 'testingDB', 'testingDB', 'testingdb');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {

        // Check the null constructor
        $award = new Award();

        $this->assertTrue($award->eql(new Award()));
        $this->assertNull($award->getID());
        $this->assertNull($award->getName());
        $this->assertNull($award->getDescription());
        $this->assertNull($award->getBadge());

        // Check the non null constructor
        $award = new Award(1, 'name', 'description', '../test/image.png');

        $this->assertFalse($award->eql(new Award()));
        $this->assertEquals(1, $award->getID());
        $this->assertEquals('name', $award->getName());
        $this->assertEquals('description', $award->getDescription());
        $this->assertEquals('../test/image.png', $award->getBadge());
    }

    public function testCreate() {
        // Create an award
        $testAward = new Award();

        $testAward->setName('name');
        $testAward->setDescription('test description');
        $testAward->setBadge('../test/image.png');
        $testAward->create();

        // Check id is now a number
        $this->assertInternalType('int', $testAward->getID());

        $stmt = $this->_connection->prepare("SELECT `AwardID`,`AwardName`,`AwardDescription`,`AwardBadge` FROM `Awards` WHERE `AwardID`=?");
        $stmt->bind_param('i', $testAward->getID());
        $stmt->execute();
        $stmt->bind_result($awardID, $name, $description, $badge);

        // Check only one result
        $this->assertEquals(1, $stmt->num_rows);

        // Check results match object
        $stmt->fetch();
        $this->assertEquals($testAward->getID(), $awardID);
        $this->assertEquals($testAward->getName(), $name);
        $this->assertEquals($testAward->getDescription(), $description);
        $this->assertEquals($testAward->getBadge(), $badge);

        // Clean up
        $testAward->delete();
    }

    public function testBlankCreate() {
        // Make an award
        $award = new Award();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Now trigger it
        $award->create();
    }

    public function testUpdate() {
        // Create an award
        $testAward = new Award();

        $testAward->setName('name');
        $testAward->setDescription('test description');
        $testAward->setBadge('../test/image.png');
        $testAward->create();

        // Now update the newly created award
        $testAward->setName('name2');
        $testAward->setDescription('description 2');
        $testAward->setBadge('./path/badge.png');
        $testAward->update();

        $stmt = $this->_connection->prepare("SELECT `AwardID`,`AwardName`,`AwardDescription`,`AwardBadge` FROM `Awards` WHERE `AwardID`=?");
        $stmt->bind_param('i', $testAward->getID());
        $stmt->execute();
        $stmt->bind_result($awardID, $name, $description, $badge);

        // Check only one result
        $this->assertEquals(1, $stmt->num_rows);

        // Check results match updated object
        $stmt->fetch();
        $this->assertEquals($testAward->getID(), $awardID);
        $this->assertEquals($testAward->getName(), $name);
        $this->assertEquals($testAward->getDescription(), $description);
        $this->assertEquals($testAward->getBadge(), $badge);

        // Clean up
        $testAward->delete();
    }

    public function testBlankUpdate() {
        // Make an award
        $award = new Award();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Now trigger it
        $award->update();
    }

    public function testDelete() {
        // Create an award
        $testAward = new Award();

        $testAward->setName('name');
        $testAward->setDescription('test description');
        $testAward->setBadge('../test/image.png');
        $testAward->create();

        // Grab id before its removed
        $id = $testAward->getID();

        // Now delete it
        $testAward->delete();

        $stmt = $this->_connection->prepare("SELECT `AwardID`,`AwardName`,`AwardDescription`,`AwardBadge` FROM `Awards` WHERE `AwardID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($awardID, $name, $description, $badge);

        // Check only one result
        $this->assertEquals(0, $stmt->num_rows);
        $stmt->close();
    }

    public function testSelectWithInput() {
        // Create an award
        $testAward = [];

        $testAward[0] = new Award();
        $testAward[0]->setName('name');
        $testAward[0]->setDescription('test description');
        $testAward[0]->setBadge('../test/image.png');
        $testAward[0]->create();

        $testAward[1] = new Award();
        $testAward[1]->setName('name2');
        $testAward[1]->setDescription('test description2');
        $testAward[1]->setBadge('../test/image2.png');
        $testAward[1]->create();

        $testAward[2] = new Award();
        $testAward[2]->setName('name');
        $testAward[2]->setDescription('test description3');
        $testAward[2]->setBadge('../test/image3.png');
        $testAward[2]->create();

        // Get and check a single award
        $selectedSingle = Award::get($testAward[0]->getID());

        $this->assertInstanceOf(Award::class, $selectedSingle);
        $this->assertEquals($testAward[0]->getID(), $selectedSingle->getID());
        $this->assertEquals($testAward[0]->getName(), $selectedSingle->getName());
        $this->assertEquals($testAward[0]->getDescription(), $selectedSingle->getDescription());
        $this->assertEquals($testAward[0]->getBadge(), $selectedSingle->getBadge());

        $selectedMultiple = Award::get(array($testAward[1]->getID(), $testAward[2]->getID()));

        // Check it is an array
        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));

        // Check they are awards
        $this->assertInstanceOf(Award::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Award::class, $selectedMultiple[1]);

        if($testAward[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testAward[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testAward[1]->getName(), $selectedMultiple[$i]->getName());
        $this->assertEquals($testAward[1]->getDescription(), $selectedMultiple[$i]->getDescription());
        $this->assertEquals($testAward[1]->getBadge(), $selectedMultiple[$i]->getBadge());

        $this->assertEquals($testAward[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testAward[2]->getName(), $selectedMultiple[$j]->getName());
        $this->assertEquals($testAward[2]->getDescription(), $selectedMultiple[$j]->getDescription());
        $this->assertEquals($testAward[2]->getBadge(), $selectedMultiple[$j]->getBadge());

        // Clean up
        foreach($testAward as $award) {
            $award->delete();
        }
    }

    public function testSelectAll() {
        // Create an award
        $testAward = [];

        $testAward[0] = new Award();
        $testAward[0]->setName('name');
        $testAward[0]->setDescription('test description');
        $testAward[0]->setBadge('../test/image.png');
        $testAward[0]->create();

        $testAward[1] = new Award();
        $testAward[1]->setName('name2');
        $testAward[1]->setDescription('test description2');
        $testAward[1]->setBadge('../test/image2.png');
        $testAward[1]->create();


        $selectedMultiple = Award::get();

        // Check it is an array
        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));

        // Check they are awards
        $this->assertInstanceOf(Award::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Award::class, $selectedMultiple[1]);

        if($testAward[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testAward[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testAward[0]->getName(), $selectedMultiple[$i]->getName());
        $this->assertEquals($testAward[0]->getDescription(), $selectedMultiple[$i]->getDescription());
        $this->assertEquals($testAward[0]->getBadge(), $selectedMultiple[$i]->getBadge());

        $this->assertEquals($testAward[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testAward[1]->getName(), $selectedMultiple[$j]->getName());
        $this->assertEquals($testAward[1]->getDescription(), $selectedMultiple[$j]->getDescription());
        $this->assertEquals($testAward[1]->getBadge(), $selectedMultiple[$j]->getBadge());

        // Clean up
        foreach($testAward as $award) {
            $award->delete();
        }
    }

    public function testEql() {
        // Create an award
        $testAward = [];

        $testAward[0] = new Award();
        $testAward[0]->setName('name');
        $testAward[0]->setDescription('test description');
        $testAward[0]->setBadge('../test/image.png');

        $testAward[1] = new Award();
        $testAward[1]->setName('name');
        $testAward[1]->setDescription('test description');
        $testAward[1]->setBadge('../test/image.png');

        $testAward[2] = new Award();
        $testAward[2]->setName('name2');
        $testAward[2]->setDescription('test description2');
        $testAward[2]->setBadge('../test/image2.png');

        // Check same object is eql
        $this->assertTrue($testAward[0]->eql($testAward[0]));

        // Check same details are eql
        $this->assertTrue($testAward[0]->eql($testAward[1]));

        // Check different arent equal
        $this->assertFalse($testAward[0]->eql($testAward[2]));
    }

}
