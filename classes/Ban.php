<?php
namespace AMC\Classes;

class Ban implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_userID = null;
    private $_adminID = null;
    private $_unbanAdminID = null;
    private $_banReason = null;
    private $_banDate = null;
    private $_unbanDate = null;
    private $_banActive = null;
    private $_connection = null;

    public function __construct($id = null, $userID = null, $adminID = null, $unbanAdminID = null, $banReason = null, $banDate = null, $unbanDate = null, $banActive = null) {
        $this->_id = $id;
        $this->_userID = $userID;
        $this->_adminID = $adminID;
        $this->_unbanAdminID = $unbanAdminID;
        $this->_banReason = $banReason;
        $this->_banDate = $banDate;
        $this->_unbanDate = $unbanDate;
        $this->_banActive = $banActive;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new Ban())) {
            //TODO: Throw exception
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Bans` (`UserID`,`AdminID`,`UnbanAdminID`,`BanReason`,`BanDate`,`UnbanDate`,`BanActive`) VALUES (?,?,?,?,?,?,?)")) {
                $stmt->bind_param('iiisiii', $this->_userID, $this->_adminID, $this->_unbanAdminID, $this->_banReason, $this->_banDate, $this->_unbanDate, Database::toNumeric($this->_banActive));
                $stmt->execute();
                $stmt->close();
            }
            else {
                //TODO: Throw exception
            }
        }
    }

    public function update() {
        if($this->eql(new Ban())) {
            //TODO: Throw exception
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Bans` SET `UserID`=?,`AdminID`=?,`UnbanAdminID`=?,`BanReason`=?,`BanDate`=?,`UnbanDate`=?,`BanActive`=? WHERE `BanID`=?")) {
                $stmt->bind_param('iiisiii', $this->_userID, $this->_adminID, $this->_unbanAdminID, $this->_banReason, $this->_banDate, $this->_unbanDate, Database::toNumeric($this->_banActive));
                $stmt->execute();
                $stmt->close();
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
    }

    public function eql($anotherObject) {
        if(\get_class($this) == \get_class($anotherObject)) {
            if($this->_id == $anotherObject->getID() && $this->_userID == $anotherObject->getUserID() && $this->_adminID == $anotherObject->getAdminID() && $this->_unbanAdminID == $anotherObject->getUnbanAdminID() && $this->_banReason == $anotherObject->getBanReason() && $this->_banDate == $anotherObject->getBanDate() && $this->_unbanDate == $anotherObject->unbanDate() && $this->_banActive == $anotherObject->getBanActive()) {
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

    public function getBanReason() {
        return $this->_banReason;
    }

    public function getBanDate() {
        return $this->_banDate;
    }

    public function getUnbanDate() {
        return $this->_unbanDate;
    }

    public function getBanActive() {
        return $this->_banActive;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setUserID($userID) {
        $this->_userID = $userID;
    }

    public function setAdminID($adminID) {
        $this->_adminID = $adminID;
    }

    public function setUnbanAdminID($unbanAdminID) {
        $this->_unbanAdminID = $unbanAdminID;
    }

    public function setBanReason($banReason) {
        $this->_banReason = $banReason;
    }

    public function setBanDate($banDate) {
        $this->_banDate = $banDate;
    }

    public function setUnbanDate($unbanDate) {
        $this->_unbanDate = $unbanDate;
    }

    public function setBanActive($banActive) {
        $this->_banActive = $banActive;
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
            if($stmt = Database::getConnection()->prepare("SELECT `BanID`,`UserID`,`AdminID`,`UnbanAdminID`,`BanReason`,`BanDate`,`UnbanDate`,`BanActive` FROM `Bans` WHERE `BanID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($banID, $userID, $adminID, $unbanAdminID, $banReason, $banDate, $unbanDate, $banActive);
                while($stmt->fetch()) {
                    $ban = new Ban();
                    $ban->setID($banID);
                    $ban->setUserID($userID);
                    $ban->setAdminID($adminID);
                    $ban->setUnbanAdminID($unbanAdminID);
                    $ban->setBanReason($banReason);
                    $ban->setBanDate($banDate);
                    $ban->setUnbanDate($unbanDate);
                    $ban->setBanActive(Database::toBoolean($banActive));
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
                //TODO: Throw exception
            }
        }
        else if(\is_array($id) && \count($id) == 0) {
            $banResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `BanID`,`UserID`,`AdminID`,`UnbanAdminID`,`BanReason`,`BanDate`,`UnbanDate`,`BanActive` FROM `Bans`")) {
                $stmt->execute();
                $stmt->bind_result($banID, $userID, $adminID, $unbanAdminID, $banReason, $banDate, $unbanDate, $banActive);
                while($stmt->fetch()) {
                    $ban = new Ban();
                    $ban->setID($banID);
                    $ban->setUserID($userID);
                    $ban->setAdminID($adminID);
                    $ban->setUnbanAdminID($unbanAdminID);
                    $ban->setBanReason($banReason);
                    $ban->setBanDate($banDate);
                    $ban->setUnbanDate($unbanDate);
                    $ban->setBanActive(Database::toBoolean($banActive));
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
                //TODO: Throw exception
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
           $stmt->bindResult($banID);
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
    }

    public static function getByAdminID($adminID) {
        //TODO: Implement
    }

    public static function getByUnbanAdminID($unbanAdminID) {
        //TODO: Implement
    }

    public static function getByBanDate($banDate) {
        //TODO: Implement
    }
}