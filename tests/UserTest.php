<?php
namespace AMC\Tests;

use AMC\Classes\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase {

    public function testConstruct() {
        $user = new User();

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

}
