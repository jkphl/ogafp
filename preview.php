<?php

session_start();

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'res'.DIRECTORY_SEPARATOR.'ogafp.php';
define ('COUNTRY', intval($_GET['country']));

$format						= array_key_exists('format', $_GET) ? $_GET['format'] : null;
$type						= array_key_exists('type', $_GET) ? $_GET['type'] : null;
$field						= $format.'_'.$type;
$options					= array_key_exists('options', $_POST) ? $_POST['options'] : array();
$options					= count($options) ? array_fill_keys($options, true) : array();
$str						= array_key_exists('format', $_POST) ? $_POST['format'] : '';
$sessionKey					= $format.'_preview_options';
$_SESSION[$sessionKey]		= (array_key_exists($sessionKey, $_SESSION) && is_array($_SESSION[$sessionKey])) ? array_merge(array_fill_keys(array_keys($_SESSION[$sessionKey]), false), $options) : $options;

$link						= mysql_connect(HOST, USERNAME, PASSWORD);
mysql_select_db(DBNAME);
$country					= mysql_query('SELECT * FROM `countries` WHERE `country_numeric` = '.COUNTRY) or die(mysql_error());
$country					= ($country && mysql_num_rows($country)) ? mysql_fetch_assoc($country) : array();
if (array_key_exists('pobox', $_POST)) {
	$country['pobox_label']	= $_POST['pobox'];
}

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'res'.DIRECTORY_SEPARATOR.'preview.php';
die(formatPreview($str, $options, $country));

?>