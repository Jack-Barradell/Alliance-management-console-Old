<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/User.php';
require '../classes/LoginLog.php';
require '../classes/Ban.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\Database;
use AMC\Classes\User;
use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\IncorrectTypeException;
use PHPUnit\Framework\TestCase;


// TODO ################### NEED TO TEST EXCEPTION SITUATIONS ####################

class UserTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        //TODO: Setup testing db
        Database::newConnection();
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        $user = new User();

        // Check the object initialised to have null vars
        self::assertTrue($user->eql(new User()));
        self::assertNull($user->getID());
        self::assertNull($user->getFactionID());
        self::assertNull($user->getUsername());
        self::assertNull($user->getPasswordHash());
        self::assertNull($user->getEmail());
        self::assertNull($user->getBanned());
        self::assertNull($user->getActivated());
        self::assertNull($user->getLastLogin());
        self::assertNull($user->getSystemAccount());
    }

    public function testCreate() {

        // Create a test user
        $testUser = new User();

        // Fill in details of a user
        $testUser->setUsername('testName');
        $testUser->setPasswordHash("testHash");
        $testUser->setEmail("test@email.com");
        $testUser->setBanned(false);
        $testUser->setActivated(false);
        $testUser->setLastLogin(123);
        $testUser->setSystemAccount(false);

        // Now run the method
        $testUser->create();

        // Check the user has a int id
        self::assertInternalType('int', $testUser->getID());

        // Pull it from the db
        $stmt = $this->_connection->prepare("SELECT `UserID`,`Username`,`PasswordHash`,`Email`,`Banned`,`Activated`,`LastLogin`,`SystemAccount` FROM `Users` WHERE `UserID`=?");
        $stmt->bind_param('i', $testUser->getID());
        $stmt->execute();
        $stmt->bind_result($userID, $username, $passwordHash, $email, $banned, $activated, $lastLogin, $systemAccount);

        // Check only one result
        self::assertEquals(1, $stmt->num_rows);

        $stmt->fetch();
        $stmt->close();

        // Convert numerics to booleans
        if($banned == 1) {
            $convertedBanned = true;
        }
        else {
            $convertedBanned = false;
        }
        if($activated == 1) {
            $convertedActivated = true;
        }
        else {
            $convertedActivated = false;
        }
        if($systemAccount == 1) {
            $convertedSysAcc = true;
        }
        else {
            $convertedSysAcc = false;
        }

        // Compare the results of the query to the object attempted to be stored
        self::assertEquals($testUser->getID(), $userID);
        self::assertEquals($testUser->getUsername(), $username);
        self::assertEquals($testUser->getPasswordHash(), $passwordHash);
        self::assertEquals($testUser->getEmail(), $email);
        self::assertEquals($testUser->getBanned(), $convertedBanned);
        self::assertEquals($testUser->getActivated(), $convertedActivated);
        self::assertEquals($testUser->getLastLogin(), $lastLogin);
        self::assertEquals($testUser->getSystemAccount(), $convertedSysAcc);

        // Pass user to next function
        return $testUser;
    }

    public function testCreateBlankException() {

        // Create the  user
        $blankUser = new User();

        // Set the expected exception
        self::expectException(BlankObjectException::class);

        // Trigger the exception
        $blankUser->create();
    }

    /**
     * @depends testCreate
     */
    public function testUpdate(User $testUser) {

        // Update the users details
        $testUser->setUsername('testName2');
        $testUser->setPasswordHash("testHash2");
        $testUser->setEmail("test2@email.com");
        $testUser->setBanned(true);
        $testUser->setActivated(true);
        $testUser->setLastLogin(1234);
        $testUser->setSystemAccount(true);

        // Call the update function
        $testUser->update();

        // Pull it from the db
        $stmt = $this->_connection->prepare("SELECT `UserID`,`Username`,`PasswordHash`,`Email`,`Banned`,`Activated`,`LastLogin`,`SystemAccount` FROM `Users` WHERE `UserID`=?");
        $stmt->bind_param('i', $testUser->getID());
        $stmt->execute();
        $stmt->bind_result($userID, $username, $passwordHash, $email, $banned, $activated, $lastLogin, $systemAccount);

        // Check only one result
        self::assertEquals(1, $stmt->num_rows);

        $stmt->fetch();
        $stmt->close();

        // Convert numerics to booleans
        if($banned == 1) {
            $convertedBanned = true;
        }
        else {
            $convertedBanned = false;
        }
        if($activated == 1) {
            $convertedActivated = true;
        }
        else {
            $convertedActivated = false;
        }
        if($systemAccount == 1) {
            $convertedSysAcc = true;
        }
        else {
            $convertedSysAcc = false;
        }

        // Compare the results of the query to the object attempted to be stored
        self::assertEquals($testUser->getID(), $userID);
        self::assertEquals($testUser->getUsername(), $username);
        self::assertEquals($testUser->getPasswordHash(), $passwordHash);
        self::assertEquals($testUser->getEmail(), $email);
        self::assertEquals($testUser->getBanned(), $convertedBanned);
        self::assertEquals($testUser->getActivated(), $convertedActivated);
        self::assertEquals($testUser->getLastLogin(), $lastLogin);
        self::assertEquals($testUser->getSystemAccount(), $convertedSysAcc);

        // Pass user to next function
        return $testUser;
    }

    public function testUpdateBlankException() {

        // Create the  user
        $blankUser = new User();

        // Set the expected exception
        self::expectException(BlankObjectException::class);

        // Trigger the exception
        $blankUser->update();
    }

    /**
     * @depends testUpdate
     */
    public function testDelete(User $testUser) {

        // Grab the ID before delete removes it
        $id = $testUser->getID();

        // Call the delete function
        $testUser->delete();

        // Now try to pull the data from db
        // Pull it from the db
        $stmt = $this->_connection->prepare("SELECT `UserID`,`Username`,`PasswordHash`,`Email`,`Banned`,`Activated`,`LastLogin`,`SystemAccount` FROM `Users` WHERE `UserID`=?");
        $stmt->bind_param('i', id);
        $stmt->execute();

        // Check there are no results now
        self::assertEquals(0, $stmt->num_rows);

        $stmt->close();
    }

    public function testEql() {
        $testUser = [];

        $testUser[0]->setUsername('testName');
        $testUser[0]->setPasswordHash("testHash");
        $testUser[0]->setEmail("test@email.com");
        $testUser[0]->setBanned(false);
        $testUser[0]->setActivated(false);
        $testUser[0]->setLastLogin(123);
        $testUser[0]->setSystemAccount(false);

        $testUser[1]->setUsername('testName2');
        $testUser[1]->setPasswordHash("testHash2");
        $testUser[1]->setEmail("test2@email.com");
        $testUser[1]->setBanned(true);
        $testUser[1]->setActivated(true);
        $testUser[1]->setLastLogin(1234);
        $testUser[1]->setSystemAccount(true);

        $testUser[2]->setUsername('testName2');
        $testUser[2]->setPasswordHash("testHash2");
        $testUser[2]->setEmail("test2@email.com");
        $testUser[2]->setBanned(true);
        $testUser[2]->setActivated(true);
        $testUser[2]->setLastLogin(1234);
        $testUser[2]->setSystemAccount(true);

        // Check same object are equal
        self::assertTrue($testUser[0]->eql($testUser[0]));

        // Check different objects are not equal
        self::assertTrue($testUser[0]->eql($testUser[1]));

        // Check same details are equal
        self::assertTrue($testUser[1]->eql($testUser[2]));
    }

    public function testUserExists() {
        // Create a test user
        $testUser = new User();

        $testUser->setUsername('testName');
        $testUser->setPasswordHash("testHash");
        $testUser->setEmail("test@email.com");
        $testUser->setBanned(false);
        $testUser->setActivated(true);
        $testUser->setLastLogin(123);
        $testUser->setSystemAccount(false);
        $testUser->create();

        // Check they exist by ID
        self::assertTrue(User::userExists($testUser->getID()));

        // Check they exist by username
        self::assertTrue(User::userExists('testName'));

        // Check username is returned by ID
        self::assertEquals('testName', User::userExists($testUser->getID(), false, true));

        // Check ID is returned by username
        self::assertEquals($testUser->getID(), User::userExists('testName', false, true));

        // Now de activate the user
        $testUser->setActivated(false);
        $testUser->update();

        // Check old tests return false
        self::assertFalse(User::userExists($testUser->getID()));
        self::assertFalse(User::userExists('testName'));
        self::assertFalse(User::userExists($testUser->getID(), false, true));
        self::assertFalse(User::userExists('testName', false, true));

        // Now check they work when including inactive
        // Check they exist by ID
        self::assertTrue(User::userExists($testUser->getID()), true);

        // Check they exist by username
        self::assertTrue(User::userExists('testName'), true);

        // Check username is returned by ID
        self::assertEquals('testName', User::userExists($testUser->getID(), true, true));

        // Check ID is returned by username
        self::assertEquals($testUser->getID(), User::userExists('testName', true, true));

        // Clean up the user from db
        $testUser->delete();
    }

    public function testUserExistsInputException() {

        // Set the correct exception expectation
        self::expectException(IncorrectTypeException::class);

        // Now trigger it
        User::userExists(false, false, false);
    }

    public function testEmailExists() {
        // Create a test user
        $testUser = new User();

        $testUser->setUsername('testName');
        $testUser->setPasswordHash("testHash");
        $testUser->setEmail("test@email.com");
        $testUser->setBanned(false);
        $testUser->setActivated(true);
        $testUser->setLastLogin(123);
        $testUser->setSystemAccount(false);
        $testUser->create();

        // Check if the email is in
        self::assertTrue(User::emailExists("test@email.com"));

        // Check correct id is returned
        self::assertEmpty($testUser->getID(), User::emailExists("test@email.com", false, true));

        // Now de activate the user
        $testUser->setActivated(false);
        $testUser->update();

        // Check it no longer shows up
        self::assertFalse(User::emailExists("test@email.com"));
        self::assertFalse(User::emailExists("test@email.com", false, true));

        // Check it shows up when including inactive
        // Check if the email is in
        self::assertTrue(User::emailExists("test@email.com"), true, true);

        // Check correct id is returned
        self::assertEmpty($testUser->getID(), User::emailExists("test@email.com", true, true));

        // Clean up the user from db
        $testUser->delete();
    }

    public function testEmailExistsInputException() {

        // Set the correct exception expectation
        self::expectException(IncorrectTypeException::class);

        // Now trigger it
        User::emailExists(false, false, false);
        User::emailExists(1, false, false);
    }

    public function testRegisterAccount() {
        //TODO: Implement
    }

    public function testSelectWithInput() {
        //TODO: Implement
    }

    public function testSelectAll() {
        //TODO: Implement
    }

    public function testLogin() {
        //TODO: Implement
    }

    public function testBan() {
        //TODO: Implement
    }

    public function testUnban() {
        //TODO: Implement
    }

}
