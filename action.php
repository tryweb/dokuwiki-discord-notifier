<?php
/**
 * DokuWiki Plugin Slack Integration (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Jeremy Ebler <jebler@gmail.com> 2011-09-29
 *
 * DokuWiki log: https://github.com/cosmocode/log.git
 * @author  Adrian Lang <lang@cosmocode.de> 2010-03-28
 *

 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require 'vendor/autoload.php';

class action_plugin_slackhq extends DokuWiki_Action_Plugin {

    function register(&$controller) {
       $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_action_act_preprocess');
    }

    function handle_action_act_preprocess(&$event, $param) {
        global $lang;
        if (isset($event->data['save'])) {
            if ($event->data['save'] == $lang['btn_save']) {
                $this->handle();
            }
        }
        return;
    }

    private function handle() {
       global $SUM;
        global $INFO;

        /* Namespace filter */
        $ns = $this->getConf('slackhq_namespaces');
        if (!empty($ns)) {
            $namespaces = explode(',', $ns);
            $current_namespace = explode(':', $INFO['namespace']);
            if (!in_array($current_namespace[0], $namespaces)) {
                return;
            }
        }

        $fullname = $INFO['userinfo']['name'];
        $username = $INFO['client'];
        $page     = $INFO['namespace'] . $INFO['id'];
        $summary  = $SUM;
        $minor    = (boolean) $_REQUEST['minor'];

        $token = $this->getConf('slackhq_token');
        $team = $this->getConf('slackhq_team');
        $channel = $this->getConf('slackhq_room');
        $from = $this->getConf('slackhq_name');

        $client = new Slack\Client($team , $token);
        $slack = new Slack\Notifier($client);

       

        $say = '<b>' . $fullname . '</b> '.$this->getLang('slackhq_update').'<b><a href="' . $this->urlize() . '">' . $INFO['id'] . '</a></b>';
        if ($minor) $say = $say . ' ['.$this->getLang('slackhq_minor').']';
        if ($summary) $say = $say . '<br /><em>' . $summary . '</em>';
        
        $message = new Slack\Message\Message('Hello world');
        
        $message->setChannel($channel)->setIconEmoji(':ghost:')->setUsername($from);

        $slack->notify($message);

    }

    /* Make our URLs! */
    private function urlize() {

        global $INFO;
        global $conf;
        $page = $INFO['id'];

        switch($conf['userewrite']) {
            case 0:
                $url = DOKU_URL . "doku.php?id=" . $page;
                break;
            case 1:
                if ($conf['useslash']) {
                    $page = str_replace(":", "/", $page);
                }
                $url = DOKU_URL . $page;
                break;
            case 2:
                if ($conf['useslash']) {
                    $page = str_replace(":", "/", $page);
                }
                $url = DOKU_URL . "doku.php/" . $page;
                break;
        }
        return $url;
    }
}

// vim:ts=4:sw=4:et:enc=utf-8:
