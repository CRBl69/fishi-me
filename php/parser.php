<?php
require_once 'user.php';
/**
 * A parser class containing different alogrithms to parse different types of text
 */
class Parser {
    function __construct(){

    }

    /**
     * parses basic markdown to html
     * @param string $text string variable to be parsed
     * @return string the parsed variable
     */
    function markdown($text){
        $text = str_replace("\\\\", "&#92;", $text);

        $code_line = "/(?<!\\\\)`((?:\\\\`|[^`])*[^\\\\])`/";

        $text = preg_replace_callback(
            $code_line,
            function ($m) {
                return "<code>" . preg_replace('/([\*\_\~\|\(\)])/', '\\\\$1', $m[1]) . "</code>";
            },
            $text
        );

        $gras = "/(?<!\\\\)[*_]{2}((?:\\\\[*_]|\\\\[*_][*_]|[*_][^*_]|[^*_])*[^\\\\])[*_]{2}/";

        $text = preg_replace_callback(
            $gras,
            function ($m) {
                return "<b>" . $m[1] . "</b>";
            },
            $text
        );

        $italique = "/(?<!\\\\)[*_]((?:\\\\[*_]|[^*_])*[^\\\\])[*_]/";

        $text = preg_replace_callback(
            $italique,
            function ($m) {
                return "<i>" . $m[1] . "</i>";
            },
            $text
        );

        $barre = "/(?<!\\\\)~~((?:\\\\~|\\\\~~|~[^~]|[^~])*[^\\\\])~~/";

        $text = preg_replace_callback(
            $barre,
            function ($m) {
                return "<del>" . $m[1] . "</del>";
            },
            $text
        );

        $spoiler = "/(?<!\\\\)\|{2}((?:\\\\[\|]|\\\\[\|][\|]|[\|][^\|]|[^\|])*[^\\\\])\|{2}/";

        $text = preg_replace_callback(
            $spoiler,
            function ($m) {
                return "<span class=\"spoiler\">" . $m[1] . "</span>";
            },
            $text
        );

        $text = str_replace("\n", "<br>", $text);
        $text = str_replace('\n', "<br>", $text);
        $text = str_replace('\\', "", $text);

        return $text;
    }

    /**
     * converts links to a tags
     * @param string $text the text which needs to be converted
     * @return string the text with links converted to tags
     */
    function links($text){
        $regex = "/(?<!(img:|vid:))https?:\/\/[^,\" ]*[^.\", ]/";

        $text = preg_replace_callback(
            $regex,
            function ($m){
                return '<a href="'.$m[0].'">'.$m[0].'</a>';
            },
            $text
        );

        return $text;
    }

    /**
     * converts @username to an a tag that sends the user to the user's page
     * @param string $html the variable that needs to be parsed
     * @return string the parsed text with @s replaced with a tags
     */
    function at($html){
        $regex = "/@([A-Za-z0-9_]+)/";

        $html = preg_replace_callback(
            $regex,
            function ($m){
                $user = new User($m[1]);
                if($user->valid() == false) {
                    return $m[0];
                }
                return $user->link_profile();
            },
            $html
        );

        return $html;
    }

    function images($text){
        $regex = "/\(img:(https?:\/\/[^\")]+)\)/";

        $text = preg_replace_callback(
            $regex,
            function ($m){
                return '<img class="post-img" src="'.$m[1].'" loading="lazy" onclick="openImage(event)">';
            },
            $text
        );

        return $text;
    }

    function youtube_video($text){
        $regex = "/\(vid:https:\/\/www\.youtube\.com\/watch\?v=(.*)\)/";

        $text = preg_replace_callback(
            $regex,
            function ($m){
                return '<iframe class="post-vid" src="https://www.youtube.com/embed/'.$m[1].'" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
            },
            $text
        );

        return $text;
    }
}