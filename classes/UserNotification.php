<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class UserNotification implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_userID = null;
    private $_notificationID = null;
    private $_acknowledged = null;
    private $_connection = null;

    public function __construct($id = null, $userID = null, $notificationID = null, $acknowledged = null) {
        $this->_id = $id;
        $this->_userID = $userID;
        $this->_notificationID = $notificationID;
        $this->_acknowledged = $acknowledged;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new UserNotification())) {
            throw new BlankObjectException("Cannot store a blank user notification");
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `User_Notifications` (`UserID`,`NotificationID`,`UserNotificationAcknowledged`) VALUES (?,?,?)")) {
                $stmt->bind_param("iii", $this->_userID, $this->_notificationID, Database::toNumeric($this->_acknowledged));
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function update() {
        if($this->eql(new UserNotification())) {
            throw new BlankObjectException("Cannot store a blank user notification");
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `User_Notifications` SET `UserID`=?,`NotificationID`=?,`UserNotificationAcknowledged`=? WHERE `UserNotificationID`=?")) {
                $stmt->bind_param('iiii', $this->_userID, $this->_notificationID, Database::toNumeric($this->_acknowledged), $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `User_Notifications` WHERE `UserNotificationID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_userID == $anotherObject->getUserID() && $this->_notificationID == $anotherObject->getNotificationID() && $this->_acknowledged == $anotherObject->getAcknowledged()) {
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

    public function getUserID() {
        return $this->_userID;
    }

    public function getNotificationID() {
        return $this->_notificationID;
    }

    public function getAcknowledged() {
        return $this->_acknowledged;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setUserID($userID) {
        $this->_userID = $userID;
    }

    public function setNotificationID($notificationID) {
        $this->_notificationID = $notificationID;
    }

    public function setAcknowledged($acknowledged) {
        $this->_acknowledged = $acknowledged;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $userNotificationResult = [];
            $typeArray = [];
            $refs = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `UserNotificationID`,`UserID`,`NotificationID`,`UserNotificationAcknowledged` FROM `User_Notifications` WHERE `UserNotificationID` IN (" . $questionString . ") ")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($userNotificationID, $userID, $notificationID, $acknowledged);
                while($stmt->fetch()) {
                    $userNotification = new UserNotification();
                    $userNotification->setID($userNotificationID);
                    $userNotification->setUserID($userID);
                    $userNotification->setNotificationID($notificationID);
                    $userNotification->setAcknowledged(Database::toBoolean($acknowledged));
                    $userNotificationResult[] = $userNotification;
                }
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
        else if(\is_array($id) &&  \count($id) == 0) {
            $userNotificationResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `UserNotificationID`,`UserID`,`NotificationID`,`UserNotificationAcknowledged` FROM `User_Notifications`")) {
                $stmt->execute();
                $stmt->bind_result($userNotificationID, $userID, $notificationID, $acknowledged);
                while($stmt->fetch()) {
                    $userNotification = new UserNotification();
                    $userNotification->setID($userNotificationID);
                    $userNotification->setUserID($userID);
                    $userNotification->setNotificationID($notificationID);
                    $userNotification->setAcknowledged(Database::toBoolean($acknowledged));
                    $userNotificationResult[] = $userNotification;
                }
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
        else {
            return null;
        }
    }

    public static function getByUser($userID) {
        if($stmt = Database::getConnection()->prepare("SELECT `UserNotificationID` FROM `User_Notifications` WHERE `UserID`=?")) {
            $stmt->bind_param('i', $userID);
            $stmt->execute();
            $stmt->bind_result($userNotificationID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $userNotificationID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return UserNotification::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException("Failed to bind query");
        }
    }
}