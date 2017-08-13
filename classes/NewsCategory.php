<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class NewsCategory implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_name = null;
    private $_image = null;
    private $_connection = null;

    public function __construct($id = null, $name = null, $image = null) {
        $this->_id = $id;
        $this->_name = $name;
        $this->_image = $image;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new NewsCategory())) {
            throw new BlankObjectException('Cannot store a blank News Category.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `News_Categories`(`NewsCategoryName`,`NewsCategoryImage`) VALUES (?,?)")) {
                $stmt->bind_param('ss', $this->_name, $this->_image);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new NewsCategory())) {
            throw new BlankObjectException('Cannot store a blank News Category.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `News_Categories` SET `NewsCategoryName`=?,`NewsCategoryImage`=? WHERE `NewsCategoryID`=?")) {
                $stmt->bind_param('ssi', $this->_name, $this->_image, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `News_Categories` WHERE `NewsCategoryID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_name == $anotherObject->getName() && $this->_image == $anotherObject->getImage()) {
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

    // Setters and getters

    public function getID() {
        return $this->_id;
    }

    public function getName() {
        return $this->_name;
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

    public function setImage($image) {
        $this->_image = $image;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $newsCategoryResult = [];
            $refs = [];
            $typeArray = [];
            $typeArray[0] = 'i';
            $questionString = '?';
            foreach($id as $key => $value) {
                $refs[$key] =& $id[$key];
            }
            for($i = 0; $i < \count($id) - 1; $i++) {
                $typeArray .= 'i';
                $questionString .= ',?';
            }
            $param = \array_merge($typeArray, $refs);
            if($stmt = Database::getConnection()->prepare("SELECT `NewsCategoryID`,`NewsCategoryName`,`NewsCategoryImage` FROM `News_Categories` WHERE `NewsCategoryID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($newsCategoryID, $name, $image);
                while($stmt->fetch()) {
                    $newsCategory = new NewsCategory();
                    $newsCategory->setID($newsCategoryID);
                    $newsCategory->setName($name);
                    $newsCategory->setImage($image);
                    $newsCategoryResult[] = $newsCategory;
                }
                $stmt->close();
                if(\count($newsCategoryResult) > 0) {
                    return $newsCategoryResult;
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
            $newsCategoryResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `NewsCategoryID`,`NewsCategoryName`,`NewsCategoryImage` FROM `News_Categories`")) {
                $stmt->execute();
                $stmt->bind_result($newsCategoryID, $name, $image);
                while($stmt->fetch()) {
                    $newsCategory = new NewsCategory();
                    $newsCategory->setID($newsCategoryID);
                    $newsCategory->setName($name);
                    $newsCategory->setImage($image);
                    $newsCategoryResult[] = $newsCategory;
                }
                $stmt->close();
                if(\count($newsCategoryResult) > 0) {
                    return $newsCategoryResult;
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
}