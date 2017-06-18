<?php
namespace AMC\Classes;

interface DataObject {

    public function create();

    public function update();

    public function delete();

    public function eql($anotherObject);

    public static function select($id);
}