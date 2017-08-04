<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\DuplicateEntryException;
use AMC\Exceptions\IncorrectTypeException;
use AMC\Exceptions\InvalidGroupException;
use AMC\Exceptions\InvalidUserException;
use AMC\Exceptions\MissingPrerequisiteException;
use AMC\Exceptions\NullGetException;
use AMC\Exceptions\QueryStatementException;

class User implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_factionID = null;
    private $_username = null;
    private $_passwordHash = null;
    private $_email = null;
    private $_banned = null;
    private $_activated = null;
    private $_lastLogin = null;
    private $_systemAccount = null;
    private $_connection = null;

    public function __construct($id = null, $username = null, $passwordHash = null, $email = null, $banned = null, $activated = null, $lastLogin = null, $systemAccount = null, $factionID = null)  {
        $this->_id = $id;
        $this->_username = $username;
        $this->_passwordHash = $passwordHash;
        $this->_email = $email;
        $this->_banned = $banned;
        $this->_activated = $activated;
        $this->_lastLogin = $lastLogin;
        $this->_systemAccount = $systemAccount;
        $this->_factionID = $factionID;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new User())) {
            throw new BlankObjectException('Cannot store blank user');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Users` (`Username`,`PasswordHash`,`Email`,`Banned`,`Activated`,`LastLogin`,`SystemAccount`, `FactionID`) VALUES (?,?,?,?,?,?,?,?)")) {
                $stmt->bind_param('sssiiiii', $this->_username, $this->_passwordHash, $this->_email, Database::toNumeric($this->_banned), Database::toNumeric($this->_activated), $this->_lastLogin, Database::toNumeric($this->_systemAccount), $this->_factionID);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query');
            }
        }
    }

    public function update() {
        if($this->eql(new User())) {
            throw new BlankObjectException('Cannot store blank user');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Users` SET `Username`=?,`PasswordHash`=?,`Email`=?,`Banned`=?,`Activated`=?,`LastLogin`=?,`SystemAccount`=?,`FactionID`=? WHERE `UserID`=?")) {
                $stmt->bind_param('sssiiiiii', $this->_username, $this->_passwordHash, $this->_email,  Database::toNumeric($this->_banned), Database::toNumeric($this->_activated), $this->_lastLogin, Database::toNumeric($this->_systemAccount, $this->_factionID, $this->_id));
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query');
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
        else {
            throw new QueryStatementException("Failed to bind query");
        }
    }

    public function eql($anotherObject) {
        if(\get_class($anotherObject) == \get_class($this)) {
            if($this->_id == $anotherObject->getID() && $this->_username == $anotherObject->getUsername() && $this->_passwordHash == $anotherObject->getPasswordHash() && $this->_email == $anotherObject->getEmail() && $this->_banned == $anotherObject->getBanned() && $this->_activated == $anotherObject->getActivated() && $this->_lastLogin == $anotherObject->getLastLogin() && $this->_systemAccount == $anotherObject->getSystemAccount() && $this->_factionID == $anotherObject->getFactionID()) {
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
        if(self::userExists($adminID)) {
            if(\is_numeric($banExpiry)) {
                $banReason = $this->_connection->real_escape_string($banReason);
                $time = \time();
                $ban = new Ban();
                $ban->setUserID($this->_id);
                $ban->setAdminID($adminID);
                $ban->setBanDate($time);
                $ban->setReason($banReason);
                $ban->setExpiry($banExpiry);
                $ban->commit();
                $this->_banned = true;
                $this->commit();

                // Create admin log
                $admin = User::get($adminID);
                $adminLog = new AdminLog();
                $adminLog->setAdminID($adminID);
                $adminLog->setEvent($admin->getUsername() . ' banned user: ' . $this->_username . ' with id: ' . $this->_id);
                $adminLog->setTimestamp($time);
                $adminLog->commit();

                // Create notification
                $notification = new Notification();
                $notification->setBody('You were banned by ' . $admin->getUsername() . '. Reason: ' . $banReason);
                $notification->setTimestamp($time);
                $notification->issueToUser($this->_id);
            }
            else {
                throw new IncorrectTypeException('AdminID must be an int was given ' . $adminID);
            }
        }
        else {
            throw new InvalidUserException('No admin with ID' . $adminID);
        }
    }

    public function unban($unbanAdminID, $reactivateAccount = true) {
        if($this->_banned) {
            if(self::userExists($unbanAdminID)) {
                if(!\is_null($bans = Ban::getByUserID($this->_id))) {
                    $time = \time();
                    foreach($bans as $ban) {
                        $ban->setActive(false);
                        $ban->setUnbanTime($time);
                        $ban->setUnbanAdminID($unbanAdminID);
                        $ban->commit();
                        $this->_banned = false;
                        $this->_activated = $reactivateAccount;
                        $this->commit();
                    }

                    // Create admin log
                    $admin = User::get($unbanAdminID);
                    $adminLog = new AdminLog();
                    $adminLog->setAdminID($unbanAdminID);
                    $adminLog->setEvent($admin->getUsername() . ' unbanned user: ' . $this->_username . ' with id: ' . $this->_id);
                    $adminLog->setTimestamp($time);
                    $adminLog->commit();

                    // Create notification
                    $notification = new Notification();
                    $notification->setBody('You were unbanned by ' . $admin->getUsername());
                    $notification->setTimestamp($time);
                    $notification->issueToUser($this->_id);
                }
                else {
                    throw new NullGetException('No bans found for userID: ' . $this->_id);
                }
            }
            else {
                throw new InvalidUserException('No admin with ID: ' . $unbanAdminID);
            }
        }
        else {
            throw new MissingPrerequisiteException('Tried to unban a non-banned user with ID: ' . $this->_id);
        }
    }

    public function changePassword($newPassword) {
        $newPassword = $this->_connection->real_escape_string($newPassword);
        $options = [
            'cost' => 12,
        ];
        $this->_passwordHash = \password_hash($newPassword, PASSWORD_BCRYPT, $options);
        $this->commit();

        // Create a notification
        $notification = new Notification();
        $notification->setBody('You changed your password.');
        $notification->setTimestamp(\time());
        $notification->issueToUser($this->_id);
    }

    // Checks if the user specifically has the priv
    public function hasUserPrivilege($privilegeName) {
        $privilegeArray = Privilege::getByName($privilegeName);
        if(\is_null($privilegeArray)) {
            throw new NullGetException("No privilege found with name " . $privilegeName);
        }
        else {
            $privID = $privilegeArray[0]->getID();
            $userPrivs = UserPrivilege::getByUserID($this->_id);
            foreach($userPrivs as $userPriv) {
                // If there is a user priv for the user with matching id to selected priv, then the user has the priv
                if($userPriv->getPrivilegeID() == $privID) {
                    return true;
                }
            }
            // If loop is exited without a return then the user did not have the priv
            return false;
        }
    }

    // Checks if the user has the priv, or if they inherit the priv from a group
    public function hasPrivilege($privilegeName) {
        if($this->hasUserPrivilege($privilegeName)) {
            return true;
        }
        else {
            $groups = $this->getGroups();
            if(\is_array($groups)) {
                foreach($groups as $group) {
                    if($group->hasGroupPrivilege($privilegeName)) {
                        return true;
                    }
                }
            }
            return false;
        }
    }

    // Join table controls

    public function addToGroup($groupID) {
        if(Group::groupExists($groupID)) {
            $userGroup = new UserGroup();
            $userGroup->setUserID($this->_id);
            $userGroup->setGroupID($groupID);
            $userGroup->commit();
        }
        else {
            throw new InvalidGroupException('No group found with id ' .  $groupID);
        }
    }

    public function removeFromGroup($groupID) {
        if(Group::groupExists($groupID)) {
            if($this->isInGroup($groupID)) {
                $userGroups = UserGroup::getByUserID($this->_id);
                foreach($userGroups as $userGroup) {
                    if($userGroup->getGroupID() == $groupID) {
                        $userGroup->toggleDelete();
                        $userGroup->commit();
                    }
                }
            }
            else {
                throw new MissingPrerequisiteException('Tried to remove user with ID ' . $this->_id . ' from group with id ' . $groupID . ' when they are not a member.');
            }
        }
        else {
            throw new InvalidGroupException('No group found with id ' .  $groupID);
        }
    }

    public function isInGroup($groupID) {
        if(Group::groupExists($groupID)) {
            $userGroups = UserGroup::getByUserID($this->_id);
            if(\is_null($userGroups)) {
                return false;
            }
            else {
                foreach($userGroups as $userGroup) {
                    if($userGroup->getGroupID() == $groupID) {
                        return true;
                    }
                }
                return false;
            }
        }
        else {
            throw new InvalidGroupException('No group found with id ' .  $groupID);
        }
    }

    public function getGroups() {
        $userGroups = UserGroup::getByUserID($this->_id);
        if(\is_null($userGroups)) {
            return null;
        }
        else {
            $input = [];
            foreach ($userGroups as $group) {
                $input[] = $group->getGroupID();
            }
            return Group::get($input);
        }
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

    public function getFactionID() {
        return $this->_factionID;
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

    public function setFactionID($id) {
        $this->_factionID = $id;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0 ) {
            $userResult = [];
            $typeArray =[];
            $refs = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `UserID`,`Username`,`PasswordHash`,`Email`,`Banned`,`Activated`,`LastLogin`,`SystemAccount`,`FactionID` FROM `Users` WHERE `UserID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($userID, $username, $passwordHash, $email, $banned, $activated, $lastLogin, $systemAccount, $factionID);
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
                    $user->setFactionID($factionID);
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
                throw new QueryStatementException('Failed to bind query');
            }
        }
        else if(\is_array($id) && \count($id) == 0) {
            $userResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `UserID`,`Username`,`PasswordHash`,`Email`,`Banned`,`Activated`,`LastLogin`,`SystemAccount`,`FactionID` FROM `Users`")) {
                $stmt->execute();
                $stmt->bind_result($userID, $username, $passwordHash, $email, $banned, $activated, $lastLogin, $systemAccount, $factionID);
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
                    $user->setFactionID($factionID);
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
                throw new QueryStatementException('Failed to bind query');
            }
        }
        else {
            return null;
        }
    }

    public static function userExists($usernameOrID, $includeInactive = false, $returnUsernameOrID = false) {
        if(\is_numeric($usernameOrID)) {
            $vars = [];
            $typeArray = [];
            if($includeInactive) {
                $query = "SELECT `Username` FROM `Users` WHERE `UserID`=?";
                $typeArray[0] = 'i';
                $vars[] = $usernameOrID;
            }
            else {
                $query = "SELECT `Username` FROM `Users` WHERE `UserID`=?,`Activated`=?";
                $typeArray[0] = 'ii';
                $vars[] = $usernameOrID;
                $vars[] = Database::toNumeric(false);
            }
            if($stmt = Database::getConnection()->prepare($query)) {
                $param = \array_merge($typeArray, $vars);
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($username);
                $stmt->fetch();
                $stmt->close();
                if(\is_string($username)) {
                    if($returnUsernameOrID) {
                        return $username;
                    }
                    else {
                        return true;
                    }
                }
                else {
                    return false;
                }
            }
            else {
                throw new QueryStatementException('Failed to bind query');
            }
        }
        else if(\is_string($usernameOrID)) {
            $vars = [];
            $typeArray = [];
            if($includeInactive) {
                $query = "SELECT `UserID` FROM `Users` WHERE `Username`=?";
                $typeArray[0] = 's';
                $vars[] = $usernameOrID;
            }
            else {
                $query = "SELECT `UserID` FROM `Users` WHERE `Username`=?,`Activated`=?";
                $typeArray[0] = 'si';
                $vars[] = $usernameOrID;
                $vars[] = Database::toNumeric(false);
            }
            if($stmt = Database::getConnection()->prepare($query)) {
                $param = \array_merge($typeArray, $vars);
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($userID);
                $stmt->fetch();
                $stmt->close();
                if(\is_numeric($userID)) {
                    if($returnUsernameOrID) {
                        return $userID;
                    }
                    else {
                        return true;
                    }
                }
                else {
                    return false;
                }
            }
            else {
                throw new QueryStatementException('Failed to bind query');
            }
        }
        else {
            throw new IncorrectTypeException("Must provide id (int) or string for name was given " . $usernameOrID);
        }
    }

    public static function emailExists($email, $includeInactive = false, $returnID = false) {
        if(\is_string($email)) {
            $typeArray = [];
            $vars = [];
            if($includeInactive) {
                $query = "SELECT `UserID` FROM `Users` WHERE `Email`=?";
                $typeArray[0] = 's';
                $vars[] = $email;
            }
            else {
                $query = "SELECT `UserID` FROM `Users` WHERE `Email`=?,`Activated`=?";
                $typeArray[0] = 'si';
                $vars[] = $email;
                $vars[] = Database::toNumeric(false);
            }
            if($stmt = Database::getConnection()->prepare($query)) {
                $param = \array_merge($typeArray, $vars);
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($userID);
                $stmt->fetch();
                $stmt->close();
                if(\is_numeric($userID)) {
                    if($returnID) {
                        return $userID;
                    }
                    else {
                        return true;
                    }
                }
                else {
                    return false;
                }
            }
            else {
                throw new QueryStatementException('Failed to bind query');
            }
        }
        else {
            throw new IncorrectTypeException('Email must be provided as string was given' . $email);
        }
    }

    public static function registerAccount($username, $password, $email, $activated = true, $systemAccount = false) {
        $username = \trim(Database::getConnection()->real_escape_string($username));
        $password = \trim(Database::getConnection()->real_escape_string($password));
        $email = \trim(Database::getConnection()->real_escape_string($email));
        if(!\is_string($username) || $username == '') {
            throw new IncorrectTypeException('Username must be a string and not be blank was given ' . $username);
        }
        if(User::userExists($username, true)) {
            throw new DuplicateEntryException('User with username ' . $username . ' already exists');
        }
        if(!\is_string($password) || $password == '') {
            throw new IncorrectTypeException('Password must be a string and not be blank was given ' . $password);
        }
        if(!\is_string($email) || $email == '') {
            throw new IncorrectTypeException('Email must be a string and not be blank was given ' . $email);
        }
        if(User::emailExists($email, true)) {
            throw new DuplicateEntryException('User with email ' . $email . ' already exists');
        }
        if(!\is_bool($activated)) {
            throw new IncorrectTypeException('Activated state must be a boolean was given ' . $activated);
        }
        if(!\is_bool($systemAccount)) {
            throw new IncorrectTypeException('System account state must be a boolean was given ' . $systemAccount);
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

        // Create notification
        $notification = new Notification();
        $notification->setBody('Account created.');
        $notification->setTimestamp(\time());
        $notification->issueToUser($user->getID());
    }

    public static function login($username, $password) {
        $time = \time();
        if(User::userExists($username)) {
            $uid = User::userExists($username, false, true);
            $user = User::get($uid);
            if(\password_verify($password, $user->getPasswordHash())) {
                $return = $user;
                $result = 'Success';
                $user->setLastLogin($time);
            }
            else {
                $return = null;
                $result = "Failed";
            }

        }
        else {
            if(User::userExists($username, true)){
                $uid = User::userExists($username, true, true);
                $return = null;
                $result = "Banned";
            }
            else {
                return null;
            }
        }
        $login = new LoginLog();
        $login->setUserID($uid);
        $login->setResult($result);
        $login->setIP($_SERVER['REMOTE_ADDR']);
        $login->setTimestamp($time);
        $login->commit();
        return $return;
    }

}