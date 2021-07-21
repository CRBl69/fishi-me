<?php
require_once 'user.php';
require_once 'notif.php';
require_once 'db-config.php';
define('ROOT_PATH', dirname(__DIR__));

/**
 * A simple Page class
 */

class Page {
    /** @var string the html of the page */
    private $html;

    /**
     * Initiates the $html variable
     * @param string $title the title of the webpage
     */
    function __construct($title, $include_top_nav = true){
        $_SESSION['last_page'] = $_SERVER['REQUEST_URI'];
        $handle = fopen("../../res/html/index.html", "r");
        $this->html = fread($handle, filesize("../../res/html/index.html"));
        $this->html = str_replace("<!-- TITLE -->", $title, $this->html);
        if(isset($_COOKIE['theme'])){
            $this->html = str_replace("<!-- THEME -->", $_COOKIE['theme'], $this->html);
        }else{
            $this->html = str_replace("<!-- THEME -->", 'darkv2.css', $this->html);
        }
        if($include_top_nav) $this->html = str_replace("<!-- TOP NAV -->", top_nav_html(), $this->html);
        if(isset($_SESSION['id'])){
            $this->script('/res/js/keybinds.js');
        }
    }

    /**
     * Appends the argument to the $html variable in the body section
     * @param string $body the string to append to the body of the page
     */
    function body($body){
        $this->html = str_replace("<!-- BODY -->", $body . "\n<!-- BODY -->", $this->html);
    }

    /**
     * Appends the file contents to the $html variable
     * @param string $file the location of the file to read
     */
    function readFile($file){
        if(func_num_args() == 1){
            $handle = fopen(ROOT_PATH."$file", "r");
            $content = fread($handle, filesize(ROOT_PATH."$file"));
            $this->body($content);
        }else{
            if(func_num_args() % 2 == 0){
                return false;
            }else{
                $handle = fopen(ROOT_PATH."$file", "r");
                $content = fread($handle, filesize(ROOT_PATH."$file"));

                for($i=1;$i<func_num_args();$i+=2) {
                        $content = str_replace(func_get_arg($i), func_get_arg($i+1), $content);
                }

                $this->body($content);
            }
        }
    }

    /**
     * Adds the script at the end of the page
     * @param string $stript the location of the script
     */
    function script($script){
        $this->html = str_replace("<!-- SCRIPTS -->", "<script src=\"".$script."\"></script><!-- SCRIPTS -->", $this->html);
    }

    /**
     * Creates a meta tag
     * @param string $name property attribute of the meta tag
     * @param string $dta content attribute of the meta tag
     */
    function meta($name, $data){
        $name = htmlspecialchars($name);
        $data = htmlspecialchars($data);
        $metadata = "<meta property=\"$name\" content=\"$data\"></meta>";
        $this->html = str_replace("<!-- META -->", $metadata . "<!-- META -->", $this->html);
    }

    /**
     * Used for making the picture bigger when a link is shared on discord
     */
    function twitter_meta(){
        $this->html = str_replace("<!-- META -->", '<meta name="twitter:card" content="summary_large_image"><!-- META -->', $this->html);
    }

    /**
     * Renders (echo) the $html variable
     */
    function render(){
        echo $this->html;
    }
}

function top_nav_html(){
    if(isset($_SESSION['id'])){
        $handle = fopen("../../res/html/top-nav-logged.html", "r");
        $content = fread($handle, filesize("../../res/html/top-nav-logged.html"));
        $content = str_replace("<!-- WEBSITE -->", $_SERVER['HTTP_HOST'], $content);
        $content = str_replace("<!-- HTTP -->", $_SERVER['REQUEST_SCHEME'], $content);
        $content = str_replace("<!-- PROFILE -->", $_SESSION['username'], $content);
        $user = new User($_SESSION['id']);
        $unread_dms = $user->unread_dms();
        if(!$unread_dms == 0){
            $content = str_replace("<!-- UNREAD_DMS -->", "($unread_dms)", $content);
            $content = str_replace("<!-- UNREAD_DMS_STYLE -->", ' style="color: red"', $content);
        }else{
            $content = str_replace("<!-- UNREAD_DMS_STYLE -->", '', $content);
        }

        return $content;
    }else{
        $handle = fopen("../../res/html/top-nav.html", "r");
        $content = fread($handle, filesize("../../res/html/top-nav.html"));
        $content = str_replace("<!-- WEBSITE -->", $_SERVER['HTTP_HOST'], $content);
        return $content;
    }
}