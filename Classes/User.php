<?php
namespace AMC\Classes;

class User implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_username = null;
    private $_passwordHash = null;
    private $_email = null;
    private $_banned = null;
    private $_activated = null;
    private $_lastLogin = null;
    private $_systemAccount = null;
    private $_connection = null;

    public function __construct($id = null, $username = null, $passwordHash = null, $email = null, $banned = null, $activated = null, $lastLogin = null, $systemAccount = null)  {
        $this->_id = $id;
        $this->_username = $username;
        $this->_passwordHash = $passwordHash;
        $this->_email = $email;
        $this->_banned = $banned;
        $this->_activated = $activated;
        $this->_lastLogin = $lastLogin;
        $this->_systemAccount = $systemAccount;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new User())) {
            //TODO: Throw exception
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Users` (`Username`,`PasswordHash`,`Email`,`Banned`,`Activated`,`LastLogin`,`SystemAccount`) VALUES (?,?,?,?,?,?,?)")) {
                $stmt->bind_param('sssiiii', $this->_username, $this->_passwordHash, $this->_email, Database::toNumeric($this->_banned), Database::toNumeric($this->_activated), $this->_lastLogin, Database::toNumeric($this->_systemAccount));
                $stmt->execute();
                $stmt->close();
            }
            else {
                //TODO: Throw exception
            }
        }
    }

    public function update() {
        if($this->eql(new User())) {
            // TODO: Throw exception
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Users` SET `Username`=?,`PasswordHash`=?,`Email`=?,`Banned`=?,`Activated`=?,`LastLogin`=?,`SystemAccount`=? WHERE `UserID`=?")) {
                $stmt->bind_param('sssiiiii', $this->_username, $this->_passwordHash, $this->_email,  Database::toNumeric($this->_banned), Database::toNumeric($this->_activated), $this->_lastLogin, Database::toNumeric($this->_systemAccount, $this->_id));
                $stmt->execute();
                $stmt->close();
            }
            else {
                //TODO: Throw exception
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Users` WHERE `UserID`=?")) {
            $stmt->bind_param('i', $this->_id);
            $stmt->execute();
            $stmt->close();
            $this->_id = null;
        }
    }

    public function eql($anotherObject) {
        if(\get_class($anotherObject) == \get_class($this)) {
            if($this->_id == $anotherObject->getID() && $this->_username == $anotherObject->getUsername() && $this->_passwordHash == $anotherObject->getPasswordHash() && $this->_email == $anotherObject->getEmail() && $this->_banned == $anotherObject->getBanned() && $this->_activated == $anotherObject->getActivated() && $this->_lastLogin == $anotherObject->getLastLogin() && $this->_systemAccount == $anotherObject->getSystemAccount()) {
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

    public function ban($adminID, $banReason, $banExpiry) {
        //TODO: Implement
    }

    public function unban($unbanAdminID, $reactivateAccount = true) {
        //TODO: Implement
    }

    public function changePassword($newPassword) {
        $newPassword = $this->_connection->real_escape_string($newPassword);
        $options = [
            'cost' => 12,
        ];
        $this->_passwordHash = \password_hash($newPassword, PASSWORD_BCRYPT, $options);
    }

    // Setters and getters

    public function getID() {
        return $this->_id;
    }

    public function getUsername() {
        return $this->_username;
    }

    public function getPasswordHash() {
        return $this->_passwordHash;
    }

    public function getEmail() {
        return $this->_email;
    }

    public function getBanned() {
        return $this->_banned;
    }

    public function getActivated() {
        return $this->_activated;
    }

    public function getLastLogin() {
        return $this->_lastLogin;
    }

    public function getSystemAccount() {
        return $this->_systemAccount;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setUsername($username) {
        $this->_username = $username;
    }

    public function setPasswordHash($passwordHash) {
        $this->_passwordHash = $passwordHash;
    }

    public function setEmail($email) {
        $this->_email = $email;
    }

    public function setBanned($banned) {
        $this->_banned = $banned;
    }

    public function setActivated($activated) {
        $this->_activated = $activated;
    }

    public function setLastLogin($lastLogin) {
        $this->_lastLogin = $lastLogin;
    }

    public function setSystemAccount($systemAccount) {
        $this->_systemAccount = $systemAccount;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0 ) {
            $userResult = [];
            $typeArray =[];
            $refs = [];
            $typeArray[0] = '';
            $questionString = '?';
            foreach($id as $key => $value) {
                $refs[$key] =& $id[$key];
            }
            for($i = 0; $i < \count($id); $i++) {
                $typeArray[0] .= 'i';
                if($i < \count($id) - 1) {
                    $questionString .= ',?';
                }
            }
            $param = \array_merge($typeArray, $refs);
            if($stmt = Database::getConnection()->prepare("SELECT `UserID`,`Username`,`PasswordHash`,`Email`,`Banned`,`Activated`,`LastLogin`,`SystemAccount` FROM `Users` WHERE `UserID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($userID, $username, $passwordHash, $email, $banned, $activated, $lastLogin, $systemAccount);
                while($stmt->fetch()) {
                    $user = new User();
                    $user->setID($userID);
                    $user->setUsername($username);
                    $user->setPasswordHash($passwordHash);
                    $user->setEmail($email);
                    $user->setBanned(Database::toBoolean($banned));
                    $user->setActivated(Database::toBoolean($activated));
                    $user->setLastLogin($lastLogin);
                    $user->setSystemAccount(Database::toBoolean($systemAccount));
                    $userResult[] = $user;
                }
                $stmt->close();
                if(\count($userResult) > 0) {
                    return $userResult;
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
            $userResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `UserID`,`Username`,`PasswordHash`,`Email`,`Banned`,`Activated`,`LastLogin`,`SystemAccount` FROM `Users`")) {
                $stmt->execute();
                $stmt->bind_result($userID, $username, $passwordHash, $email, $banned, $activated, $lastLogin, $systemAccount);
                while($stmt->fetch()) {
                    $user = new User();
                    $user->setID($userID);
                    $user->setUsername($username);
                    $user->setPasswordHash($passwordHash);
                    $user->setEmail($email);
                    $user->setBanned(Database::toBoolean($banned));
                    $user->setActivated(Database::toBoolean($activated));
                    $user->setLastLogin($lastLogin);
                    $user->setSystemAccount(Database::toBoolean($systemAccount));
                    $userResult[] = $user;
                }
                $stmt->close();
                if(\count($userResult) > 0) {
                    return $userResult;
                }
                else {
                    return null;
                }
            }
        }
        else {
            return null;
        }
    }

    public static function userExists($usernameOrID, $includeInactive = false, $returnUsernameOrID = false) {
        // TODO: Implement
    }

    public static function emailExists($email, $includeInactive = false, $returnID = false) {
        // TODO: Implement
    }

    public static function registerAccount($username, $password, $email, $activated = true, $systemAccount = false) {
        $username = \trim(Database::getConnection()->real_escape_string($username));
        $password = \trim(Database::getConnection()->real_escape_string($password));
        $email = \trim(Database::getConnection()->real_escape_string($email));
        if(!\is_string($username) || $username == '') {
            // TODO: Throw exception
            return false;
        }
        if(User::userExists($username, true)) {
            // TODO: Throw exception
            return false;
        }
        if(!\is_string($password) || $password == '') {
            // TODO: Throw exception
            return false;
        }
        if(!\is_string($email) || $email == '') {
            // TODO: Throw exception
            return false;
        }
        if(User::emailExists($email, true)) {
            // TODO: Throw exception
            return false;
        }
        if(!\is_bool($activated)) {
            // TODO: Throw exception
            return false;
        }
        if(!\is_bool($systemAccount)) {
            // TODO: Throw exception
            return false;
        }
        $options = [
            'cost' => 12,
        ];
        $passwordHash = \password_hash($password, PASSWORD_BCRYPT, $options);
        $user = new User();
        $user->setUsername($username);
        $user->setPasswordHash($passwordHash);
        $user->setEmail($email);
        $user->setActivated($activated);
        $user->setBanned(false);
        $user->setLastLogin(-1);
        $user->setSystemAccount($systemAccount);
        $user->commit();
        // TODO: create notification for user registered
        return true;
    }

    public static function login($username, $password) {
        if(User::userExists($username)) {
            $uid = User::userExists($username, false, true);
            $user = User::get($uid);
            if(\password_verify($password, $user->getPasswordHash())) {
                $return = $user;
                $result = 'Success';
            }
            else {
                $return = null;
                $result = "Failed";
            }

        }
        else {
            if(User::userExists($username, true)){
                $return = null;
                $result = "Banned";
            }
            else {
                return null;
            }
        }
        // TODO: Login log
        return $return;
    }

}