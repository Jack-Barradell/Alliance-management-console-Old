<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class IntelligenceType implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_name = null;
    private $_connection = null;

    public function __construct($id = null, $name = null) {
        $this->_id = $id;
        $this->_name = $name;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new IntelligenceType())) {
            throw new BlankObjectException('Cannot store a blank Intelligence type.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Intelligence_Types`(`IntelligenceTypeName`) VALUES (?)")) {
                $stmt->bind_param('s', $this->_name);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new IntelligenceType())) {
            throw new BlankObjectException('Cannot store a blank Intelligence Type.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Intelligence_Types` SET `IntelligenceTypeName`=? WHERE `IntelligenceTypeID`=?")) {
                $stmt->bind_param('si', $this->_name, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Intelligence_Types` WHERE `IntelligenceTypeID`=?")) {
            $stmt->bind_param('i', $this->_id);
            $stmt->execute();
            $stmt->close();
            $this->_id = null;
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public function eql($anotherObject) {
        //TODO: Implement
    }

    // Setters and getters

    public function getID() {
        return $this->_id;
    }

    public function getName() {
        return $this->_name;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setName($name) {
        $this->_name = $name;
    }

    // Statics

    public static function select($id) {
        //TODO: Implement
    }
}