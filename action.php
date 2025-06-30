<?php
/**
 * DokuWiki Plugin Discord Notifier ( Action Component )
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

class action_plugin_discordnotifier extends DokuWiki_Action_Plugin {

	function register ( Doku_Event_Handler $controller ) {
		$controller -> register_hook ( 'COMMON_WIKIPAGE_SAVE', 'AFTER', $this, '_handle' );
	}

	function _handle ( Doku_Event $event, $param ) {
		/** @var helper_plugin_approve $helper */
		$helper = plugin_load('helper', 'discordnotifier');

		// filter writes to attic
		if ( $helper -> attic_write ( $event -> data['file'] ) ) return;

		// filter namespace
		if ( !$helper -> valid_namespace ( ) ) return;

		// filer event
		if ( !$helper -> set_event ( $event ) ) return;

		// set payload text
		$helper -> set_payload_text ( $event );

		// submit payload
		$helper -> submit_payload ( );
	}

}
