<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\InvalidUserException;
use AMC\Exceptions\QueryStatementException;

class Ban implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_userID = null;
    private $_adminID = null;
    private $_unbanAdminID = null;
    private $_reason = null;
    private $_banDate = null;
    private $_unbanDate = null;
    private $_active = null;
    private $_expiry = null;
    private $_connection = null;

    public function __construct($id = null, $userID = null, $adminID = null, $unbanAdminID = null, $banReason = null, $banDate = null, $unbanDate = null, $banActive = null, $banExpiry = null) {
        $this->_id = $id;
        $this->_userID = $userID;
        $this->_adminID = $adminID;
        $this->_unbanAdminID = $unbanAdminID;
        $this->_reason = $banReason;
        $this->_banDate = $banDate;
        $this->_unbanDate = $unbanDate;
        $this->_active = $banActive;
        $this->_expiry = $banExpiry;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new Ban())) {
            throw new BlankObjectException('Cannot store a blank Ban.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Bans` (`UserID`,`AdminID`,`UnbanAdminID`,`BanReason`,`BanDate`,`UnbanDate`,`BanActive`,`BanExpiry`) VALUES (?,?,?,?,?,?,?,?)")) {
                $stmt->bind_param('iiisiii', $this->_userID, $this->_adminID, $this->_unbanAdminID, $this->_reason, $this->_banDate, $this->_unbanDate, Database::toNumeric($this->_active), $this->_expiry);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new Ban())) {
            throw new BlankObjectException('Cannot store a blank Ban.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Bans` SET `UserID`=?,`AdminID`=?,`UnbanAdminID`=?,`BanReason`=?,`BanDate`=?,`UnbanDate`=?,`BanActive`=?,`BanExpiry`=? WHERE `BanID`=?")) {
                $stmt->bind_param('iiisiii', $this->_userID, $this->_adminID, $this->_unbanAdminID, $this->_reason, $this->_banDate, $this->_unbanDate, Database::toNumeric($this->_active), $this->_expiry, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Bans` WHERE `BanID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_userID == $anotherObject->getUserID() && $this->_adminID == $anotherObject->getAdminID() && $this->_unbanAdminID == $anotherObject->getUnbanAdminID() && $this->_reason == $anotherObject->getReason() && $this->_banDate == $anotherObject->getBanDate() && $this->_unbanDate == $anotherObject->getUnbanDate() && $this->_active == $anotherObject->getActive()) {
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

    // Setters and Getters

    public function getID() {
        return $this->_id;
    }

    public function getUserID() {
        return $this->_userID;
    }

    public function getAdminID() {
        return $this->_adminID;
    }

    public function getUnbanAdminID() {
        return $this->_unbanAdminID;
    }

    public function getReason() {
        return $this->_reason;
    }

    public function getBanDate() {
        return $this->_banDate;
    }

    public function getUnbanDate() {
        return $this->_unbanDate;
    }

    public function getActive() {
        return $this->_active;
    }

    public function getExpiry() {
        return $this->_expiry;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setUserID($userID, $verify = false) {
        if($verify) {
            if(User::userExists($userID)) {
                $this->_userID = $userID;
            }
            else {
                throw new InvalidUserException('No user exists with id ' . $userID);
            }
        }
        else {
            $this->_userID = $userID;
        }
    }

    public function setAdminID($adminID, $verify = false) {
        if($verify) {
            if(User::userExists($adminID)) {
                $this->_adminID = $adminID;
            }
            else {
                throw new InvalidUserException('No user exists with id ' . $userID);
            }
        }
        else {
            $this->_adminID = $adminID;
        }
    }

    public function setUnbanAdminID($unbanAdminID, $verify = false) {
        if($verify) {
            if(User::userExists($unbanAdminID)) {
                $this->_unbanAdminID = $unbanAdminID;
            }
            else {
                throw new InvalidUserException('No user exists with id ' . $userID);
            }
        }
        else {
            $this->_unbanAdminID = $unbanAdminID;
        }
    }

    public function setReason($banReason) {
        $this->_reason = $banReason;
    }

    public function setBanDate($banDate) {
        $this->_banDate = $banDate;
    }

    public function setUnbanDate($unbanDate) {
        $this->_unbanDate = $unbanDate;
    }

    public function setActive($banActive) {
        $this->_active = $banActive;
    }

    public function setExpiry($banExpiry) {
        $this->_expiry = $banExpiry;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $banResult = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `BanID`,`UserID`,`AdminID`,`UnbanAdminID`,`BanReason`,`BanDate`,`UnbanDate`,`BanActive`,`BanExpiry` FROM `Bans` WHERE `BanID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($banID, $userID, $adminID, $unbanAdminID, $banReason, $banDate, $unbanDate, $banActive, $banExpiry);
                while($stmt->fetch()) {
                    $ban = new Ban();
                    $ban->setID($banID);
                    $ban->setUserID($userID);
                    $ban->setAdminID($adminID);
                    $ban->setUnbanAdminID($unbanAdminID);
                    $ban->setReason($banReason);
                    $ban->setBanDate($banDate);
                    $ban->setUnbanDate($unbanDate);
                    $ban->setActive(Database::toBoolean($banActive));
                    $ban->setExpiry($banExpiry);
                    $banResult[] = $ban;
                }
                $stmt->close();
                if(count($banResult) > 0) {
                    return $banResult;
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
            $banResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `BanID`,`UserID`,`AdminID`,`UnbanAdminID`,`BanReason`,`BanDate`,`UnbanDate`,`BanActive`,`BanExpiry` FROM `Bans`")) {
                $stmt->execute();
                $stmt->bind_result($banID, $userID, $adminID, $unbanAdminID, $banReason, $banDate, $unbanDate, $banActive, $banExpiry);
                while($stmt->fetch()) {
                    $ban = new Ban();
                    $ban->setID($banID);
                    $ban->setUserID($userID);
                    $ban->setAdminID($adminID);
                    $ban->setUnbanAdminID($unbanAdminID);
                    $ban->setReason($banReason);
                    $ban->setBanDate($banDate);
                    $ban->setUnbanDate($unbanDate);
                    $ban->setActive(Database::toBoolean($banActive));
                    $ban->setExpiry($banExpiry);
                    $banResult[] = $ban;
                }
                $stmt->close();
                if(count($banResult) > 0) {
                    return $banResult;
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

    public static function getByActive($active) {
       if($stmt = Database::getConnection()->prepare("SELECT `BanID` FROM `Bans` WHERE `BanActive`=?")) {
           $stmt->bind_param('i', Database::toNumeric($active));
           $input = [];
           $stmt->execute();
           $stmt->bind_result($banID);
           while($stmt->fetch()) {
               $input[] = $banID;
           }
           $stmt->close();
           if(\count($input) > 0) {
               return Ban::get($input);
           }
           else {
               return null;
           }
       }
       else {
           throw new QueryStatementException('Failed to bind query.');
       }
    }

    public static function getByAdminID($adminID) {
        if($stmt = Database::getConnection()->prepare("SELECT `BanID` FROM `Bans` WHERE `AdminID`=?")) {
            $stmt->bind_param('i', $adminID);
            $stmt->execute();
            $stmt->bind_result($banID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $banID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return Ban::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public static function getByUnbanAdminID($unbanAdminID) {
        if($stmt = Database::getConnection()->prepare("SELECT `BanID` FROM `Bans` WHRE `UnbanAdminID`=?")) {
            $stmt->bind_param('i', $unbanAdminID);
            $stmt->execute();
            $stmt->bind_result($banID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $banID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return Ban::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public static function getByBanDate($banDate) {
        if($stmt = Database::getConnection()->prepare("SELECT `BanID` FROM `Bans` WHERE `BanDate`=?")) {
            $stmt->bind_param('i', $banDate);
            $stmt->execute();
            $stmt->bind_result($banID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $banID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return Ban::get($input);
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
        if($stmt = Database::getConnection()->prepare("SELECT `BanID` FROM `Bans` WHREE `UserID`=?")) {
            $stmt->bind_param('i', $userID);
            $stmt->execute();
            $stmt->bind_result($banID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $banID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return Ban::get($input);
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