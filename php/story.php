<?php
require_once 'html_element.php';
require_once 'db-config.php';
require_once 'user.php';
/**
 * Story class
 */
class Story implements html_element {
    private $userid;
    private $image;
    private $date;
    private $id;

    function __construct($id){
        $con = connect();

        $id = $con->real_escape_string($id);
        $this->id = $id;

        $req = "SELECT * FROM stories WHERE id = $id";

        if($result = $con->query($req)){
            if($story = $result->fetch_assoc()){
                $this->id = htmlspecialchars($story['id']);
                $this->image = htmlspecialchars($story['content']);
                $this->userid = $story['user_id'];
                $this->date = new DateTime($story['date']);
            }else{
                $this->date = new DateTime();
                $this->image = "";
                $this->userid = 0;
                $this->userid = 1;
            }
        }
    }

    /**
     * Gets the html of the story
     * @return string the html to diplay the story
     */
    function html(){
        $img = $this->image;
        $user = new User($this->userid);

        $time_to_live = 1- (date_timestamp_get(new DateTime()) - $this->date->getTimestamp()) / (5 * 60);

        $handle = fopen("../../res/html/story.html", "r");
        $html = fread($handle, filesize("../../res/html/story.html"));
        $html = str_replace("<!-- PROFILE -->", "<img class=\"story-picture\" src=\"{$user->get_profile_picture()}\">", $html);
        $html = str_replace("<!-- TIME -->", "<canvas id=\"story-{$this->id}\" height=70 width=70 timeleft=\"$time_to_live\"></canvas><script defer=\"defer\">
            
        </script>", $html);
        $html = str_replace("<!-- IMG -->", "<img loading=\"lazy\" src=\"$img\">", $html);

        return $html;
    }

}