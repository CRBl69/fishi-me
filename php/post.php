<?php
require_once 'comment.php';
require_once 'db-config.php';
require_once 'parser.php';
require_once 'html_element.php';
require_once 'user.php';
/**
 * A simple Post class
 */
class Post implements html_element {
    private $username;
    private $userid;
    private $title;
    private $text;
    private $date;
    private $id;

    function __construct($id){
        $con = connect();

        $id = $con->real_escape_string($id);

        $this->id = $id;
        $req = "SELECT title, text, userid, date, users.id, users.username FROM
        posts INNER JOIN users ON users.id = posts.userid WHERE posts.id = $id";
        if($result = $con->query($req)){
            if($post = $result->fetch_assoc()){
                $this->title = htmlspecialchars($post['title']);
                $this->text = htmlspecialchars($post['text']);
                $this->date = new DateTime($post['date']);
                $this->username = htmlspecialchars($post['username']);
                $this->userid = $post['id'];
            }else{
                $this->title = 'Post not found';
                $this->text = 'There is no post for the given postid';
                $this->date = new DateTime();
                $this->username = "User not found";
                $this->userid = 0;
            }
        }
    }

    /**
     * Gets the html of the post
     * @return string the html to diplay the post
     */
    function html($trim = true){
        $title = $this->title;
        $username = $this->username;
        $text = $this->text;

        if($trim) {
            $text = substr($text, 0, 500);
            $text = preg_replace('/^([^\n]*\n[^\n]*\n[^\n]*\n[^\n]*\n[^\n]*\n)(\n|.){0,}/', '$1...', $text);
        }

        $parser = new Parser();
        $user = new User($this->userid);

        $text = $parser->markdown($text);
        $text = $this->sondage($text);
        $text = $parser->links($text);
        $text = $parser->youtube_video($text);
        $text = $parser->images($text);
        $text = $parser->at($text);

        $handle = fopen("../../res/html/post.html", "r");
        $html = fread($handle, filesize("../../res/html/post.html"));
        $html = str_replace("<!-- USER -->", $user->link_profile(), $html);
        $html = str_replace("<!-- TITLE -->", "<a class=\"title\" href=\"".$_SERVER["REQUEST_SCHEME"]."://".$_SERVER['HTTP_HOST']."/post?postid={$this->id}\">".$title.'</a>', $html);
        $html = str_replace("<!-- TEXT -->", $text, $html);
        $html = preg_replace("/<!-- DATE -->/", $this->date->format('Y-m-d H:i:s'), $html, 1);
        $html = str_replace("<!-- POST-LINK -->", "<button title=\"Copy link\" onclick=\"copyLink(this, '".$_SERVER["REQUEST_SCHEME"]."://".$_SERVER['HTTP_HOST']."/post?postid={$this->id}')\" class=\"post-icon\"><span class=\"material-icons\">share</span></button>", $html);
        $html = str_replace("<!-- LIKE -->", "/php/like_it.php?postid=".$this->id, $html);

        $html = str_replace("<!-- POSTID -->", $this->id, $html);

        $con = connect();
        if(isset($_SESSION['id'])){
            $req = "SELECT id FROM likes WHERE userid = {$_SESSION['id']} AND postid = {$this->id}";
            if($result = $con->query($req)){
                if($res = $result->fetch_assoc()){
                    $html = str_replace("<!-- ICON -->", 'favorite', $html);
                    $html = str_replace("<!-- ICON_TITLE -->", 'Like', $html);
                } else {
                    $html = str_replace("<!-- ICON -->", 'favorite_border', $html);
                    $html = str_replace("<!-- ICON_TITLE -->", 'Unlike', $html);
                }
            } else {
                $_SESSION['notification'] = "SQL error: " . $con->error;
            }
        }else{
            $html = str_replace("<!-- ICON -->", 'favorite_border', $html);
        }

        if($result = $con->query("SELECT * FROM likes WHERE postid = {$this->id}")){
            $nb = count($result->fetch_all());
            $html = str_replace("<!-- NB_LIKES -->", $nb, $html);

        }

        if($last_comment = $this->get_last_comment()){
            $html = str_replace("<!-- LAST_COMMENT -->", '<hr><p>Last comment:</p>'.$last_comment->html(), $html);
        }


        if(isset($_SESSION['id'])){
            if($_SESSION['moderator'] == 1 || $this->userid == $_SESSION['id']){
                $html = str_replace("<!-- DELETE -->", "<button onclick=\"deletePost(this, {$this->id})\" class=\"post-icon\" title=\"Delete post\"><span class=\"material-icons\">delete</span></button>", $html);
            }
        }


        return $html;
    }

    /**
     * Gets the comments of the post
     * @return array an array of `Comment` objects
     */
    function get_comments(){
        $req = "SELECT id FROM comments WHERE post_id = {$this->id} ORDER BY date ASC";

        $con = connect();

        $comments = [];
        if($result = $con->query($req)){
            while($comment = $result->fetch_assoc()){
                array_push($comments, new Comment($comment['id']));
            }
        }
        return $comments;
    }

    /**
     * Gets the last comment of the post
     * @return Comment the last comment of the post
     */
    function get_last_comment(){
        $req = "SELECT MAX(id) FROM comments WHERE post_id = {$this->id}";

        $con = connect();

        $c = null;
        if($result = $con->query($req)){
            //var_dump($result->fetch_all());
            if(($comment = $result->fetch_assoc() )&& $comment['MAX(id)']){
                $c = new Comment($comment['MAX(id)']);
            }else{
                return null;
            }
        }
        return $c;
    }

    /**
     * A function that handles sondages in posts
     * @return string the body of the post parsed for sondages
     */
    function sondage($html){
        $radio = "/\[[^[]+\]/";

        if(preg_match_all($radio, $html, $matches)){

            // Connection to the sql server
            $con = connect();
    
            $req = "SELECT * FROM sondages WHERE postid = {$this->id}";
            $self_vote = "";
            $sondage_results = [];
            if($result = $con->query($req)){
                while($sondage = $result->fetch_assoc()){
                    if(isset($_SESSION['id']) && $sondage['voter'] == $_SESSION['id']){
                        $self_vote = $sondage['vote_value'];
                    }
                    if(array_key_exists($sondage['vote_value'], $sondage_results)){
                        $sondage_results[$sondage['vote_value']]++;
                    }else{
                        $sondage_results[$sondage['vote_value']] = 1;
                    }
                }
            }

            $html = '<form id="'.$this->id.'" action="/php/sondage.php" method="POST"><input style="display: none" name="postid" value="'.$this->id.'">' . $html . '<br><button type="submit">Vote</button><span class="date post-date"><!-- DATE --></span></form>';

            foreach($matches as $matche){
                if($matche){
                    foreach($matche as $match){
                        $vvv = str_replace("\"", "", $match);
                        $vvv = str_replace("[", "", $vvv);
                        $vvv = str_replace("]", "", $vvv);
                        if(array_key_exists($vvv, $sondage_results)){
                            $votes_number = $sondage_results[$vvv];
                        }else{
                            $votes_number = 0;
                        }
                        if($self_vote == $vvv){
                            $new_match = "<input type=\"radio\" name=\"{$this->id}\" value=\"$vvv\" checked> [$votes_number] $vvv";
                        }else{
                            $new_match = "<input type=\"radio\" name=\"{$this->id}\" value=\"$vvv\"> [$votes_number] $vvv";
                        }
                        $html = str_replace($match, $new_match, $html);
                    }
                }
            }
        }
        return $html;
    }

    /**
     * Gets the title of the post
     * @return string title
     */
    function get_title(){
        return $this->title;
    }

    /**
     * Gets the title of the post
     * @return string title
     */
    function get_id(){
        return $this->id;
    }

    /**
     * Gets the author of the post
     * @return string author
     */
    function get_author(){
        return $this->username;
    }

    /**
     * Gets the content of the post
     * @return string content
     */
    function get_content(){
        return $this->text;
    }

    /**
     * Checks to see if there is an image in the post
     * @return false if no image
     * @return string link of the image
     */
    function get_image(){
        $regex = "/\(img:(https?:\/\/[^)]+)\)/";

        if(!preg_match($regex, $this->text, $ret)) return false;
        // print_r($ret);
        return $ret[1];
    }
}
