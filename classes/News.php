<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class News implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_authorID = null;
    private $_newsCategoryID = null;
    private $_title = null;
    private $_thumbnail = null;
    private $_teaser = null;
    private $_body = null;
    private $_published = null;
    private $_membersOnly = null;
    private $_commentsAllowed = null;
    private $_connection = null;

    public function __construct($id = null, $authorID = null, $newsCategoryID = null, $title = null, $thumbnail = null, $teaser = null, $body = null, $published = null, $membersOnly = null, $commentsAllowed = null) {
        $this->_id = $id;
        $this->_authorID = $authorID;
        $this->_newsCategoryID = $newsCategoryID;
        $this->_title = $title;
        $this->_thumbnail = $thumbnail;
        $this->_teaser = $teaser;
        $this->_body = $body;
        $this->_published = $published;
        $this->_membersOnly = $membersOnly;
        $this->_commentsAllowed = $commentsAllowed;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new News())) {
            throw new BlankObjectException('Cannot store blank News.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `News`(`AuthorID`,`NewsCategoryID`,`NewsTitle`,`NewsThumbnail`,`NewsTeaser`,`NewsBody`,`NewsPublished`,`NewsMembersOnly`,`NewsCommentsAllowed`) VALUES (?,?,?,?,?,?,?,?,?)")) {
                $stmt->bind_param('iissssiii', $this->_authorID, $this->_newsCategoryID, $this->_title, $this->_thumbnail, $this->_teaser, $this->_body, Database::toNumeric($this->_published), Database::toNumeric($this->_membersOnly), Database::toNumeric($this->_commentsAllowed));
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new News())) {
            throw new BlankObjectException('Cannot store blank News.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `News` SET `AuthorID`=?,`NewsCategoryID`=?,`NewsTitle`=?,`NewsThumbnail`=?,`NewsTeaser`=?,`NewsBody`=?,`NewsPublished`=?,`NewsMembersOnly`=?,`NewsCommentsAllowed`=? WHERE `NewsID`=?")) {
                $stmt->bind_param('iissssiiii', $this->_authorID, $this->_newsCategoryID, $this->_title, $this->_thumbnail, $this->_teaser, $this->_body, Database::toNumeric($this->_published), Database::toNumeric($this->_membersOnly), Database::toNumeric($this->_commentsAllowed), $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `News` WHERE `NewsID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_authorID == $anotherObject->getAuthorID() && $this->_newsCategoryID == $anotherObject->getNewsCategoryID() && $this->_title == $anotherObject->getTitle() && $this->_thumbnail == $anotherObject->getThumbnail() && $this->_teaser == $anotherObject->getTeaser() && $this->_body == $anotherObject->getBody() && $this->_published == $anotherObject->getPublished() && $this->_membersOnly == $anotherObject->getMembersOnly() && $this->_commentsAllowed == $anotherObject->getCommentsAllowed()) {
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

    public function getAuthorID() {
        return $this->_authorID;
    }

    public function getNewsCategoryID() {
        return $this->_newsCategoryID;
    }

    public function getTitle() {
        return $this->_title;
    }

    public function getThumbnail() {
        return $this->_thumbnail;
    }

    public function getTeaser() {
        return $this->_teaser;
    }

    public function getBody() {
        return $this->_body;
    }

    public function getPublished() {
        return $this->_published;
    }

    public function getMembersOnly() {
        return $this->_membersOnly;
    }

    public function getCommentsAllowed() {
        return $this->_commentsAllowed;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setAuthorID($authorID) {
        $this->_authorID = $authorID;
    }

    public function setNewsCategoryID($newsCategoryID) {
        $this->_newsCategoryID = $newsCategoryID;
    }

    public function setTitle($title) {
        $this->_title = $title;
    }

    public function setThumbnail($thumbnail) {
        $this->_thumbnail = $thumbnail;
    }

    public function setTeaser($teaser) {
        $this->_teaser = $teaser;
    }

    public function setBody($body) {
        $this->_body = $body;
    }

    public function setPublished($published) {
        $this->_published = $published;
    }

    public function setMembersOnly($membersOnly) {
        $this->_membersOnly = $membersOnly;
    }

    public function setCommentsAllowed($commentsAllowed) {
        $this->_commentsAllowed = $commentsAllowed;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $newsResult = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `NewsID`,`AuthorID`,`NewsCategoryID`,`NewsTitle`,`NewsThumbnail`,`NewsTeaser`,`NewsBody`,`NewsPublished`,`NewsMembersOnly`,`NewsCommentsAllowed` FROM `News` WHERE `NewsID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($newsID, $authorID, $newsCategoryID, $title, $thumbnail, $teaser, $body, $published, $membersOnly, $commentsAllowed);
                while($stmt->fetch()) {
                    $news = new News();
                    $news->setID($newsID);
                    $news->setAuthorID($authorID);
                    $news->setNewsCategoryID($newsCategoryID);
                    $news->setTitle($title);
                    $news->setThumbnail($thumbnail);
                    $news->setTeaser($teaser);
                    $news->setBody($body);
                    $news->setPublished(Database::toBoolean($published));
                    $news->setMembersOnly(Database::toBoolean($membersOnly));
                    $news->setCommentsAllowed(Database::toBoolean($commentsAllowed));
                    $newsResult[] = $news;
                }
                $stmt->close();
                if(\count($newsResult) > 0) {
                    return $newsResult;
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
            $newsResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `NewsID`,`AuthorID`,`NewsCategoryID`,`NewsTitle`,`NewsThumbnail`,`NewsTeaser`,`NewsBody`,`NewsPublished`,`NewsMembersOnly`,`NewsCommentsAllowed` FROM `News`")) {
                $stmt->execute();
                $stmt->bind_result($newsID, $authorID, $newsCategoryID, $title, $thumbnail, $teaser, $body, $published, $membersOnly, $commentsAllowed);
                while($stmt->fetch()) {
                    $news = new News();
                    $news->setID($newsID);
                    $news->setAuthorID($authorID);
                    $news->setNewsCategoryID($newsCategoryID);
                    $news->setTitle($title);
                    $news->setThumbnail($thumbnail);
                    $news->setTeaser($teaser);
                    $news->setBody($body);
                    $news->setPublished(Database::toBoolean($published));
                    $news->setMembersOnly(Database::toBoolean($membersOnly));
                    $news->setCommentsAllowed(Database::toBoolean($commentsAllowed));
                    $newsResult[] = $news;
                }
                $stmt->close();
                if(\count($newsResult) > 0) {
                    return $newsResult;
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

    public static function getByAuthorID($authorID) {
        if($stmt = Database::getConnection()->prepare("SELECT `NewsID` FROM `News` WHERE `AuthorID`=?")) {
            $stmt->bind_param('i', $authorID);
            $stmt->execute();
            $stmt->bind_result($newsID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $newsID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return News::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public static function getByNewsCategoryID($newsCategoryID) {
        if($stmt = Database::getConnection()->prepare("SELECT `NewsID` FROM `News` WHERE `NewsCategoryID`=?")) {
            $stmt->bind_param('i', $newsCategoryID);
            $stmt->execute();
            $stmt->bind_result($newsID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $newsID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return News::get($input);
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