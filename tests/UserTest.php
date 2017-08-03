<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/User.php';
require '../classes/Faction.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\User;
use AMC\Classes\Database;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('jirbj.co.uk', 'testingDB', 'testingDB', 'testingdb');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {

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

    public function testBan() {

    }

    public function testIncorrectTypeBan() {

    }

    public function testInvalidUserBan() {

    }

    public function testUnban() {

    }

    public function testNullGetUnban() {

    }

    public function testInvalidUserUnban() {

    }

    public function testMissingPrerequisiteUnban() {

    }

    public function testChangePassword() {

    }

    public function testUserExists() {

    }

    public function testIncorrectTypeUserExists() {

    }

    public function testEmailExists() {

    }

    public function testIncorrectTypeEmailExists() {

    }

    public function testRegisterAccount() {

    }

    public function testIncorrectTypeRegisterAccount() {

    }

    public function testDuplicateEntryRegisterAccount() {

    }

    public function testLogin() {

    }

}
