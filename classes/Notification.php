<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\InvalidUserException;
use AMC\Exceptions\QueryStatementException;

class Notification implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_notificationBody = null;
    private $_notificationTimestamp = null;
    private $_connection = null;

    public function __construct($id = null, $body = null, $timestamp = null) {
        $this->_id = $id;
        $this->_notificationBody = $body;
        $this->_notificationTimestamp = $timestamp;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new Notification())) {
            throw new BlankObjectException("Cannot store blank notification");
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Notifications` (`NotificationBody`,`NotificationTimestamp`) VALUES (?,?)")) {
                $stmt->bind_param('si', $this->_notificationBody, $this->_notificationTimestamp);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function update() {
        if($this->eql(new Notification())) {
            throw new BlankObjectException("Cannot store blank notification");
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Notifications` SET `NotificationBody`=?,`NotificationTimestamp`=? WHERE `NotificationID`=?")) {
                $stmt->bind_param('sii', $this->_notificationBody, $this->_notificationTimestamp, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Notifications` WHERE `NotificationID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_notificationBody == $anotherObject->getBody() && $this->_notificationTimestamp == $anotherObject->getTimestamp()) {
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

    public function issueToUser($userID) {
        if(User::userExists($userID, true)) {
            // Commit the notification
            $this->commit();

            // Attach it to the user
            $userNotification = new UserNotification();
            $userNotification->setUserID($userID);
            $userNotification->setNotificationID($this->_id);
            $userNotification->setAcknowledged(false);
            $userNotification->commit();
        }
        else {
            throw new InvalidUserException('User ' . $userID . ' does not exist');
        }
    }

    // Getters and setters

    public function getID() {
        return $this->_id;
    }

    public function getBody() {
        return $this->_notificationBody;
    }

    public function getTimestamp() {
        return $this->_notificationTimestamp;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setBody($body) {
        $this->_notificationBody = $body;
    }

    public function setTimestamp($timestamp) {
        $this->_notificationTimestamp = $timestamp;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $notificationResult = [];
            $refs = [];
            $typeArray = [];
            $typeArray[0] = 'i';
            $questionString = '?';
            foreach ($id as $key => $value) {
                $refs[$key] =& $id[$key];
            }
            for($i = 0; $i < \count($id);  $i++) {
                $typeArray[0] .= 'i';
                $questionString .= ',?';
            }
            $param = \array_merge($typeArray, $refs);
            if($stmt = Database::getConnection()->prepare("SELECT `NotificationID`,`NotificationBody`,`NotificationTimestamp` FROM `Notifications` WHERE `NotificationID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($notificationID, $body, $time);
                while($stmt->fetch()) {
                    $notification = new Notification();
                    $notification->setID($notificationID);
                    $notification->setBody($body);
                    $notification->setTimestamp($time);
                    $notificationResult[] = $notification;
                }
                $stmt->close();
                if(\count($notificationResult) > 0) {
                    return $notificationResult;
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
            $notificationResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `NotificationID`,`NotificationBody`,`NotificationTimestamp` FROM `Notifications`")) {
                $stmt->execute();
                $stmt->bind_result($notificationID, $body, $time);
                while($stmt->fetch()) {
                    $notification = new Notification();
                    $notification->setID($notificationID);
                    $notification->setBody($body);
                    $notification->setTimestamp($time);
                    $notificationResult[] = $notification;
                }
                $stmt->close();
                if(\count($notificationResult) > 0) {
                    return $notificationResult;
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