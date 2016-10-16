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

?><!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Country list [Open Global Address Format Project]</title>
        <link rel="stylesheet" type="text/css" href="css/common.css" media="all" />
        <link rel="stylesheet" type="text/css" href="css/list.css" media="all" />
    </head><?php 

$link					= mysql_connect(HOST, USERNAME, PASSWORD);
if (!$link):
	?><body><p class="error">Could not connect to database. Exit.</p><?php
	exit;
elseif (!mysql_select_db(DBNAME, $link)):
	?><body><p class="error">Could not select database. Exit.</p><?php
else:
	?><body class="pad">
    	<h1 id="ogafp-list-header">OGAFP Country list <a href="#ogafp-export-header">(Go to export option)</a></h1>
    	<table id="ogafp-list">
    		<tr class="header bold">
    			<th>&nbsp;</th>
    			<th colspan="3" class="center">ISO 3166-1</th>
    			<th>&nbsp;</th>
    			<th colspan="5" class="center">Address formats</th>
    			<th colspan="4" class="center">Subdivisions</th>
    			<th class="center">Documentation</th>
    		</tr>
    		<tr class="header">
    			<th class="center">#</th>
    			<th>Numeric</th>
    			<th>ALPHA-2</th>
    			<th>ALPHA-3</th>
    			<th>Country</th>
    			<th>Person standard</th>
    			<th>Person natural</th>
    			<th>Person sortable</th>
    			<th>Address complete</th>
    			<th>Address Short</th>
    			<th>ISO 3166-2</th>
    			<th>Other</th>
    			<th>w/o language</th>
    			<th>w/o script</th>
    			<th class="center">UPU PDF</th>
    		</tr><?php 
    		
    $countriesQuery				= 'SELECT 
										`countries`.*,
 										COUNT(DISTINCT `country_subdivisions`.`subdivision`) AS `subdivisions`,
 										COUNT(DISTINCT `country_subdivisions`.`subdivision`, `country_subdivisions`.`type`) AS `subdivisions_with_type`,
 										COUNT(DISTINCT `country_subdivisions`.`subdivision_code`) AS `iso_subdivisions`,
 										COUNT(DISTINCT `country_subdivisions`.`subdivision_code`, `country_subdivisions`.`type`) AS `iso_subdivisions_with_type`,
 										COUNT(`country_subdivision_names`.`subdivision`) AS `subdivision_names`,
 										COUNT(`country_subdivision_names`.`language`) AS `subdivision_names_with_language`,
 										COUNT(`country_subdivision_names`.`script`) AS `subdivision_names_with_script`
 									FROM
 										`countries`
 									LEFT JOIN
 										`country_subdivisions` USING (`country_numeric`)
 									LEFT JOIN
 										`country_subdivision_names` ON `country_subdivisions`.`subdivision` = `country_subdivision_names`.`subdivision`
 									GROUP BY
 										`country_numeric`
 									ORDER BY
 										`countries`.`name_english`';
    $countriesResult			= mysql_query($countriesQuery);
    $countryIndex				= 0;
    while($country = mysql_fetch_assoc($countriesResult)):
    	++$countryIndex;
    	$subdivisions						= intval($country['subdivisions']);
    	$subdivisionsWithType				= intval($country['subdivisions_with_type']);
    	$subdivisionsWithoutType			= $subdivisions - $subdivisionsWithType;
    	$isoSubdivisions					= intval($country['iso_subdivisions']);
    	$isoSubdivisionsWithType			= intval($country['iso_subdivisions_with_type']);
    	$isoSubdivisionsWithoutType			= $isoSubdivisions - $isoSubdivisionsWithType;
    	$nonIsoSubdivisions					= $subdivisions - $isoSubdivisions;
    	$nonIsoSubdivisionsWithType			= $subdivisionsWithType - $isoSubdivisionsWithType;
    	$nonIsoSubdivisionsWithoutType		= $subdivisionsWithoutType - $isoSubdivisionsWithoutType;
    	$subdivisionNames					= intval($country['subdivision_names']);
    	$subdivisionNamesWithLanguage		= intval($country['subdivision_names_with_language']);
    	$subdivisionNamesWithoutLanguage	= $subdivisionNames - $subdivisionNamesWithLanguage;
    	$subdivisionNamesWithScript			= intval($country['subdivision_names_with_script']);
    	$subdivisionNamesWithoutScript		= $subdivisionNames - $subdivisionNamesWithScript;
    	

    		?><tr id="<?php echo $country['country_alpha_3']; ?>">
    			<td class="center"><?php echo $countryIndex; ?></td>	
    			<td><?php echo $country['country_numeric']; ?></td>	
    			<td><?php echo $country['country_alpha_2']; ?></td>	
    			<td><?php echo $country['country_alpha_3']; ?></td>	
    			<th><?php echo $country['name_english']; ?></th>
    			<td><a href="edit.php?country=<?php echo $country['country_numeric']; ?>&format=person&type=standard" class="<?php echo is_null($country['person_standard']) ? 'new' : 'edit'; ?>"><?php echo is_null($country['person_standard']) ? 'New' : 'Edit'; ?></a></td>
				<td><a href="edit.php?country=<?php echo $country['country_numeric']; ?>&format=person&type=natural" class="<?php echo is_null($country['person_natural']) ? 'new' : 'edit'; ?>"><?php echo is_null($country['person_natural']) ? 'New' : 'Edit'; ?></a></td>
				<td><a href="edit.php?country=<?php echo $country['country_numeric']; ?>&format=person&type=sortable" class="<?php echo is_null($country['person_sortable']) ? 'new' : 'edit'; ?>"><?php echo is_null($country['person_sortable']) ? 'New' : 'Edit'; ?></a></td>
				<td><a href="edit.php?country=<?php echo $country['country_numeric']; ?>&format=address&type=full" class="<?php echo is_null($country['address_full']) ? 'new' : 'edit'; ?>"><?php echo is_null($country['address_full']) ? 'New' : 'Edit'; ?></a></td>
				<td><a href="edit.php?country=<?php echo $country['country_numeric']; ?>&format=address&type=short" class="<?php echo is_null($country['address_short']) ? 'new' : 'edit'; ?>"><?php echo is_null($country['address_short']) ? 'New' : 'Edit'; ?></a></td>
				<td class="center"><a href="subdivisions.php?country=<?php echo $country['country_numeric']; ?>" class="doc"><?php if ($isoSubdivisions): if ($isoSubdivisionsWithoutType): ?><span class="highlight"><?php echo $isoSubdivisions; ?></span><?php else: echo $isoSubdivisions; endif; else: ?>-<?php endif; ?></a></td>
				<td class="center"><a href="subdivisions.php?country=<?php echo $country['country_numeric']; ?>" class="doc"><?php if ($nonIsoSubdivisions): if($nonIsoSubdivisionsWithoutType): ?><span class="highlight"><?php echo $nonIsoSubdivisions; ?></span><?php else: echo $nonIsoSubdivisions; endif; else: ?>-<?php endif; ?></a></td>
				<td class="center"><a href="subdivisions.php?country=<?php echo $country['country_numeric']; ?>" class="doc"><?php if ($subdivisionNamesWithoutLanguage): ?><span class="highlight"><?php echo $subdivisionNamesWithoutLanguage; ?></span><?php else: ?>-<?php endif; ?></a></td>
				<td class="center"><a href="subdivisions.php?country=<?php echo $country['country_numeric']; ?>" class="doc"><?php if ($subdivisionNamesWithoutScript): ?><span class="highlight"><?php echo $subdivisionNamesWithoutScript; ?></span><?php else: ?>-<?php endif; ?></a></td>
				<td class="center"><?php echo is_file('res'.DIRECTORY_SEPARATOR.'upu-pdf'.DIRECTORY_SEPARATOR.strtolower($country['country_alpha_3']).'En.pdf') ? '<a href="res'.DIRECTORY_SEPARATOR.'upu-pdf'.DIRECTORY_SEPARATOR.strtolower($country['country_alpha_3']).'En.pdf" target="_blank"><img src="img/pdf.png"/></a>' : '&nbsp;'; ?></td>
    		</tr><?php
	
    endwhile;
    
		?></table>
		<h1 id="ogafp-export-header">Export OGAFP data <a href="#">(Go to country list)</a></h1>
		<form method="post" action="res/firelike/export.php">
			<input type="submit" name="export" value="Export für die Firelike E-Business-Suite"/>
		</form><?php
endif;
	?></body>
</html>