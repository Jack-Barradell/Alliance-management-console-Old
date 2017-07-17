<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class Message implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_senderID = null;
    private $_subject = null;
    private $_body = null;
    private $_timestamp = null;
    private $_connection = null;

    public function __construct($id = null, $senderID = null, $subject = null, $body = null, $timestamp = null) {
        $this->_id = $id;
        $this->_senderID = $senderID;
        $this->_subject = $subject;
        $this->_body = $body;
        $this->_timestamp = $timestamp;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new Message())) {
            throw new BlankObjectException("Cannot store a blank message");
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Messages`(`SenderID`,`MessageSubject`,`MessageBody`,`MessageTimestamp`) VALUES (?,?,?,?)")) {
                $stmt->bind_param('issi', $this->_senderID, $this->_subject, $this->_body, $this->_timestamp);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function update() {
        if($this->eql(new Message())) {
            throw new BlankObjectException("Cannot store a blank message");
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Messages` SET `SenderID`=?,`MessageSubject`=?,`MessageBody`=?,`MessageTimestamp`=? WHERE `MessageID`=?")) {
                $stmt->bind_param('issii', $this->_senderID, $this->_subject, $this->_body, $this->_timestamp, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Messages` WHERE `MessageID`=?")) {
            $stmt->bind_param('i', $this->_id);
            $stmt->execute();
            $stmt->close();
            $this->_id = null;
        }
        else {
            throw new QueryStatementException("Failed to bind query");
        }
    }

    public function eql($anotherObject) {
        if(\get_class($this) == \get_class($anotherObject)) {
            if($this->_id == $anotherObject->getID() && $this->_senderID == $anotherObject->getSenderID() && $this->_subject == $anotherObject->getSubject() && $this->_body == $anotherObject->getBody() && $this->_timestamp == $anotherObject->getTimestamp()) {
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

    public function getSenderID() {
        return $this->_senderID;
    }

    public function getSubject() {
        return $this->_subject;
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

    public function setSenderID($senderID) {
        $this->_senderID = $senderID;
    }

    public function setSubject($subject) {
        $this->_subject = $subject;
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
            $messageResult = [];
            $refs = [];
            $typeArray = [];
            $typeArray[0] = 'i';
            $questionString = '?';
            foreach($id as $key => $value) {
                $refs[$key] =& $id[$key];
            }
            for($i = 0; $i < \count($id); $i++) {
                $typeArray[0] .= 'i';
                $questionString .= ',?';
            }
            $param = \array_merge($typeArray, $refs);
            if($stmt = Database::getConnection()->prepare("SELECT `MessageID`,`SenderID`,`MessageSubject`,`MessageBody`,`MessageTimestamp` FROM `Messages` WHERE `MessageID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_param($messageID, $senderID, $subject, $body, $timestamp);
                while($stmt->fetch()) {
                    $message = new Message();
                    $message->setID($messageID);
                    $message->setSenderID($senderID);
                    $message->setSubject($subject);
                    $message->setBody($body);
                    $message->setTimestamp($timestamp);
                    $messageResult[] = $message;
                }
                $stmt->close();
                if(\count($messageResult) > 0) {
                    return $messageResult;
                }
                else {
                    return null;
                }
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
        else if(\is_array($id) && \count($id) == 0) {
            $messageResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `MessageID`,`SenderID`,`MessageSubject`,`MessageBody`,`MessageTimestamp` FROM `Messages`")) {
                $stmt->execute();
                $stmt->bind_param($messageID, $senderID, $subject, $body, $timestamp);
                while($stmt->fetch()) {
                    $message = new Message();
                    $message->setID($messageID);
                    $message->setSenderID($senderID);
                    $message->setSubject($subject);
                    $message->setBody($body);
                    $message->setTimestamp($timestamp);
                    $messageResult[] = $message;
                }
                $stmt->close();
                if(\count($messageResult) > 0) {
                    return $messageResult;
                }
                else {
                    return null;
                }
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
        else {
            return null;
        }
    }

}