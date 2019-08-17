<?php
/**
 * https://birdie0.github.io/discord-webhooks-guide/structure/embed/color.html
 */

$conf = array();
include "../conf/default.php";

function send_file($webhook, $path_img)
{
    // format POST data
    $file = new CURLFile($path_img);
    $file->setPostFilename("logo.png");
    $data = array('image' => $file);
    // init curl
    $ch = curl_init($webhook);
    // submit payload
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    // close curl
    Curl_close($ch);
}

$webhook = $conf['webhook'];
$path_img = "images/logo.png";
send_file($webhook, $path_img);
