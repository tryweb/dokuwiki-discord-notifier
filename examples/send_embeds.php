<?php
/**
 * https://birdie0.github.io/discord-webhooks-guide/structure/embed/color.html
 */

$conf = array();
include "../conf/default.php";

function send_embeds($webhook, $version)
{
    $title = "This is a title.";
    $color = hexdec("00cccc");
    $url = "https://github.com/zteeed/dokuwiki-discord-notifier";
    $description = "*Hi!* **Wow!** I can __use__ hyperlinks and star this project [here]($url).";
    $footer = array("text" => "Dokuwiki discordnotifier $version");

    // format POST data
    $payload = array("embeds" =>
        array(
            array("title" => $title, "color" => $color, "description" => $description, "footer" => $footer)
        ),
    );
    $json = json_encode($payload);
    print_r($json);
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
$version = $conf['notify_version'];
send_embeds($webhook, $version);
