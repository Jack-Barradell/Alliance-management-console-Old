<?php
namespace AMC\Tests;

use AMC\Classes\NewsCategory;
use AMC\Classes\Database;
use PHPUnit\Framework\TestCase;

class NewsCategoryTest extends TestCase {

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

}