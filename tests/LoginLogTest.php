<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/LoginLog.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\LoginLog;
use AMC\Classes\Database;
use AMC\Classes\User;
use AMC\Exceptions\BlankObjectException;
use PHPUnit\Framework\TestCase;

class LoginLogTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('jirbj.co.uk', 'testingDB', 'testingDB', 'testingdb');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        // Create and test null constructor
        $loginLog = new LoginLog();

        $this->assertTrue($loginLog->eql(new LoginLog()));
        $this->assertNull($loginLog->getID());
        $this->assertNull($loginLog->getUserID());
        $this->assertNull($loginLog->getResult());
        $this->assertNull($loginLog->getIP());
        $this->assertNull($loginLog->getTimestamp());

        // Create and test a non null constructor
        $loginLog = new LoginLog(1, 2, 'Fail', '123.123.123.123', 123);

        $this->assertFalse($loginLog->eql(new LoginLog()));
        $this->assertEquals(1, $loginLog->getID());
        $this->assertEquals(2, $loginLog->getUserID());
        $this->assertEquals('Fail', $loginLog->getResult());
        $this->assertEquals('123.123.123.123', $loginLog->getIP());
        $this->assertEquals(123, $loginLog->getTimestamp());
    }

    public function testCreate() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('name');
        $testUser->setPasswordHash('hashed');
        $testUser->create();

        // Create a test Login log
        $testLoginLog = new LoginLog();
        $testLoginLog->setUserID($testUser->getID());
        $testLoginLog->setResult('Fail');
        $testLoginLog->setIP('123.123.123.123');
        $testLoginLog->setTimestamp(123);
        $testLoginLog->create();

        // Check id is now an int
        $this->assertInternalType('int', $testLoginLog->getID());

        // Now pull it from the db
        $stmt = $this->_connection->prepare("SELECT `LoginLogID`,`UserID`,`LoginLogResult`,`LoginLogIP`,`LoginLogTimestamp` FROM `Login_Logs` WHERE `LoginLogID`=?");
        $stmt->bind_param('i', $testLoginLog->getID());
        $stmt->execute();
        $stmt->bind_result($loginLogID, $userID, $result, $ip, $timestamp);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        // Now check it
        $this->assertEquals($testLoginLog->getID(), $loginLogID);
        $this->assertEquals($testLoginLog->getUserID(), $userID);
        $this->assertEquals($testLoginLog->getResult(), $result);
        $this->assertEquals($testLoginLog->getIP(), $ip);
        $this->assertEquals($testLoginLog->getTimestamp(), $timestamp);

        $stmt->close();

        // Clean up
        $testLoginLog->delete();
        $testUser->delete();
    }

    public function testBlankCreate() {
        // Create a test login log
        $loginLog = new LoginLog();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        $loginLog->create();
    }

    public function testUpdate() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('name');
        $testUser[0]->setPasswordHash('hashed');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('name2');
        $testUser[1]->setPasswordHash('hashed2');
        $testUser[1]->create();

        // Create a test Login log
        $testLoginLog = new LoginLog();
        $testLoginLog->setUserID($testUser[0]->getID());
        $testLoginLog->setResult('Fail');
        $testLoginLog->setIP('123.123.123.123');
        $testLoginLog->setTimestamp(123);
        $testLoginLog->create();

        // Now update it
        $testLoginLog->setUserID($testUser[1]->getID());
        $testLoginLog->setResult('Success');
        $testLoginLog->setIP('321.321.321.321');
        $testLoginLog->setTimestamp(12345);
        $testLoginLog->update();

        // Now pull it from the db
        $stmt = $this->_connection->prepare("SELECT `LoginLogID`,`UserID`,`LoginLogResult`,`LoginLogIP`,`LoginLogTimestamp` FROM `Login_Logs` WHERE `LoginLogID`=?");
        $stmt->bind_param('i', $testLoginLog->getID());
        $stmt->execute();
        $stmt->bind_result($loginLogID, $userID, $result, $ip, $timestamp);

        // Check there is one result
        $this->assertEquals(1, $stmt->num_rows);

        $stmt->fetch();

        // Now check it
        $this->assertEquals($testLoginLog->getID(), $loginLogID);
        $this->assertEquals($testLoginLog->getUserID(), $userID);
        $this->assertEquals($testLoginLog->getResult(), $result);
        $this->assertEquals($testLoginLog->getIP(), $ip);
        $this->assertEquals($testLoginLog->getTimestamp(), $timestamp);

        $stmt->close();

        // Clean up
        $testLoginLog->delete();
        $testUser->delete();
    }

    public function testBlankUpdate() {
        // Create a test login log
        $loginLog = new LoginLog();

        // Set the expected exception
        $this->expectException(BlankObjectException::class);

        // Trigger it
        $loginLog->update();
    }

    public function testDelete() {
        // Create a test user
        $testUser = new User();
        $testUser->setUsername('name');
        $testUser->setPasswordHash('hashed');
        $testUser->create();

        // Create a test Login log
        $testLoginLog = new LoginLog();
        $testLoginLog->setUserID($testUser->getID());
        $testLoginLog->setResult('Fail');
        $testLoginLog->setIP('123.123.123.123');
        $testLoginLog->setTimestamp(123);
        $testLoginLog->create();

        // Store the id
        $id = $testLoginLog->getID();

        // Now delete it
        $testLoginLog->delete();

        // Check id is now null
        $this->assertNull($testLoginLog->getID());

        // Now check its gone
        $stmt = $this->_connection->prepare("SELECT `LoginLogID` FROM `Login_Logs` WHERE `LoginLogID`=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Check there are no results
        $this->assertEquals(0, $stmt->num_rows);

        $stmt->close();

        // Clean up
        $testUser->delete();
    }

    public function testSelectWithInput() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('name');
        $testUser[0]->setPasswordHash('hashed');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('name2');
        $testUser[1]->setPasswordHash('hashed2');
        $testUser[1]->create();

        // Create a test Login log
        $testLoginLog = [];
        $testLoginLog[0] = new LoginLog();
        $testLoginLog[0]->setUserID($testUser[0]->getID());
        $testLoginLog[0]->setResult('Fail');
        $testLoginLog[0]->setIP('123.123.123.123');
        $testLoginLog[0]->setTimestamp(123);
        $testLoginLog[0]->create();

        $testLoginLog[1] = new LoginLog();
        $testLoginLog[1]->setUserID($testUser[0]->getID());
        $testLoginLog[1]->setResult('Fail2');
        $testLoginLog[1]->setIP('123.123.321.321');
        $testLoginLog[1]->setTimestamp(12345);
        $testLoginLog[1]->create();

        $testLoginLog[2] = new LoginLog();
        $testLoginLog[2]->setUserID($testUser[1]->getID());
        $testLoginLog[2]->setResult('Fail3');
        $testLoginLog[2]->setIP('321.321.321.321');
        $testLoginLog[2]->setTimestamp(12345678);
        $testLoginLog[2]->create();

        // Select a single and check it
        $selectedSingle = LoginLog::select(array($testLoginLog[0]->getID()));

        $this->assertTrue(\is_array($selectedSingle));
        $this->assertEquals(1, \count($selectedSingle));
        $this->assertInstanceOf(LoginLog::class, $selectedSingle[0]);
        $this->assertEquals($testLoginLog[0]->getID(), $selectedSingle[0]->getID());
        $this->assertEquals($testLoginLog[0]->getUserID(), $selectedSingle[0]->getUserID());
        $this->assertEquals($testLoginLog[0]->getResult(), $selectedSingle[0]->getResult());
        $this->assertEquals($testLoginLog[0]->getIP(), $selectedSingle[0]->getIP());
        $this->assertEquals($testLoginLog[0]->getTimestamp(), $selectedSingle[0]->getTimestamp());

        // Select multiple and check them
        $selectedMultiple = LoginLog::select(array($testLoginLog[1]->getID(), $testLoginLog[2]->getID()));

        // Check it is correct
        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(LoginLog::class, $selectedMultiple[0]);
        $this->assertInstanceOf(LoginLog::class, $selectedMultiple[1]);

        if($testLoginLog[1]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testLoginLog[1]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testLoginLog[1]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testLoginLog[1]->getResult(), $selectedMultiple[$i]->getResult());
        $this->assertEquals($testLoginLog[1]->getIP(), $selectedMultiple[$i]->getIP());
        $this->assertEquals($testLoginLog[1]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());

        $this->assertEquals($testLoginLog[2]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testLoginLog[2]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testLoginLog[2]->getResult(), $selectedMultiple[$j]->getResult());
        $this->assertEquals($testLoginLog[2]->getIP(), $selectedMultiple[$j]->getIP());
        $this->assertEquals($testLoginLog[2]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());

        // Clean up
        foreach($testLoginLog as $log) {
            $log->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testSelectAll() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('name');
        $testUser[0]->setPasswordHash('hashed');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('name2');
        $testUser[1]->setPasswordHash('hashed2');
        $testUser[1]->create();

        // Create a test Login log
        $testLoginLog = [];
        $testLoginLog[0] = new LoginLog();
        $testLoginLog[0]->setUserID($testUser[0]->getID());
        $testLoginLog[0]->setResult('Fail');
        $testLoginLog[0]->setIP('123.123.123.123');
        $testLoginLog[0]->setTimestamp(123);
        $testLoginLog[0]->create();

        $testLoginLog[1] = new LoginLog();
        $testLoginLog[1]->setUserID($testUser[1]->getID());
        $testLoginLog[1]->setResult('Fail2');
        $testLoginLog[1]->setIP('123.123.321.321');
        $testLoginLog[1]->setTimestamp(12345);
        $testLoginLog[1]->create();

        // Select multiple and check them
        $selectedMultiple = LoginLog::select(array());

        // Check it is correct
        $this->assertTrue(\is_array($selectedMultiple));
        $this->assertEquals(2, \count($selectedMultiple));
        $this->assertInstanceOf(LoginLog::class, $selectedMultiple[0]);
        $this->assertInstanceOf(LoginLog::class, $selectedMultiple[1]);

        if($testLoginLog[0]->getID() == $selectedMultiple[0]->getID()) {
            $i = 0;
            $j = 1;
        }
        else {
            $i = 1;
            $j = 0;
        }

        $this->assertEquals($testLoginLog[0]->getID(), $selectedMultiple[$i]->getID());
        $this->assertEquals($testLoginLog[0]->getUserID(), $selectedMultiple[$i]->getUserID());
        $this->assertEquals($testLoginLog[0]->getResult(), $selectedMultiple[$i]->getResult());
        $this->assertEquals($testLoginLog[0]->getIP(), $selectedMultiple[$i]->getIP());
        $this->assertEquals($testLoginLog[0]->getTimestamp(), $selectedMultiple[$i]->getTimestamp());

        $this->assertEquals($testLoginLog[1]->getID(), $selectedMultiple[$j]->getID());
        $this->assertEquals($testLoginLog[1]->getUserID(), $selectedMultiple[$j]->getUserID());
        $this->assertEquals($testLoginLog[1]->getResult(), $selectedMultiple[$j]->getResult());
        $this->assertEquals($testLoginLog[1]->getIP(), $selectedMultiple[$j]->getIP());
        $this->assertEquals($testLoginLog[1]->getTimestamp(), $selectedMultiple[$j]->getTimestamp());

        // Clean up
        foreach($testLoginLog as $log) {
            $log->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testEql() {
        // Create a test Login log
        $testLoginLog = [];

        $testLoginLog[0] = new LoginLog();
        $testLoginLog[0]->setUserID(1);
        $testLoginLog[0]->setResult('Fail');
        $testLoginLog[0]->setIP('123.123.123.123');
        $testLoginLog[0]->setTimestamp(123);

        $testLoginLog[1] = new LoginLog();
        $testLoginLog[1]->setUserID(1);
        $testLoginLog[1]->setResult('Fail');
        $testLoginLog[1]->setIP('123.123.123.123');
        $testLoginLog[1]->setTimestamp(123);

        $testLoginLog[0] = new LoginLog();
        $testLoginLog[0]->setUserID(2);
        $testLoginLog[0]->setResult('Fail2');
        $testLoginLog[0]->setIP('123.123.321.321');
        $testLoginLog[0]->setTimestamp(12345);

        // Check same object is eql
        $this->assertTrue($testLoginLog[0]->eql($testLoginLog[0]));

        // Check same details are eql
        $this->assertTrue($testLoginLog[0]->eql($testLoginLog[1]));

        // Check different arent equal
        $this->assertFalse($testLoginLog[0]->eql($testLoginLog[2]));
    }

    public function testGetByUserID() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('name');
        $testUser[0]->setPasswordHash('hashed');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('name2');
        $testUser[1]->setPasswordHash('hashed2');
        $testUser[1]->create();

        // Create a test Login log
        $testLoginLog = [];
        $testLoginLog[0] = new LoginLog();
        $testLoginLog[0]->setUserID($testUser[0]->getID());
        $testLoginLog[0]->setResult('Fail');
        $testLoginLog[0]->setIP('123.123.123.123');
        $testLoginLog[0]->setTimestamp(123);
        $testLoginLog[0]->create();

        $testLoginLog[1] = new LoginLog();
        $testLoginLog[1]->setUserID($testUser[1]->getID());
        $testLoginLog[1]->setResult('Fail2');
        $testLoginLog[1]->setIP('123.123.321.321');
        $testLoginLog[1]->setTimestamp(12345);
        $testLoginLog[1]->create();

        // Pull it by user id
        $selected = LoginLog::getByUserID($testUser[0]->getID());

        // Check it is correct
        $this->assertTrue(\is_array($selected));
        $this->assertEquals(1, \count($selected));
        $this->assertInstanceOf(LoginLog::class, $selected[0]);

        $this->assertEquals($testLoginLog[0]->getID(), $selected[0]->getID());
        $this->assertEquals($testLoginLog[0]->getUserID(), $selected[0]->getUserID());
        $this->assertEquals($testLoginLog[0]->getResult(), $selected[0]->getResult());
        $this->assertEquals($testLoginLog[0]->getIP(), $selected[0]->getIP());
        $this->assertEquals($testLoginLog[0]->getTimestamp(), $selected[0]->getTimestamp());

        // Clean up
        foreach($testLoginLog as $log) {
            $log->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testGetByIP() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('name');
        $testUser[0]->setPasswordHash('hashed');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('name2');
        $testUser[1]->setPasswordHash('hashed2');
        $testUser[1]->create();

        // Create a test Login log
        $testLoginLog = [];
        $testLoginLog[0] = new LoginLog();
        $testLoginLog[0]->setUserID($testUser[0]->getID());
        $testLoginLog[0]->setResult('Fail');
        $testLoginLog[0]->setIP('123.123.123.123');
        $testLoginLog[0]->setTimestamp(123);
        $testLoginLog[0]->create();

        $testLoginLog[1] = new LoginLog();
        $testLoginLog[1]->setUserID($testUser[1]->getID());
        $testLoginLog[1]->setResult('Fail2');
        $testLoginLog[1]->setIP('123.123.321.321');
        $testLoginLog[1]->setTimestamp(12345);
        $testLoginLog[1]->create();

        // Pull it by ip
        $selected = LoginLog::getByIP('123.123.123.123');

        // Check it is correct
        $this->assertTrue(\is_array($selected));
        $this->assertEquals(1, \count($selected));
        $this->assertInstanceOf(LoginLog::class, $selected[0]);

        $this->assertEquals($testLoginLog[0]->getID(), $selected[0]->getID());
        $this->assertEquals($testLoginLog[0]->getUserID(), $selected[0]->getUserID());
        $this->assertEquals($testLoginLog[0]->getResult(), $selected[0]->getResult());
        $this->assertEquals($testLoginLog[0]->getIP(), $selected[0]->getIP());
        $this->assertEquals($testLoginLog[0]->getTimestamp(), $selected[0]->getTimestamp());

        // Clean up
        foreach($testLoginLog as $log) {
            $log->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testGetByTimestamp() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('name');
        $testUser[0]->setPasswordHash('hashed');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('name2');
        $testUser[1]->setPasswordHash('hashed2');
        $testUser[1]->create();

        // Create a test Login log
        $testLoginLog = [];
        $testLoginLog[0] = new LoginLog();
        $testLoginLog[0]->setUserID($testUser[0]->getID());
        $testLoginLog[0]->setResult('Fail');
        $testLoginLog[0]->setIP('123.123.123.123');
        $testLoginLog[0]->setTimestamp(123);
        $testLoginLog[0]->create();

        $testLoginLog[1] = new LoginLog();
        $testLoginLog[1]->setUserID($testUser[1]->getID());
        $testLoginLog[1]->setResult('Fail2');
        $testLoginLog[1]->setIP('123.123.321.321');
        $testLoginLog[1]->setTimestamp(12345);
        $testLoginLog[1]->create();

        // Pull it by timestamp
        $selected = LoginLog::getByTimestamp(123);

        // Check it is correct
        $this->assertTrue(\is_array($selected));
        $this->assertEquals(1, \count($selected));
        $this->assertInstanceOf(LoginLog::class, $selected[0]);

        $this->assertEquals($testLoginLog[0]->getID(), $selected[0]->getID());
        $this->assertEquals($testLoginLog[0]->getUserID(), $selected[0]->getUserID());
        $this->assertEquals($testLoginLog[0]->getResult(), $selected[0]->getResult());
        $this->assertEquals($testLoginLog[0]->getIP(), $selected[0]->getIP());
        $this->assertEquals($testLoginLog[0]->getTimestamp(), $selected[0]->getTimestamp());

        // Clean up
        foreach($testLoginLog as $log) {
            $log->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

    public function testGetByResult() {
        // Create a test user
        $testUser = [];
        $testUser[0] = new User();
        $testUser[0]->setUsername('name');
        $testUser[0]->setPasswordHash('hashed');
        $testUser[0]->create();

        $testUser[1] = new User();
        $testUser[1]->setUsername('name2');
        $testUser[1]->setPasswordHash('hashed2');
        $testUser[1]->create();

        // Create a test Login log
        $testLoginLog = [];
        $testLoginLog[0] = new LoginLog();
        $testLoginLog[0]->setUserID($testUser[0]->getID());
        $testLoginLog[0]->setResult('Fail');
        $testLoginLog[0]->setIP('123.123.123.123');
        $testLoginLog[0]->setTimestamp(123);
        $testLoginLog[0]->create();

        $testLoginLog[1] = new LoginLog();
        $testLoginLog[1]->setUserID($testUser[1]->getID());
        $testLoginLog[1]->setResult('Success');
        $testLoginLog[1]->setIP('123.123.321.321');
        $testLoginLog[1]->setTimestamp(12345);
        $testLoginLog[1]->create();

        // Pull it by result
        $selected = LoginLog::getByResult('Fail');

        // Check it is correct
        $this->assertTrue(\is_array($selected));
        $this->assertEquals(1, \count($selected));
        $this->assertInstanceOf(LoginLog::class, $selected[0]);

        $this->assertEquals($testLoginLog[0]->getID(), $selected[0]->getID());
        $this->assertEquals($testLoginLog[0]->getUserID(), $selected[0]->getUserID());
        $this->assertEquals($testLoginLog[0]->getResult(), $selected[0]->getResult());
        $this->assertEquals($testLoginLog[0]->getIP(), $selected[0]->getIP());
        $this->assertEquals($testLoginLog[0]->getTimestamp(), $selected[0]->getTimestamp());

        // Clean up
        foreach($testLoginLog as $log) {
            $log->delete();
        }
        foreach($testUser as $user) {
            $user->delete();
        }
    }

}
