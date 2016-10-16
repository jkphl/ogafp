<?php

/**
 * Open Global Address Format Project: Country list
 *
 * @category	OGAFP
 * @subpackage	OGAFB_Backend
 * @copyright	Copyright © 2012 tollwerk® GmbH (http://tollwerk.de)
 * @license		http://www.firelike.net/license     Firelike license
 * @author		Dipl.-Ing. Joschi Kuphal <info@ogafp.org>
 */

session_start();
ob_start();

require_once 'res/ogafp.php';
define ('COUNTRY', trim($_GET['country']));

?><!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Country list [Open Global Address Format Project]</title>
        <link rel="stylesheet" type="text/css" href="css/common.css" media="all" />
        <link rel="stylesheet" type="text/css" href="css/subdivision.css" media="all" />
        <link rel="stylesheet" type="text/css" href="css/menu.css" media="all" />
    </head><?php 

$link					= mysql_connect(HOST, USERNAME, PASSWORD);
if (!$link):
	?><body><p class="error">Could not connect to database. Exit.</p><?php
	exit;
elseif (!mysql_select_db(DBNAME, $link)):
	?><body><p class="error">Could not select database. Exit.</p><?php
else:
	?><body class="doc noscroll">
		<nav id="docnav">
			<form id="romanization" method="post"><input type="text" name="romanization" /><input type="submit" name="addromanization" value="Romanisierung hinzufügen"></form><?php 

		$countryResult				= mysql_query('SELECT * FROM `countries` WHERE `country_numeric` = '.COUNTRY.' LIMIT 1') or die(mysql_error());
		$editCountry				= ($countryResult && mysql_num_rows($countryResult)) ? mysql_fetch_assoc($countryResult) : null;
		$countriesQuery				= 'SELECT
										`countries`.*,
 										COUNT(DISTINCT `country_subdivisions`.`subdivision`) AS `subdivisions`,
 										COUNT(DISTINCT `country_subdivisions`.`subdivision_code`) AS `iso_subdivisions`
 									FROM
 										`countries`
 									INNER JOIN
 										`country_subdivisions` USING (`country_numeric`)
 									GROUP BY
 										`country_numeric`
 									HAVING
 										`subdivisions` > 0
 									ORDER BY
 										`countries`.`name_english`';
		$countriesResult			= mysql_query($countriesQuery);
		$countries					= array();
		while ($country = mysql_fetch_assoc($countriesResult)) {
			$countries[$country['country_numeric']]			= array('alpha_2' => $country['country_alpha_2'], 'alpha_3' => $country['country_alpha_3']);
		}
		$countryIndices				= array_keys($countries);
		$currentCountry				= array_search(COUNTRY, $countryIndices);
		$iframeUrls					= array(
			'wiki'					=> 'http://en.wikipedia.org/wiki/ISO_3166-2:'.$editCountry['country_alpha_2'],
			'subdivision'			=> 'subdivision.php?country='.COUNTRY.'&extended=1',
		);

			?><a href="index.php#<?php echo $countries[COUNTRY]['alpha_3']; ?>">Zurück zur Liste</a><?php
			
		if ($currentCountry > 0): 
		
			?><a href="subdivisions.php?country=<?php echo $countryIndices[$currentCountry - 1]; ?>" target="_top">Voriges Land</a><?php
		
		endif;
		
		if ($currentCountry < (count($countries) - 1)):
			
			?><a href="subdivisions.php?country=<?php echo $countryIndices[$currentCountry + 1]; ?>" target="_top">Nächstes Land</a><?php
		
		endif;
		
			?><a href="edit.php?country=<?php echo COUNTRY; ?>&format=address&type=full" target="_top">Adressformate</a><?php
			
		if ($editCountry['country_alpha_2']):
		
			?><a href="<?php echo $iframeUrls['wiki']; ?>" target="docframe" onclick="$('#currentdoc')[0].value='wiki'">Wikipedia ISO 3166-2</a><?php
		
		endif;
			
			?><a href="<?php echo $iframeUrls['subdivision']; ?>" target="docframe" onclick="$('#currentdoc')[0].value='subdivision'">Subdivisionen</a>
		</nav>
		
		<div id="pdf"><iframe src="<?php echo $iframeUrls['subdivision']; ?>" name="docframe" id="docframe"></iframe></div>
	</body><?php
endif;
	?></body>
</html>
