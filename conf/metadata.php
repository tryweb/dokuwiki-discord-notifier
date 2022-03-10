<?php
$meta['namespaces'] = array('string');
$meta['notify_create'] = array('onoff');
$meta['notify_edit'] = array('onoff');
$meta['notify_edit_minor'] = array('onoff');
$meta['notify_delete'] = array('onoff');
$meta['notify_show_summary'] = array('onoff');
$meta['notify_show_name'] = array('multichoice', '_choices' => array('real name','username') );
$meta['webhook'] = array('string');
