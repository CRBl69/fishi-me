<?php
require_once 'post.php';
require_once 'parser.php';
require_once 'html_element.php';
require_once 'db-config.php';
define('ROOT_PATH_USER', dirname(__DIR__));
/**
 * A simple User class
 */
class User implements html_element {
    private $username;
    private $email;
    private $description;
    private $id;
    private $moderator;

    function __construct($id){
        $con = connect();
        if(is_numeric($id)){
            $id = $con->real_escape_string($id);

            $req = "SELECT * FROM users WHERE id = $id";
            if($result = $con->query($req)){
                if($user = $result->fetch_assoc()){
                    $this->id = $user['id'];
                    $this->description = htmlspecialchars($user['description']);
                    $this->username = htmlspecialchars($user['username']);
                    $this->email = htmlspecialchars($user['email']);
                    $this->moderator = $user['moderator'];
                }else{
                    $this->id = 0;
                    $this->description = 'No description';
                    $this->username = 'User not found';
                    $this->moderator = 0;
                    $this->email = '';
                }
            }
        }else{
            $username = $con->real_escape_string($id);

            $req = "SELECT * FROM users WHERE username = '$username'";
            if($result = $con->query($req)){
                if($user = $result->fetch_assoc()){
                    $this->id = $user['id'];
                    $this->description = htmlspecialchars($user['description']);
                    $this->username = htmlspecialchars($user['username']);
                    $this->email = htmlspecialchars($user['email']);
                    $this->moderator = $user['moderator'];
                }else{
                    $this->id = 0;
                    $this->description = 'No description';
                    $this->username = 'User not found';
                    $this->email = '';
                }
            }
        }
    }

    /**
     * Gets the validity of the user
     * @return bool wether the user is real or not
     */
    function valid(){
        if($this->id){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Gets the html of the user
     * @return string the html to diplay the user
     */
    function html(){
        $username = $this->username;
        $text = $this->description;
        if(empty($text)) $text = "No description";

        $parser = new Parser();

        $text = $parser->markdown($text);
        $text = $parser->at($text);
        $text = $parser->links($text);

        $nb_followers = count($this->get_followers());
        $nb_following = count($this->get_following());

        $handle = fopen(ROOT_PATH_USER."/res/html/user.html", "r");
        $html = fread($handle, filesize(ROOT_PATH_USER."/res/html/user.html"));
        $html = str_replace("<!-- USERNAME -->", $username, $html);
        $html = str_replace("<!-- TEXT -->", $text, $html);
        $html = str_replace("<!-- FOLLOWERS -->", $nb_followers, $html);
        $html = str_replace("<!-- FOLLOWING -->", $nb_following, $html);
        $image = $this->get_profile_picture();
        $html = str_replace("<!-- PROFILE_PICTURE -->", '<img class="profile-picture" src="'.$image."\" width=\"128\" height=\"128\">", $html);

        $already_follows = false;

        if(isset($_SESSION['id']) && $_SESSION['id'] == $this->id){
            $html = str_replace("<!-- EDIT -->", '<a href="/edit_profile"><button>Edit</button></a>', $html);
        }else if(isset($_SESSION['id']) && $_SESSION['id'] != $this->id){
            $con = connect();

            $req = "SELECT * FROM follows WHERE following = {$this->id} AND follower = {$_SESSION['id']}";
            if ($result = $con->query($req)) {
                while ($b = $result->fetch_assoc()) {
                    $already_follows = true;
                    break;
                }
            }
        }

        $follow_html = '<div style="display: flex; flex-direction: row">';

        if (isset($_SESSION['id']) && !$already_follows && ($this->id != $_SESSION['id'])) {
            $follow_html .= '
                <form action="/php/follow.php" method="POST">
                    <input style="display: none" type="text" name="following" id="following" value="' . $this->id . '">
                    <input style="display: none" type="text" name="follower" id="follower" value="' . $_SESSION['id'] . '">
                    <button type="submit">Follow</button>
                </form>
            ';

            $follow_html .= '
                <a id="dm-button-'.$this->id.'" href="/dm?userid='.$this->id.'"><button>DM</button></a>
            ';
        } else if (isset($_SESSION['id']) && $already_follows && ($this->id != $_SESSION['id'])) {
            $follow_html .= '
                <form action="/php/unfollow.php" method="POST">
                    <input style="display: none" type="text" name="following" id="following" value="' . $this->id . '">
                    <input style="display: none" type="text" name="follower" id="follower" value="' . $_SESSION['id'] . '">
                    <button type="submit">Unfollow</button>
                </form>
            ';

            $follow_html .= '
                <a id="dm-button-'.$this->id.'" class="dm-button" href="/dm?userid='.$this->id.'"><button>DM</button></a>
            ';
        }
        if(isset($_SESSION['id']) && ($_SESSION['id'] == 1 || $_SESSION['id'] == 2) && ($this->id != 1 && $this->id != 2)){
            $follow_html .= '
                <form action="/php/delete_account.php" method="POST" onsubmit="return confirm(\'Are you sure you want to delete '.$this->username.'\\\'s account ?\');">
                    <input style="display: none" type="text" name="id" value="' . $this->id . '">
                    <button type="submit">Delete</button>
                </form>
            ';
        }
        if(isset($_SESSION['id']) && ($_SESSION['id'] == 1 || $_SESSION['id'] == 2)){
            if($this->moderator == 0) {
                $follow_html .= '
                    <form action="/php/add_admin.php" method="POST">
                        <input style="display: none" type="text" name="id" value="' . $this->id . '">
                        <button type="submit">Add as admin</button>
                    </form>
                ';
            } else {
                $follow_html .= '
                    <form action="/php/remove_admin.php" method="POST">
                        <input style="display: none" type="text" name="id" value="' . $this->id . '">
                        <button type="submit">Remove admin</button>
                    </form>
                ';
            }
        }
        $follow_html .= '</div>';
        $html = str_replace("<!-- FOLLOW -->", $follow_html, $html);

        return $html;
    }

    /**
     * Returns a link to the user's profile page
     * @return string an a tag that redirects to the user's profile
     */
    function link_profile(){
        if(isset($this->username)){
            return '
            <div class="popup">
                <a class="user-link"
                    onmouseenter="if(document.querySelector(\'.show\'))document.querySelector(\'.show\').className=\'popuptext\';this.parentNode.getElementsByTagName(\'div\')[0].classList.add(\'show\');"
                    onmouseleave="this.parentNode.getElementsByTagName(\'div\')[0].classList.remove(\'show\');"
                href="/profile/'.$this->username.'">
                    @'.$this->username.'
                </a>
                <div class="popuptext">
                    '.$this->html().'
                </div>
            </div>';
        }
    }

    /**
     * Returns a link to the user's dm page
     * @return string an a tag that redirects to the user's dm
     */
    function link_dm(){
        if(!isset($_SESSION['id'])) return '';
        $con = connect();
        $req = "SELECT * FROM dms WHERE (from_user = {$this->id} AND to_user = {$_SESSION['id']}) OR (to_user = {$this->id} AND from_user = {$_SESSION['id']}) ORDER BY date DESC";
        $last_dm_str = "";
        if($result = $con->query($req)){
            $last_dm = $result->fetch_assoc();
            if($last_dm){
                $last_dm_str = $last_dm['from_user'] == $_SESSION['id'] ? 'You: ' : $this->username . ': ';
                $last_dm_str .= strlen($last_dm['content']) > 25 ? htmlspecialchars(substr($last_dm['content'], 0, 25)) . '...' : htmlspecialchars($last_dm['content']);
            }
        }
        return '
        <div style="width: 15vw; padding: 5px">
            <div class="space-between"><a class="unique-dm-class" href="/dm?userid='.$this->id.'">' . $this->username .'</a><div class="date">'.$last_dm['date'].'</div></div>
            <span style="color: var(--text-unimportant); max-width: 15vw; overflow: hidden; text-overflow: clip; display: inline-block; font-size: .8rem">'.$last_dm_str.'</span>
        </div>';
    }

    /**
     * Get the username of the user
     * @return string the username of the user
     */
    function get_username(){
        return $this->username;
    }

    /**
     * Get the id of the user
     * @return int the id of the user
     */
    function get_id(){
        return $this->id;
    }

    /**
     * Gets all the posts of the user
     * @return Post[] the posts of the user
     */
    function get_posts(){
        $req = "SELECT id FROM posts WHERE userid = {$this->id} ORDER BY date DESC";

        $con = connect();

        $posts = [];
        if($result = $con->query($req)){
            while($post = $result->fetch_assoc()){
                array_push($posts, new Post($post['id']));
            }
        }
        return $posts;
    }

    /**
     * Gets all the followers of the user
     * @return User[] the users the user follows
     */
    function get_followers(){
        $req = "SELECT * FROM follows WHERE following = ". $this->id;

        $con = connect();
        $followers = [];
        if($result = $con->query($req)){
            while($user = $result->fetch_assoc()){
                array_push($followers, new User($user['follower']));
            }
        }
        return $followers;
    }

    /**
     * Gets all the users the user follows
     * @return User[] the users the user follows
     */
    function get_following(){
        $req = "SELECT * FROM follows WHERE follower = ". $this->id;

        // Connection to the sql server
        $con = connect();
        $followers = [];
        if($result = $con->query($req)){
            while($user = $result->fetch_assoc()){
                array_push($followers, new User($user['following']));
            }
        }
        return $followers;
    }

    /**
     * Gets the profile picture of the user
     * @return string the link to the user's profile picture
     */
    function get_profile_picture(){
        if(!file_exists("/var/www/html/data/{$this->id}.jpg")){
            return $_SERVER["REQUEST_SCHEME"]."://{$_SERVER['HTTP_HOST']}/res/fishy_old.png";
        }else{
            return $_SERVER["REQUEST_SCHEME"]."://{$_SERVER['HTTP_HOST']}/data/{$this->id}.jpg";
        }
    }

    /**
     * Get the description of the user
     * @return string the description of the user
     */
    function get_description(){
        return $this->description;
    }

    /**
     * Gets the number of unread DMs a user has
     * @return int unread DMs
     */
    function unread_dms(){
        if(!isset($_SESSION['id'])) return 0;

        $con = connect();

        $req = "SELECT id FROM dms WHERE to_user = {$_SESSION['id']} AND seen = 0";

        if($result = $con->query($req)){
            $dm = $result->fetch_all();
            return count($dm);
        }
        return 0;
    }

    /**
     * Gets the users a user has DMs with
     * @return User[] the users with which the user has unread DMs with
     */
    function get_dm_users(){
        if(!isset($_SESSION['id'])) return 0;

        $con = connect();

        $req = "SELECT from_user, to_user FROM dms WHERE to_user = {$_SESSION['id']} OR from_user = {$_SESSION['id']} ORDER BY date DESC";

        $dms = [];
        if($result = $con->query($req)){
            while($dm = $result->fetch_assoc()){
                if($dm['to_user'] == $_SESSION['id']){
                    $is = false;
                    foreach($dms as $md){
                        if($md->id == $dm['from_user']) $is = true;
                    }
                    if(!$is) array_push($dms, new User($dm['from_user']));
                }else{
                    $is = false;
                    foreach($dms as $md){
                        if($md->id == $dm['to_user']) $is = true;
                    }
                    if(!$is) array_push($dms, new User($dm['to_user']));
                }
            }
        }
        return $dms;
    }
}
