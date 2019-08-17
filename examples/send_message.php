<?php
/**
 * https://birdie0.github.io/discord-webhooks-guide/structure/embed/color.html
 */

$conf = array();
include "../conf/default.php";

function send_message($webhook, $message)
{
    // format POST data
    $payload = array("content" => "$message");
    $json = json_encode($payload);
    // init curl
    $ch = curl_init($webhook);
    // submit payload
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    // close curl
    Curl_close($ch);
}

$webhook = $conf['webhook'];
$message = "This is a message.";
send_message($webhook, $message);
