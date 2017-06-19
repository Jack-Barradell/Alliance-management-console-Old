<?php
namespace AMC\Classes;

trait Getable {

    public static function get($id = []) {
        if(\is_array($id)) {
            $castedArray = \array_map(function($id) { return (int)$id;}, $id);
            $objectArray = self::select($castedArray);
            return $objectArray;
        }
        else if(\is_numeric($id)) {
            $array = [];
            $id = (int)$id;
            $array[] = $id;
            $objectArray = self::select($array);
            if(\is_array($objectArray) && \count($objectArray) == 0) {
                return $objectArray[0];
            }
            else {
                return null;
            }
        }
        else {
            return null;
        }
    }
}