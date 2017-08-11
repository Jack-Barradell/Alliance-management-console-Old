<?php
namespace AMC\Tests;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Rank.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Classes\UserRank;
use AMC\Classes\Database;
use PHPUnit\Framework\TestCase;

class UserRankTest extends TestCase {

    private $_connection;

    public function setUp() {
        parent::setUp();
        Database::newConnection('jirbj.co.uk', 'testingDB', 'testingDB', 'testingdb');
        $this->_connection = Database::getConnection();
    }

    public function testConstruct() {
        //TODO: Implement
    }

    public function testCreate() {
        //TODO: Implement
    }

    public function testBlankCreate() {
        //TODO: Implement
    }

    public function testUpdate() {
        //TODO: Implement
    }

    public function testBlankUpdate() {
        //TODO: Implement
    }

    public function testDelete() {
        //TODO: Implement
    }

    public function testSelectWithInput() {
        //TODO: Implement
    }

    public function testSelectAll() {
        //TODO: Implement
    }

    public function testEql() {
        //TODO: Implement
    }

    public function testGetByUserID() {
        //TODO: Implement
    }

    public function testGetByRankID() {
        //TODO: Implement
    }

}
