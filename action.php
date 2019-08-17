<?php
/**
 * DokuWiki Plugin Discord Notifier (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */


if (!defined('DOKU_INC')) die();

//require_once (DOKU_INC.'inc/changelog.php');

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

class action_plugin_discordnotifier extends DokuWiki_Action_Plugin
{

    var $_event = null;
    var $_payload = null;

    function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('IO_WIKIPAGE_WRITE', 'AFTER', $this, '_handle');
    }

    function _handle(Doku_Event $event, $param)
    {

        // filter writes to attic
        if ($this->_attic_write($event)) return;

        // filter namespace
        if (!$this->_valid_namespace()) return;

        // filer event
        if (!$this->_set_event($event)) return;

        // set payload text
        $this->_set_payload_text();

        // submit payload
        $this->_submit_payload();

    }

    private function _attic_write($event)
    {
        $filename = $event->data[0][0];
        if (strpos($filename, 'data/attic') !== false) return true;
    }

    private function _valid_namespace()
    {
        global $INFO;
        $validNamespaces = $this->getConf('namespaces');
        if (!empty($validNamespaces)) {
            $validNamespacesArr = explode(',', $validNamespaces);
            $thisNamespaceArr = explode(':', $INFO['namespace']);
            return in_array($thisNamespaceArr[0], $validNamespacesArr);
        } else {
            return true;
        }
    }

    private function _set_event($event)
    {
        global $ID;
        global $INFO;
        $data = $event->data;
        $contents = $data[0][1];
        $newRev = $data[3];
        $oldRev = $INFO['meta']['last_change']['date'];
        if (!empty($contents) && empty($newRev) && empty($oldRev) && $this->getConf('notify_create') == 1) {
            $this->_event = 'create';
            return true;
        } elseif (!empty($contents) && empty($newRev) && !empty($oldRev) && $this->getConf('notify_edit') == 1) {
            $this->_event = 'edit';
            return true;
        } elseif (empty($contents) && empty($newRev) && $this->getConf('notify_delete') == 1) {
            $this->_event = 'delete';
            return true;
        } else {
            return false;
        }
    }

    private function _set_payload_text()
    {
        global $INFO;

        $embed_color = hexdec("37474f"); // default value
        switch ($this->_event) {
            case 'create':
                $event = "created";
                $embed_color = hexdec("00cc00");
                break;
            case 'edit':
                $event = "updated";
                $embed_color = hexdec("00cccc");
                break;
            case 'delete':
                $event = "removed";
                $embed_color = hexdec("cc0000");
                break;
        }
        $user = $INFO['userinfo']['name'];
        $link = $this->_get_url();
        $page = $INFO['id'];
        $description = "{$user} {$event} page [__{$page}__]({$link})";

        if ($this->_event != 'delete') {
            $oldRev = $INFO['meta']['last_change']['date'];
            if (!empty($oldRev)) {
                $diffURL = $this->_get_url($oldRev);
                $description .= " \([Compare changes]({$diffURL})\)";
            }
        }
        $title = "New event";
        $footer = array("text" => "Dokuwiki discordnotifier v1.0.0");
        $payload = array("embeds" =>
            array(
                ["title" => $title, "color" => $embed_color, "description" => $description, "footer" => $footer]
            ),
        );
        $this->_payload = $payload;
    }

    private function _get_url($oldRev = null)
    {
        global $conf;
        global $INFO;
        $page = $INFO['id'];
        if (($conf['userewrite'] == 1 || $conf['userewrite'] == 2) && $conf['useslash'] == true) {
            return str_replace(":", "/", $page);
        }
        switch ($conf['userewrite']) {
            case 0:
                $url = DOKU_URL . "doku.php?id={$page}";
                break;
            case 1:
                $url = DOKU_URL . $page;
                break;
            case 2:
                $url = DOKU_URL . "doku.php/{$page}";
                break;
        }
        if (!empty($oldRev)) {
            switch ($conf['userewrite']) {
                case 0:
                    $url .= "&do=diff&rev={$oldRev}";
                    break;
                case 1:
                case 2:
                    $url .= "?do=diff&rev={$oldRev}";
                    break;
            }
        }
        return $url;
    }

    private function _submit_payload()
    {
        global $conf;

        // init curl
        $ch = curl_init($this->getConf('webhook'));

        // use proxy if defined
        $proxy = $conf['proxy'];
        if (!empty($proxy['host'])) {

            // configure proxy address and port
            $proxyAddress = $proxy['host'] . ':' . $proxy['port'];
            curl_setopt($ch, CURLOPT_PROXY, $proxyAddress);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            // include username and password if defined
            if (!empty($proxy['user']) && !empty($proxy['pass'])) {
                $proxyAuth = $proxy['user'] . ':' . conf_decodeString($proxy['port']);
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyAuth);
            }

        }

        // submit payload
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->_payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);

        // close curl
        Curl_close($ch);

    }

}
