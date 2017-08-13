<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class NewsComment {
    use Getable;
    use Storable;

    private $_id = null;
    private $_newsID = null;
    private $_userID = null;
    private $_body = null;
    private $_timestamp = null;
    private $_connection = null;

    public function __construct($id = null, $newsID = null, $userID = null, $body = null, $timestamp = null) {
        $this->_id = $id;
        $this->_newsID = $newsID;
        $this->_userID = $userID;
        $this->_body = $body;
        $this->_timestamp = $timestamp;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new NewsComment())) {
            throw new BlankObjectException('Cannot store a blank News Comment.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `News_Comments`(`NewsID`,`UserID`,`NewsCommentBody`,`NewsCommentTimestamp`) VALUES (?,?,?,?)")) {
                $stmt->bind_param('iisi', $this->_newsID, $this->_userID, $this->_body, $this->_timestamp);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new NewsComment())) {
            throw new BlankObjectException('Cannot store a blank News Comment.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `News_Comments` SET `NewsID`=?,`UserID`=?,`NewsCommentBody`=?,`NewsCommentTimestamp`=? WHERE `NewsCommentID`=?")) {
                $stmt->bind_param('iisii', $this->_newsID, $this->_userID, $this->_body, $this->_timestamp, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `News_Comments` WHERE `NewsCommentID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_newsID == $anotherObject->getNewsID() && $this->_userID == $anotherObject->getUserID() && $this->_body == $anotherObject->getBody() && $this->_timestamp == $anotherObject->getTimestamp()) {
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

    public function getNewsID() {
        return $this->_newsID;
    }

    public function getUserID() {
        return $this->_userID;
    }

    public function getBody() {
        return $this->_body;
    }

    public function getTimestamp() {
        return $this->_timestamp;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setNewsID($newsID) {
        $this->_newsID = $newsID;
    }

    public function setUserID($userID) {
        $this->_userID = $userID;
    }

    public function setBody($body) {
        $this->_body = $body;
    }

    public function setTimestamp($timestamp) {
        $this->_timestamp = $timestamp;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $newsCommentResult = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `NewsCommentID`,`NewsID`,`UserID`,`NewsCommentBody`,`NewsCommentTimestamp` FROM `News_Comments` WHERE `NewsCommentID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($newsCommentID, $newsID, $userID, $body, $timestamp);
                while($stmt->fetch()) {
                    $newsComment = new NewsComment();
                    $newsComment->setID($newsCommentID);
                    $newsComment->setNewsID($newsID);
                    $newsComment->setUserID($userID);
                    $newsComment->setBody($body);
                    $newsComment->setTimestamp($timestamp);
                    $newsCommentResult[] = $newsComment;
                }
                $stmt->close();
                if(\count($newsCommentResult) > 0) {
                    return $newsCommentResult;
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
            $newsCommentResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `NewsCommentID`,`NewsID`,`UserID`,`NewsCommentBody`,`NewsCommentTimestamp` FROM `News_Comments`")) {
                $stmt->execute();
                $stmt->bind_result($newsCommentID, $newsID, $userID, $body, $timestamp);
                while($stmt->fetch()) {
                    $newsComment = new NewsComment();
                    $newsComment->setID($newsCommentID);
                    $newsComment->setNewsID($newsID);
                    $newsComment->setUserID($userID);
                    $newsComment->setBody($body);
                    $newsComment->setTimestamp($timestamp);
                    $newsCommentResult[] = $newsComment;
                }
                $stmt->close();
                if(\count($newsCommentResult) > 0) {
                    return $newsCommentResult;
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

    public static function getByNewsID($newsID) {
        if($stmt = Database::getConnection()->prepare("SELECT `NewsCommentID` FROM `News_Comments` WHERE `NewsID`=?")) {
            $stmt->bind_param('i', $newsID);
            $stmt->execute();
            $stmt->bind_result($newsCommentID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $newsCommentID;
            }
            if(\count($input) > 0) {
                return NewsComment::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public static function getByUserID($userID) {
        if($stmt = Database::getConnection()->prepare("SELECT `NewsCommentID` FROM `News_Comments` WHERE `UserID`=?")) {
            $stmt->bind_param('i', $userID);
            $stmt->execute();
            $stmt->bind_result($newsCommentID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $newsCommentID;
            }
            if(\count($input) > 0) {
                return NewsComment::get($input);
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