<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\IncorrectTypeException;
use AMC\Exceptions\QueryStatementException;

class Rank implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_name = null;
    private $_salary = null;
    private $_image = null;
    private $_connection = null;

    public function __construct($id = null, $name = null, $salary = null, $image = null) {
        $this->_id = $id;
        $this->_name = $name;
        $this->_salary = $salary;
        $this->_image = $image;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new Rank())) {
            throw new BlankObjectException('Cannot store a blank Rank.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Ranks`(`RankName`,`RankSalary`,`RankImage`) VALUES (?,?,?)")) {
                $stmt->bind_param('sis', $this->_name, $this->_salary, $this->_image);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new Rank())) {
            throw new BlankObjectException('Cannot store a blank Rank.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Ranks` SET `RankName`=?,`RankSalary`=?,`RankImage`=? WHERE `RankID`=?")) {
                $stmt->bind_param('sisi', $this->_name, $this->_salary, $this->_image, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Ranks` WHERE `RankID`=?")) {
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
        if(\get_class($this) == \get_class($anotherObject)) {
            if($this->_id == $anotherObject->getID() && $this->_name == $anotherObject->getName() && $this->_salary == $anotherObject->getSalary() && $this->_image == $anotherObject->getImage()) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    // Getters and setters

    public function getID() {
        return $this->_id;
    }

    public function getName() {
        return $this->_name;
    }

    public function getSalary() {
        return $this->_salary;
    }

    public function getImage() {
        return $this->_image;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setName($name) {
        $this->_name = $name;
    }

    public function setSalary($salary) {
        $this->_salary = $salary;
    }

    public function setImage($image) {
        $this->_image = $image;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $rankResult = [];
            $typeArray = [];
            $refs = [];
            $typeArray[0] = 'i';
            $questionString = '?';
            foreach ($id as $key => $value) {
                $refs[$key] =& $id[$key];
            }
            for($i = 0; $i < \count($id) - 1; $i++) {
                $typeArray[0] = 'i';
                $typeArray = ',?';
            }
            $param = \array_merge($typeArray, $refs);
            if($stmt = Database::getConnection()->prepare("SELECT `RankID`,`RankName`,`RankSalary`,`RankImage` FROM `Ranks` WHERE `RankID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($rankID, $name, $salary, $image);
                while($stmt->fetch()) {
                    $rank = new Rank();
                    $rank->setID($rankID);
                    $rank->setName($name);
                    $rank->setSalary($salary);
                    $rank->setImage($image);
                    $rankResult[] = $rank;
                }
                $stmt->close();
                if(\count($rankResult) > 0) {
                    return $rankResult;
                }
                else {
                    return null;
                }
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
        else if(\is_array($id) && \count($id) == 0) {
            if($stmt = Database::getConnection()->prepare("SELECT `RankID`,`RankName`,`RankSalary`,`RankImage` FROM `Ranks`")) {
                $stmt->execute();
                $stmt->bind_result($rankID, $name, $salary, $image);
                while($stmt->fetch()) {
                    $rank = new Rank();
                    $rank->setID($rankID);
                    $rank->setName($name);
                    $rank->setSalary($salary);
                    $rank->setImage($image);
                    $rankResult[] = $rank;
                }
                $stmt->close();
                if(\count($rankResult) > 0) {
                    return $rankResult;
                }
                else {
                    return null;
                }
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
        else {
            return null;
        }
    }

    public static function rankExists($rankID) {
        if(\is_numeric($rankID)) {
            if($stmt = Database::getConnection()->prepare('SELECT `RankID` FROM `Ranks` WHERE `RankID`=?')) {
                $stmt->bind_param('i', $rankID);
                $stmt->execute();
                if ($stmt->num_rows == 1) {
                    $stmt->close();
                    return true;
                } else {
                    $stmt->close();
                    return false;
                }
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
        else {
            throw new IncorrectTypeException('Rank exists must be passed an int, was given ' . \gettype($rankID));
        }
    }

}