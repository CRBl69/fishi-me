<?php
require_once 'parser.php';
require_once 'html_element.php';
require_once 'db-config.php';
/**
 * Comment class
 */
class Comment implements html_element {
    private $userid;
    private $text;
    private $date;
    private $id;

    function __construct($id){
        $con = connect();

        $id = $con->real_escape_string($id);
        $this->id = $id;

        $req = "SELECT user_id, post_id, content, date FROM comments
            WHERE comments.id = $id
        ";

        if($result = $con->query($req)){
            if($comment = $result->fetch_assoc()){
                $this->text = htmlspecialchars($comment['content']);
                $this->userid = $comment['user_id'];
                $this->date = new DateTime($comment['date']);
            }else{
                $this->text = 'Comment not found';
                $this->date = new DateTime();
                $this->username = "User not found";
                $this->userid = 0;
            }
        }
    }

    /**
     * Gets the html of the comment
     * @return string the html to diplay the comment
     */
    function html($trim = true){
        $text = $this->text;

        $parser = new Parser();

        if($trim) {
            $text = substr($text, 0, 500);
            $text = preg_replace('/^([^\n]*\n[^\n]*\n[^\n]*\n[^\n]*\n[^\n]*\n)(\n|.){0,}/', '$1...', $text);
        }

        $text = $parser->markdown($text);
        $text = $parser->links($text);
        $text = $parser->at($text);

        $user= new User($this->userid);

        $handle = fopen("../../res/html/comment.html", "r");
        $html = fread($handle, filesize("../../res/html/comment.html"));
        $html = str_replace("<!-- LINK_PROFILE -->", $user->link_profile(), $html);
        $html = str_replace("<!-- TEXT -->", $text, $html);
        $html = str_replace("<!-- DATE -->", $this->date->format('Y-m-d H:i:s'), $html);


        if(isset($_SESSION['id']) && ($_SESSION['moderator'] == 1 || $_SESSION['id'] == $this->userid)){
            $html = str_replace('<!-- DELETE -->', '
                <form action="/php/delete_comment.php" method="POST">
                    <input style="display: none" type="text" name="id" value="' . $this->id . '">
                    <button type="submit" class="post-icon"><span class="material-icons">delete</span></button>
                </form>
            ', $html);
        }

        return $html;
    }

}