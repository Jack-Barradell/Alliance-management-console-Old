<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class NewsEdit implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_editorID = null;
    private $_newsID = null;
    private $_timestamp = null;
    private $_connection = null;

    public function __construct($id = null, $editorID = null, $newsID = null, $timestamp = null) {
        $this->_id = $id;
        $this->_editorID = $editorID;
        $this->_newsID = $newsID;
        $this->_timestamp = $timestamp;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new NewsEdit())) {
            throw new BlankObjectException('Cannot store a blank News Edit.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `News_Edits`(`EditorID`,`NewsID`,`NewsEditTimestamp`) VALUES (?,?,?)")) {
                $stmt->bind_param('iii', $this->_editorID, $this->_newsID, $this->_timestamp);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new NewsEdit())) {
            throw new BlankObjectException('Cannot store a blank News Edit.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `News_Edits` SET `EditorID`=?,`NewsID`=?,`NewsEditTimestamp`=? WHERE `NewsEditID`=?")) {
                $stmt->bind_param('iiii', $this->_editorID, $this->_newsID, $this->_timestamp, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `News_Edits` WHERE `NewsEditID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_editorID == $anotherObject->getEditorID() && $this->_newsID == $anotherObject->getNewsID() && $this->_timestamp == $anotherObject->getTimestamp()) {
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

    public function getEditorID() {
        return $this->_editorID;
    }

    public function getNewsID() {
        return $this->_newsID;
    }

    public function getTimestamp() {
        return $this->_timestamp;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setEditorID($editorID) {
        $this->_editorID = $editorID;
    }

    public function setNewsID($newsID) {
        $this->_newsID = $newsID;
    }

    public function setTimestamp($timestamp) {
        $this->_timestamp = $timestamp;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $newsEditResult = [];
            $refs = [];
            $typeArray = [];
            $typeArray[0] = 'i';
            $questionString = '?';
            foreach($id as $key => $value) {
                $refs[$key] =& $id[$key];
            }
            for($i = 0; $i < \count($id) - 1; $i++) {
                $typeArray[0] .= 'i';
                $questionString .= ',?';
            }
            $param = \array_merge($typeArray, $refs);
            if($stmt = Database::getConnection()->prepare("SELECT `NewsEditID`,`EditorID`,`NewsID`,`NewsEditTimestamp` FROM `News_Edits` WHERE `NewsEditID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($newEditID, $editorID, $newsID, $timestamp);
                while($stmt->fetch()) {
                    $newsEdit = new NewsEdit();
                    $newsEdit->setID($newsEdit);
                    $newsEdit->setEditorID($editorID);
                    $newsEdit->setNewsID($newsID);
                    $newsEdit->setTimestamp($timestamp);
                    $newsEditResult[] = $newsEdit;
                }
                $stmt->close();
                if(\count($newsEditResult) > 0) {
                    return $newsEditResult;
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
            $newsEditResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `NewsEditID`,`EditorID`,`NewsID`,`NewsEditTimestamp` FROM `News_Edits`")) {
                $stmt->execute();
                $stmt->bind_result($newEditID, $editorID, $newsID, $timestamp);
                while($stmt->fetch()) {
                    $newsEdit = new NewsEdit();
                    $newsEdit->setID($newsEdit);
                    $newsEdit->setEditorID($editorID);
                    $newsEdit->setNewsID($newsID);
                    $newsEdit->setTimestamp($timestamp);
                    $newsEditResult[] = $newsEdit;
                }
                $stmt->close();
                if(\count($newsEditResult) > 0) {
                    return $newsEditResult;
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

    public static function getByEditorID($editorID) {
        if($stmt = Database::getConnection()->prepare("SELECT `NewsEditID` FROM `News_Edits` WHERE `EditorID`=?")) {
            $stmt->bind_param('i', $editorID);
            $stmt->execute();
            $stmt->bind_result($newsEditID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $newsEditID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return NewsEdit::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public static function getByNewsID($newsID) {
        if($stmt = Database::getConnection()->prepare("SELECT `NewsEditID` FROM `News_Edits` WHERE `NewsID`=?")) {
            $stmt->bind_param('i', $newsID);
            $stmt->execute();
            $stmt->bind_result($newsEditID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $newsEditID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return NewsEdit::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

}