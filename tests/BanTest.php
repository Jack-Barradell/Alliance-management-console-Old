<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/User.php';
require '../classes/Ban.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Ban;
use AMC\Classes\Database;
use AMC\Classes\User;
use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\InvalidUserException;
use PHPUnit\Framework\TestCase;

class BanTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('', '', '', '');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Check null constructor works
        $ban = new Ban();

        $this->assertTrue($ban->eql(new Ban()));
        $this->assertNull($ban->getID());
        $this->assertNull($ban->getUserID());
        $this->assertNull($ban->getAdminID());
        $this->assertNull($ban->getUnbanAdminID());
        $this->assertNull($ban->getReason());
        $this->assertNull($ban->getBanDate());
        $this->assertNull($ban->getUnbanDate());
        $this->assertNull($ban->getActive());
        $this->assertNull($ban->getExpiry());

        // Check var constructor works
        $ban = new Ban(1, 1, 2, 2, 'test', 123, 321, false, -1);
        $this->assertFalse($ban->eql(new Ban()));
        $this->assertEquals(1, $ban->getID());
        $this->assertEquals(1, $ban->getUserID());
        $this->assertEquals(2, $ban->getAdminID());
        $this->assertEquals(2, $ban->getUnbanAdminID());
        $this->assertEquals('test', $ban->getReason());
        $this->assertEquals(123, $ban->getBanDate());
        $this->assertEquals(321, $ban->getUnbanDate());
        $this->assertFalse($ban->getActive());
        $this->assertEquals(-1, $ban->getExpiry());
    }

    public function testCreate() {
        // Create a user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();
        // Create a admin
        $testAdmin = new User();
        $testAdmin->setUsername('testAdmin');
        $testAdmin->create();

        // Create a ban
        $testBan = new Ban();
        $testBan->setUserID($testUser->getID());
        $testBan->setAdminID($testAdmin->getID());
        $testBan->setUnbanAdminID($testAdmin->getID());
        $testBan->setReason('testban');
        $testBan->setBanDate(123);
        $testBan->setUnbanDate(124);
        $testBan->setActive(true);
        $testBan->setExpiry(-1);
        $testBan->create();

        // Check id is a number now
        $this->assertInternalType('int', $testBan->getID());

        $stmt = $this->_connection->prepare("SELECT `BanID`,`UserID`,`AdminID`,`UnbanAdminID`,`BanReason`,`BanDate`,`UnbanDate`,`BanActive`,`BanExpiry` FROM `Bans` WHERE `BanID`=?");
        $stmt->bind_param('i', $testBan->getID());
        $stmt->execute();
        $stmt->bind_result($banID, $userID, $adminID, $unbanAdminID, $reason, $banDate, $unbanDate, $active, $expiry);

        // Check only one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        if($active == 1) {
            $active = true;
        }
        else if($active == 0) {
            $active = false;
        }

        $this->assertEquals($testBan->getID(), $banID);
        $this->assertEquals($testBan->getUserID(), $userID);
        $this->assertEquals($testBan->getAdminID(), $adminID);
        $this->assertEquals($testBan->getUnbanAdminID(), $unbanAdminID);
        $this->assertEquals($testBan->getReason(), $reason);
        $this->assertEquals($testBan->getBanDate(), $banDate);
        $this->assertEquals($testBan->getUnbanDate(), $unbanDate);
        $this->assertEquals($testBan->getActive(), $active);
        $this->assertEquals($testBan->getExpiry(), $expiry);

        $stmt->close();

        // Clean up
        $testBan->delete();
        $testUser->delete();
        $testAdmin->delete();
    }

    public function testBlankCreate() {
        // Create blank ban
        $ban = new Ban();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger the exception
        try {
            $ban->create();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Ban.', $e->getMessage());
        }
    }

    public function testUpdate() {
        // Create a user
        $testUser = [];

        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser');
        $testUser[1]->create();

        // Create a admin
        $testAdmin = [];

        $testAdmin[0] = new User();
        $testAdmin[0]->setUsername('testAdmin');
        $testAdmin[0]->create();

        $testAdmin[1] = new User();
        $testAdmin[1]->setUsername('testAdmin');
        $testAdmin[1]->create();

        // Create a ban
        $testBan = new Ban();
        $testBan->setUserID($testUser[0]->getID());
        $testBan->setAdminID($testAdmin[0]->getID());
        $testBan->setUnbanAdminID($testAdmin[0]->getID());
        $testBan->setReason('testban');
        $testBan->setBanDate(123);
        $testBan->setUnbanDate(124);
        $testBan->setActive(true);
        $testBan->setExpiry(-1);
        $testBan->create();

        // Now update it
        $testBan->setUserID($testUser[1]->getID());
        $testBan->setAdminID($testAdmin[1]->getID());
        $testBan->setUnbanAdminID($testAdmin[1]->getID());
        $testBan->setReason('testban2');
        $testBan->setBanDate(234);
        $testBan->setUnbanDate(2345);
        $testBan->setActive(false);
        $testBan->setExpiry(1000);
        $testBan->update();

        $stmt = $this->_connection->prepare("SELECT `BanID`,`UserID`,`AdminID`,`UnbanAdminID`,`BanReason`,`BanDate`,`UnbanDate`,`BanActive`,`BanExpiry` FROM `Bans` WHERE `BanID`=?");
        $stmt->bind_param('i', $testBan->getID());
        $stmt->execute();
        $stmt->bind_result($banID, $userID, $adminID, $unbanAdminID, $reason, $banDate, $unbanDate, $active, $expiry);

        // Check only one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        if($active == 1) {
            $active = true;
        }
        else if($active == 0) {
            $active = false;
        }

        $this->assertEquals($testBan->getID(), $banID);
        $this->assertEquals($testBan->getUserID(), $userID);
        $this->assertEquals($testBan->getAdminID(), $adminID);
        $this->assertEquals($testBan->getUnbanAdminID(), $unbanAdminID);
        $this->assertEquals($testBan->getReason(), $reason);
        $this->assertEquals($testBan->getBanDate(), $banDate);
        $this->assertEquals($testBan->getUnbanDate(), $unbanDate);
        $this->assertEquals($testBan->getActive(), $active);
        $this->assertEquals($testBan->getExpiry(), $expiry);

        $stmt->close();

        // Clean up
        $testBan->delete();
        foreach($testAdmin as $admin) {
            $admin->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testBlankUpdate() {
        // Create blank ban
        $ban = new Ban();

        // Set expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger the exception
        try {
            $ban->update();
        } catch(BlankObjectException $e) {
            $this->assertEquals('Cannot store a blank Ban.', $e->getMessage());
        }
    }

    public function testDelete() {
        // Create a user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();
        // Create a admin
        $testAdmin = new User();
        $testAdmin->setUsername('testAdmin');
        $testAdmin->create();

        // Create a ban
        $testBan = new Ban();
        $testBan->setUserID($testUser->getID());
        $testBan->setAdminID($testAdmin->getID());
        $testBan->setUnbanAdminID($testAdmin->getID());
        $testBan->setReason('testban');
        $testBan->setBanDate(123);
        $testBan->setUnbanDate(124);
        $testBan->setActive(true);
        $testBan->setExpiry(-1);
        $testBan->create();

        // Save id
        $id = $testBan->getID();

        // Now delete it
        $testBan->delete();

        // Check id is null
        $this->assertNull($testBan->getID());

        // Check its gone
        $stmt = $this->_connection->prepare("SELECT `BanID` FROM `Bans` WHERE `BanID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check no results
        $this->assertEquals(0, $stmt->num_rows);
        $stmt->close();

        // Clean up
        $testAdmin->delete();
        $testUser->delete();
    }

    public function testSelectWithInput() {
        // Create a user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();
        // Create a admin
        $testAdmin = new User();
        $testAdmin->setUsername('testAdmin');
        $testAdmin->create();

        // Create a ban
        $testBan = [];

        $testBan[0] = new Ban();
        $testBan[0]->setUserID($testUser->getID());
        $testBan[0]->setAdminID($testAdmin->getID());
        $testBan[0]->setUnbanAdminID($testAdmin->getID());
        $testBan[0]->setReason('testban');
        $testBan[0]->setBanDate(123);
        $testBan[0]->setUnbanDate(124);
        $testBan[0]->setActive(true);
        $testBan[0]->setExpiry(-1);
        $testBan[0]->create();

        $testBan[1] = new Ban();
        $testBan[1]->setUserID($testUser->getID());
        $testBan[1]->setAdminID($testAdmin->getID());
        $testBan[1]->setUnbanAdminID($testAdmin->getID());
        $testBan[1]->setReason('testban2');
        $testBan[1]->setBanDate(1234);
        $testBan[1]->setUnbanDate(1245);
        $testBan[1]->setActive(false);
        $testBan[1]->setExpiry(123456);
        $testBan[1]->create();

        $testBan[2] = new Ban();
        $testBan[2]->setUserID($testUser->getID());
        $testBan[2]->setAdminID($testAdmin->getID());
        $testBan[2]->setUnbanAdminID($testAdmin->getID());
        $testBan[2]->setReason('testban3');
        $testBan[2]->setBanDate(12345);
        $testBan[2]->setUnbanDate(12456);
        $testBan[2]->setActive(true);
        $testBan[2]->setExpiry(1234223);
        $testBan[2]->create();

        // Get a single ban
        $selectedSingle = Ban::select(array($testBan[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(Ban::class, $selectedSingle[0]);
        $this->assertEquals($testBan[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testBan[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testBan[0]->getAdminID(), $selectedSingle[0]->getAdminID());
        $this->assertEquals($testBan[0]->getUnbanAdminID(), $selectedSingle[0]->getUnbanAdminID());
        $this->assertEquals($testBan[0]->getReason(), $selectedSingle[0]->getReason());
        $this->assertEquals($testBan[0]->getBanDate(), $selectedSingle[0]->getBanDate());
        $this->assertEquals($testBan[0]->getUnbanDate(), $selectedSingle[0]->getUnbanDate());
        $this->assertEquals($testBan[0]->getActive(), $selectedSingle[0]->getActive());
        $this->assertEquals($testBan[0]->getExpiry(), $selectedSingle[0]->getExpiry());

        $selectedMultiple = Ban::select(array($testBan[1]->getID(), $testBan[2]->getID()));

        // Check it is an array
        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));

        // Check the contents are bans
        $this->assertInstanceOf(Ban::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Ban::class, $selectedMultiple[1]);

        if($testBan[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testBan[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testBan[1]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testBan[1]->getAdminID(), $selectedMultiple[$i]->getAdminID());
        $this->assertEquals($testBan[1]->getUnbanAdminID(), $selectedMultiple[$i]->getUnbanAdminID());
        $this->assertEquals($testBan[1]->getReason(), $selectedMultiple[$i]->getReason());
        $this->assertEquals($testBan[1]->getBanDate(), $selectedMultiple[$i]->getBanDate());
        $this->assertEquals($testBan[1]->getUnbanDate(), $selectedMultiple[$i]->getUnbanDate());
        $this->assertEquals($testBan[1]->getActive(), $selectedMultiple[$i]->getActive());
        $this->assertEquals($testBan[1]->getExpiry(), $selectedMultiple[$i]->getExpiry());

        $this->assertEquals($testBan[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testBan[2]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testBan[2]->getAdminID(), $selectedMultiple[$j]->getAdminID());
        $this->assertEquals($testBan[2]->getUnbanAdminID(), $selectedMultiple[$j]->getUnbanAdminID());
        $this->assertEquals($testBan[2]->getReason(), $selectedMultiple[$j]->getReason());
        $this->assertEquals($testBan[2]->getBanDate(), $selectedMultiple[$j]->getBanDate());
        $this->assertEquals($testBan[2]->getUnbanDate(), $selectedMultiple[$j]->getUnbanDate());
        $this->assertEquals($testBan[2]->getActive(), $selectedMultiple[$j]->getActive());
        $this->assertEquals($testBan[2]->getExpiry(), $selectedMultiple[$j]->getExpiry());

        // Clean up
        foreach($testBan as $ban) {
            $ban->delete();
        }
        $testUser->delete();
        $testAdmin->delete();
    }

    public function testSelectAll() {
        // Create a user
        $testUser = new User();
        $testUser->setUsername('testUser');
        $testUser->create();
        // Create a admin
        $testAdmin = new User();
        $testAdmin->setUsername('testAdmin');
        $testAdmin->create();

        // Create a ban
        $testBan = [];

        $testBan[0] = new Ban();
        $testBan[0]->setUserID($testUser->getID());
        $testBan[0]->setAdminID($testAdmin->getID());
        $testBan[0]->setUnbanAdminID($testAdmin->getID());
        $testBan[0]->setReason('testban');
        $testBan[0]->setBanDate(123);
        $testBan[0]->setUnbanDate(124);
        $testBan[0]->setActive(true);
        $testBan[0]->setExpiry(-1);
        $testBan[0]->create();

        $testBan[1] = new Ban();
        $testBan[1]->setUserID($testUser->getID());
        $testBan[1]->setAdminID($testAdmin->getID());
        $testBan[1]->setUnbanAdminID($testAdmin->getID());
        $testBan[1]->setReason('testban2');
        $testBan[1]->setBanDate(1234);
        $testBan[1]->setUnbanDate(1245);
        $testBan[1]->setActive(false);
        $testBan[1]->setExpiry(123456);
        $testBan[1]->create();

        $selectedMultiple = Ban::select(array());

        // Check it is an array
        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));

        // Check the contents are bans
        $this->assertInstanceOf(Ban::class, $selectedMultiple[0]);
        $this->assertInstanceOf(Ban::class, $selectedMultiple[1]);

        if($testBan[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testBan[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testBan[0]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testBan[0]->getAdminID(), $selectedMultiple[$i]->getAdminID());
        $this->assertEquals($testBan[0]->getUnbanAdminID(), $selectedMultiple[$i]->getUnbanAdminID());
        $this->assertEquals($testBan[0]->getReason(), $selectedMultiple[$i]->getReason());
        $this->assertEquals($testBan[0]->getBanDate(), $selectedMultiple[$i]->getBanDate());
        $this->assertEquals($testBan[0]->getUnbanDate(), $selectedMultiple[$i]->getUnbanDate());
        $this->assertEquals($testBan[0]->getActive(), $selectedMultiple[$i]->getActive());
        $this->assertEquals($testBan[0]->getExpiry(), $selectedMultiple[$i]->getExpiry());

        $this->assertEquals($testBan[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testBan[1]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testBan[1]->getAdminID(), $selectedMultiple[$j]->getAdminID());
        $this->assertEquals($testBan[1]->getUnbanAdminID(), $selectedMultiple[$j]->getUnbanAdminID());
        $this->assertEquals($testBan[1]->getReason(), $selectedMultiple[$j]->getReason());
        $this->assertEquals($testBan[1]->getBanDate(), $selectedMultiple[$j]->getBanDate());
        $this->assertEquals($testBan[1]->getUnbanDate(), $selectedMultiple[$j]->getUnbanDate());
        $this->assertEquals($testBan[1]->getActive(), $selectedMultiple[$j]->getActive());
        $this->assertEquals($testBan[1]->getExpiry(), $selectedMultiple[$j]->getExpiry());

        // Clean up
        foreach($testBan as $ban) {
            $ban->delete();
        }
        $testUser->delete();
        $testAdmin->delete();
    }

    public function testEql() {
        // Create a ban
        $testBan = [];

        $testBan[0] = new Ban();
        $testBan[0]->setID(1);
        $testBan[0]->setUserID(1);
        $testBan[0]->setAdminID(2);
        $testBan[0]->setUnbanAdminID(2);
        $testBan[0]->setReason('testban');
        $testBan[0]->setBanDate(123);
        $testBan[0]->setUnbanDate(124);
        $testBan[0]->setActive(true);
        $testBan[0]->setExpiry(-1);

        $testBan[1] = new Ban();
        $testBan[1]->setID(1);
        $testBan[1]->setUserID(1);
        $testBan[1]->setAdminID(2);
        $testBan[1]->setUnbanAdminID(2);
        $testBan[1]->setReason('testban');
        $testBan[1]->setBanDate(123);
        $testBan[1]->setUnbanDate(124);
        $testBan[1]->setActive(true);
        $testBan[1]->setExpiry(-1);

        $testBan[2] = new Ban();
        $testBan[2]->setID(2);
        $testBan[2]->setUserID(2);
        $testBan[2]->setAdminID(3);
        $testBan[2]->setUnbanAdminID(3);
        $testBan[2]->setReason('testban2');
        $testBan[2]->setBanDate(1233);
        $testBan[2]->setUnbanDate(1244);
        $testBan[2]->setActive(false);
        $testBan[2]->setExpiry(12345465);

        // Check same object is eql
        $this->assertTrue($testBan[0]->eql($testBan[0]));

        // Check same details are eql
        $this->assertTrue($testBan[0]->eql($testBan[1]));

        // Check different arent equal
        $this->assertFalse($testBan[0]->eql($testBan[2]));
    }

    public function testGetByActive() {
        // Create a user
        $testUser = [];

        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser');
        $testUser[1]->create();

        // Create a admin
        $testAdmin = [];

        $testAdmin[0] = new User();
        $testAdmin[0]->setUsername('testAdmin');
        $testAdmin[0]->create();

        $testAdmin[1] = new User();
        $testAdmin[1]->setUsername('testAdmin');
        $testAdmin[1]->create();

        // Create a ban
        $testBan = [];

        $testBan[0] = new Ban();
        $testBan[0]->setUserID($testUser[0]->getID());
        $testBan[0]->setAdminID($testAdmin[0]->getID());
        $testBan[0]->setUnbanAdminID($testAdmin[0]->getID());
        $testBan[0]->setReason('testban');
        $testBan[0]->setBanDate(123);
        $testBan[0]->setUnbanDate(124);
        $testBan[0]->setActive(true);
        $testBan[0]->setExpiry(-1);
        $testBan[0]->create();

        $testBan[1] = new Ban();
        $testBan[1]->setUserID($testUser[1]->getID());
        $testBan[1]->setAdminID($testAdmin[1]->getID());
        $testBan[1]->setUnbanAdminID($testAdmin[1]->getID());
        $testBan[1]->setReason('testban2');
        $testBan[1]->setBanDate(1234);
        $testBan[1]->setUnbanDate(1245);
        $testBan[1]->setActive(false);
        $testBan[1]->setExpiry(123456);
        $testBan[1]->create();

        // Check the ban is there
        $selected = Ban::getByActive(true);

        $this->assertTrue(\is_array($selected));
        $this->assertEquals(1, \count($selected));
        $this->assertInstanceOf(Ban::class, $selected[0]);
        $this->assertEquals($testBan[0]->getID(), $selected[0]->getID());
        $this->assertEquals($testBan[0]->getUserID(), $selected[0]->getUserID());
        $this->assertEquals($testBan[0]->getAdminID(), $selected[0]->getAdminID());
        $this->assertEquals($testBan[0]->getUnbanAdminID(), $selected[0]->getUnbanAdminID());
        $this->assertEquals($testBan[0]->getReason(), $selected[0]->getReason());
        $this->assertEquals($testBan[0]->getBanDate(), $selected[0]->getBanDate());
        $this->assertEquals($testBan[0]->getUnbanDate(), $selected[0]->getUnbanDate());
        $this->assertEquals($testBan[0]->getActive(), $selected[0]->getActive());
        $this->assertEquals($testBan[0]->getExpiry(), $selected[0]->getExpiry());

        // Check the ban is there
        $selected = Ban::getByActive(false);

        $this->assertTrue(\is_array($selected));
        $this->assertEquals(1, \count($selected));
        $this->assertInstanceOf(Ban::class, $selected[0]);
        $this->assertEquals($testBan[1]->getID(), $selected[0]->getID());
        $this->assertEquals($testBan[1]->getUserID(), $selected[0]->getUserID());
        $this->assertEquals($testBan[1]->getAdminID(), $selected[0]->getAdminID());
        $this->assertEquals($testBan[1]->getUnbanAdminID(), $selected[0]->getUnbanAdminID());
        $this->assertEquals($testBan[1]->getReason(), $selected[0]->getReason());
        $this->assertEquals($testBan[1]->getBanDate(), $selected[0]->getBanDate());
        $this->assertEquals($testBan[1]->getUnbanDate(), $selected[0]->getUnbanDate());
        $this->assertEquals($testBan[1]->getActive(), $selected[0]->getActive());
        $this->assertEquals($testBan[1]->getExpiry(), $selected[0]->getExpiry());

        // Cleanup
        foreach($testBan as $ban) {
            $ban->delete();
        }
        foreach($testAdmin as $admin) {
            $admin->delete();
        }
        foreach ($testUser as $user) {
            $user->delete();
        }
    }

    public function testGetByUserID() {
        // Create a user
        $testUser = [];

        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser');
        $testUser[1]->create();

        // Create a admin
        $testAdmin = [];

        $testAdmin[0] = new User();
        $testAdmin[0]->setUsername('testAdmin');
        $testAdmin[0]->create();

        $testAdmin[1] = new User();
        $testAdmin[1]->setUsername('testAdmin');
        $testAdmin[1]->create();

        // Create a ban
        $testBan = [];

        $testBan[0] = new Ban();
        $testBan[0]->setUserID($testUser[0]->getID());
        $testBan[0]->setAdminID($testAdmin[0]->getID());
        $testBan[0]->setUnbanAdminID($testAdmin[0]->getID());
        $testBan[0]->setReason('testban');
        $testBan[0]->setBanDate(123);
        $testBan[0]->setUnbanDate(124);
        $testBan[0]->setActive(true);
        $testBan[0]->setExpiry(-1);
        $testBan[0]->create();

        $testBan[1] = new Ban();
        $testBan[1]->setUserID($testUser[1]->getID());
        $testBan[1]->setAdminID($testAdmin[1]->getID());
        $testBan[1]->setUnbanAdminID($testAdmin[1]->getID());
        $testBan[1]->setReason('testban2');
        $testBan[1]->setBanDate(1234);
        $testBan[1]->setUnbanDate(1245);
        $testBan[1]->setActive(false);
        $testBan[1]->setExpiry(123456);
        $testBan[1]->create();

        // Check the ban is there
        $selected = Ban::getByUserID($testUser[0]->getID());

        $this->assertTrue(\is_array($selected));
        $this->assertEquals(1, \count($selected));
        $this->assertInstanceOf(Ban::class, $selected[0]);
        $this->assertEquals($testBan[1]->getID(), $selected[0]->getID());
        $this->assertEquals($testBan[1]->getUserID(), $selected[0]->getUserID());
        $this->assertEquals($testBan[1]->getAdminID(), $selected[0]->getAdminID());
        $this->assertEquals($testBan[1]->getUnbanAdminID(), $selected[0]->getUnbanAdminID());
        $this->assertEquals($testBan[1]->getReason(), $selected[0]->getReason());
        $this->assertEquals($testBan[1]->getBanDate(), $selected[0]->getBanDate());
        $this->assertEquals($testBan[1]->getUnbanDate(), $selected[0]->getUnbanDate());
        $this->assertEquals($testBan[1]->getActive(), $selected[0]->getActive());
        $this->assertEquals($testBan[1]->getExpiry(), $selected[0]->getExpiry());

        // Cleanup
        foreach($testBan as $ban) {
            $ban->delete();
        }
        foreach($testAdmin as $admin) {
            $admin->delete();
        }
        foreach ($testUser as $user) {
            $user->delete();
        }
    }

    public function testGetByAdminID() {
        // Create a user
        $testUser = [];

        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser');
        $testUser[1]->create();

        // Create a admin
        $testAdmin = [];

        $testAdmin[0] = new User();
        $testAdmin[0]->setUsername('testAdmin');
        $testAdmin[0]->create();

        $testAdmin[1] = new User();
        $testAdmin[1]->setUsername('testAdmin');
        $testAdmin[1]->create();

        // Create a ban
        $testBan = [];

        $testBan[0] = new Ban();
        $testBan[0]->setUserID($testUser[0]->getID());
        $testBan[0]->setAdminID($testAdmin[0]->getID());
        $testBan[0]->setUnbanAdminID($testAdmin[0]->getID());
        $testBan[0]->setReason('testban');
        $testBan[0]->setBanDate(123);
        $testBan[0]->setUnbanDate(124);
        $testBan[0]->setActive(true);
        $testBan[0]->setExpiry(-1);
        $testBan[0]->create();

        $testBan[1] = new Ban();
        $testBan[1]->setUserID($testUser[1]->getID());
        $testBan[1]->setAdminID($testAdmin[1]->getID());
        $testBan[1]->setUnbanAdminID($testAdmin[1]->getID());
        $testBan[1]->setReason('testban2');
        $testBan[1]->setBanDate(1234);
        $testBan[1]->setUnbanDate(1245);
        $testBan[1]->setActive(false);
        $testBan[1]->setExpiry(123456);
        $testBan[1]->create();

        // Check the ban is there
        $selected = Ban::getByAdminID($testAdmin[0]->getID());

        $this->assertTrue(\is_array($selected));
        $this->assertEquals(1, \count($selected));
        $this->assertInstanceOf(Ban::class, $selected[0]);
        $this->assertEquals($testBan[1]->getID(), $selected[0]->getID());
        $this->assertEquals($testBan[1]->getUserID(), $selected[0]->getUserID());
        $this->assertEquals($testBan[1]->getAdminID(), $selected[0]->getAdminID());
        $this->assertEquals($testBan[1]->getUnbanAdminID(), $selected[0]->getUnbanAdminID());
        $this->assertEquals($testBan[1]->getReason(), $selected[0]->getReason());
        $this->assertEquals($testBan[1]->getBanDate(), $selected[0]->getBanDate());
        $this->assertEquals($testBan[1]->getUnbanDate(), $selected[0]->getUnbanDate());
        $this->assertEquals($testBan[1]->getActive(), $selected[0]->getActive());
        $this->assertEquals($testBan[1]->getExpiry(), $selected[0]->getExpiry());

        // Cleanup
        foreach($testBan as $ban) {
            $ban->delete();
        }
        foreach($testAdmin as $admin) {
            $admin->delete();
        }
        foreach ($testUser as $user) {
            $user->delete();
        }
    }

    public function testGetByUnbanAdminID() {
        // Create a user
        $testUser = [];

        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser');
        $testUser[1]->create();

        // Create a admin
        $testAdmin = [];

        $testAdmin[0] = new User();
        $testAdmin[0]->setUsername('testAdmin');
        $testAdmin[0]->create();

        $testAdmin[1] = new User();
        $testAdmin[1]->setUsername('testAdmin');
        $testAdmin[1]->create();

        // Create a ban
        $testBan = [];

        $testBan[0] = new Ban();
        $testBan[0]->setUserID($testUser[0]->getID());
        $testBan[0]->setAdminID($testAdmin[0]->getID());
        $testBan[0]->setUnbanAdminID($testAdmin[0]->getID());
        $testBan[0]->setReason('testban');
        $testBan[0]->setBanDate(123);
        $testBan[0]->setUnbanDate(124);
        $testBan[0]->setActive(true);
        $testBan[0]->setExpiry(-1);
        $testBan[0]->create();

        $testBan[1] = new Ban();
        $testBan[1]->setUserID($testUser[1]->getID());
        $testBan[1]->setAdminID($testAdmin[1]->getID());
        $testBan[1]->setUnbanAdminID($testAdmin[1]->getID());
        $testBan[1]->setReason('testban2');
        $testBan[1]->setBanDate(1234);
        $testBan[1]->setUnbanDate(1245);
        $testBan[1]->setActive(false);
        $testBan[1]->setExpiry(123456);
        $testBan[1]->create();

        // Check the ban is there
        $selected = Ban::getByUnbanAdminID($testAdmin[0]->getID());

        $this->assertTrue(\is_array($selected));
        $this->assertEquals(1, \count($selected));
        $this->assertInstanceOf(Ban::class, $selected[0]);
        $this->assertEquals($testBan[1]->getID(), $selected[0]->getID());
        $this->assertEquals($testBan[1]->getUserID(), $selected[0]->getUserID());
        $this->assertEquals($testBan[1]->getAdminID(), $selected[0]->getAdminID());
        $this->assertEquals($testBan[1]->getUnbanAdminID(), $selected[0]->getUnbanAdminID());
        $this->assertEquals($testBan[1]->getReason(), $selected[0]->getReason());
        $this->assertEquals($testBan[1]->getBanDate(), $selected[0]->getBanDate());
        $this->assertEquals($testBan[1]->getUnbanDate(), $selected[0]->getUnbanDate());
        $this->assertEquals($testBan[1]->getActive(), $selected[0]->getActive());
        $this->assertEquals($testBan[1]->getExpiry(), $selected[0]->getExpiry());

        // Cleanup
        foreach($testBan as $ban) {
            $ban->delete();
        }
        foreach($testAdmin as $admin) {
            $admin->delete();
        }
        foreach ($testUser as $user) {
            $user->delete();
        }
    }

    public function testGetByBanDate() {
        // Create a user
        $testUser = [];

        $testUser[0] = new User();
        $testUser[0]->setUsername('testUser');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('testUser');
        $testUser[1]->create();

        // Create a admin
        $testAdmin = [];

        $testAdmin[0] = new User();
        $testAdmin[0]->setUsername('testAdmin');
        $testAdmin[0]->create();

        $testAdmin[1] = new User();
        $testAdmin[1]->setUsername('testAdmin');
        $testAdmin[1]->create();

        // Create a ban
        $testBan = [];

        $testBan[0] = new Ban();
        $testBan[0]->setUserID($testUser[0]->getID());
        $testBan[0]->setAdminID($testAdmin[0]->getID());
        $testBan[0]->setUnbanAdminID($testAdmin[0]->getID());
        $testBan[0]->setReason('testban');
        $testBan[0]->setBanDate(123);
        $testBan[0]->setUnbanDate(124);
        $testBan[0]->setActive(true);
        $testBan[0]->setExpiry(-1);
        $testBan[0]->create();

        $testBan[1] = new Ban();
        $testBan[1]->setUserID($testUser[1]->getID());
        $testBan[1]->setAdminID($testAdmin[1]->getID());
        $testBan[1]->setUnbanAdminID($testAdmin[1]->getID());
        $testBan[1]->setReason('testban2');
        $testBan[1]->setBanDate(1234);
        $testBan[1]->setUnbanDate(1245);
        $testBan[1]->setActive(false);
        $testBan[1]->setExpiry(123456);
        $testBan[1]->create();

        // Check the ban is there
        $selected = Ban::getByBanDate($testBan[0]->getBanDate());

        $this->assertTrue(\is_array($selected));
        $this->assertEquals(1, \count($selected));
        $this->assertInstanceOf(Ban::class, $selected[0]);
        $this->assertEquals($testBan[1]->getID(), $selected[0]->getID());
        $this->assertEquals($testBan[1]->getUserID(), $selected[0]->getUserID());
        $this->assertEquals($testBan[1]->getAdminID(), $selected[0]->getAdminID());
        $this->assertEquals($testBan[1]->getUnbanAdminID(), $selected[0]->getUnbanAdminID());
        $this->assertEquals($testBan[1]->getReason(), $selected[0]->getReason());
        $this->assertEquals($testBan[1]->getBanDate(), $selected[0]->getBanDate());
        $this->assertEquals($testBan[1]->getUnbanDate(), $selected[0]->getUnbanDate());
        $this->assertEquals($testBan[1]->getActive(), $selected[0]->getActive());
        $this->assertEquals($testBan[1]->getExpiry(), $selected[0]->getExpiry());

        // Cleanup
        foreach($testBan as $ban) {
            $ban->delete();
        }
        foreach($testAdmin as $admin) {
            $admin->delete();
        }
        foreach ($testUser as $user) {
            $user->delete();
        }
    }

    public function testSetUserID() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('test');
        $testUser->create();

        // Create a test ban
        $testBan = new Ban();

        // Attempt to set user id
        try {
            $testBan->setUserID($testUser->getID(), true);
            $this->assertEquals($testUser->getID(), $testBan->getID());
        } finally {
            $testUser->delete();
        }
    }

    public function testInvalidUserSetUserID() {
        // Get max user id
        $stmt = Database::getConnection()->prepare("SELECT `UserID` FROM `Users` ORDER BY `UserID` DESCENDING LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($userID);
        if($stmt->fetch()) {
            $tryID = $userID+1;
        }
        else {
            $tryID = 1;
        }

        // Create test ban
        $testBan = new Ban();

        // Set expected exception
        $this->expectException(InvalidUserException::class);

        try {
            $testBan->setUserID($tryID, true);
        } catch(InvalidUserException $e) {
            $this->assertEquals('No user exists with id ' . $tryID, $e->getMessage());
        }
    }

    public function testSetAdminID() {
        // Create a test admin
        $testAdmin = new User();
        $testAdmin->setUsername('test');
        $testAdmin->create();

        // Create a test ban
        $testBan = new Ban();

        // Attempt to set admin id
        try {
            $testBan->setAdminID($testAdmin->getID(), true);
            $this->assertEquals($testAdmin->getID(), $testBan->getID());
        } finally {
            $testAdmin->delete();
        }
    }

    public function testInvalidUserSetAdminID() {
        // Get max user id
        $stmt = Database::getConnection()->prepare("SELECT `UserID` FROM `Users` ORDER BY `UserID` DESCENDING LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($userID);
        if($stmt->fetch()) {
            $tryID = $userID+1;
        }
        else {
            $tryID = 1;
        }

        // Create test ban
        $testBan = new Ban();

        // Set expected exception
        $this->expectException(InvalidUserException::class);

        try {
            $testBan->setAdminID($tryID, true);
        } catch(InvalidUserException $e) {
            $this->assertEquals('No user exists with id ' . $tryID, $e->getMessage());
        }
    }

    public function testSetUnbanAdminID() {
        // Create a test admin
        $testAdmin = new User();
        $testAdmin->setUsername('test');
        $testAdmin->create();

        // Create a test ban
        $testBan = new Ban();

        // Attempt to set admin id
        try {
            $testBan->setUnbanAdminID($testAdmin->getID(), true);
            $this->assertEquals($testAdmin->getID(), $testBan->getID());
        } finally {
            $testAdmin->delete();
        }
    }

    public function testInvalidUserSetUnbanAdminID() {
        // Get max user id
        $stmt = Database::getConnection()->prepare("SELECT `UserID` FROM `Users` ORDER BY `UserID` DESCENDING LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($userID);
        if($stmt->fetch()) {
            $tryID = $userID+1;
        }
        else {
            $tryID = 1;
        }

        // Create test ban
        $testBan = new Ban();

        // Set expected exception
        $this->expectException(InvalidUserException::class);

        try {
            $testBan->setUnbanAdminID($tryID, true);
        } catch(InvalidUserException $e) {
            $this->assertEquals('No user exists with id ' . $tryID, $e->getMessage());
        }
    }

}
