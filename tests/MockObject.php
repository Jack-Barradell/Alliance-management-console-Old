<?php
namespace AMC\Tests;


use AMC\Classes\DataObject;
use AMC\Classes\Getable;
use AMC\Classes\Storable;

class MockObject implements DataObject {
    use Getable;
    use Storable;

    public function create() {
        return 'Create';
    }

    public function update() {
        return 'Update';
    }

    public function delete() {
        return 'Delete';
    }

    public function eql($anotherObject) {
        return 'eql';
    }

    public static function select($id) {
        return 'Select';
    }
}