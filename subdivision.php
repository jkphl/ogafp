<?php

/**
 * Open Global Address Format Project: Address format editor
 *
 * @type	OGAFP
 * @subpackage	OGAFB_Backend
 * @copyright	Copyright © 2012 tollwerk® GmbH (http://tollwerk.de)
 * @license		http://www.firelike.net/license	 Firelike license
 * @author		Dipl.-Ing. Joschi Kuphal <info@ogafp.org>
 */

session_start();
ob_start();

require_once 'res/ogafp.php';
define ('COUNTRY', intval($_GET['country']));
define ('EXTENDED', (boolean)$_GET['extended']);

?><!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Country subdivision editor [Open Global Address Format Project]</title>
		<link rel="stylesheet" type="text/css" href="css/common.css" media="all" />
		<link rel="stylesheet" type="text/css" href="css/subdivision.css" media="all" />
		<script type="text/javascript" src="javascript/jquery-1.8.2.min.js"></script>
		<script type="text/javascript" src="javascript/subdivision.js"></script>
	</head>
	<body class="white pad"><?php 

$link						= mysql_connect(HOST, USERNAME, PASSWORD);
if (!$link):

		?><p class="error">Could not connect to database. Exit.</p><?php
	
	exit;
	
elseif (!mysql_select_db(DBNAME, $link)):
	
		?><p class="error">Could not select database. Exit.</p><?php

endif;

mysql_query('SET NAMES utf8');
$functionValues					= '';
$functionPriority				= 0;
$country						= mysql_query('SELECT * FROM `countries` WHERE `country_numeric` = '.COUNTRY);
$country						= ($country && mysql_num_rows($country)) ? mysql_fetch_assoc($country) : null;

// If no country is given
if (!$country):

		?><p class="error">Could not fetch country. Exit.</p><?php
	
// Else
else:

	// Im erweiterten Modus: Ermitteln der verfügbare Romanisierungen
	if (EXTENDED):

		// If a subdivision type has been submitted
		$type						= array_key_exists('type', $_POST) ? intval($_POST['type']) : false;
		if ($type !== false) {
			$type					= $type ? $type : null;
		
			// If a subdivision name piority has been submitted
			if (array_key_exists('priority', $_POST) && strlen($_POST['priority'])) {
				$priority			= intval($_POST['priority']);
				$language			= (array_key_exists('language', $_POST) && is_array($_POST['language']) && array_key_exists($priority, $_POST['language'])) ? $_POST['language'][$priority] : false;
				$language			= ($language === false) ? false : (intval($language) ? intval($language) : null);
				$script				= (array_key_exists('script', $_POST) && is_array($_POST['script']) && array_key_exists($priority, $_POST['script'])) ? $_POST['script'][$priority] : false;
				$script				= ($script === false) ? false : (intval($script) ? intval($script) : null);
				$romanization		= (array_key_exists('romanization', $_POST) && is_array($_POST['romanization']) && array_key_exists($priority, $_POST['romanization'])) ? $_POST['romanization'][$priority] : false;
				$romanization		= ($romanization === false) ? false : (intval($romanization) ? intval($romanization) : null);
				
				// If a subdivision name language, script or romanization has to be updated
				if (($language !== false) && ($script !== false) && ($romanization !== false)) {
					mysql_query('UPDATE `country_subdivision_names`
						INNER JOIN `country_subdivisions`
						ON `country_subdivision_names`.`subdivision` = `country_subdivisions`.`subdivision`
							AND `country_subdivisions`.`type` <=> '.(($type === null) ? 'NULL' : $type).'
							AND `country_subdivisions`.`country_numeric` = '.COUNTRY.'
						SET `country_subdivision_names`.`language` = '.(($language === null) ? 'NULL' : $language).',
							`country_subdivision_names`.`script` = '.(($script === null) ? 'NULL' : $script).',
							`country_subdivision_names`.`romanization` = '.(($romanization === null) ? 'NULL' : $romanization).'
						WHERE
							`country_subdivision_names`.`priority` = '.$priority) or die(mysql_error());
				}
					
			// Else: If priorities are to be swapped
			} elseif (array_key_exists('swap', $_POST) && is_array($_POST['swap']) && (count($_POST['swap']) == 1)) {
				$swap				= explode('-', key($_POST['swap']));
				if (count($swap) == 2) {
					mysql_query('UPDATE `country_subdivision_names` INNER JOIN `country_subdivisions` ON `country_subdivision_names`.`subdivision` = `country_subdivisions`.`subdivision` AND `country_subdivisions`.`type` <=> '.(($type === null) ? 'NULL' : $type).' AND `country_subdivisions`.`country_numeric` = "'.COUNTRY.'" SET `country_subdivision_names`.`priority` = -1 WHERE `country_subdivision_names`.`priority` = '.$swap[0]) or die(mysql_error());
					mysql_query('UPDATE `country_subdivision_names` INNER JOIN `country_subdivisions` ON `country_subdivision_names`.`subdivision` = `country_subdivisions`.`subdivision` AND `country_subdivisions`.`type` <=> '.(($type === null) ? 'NULL' : $type).' AND `country_subdivisions`.`country_numeric` = "'.COUNTRY.'" SET `country_subdivision_names`.`priority` = '.$swap[0].' WHERE `country_subdivision_names`.`priority` = '.$swap[1]) or die(mysql_error());
					mysql_query('UPDATE `country_subdivision_names` INNER JOIN `country_subdivisions` ON `country_subdivision_names`.`subdivision` = `country_subdivisions`.`subdivision` AND `country_subdivisions`.`type` <=> '.(($type === null) ? 'NULL' : $type).' AND `country_subdivisions`.`country_numeric` = "'.COUNTRY.'" SET `country_subdivision_names`.`priority` = '.$swap[1].' WHERE `country_subdivision_names`.`priority` = -1') or die(mysql_error());
				}
			}
		}
		
		// If a function should be performed
		if (array_key_exists('do', $_POST) && is_array($_POST['do']) && count($_POST['do']) && ((array_key_exists('subdivision', $_POST) && is_array($_POST['subdivision']) && count($_POST['subdivision'])) || (key($_POST['do']) == 'function'))) {
			$subdivisions							= $_POST['subdivision'];
			$type									= (array_key_exists('type', $_POST) && $_POST['type']) ? $_POST['type'] : null;
			switch(key($_POST['do'])) {
					
				// Abbreviation
				case 'abbreviation':
					mysql_query('UPDATE `country_subdivisions` SET `abbreviation` = SUBSTRING(`subdivision`, 4) WHERE `country_numeric` = '.COUNTRY.' AND `subdivision` IN ("'.implode('", "', $subdivisions).'") AND '.(($type === null) ? 'ISNULL(`type`)' : '`type` = '.intval($type))) or die(mysql_error());
					break;
						
				// Type
				case 'type':
					$setType						= (array_key_exists('set-type', $_POST) && $_POST['set-type']) ? $_POST['set-type'] : null;
					mysql_query('UPDATE `country_subdivisions` SET `type` = '.(($setType === null) ? 'NULL' : '"'.mysql_real_escape_string($setType).'"').' WHERE `country_numeric` = '.COUNTRY.' AND `subdivision` IN ("'.implode('", "', $subdivisions).'") AND '.(($type === null) ? 'ISNULL(`type`)' : '`type` = '.intval($type))) or die(mysql_error());
					break;
						
				// Misc. functions
				case 'function':
					$function						= $_POST['do']['function'];
					$functionValues					= array_key_exists('function', $_POST) ? trim($_POST['function']) : null;
					$functionPriority				= intval($_POST['do']['priority']);
					$functionRemain					= array();
					$subdivisions					= array();
					if ($functionValues) {
						$lines		= array();
						foreach (preg_split("%[\r\n]+%", $functionValues) as $line) {
							$line					= trim($line);
							if (strlen($line)) {
								if (preg_match("%^([A-Z]{2}\-[A-Za-z0-9]+)\s+([^\t]+)%u", $line, $keyValue)) {
		
									// If a title should be changed
									if ($function == 'label') {
											
										// Query a possibly existing record
										$recordResult				= mysql_query('SELECT * FROM `country_subdivision_names` WHERE `priority` = '.intval($functionPriority).' AND `subdivision` = "'.mysql_real_escape_string($keyValue[1]).'"') or die(mysql_error());
											
										// If a matching record doesn't exist yet: Create it
										if (!$recordResult || !mysql_num_rows($recordResult)) {
	
											// If a title has been given
											if ($keyValue[2] != '-') {
												$functionLanguage				= intval($_POST['do']['language']);
												$functionScript					= intval($_POST['do']['script']);
												
												// If a language and a script have been submitted as well
												if ($functionLanguage && $functionScript) {
													mysql_query('INSERT INTO `country_subdivision_names` (`subdivision`, `priority`, `language`, `script`, `romanization`, `name`)
														VALUES ("'.mysql_real_escape_string($keyValue[1]).'", '.intval($functionPriority).', '.$functionLanguage.', '.$functionScript.', NULL, "'.mysql_real_escape_string($keyValue[2]).'")') or die(mysql_error());
												
												// Else 
												} else {
													$functionRemain[]			= $line;
												}
											}
												
										// Else: Update the record if a reasonable title has been submitted
										} elseif ($keyValue[2] != '-') {
											mysql_query('UPDATE `country_subdivision_names` SET `name` = "'.mysql_real_escape_string($keyValue[2]).'" WHERE `priority` = '.intval($functionPriority).' AND `subdivision` = "'.mysql_real_escape_string($keyValue[1]).'"') or die(mysql_error());
	
										// Else: Delete the record
										} else {
											mysql_query('DELETE FROM `country_subdivision_names` WHERE `priority` = '.intval($functionPriority).' AND `subdivision` = "'.mysql_real_escape_string($keyValue[1]).'"') or die(mysql_error());
										}
											
									// Else
									} else {
											
										// Query the record
										$recordResult				= mysql_query('SELECT * FROM `country_subdivisions` WHERE `country_numeric` = '.COUNTRY.' AND `subdivision` = "'.mysql_real_escape_string($keyValue[1]).'"');

										// If the record cannot be retrieved
										if (!$recordResult || (($recordCount = mysql_num_rows($recordResult)) != 1)) {
											echo '<p class="error">Datensatz nicht verfügbar oder nicht eindeutig: '.htmlspecialchars($keyValue[1]).' ('.$recordCount.')</p>';
											$functionRemain[]		= $line;
	
										// Else: Update the record
										} else {
	
											// Special case: Update the ID as well whenn the ISO 3166-2 code get's updated
											if ($function == 'subdivision_code') {
												$additional			= ', `subdivision` = "'.mysql_real_escape_string($keyValue[2]).'"';
												
											// Else
											} else {
												$additional			= '';
											}
	
											// Update the record
											$sql					= 'UPDATE `country_subdivisions` SET `'.$function.'` = "'.mysql_real_escape_string($keyValue[2]).'"'.$additional.' WHERE `country_numeric` = '.COUNTRY.' AND `subdivision` = "'.mysql_real_escape_string($keyValue[1]).'"';
											//echo $sql.'<br/>';
											$recordResult			= mysql_query($sql);
											
											// If the record could not be updated: Error
											if (!$recordResult || (mysql_affected_rows() < 0)) {
												echo '<p class="error">Fehler beim Aktualisieren des Datensatzes: '.htmlspecialchars($keyValue[1]).'</p>';
												$functionRemain[]	= $line;
											}
										}
									}
									
								// Else: error
								} else {
									echo '<p class="error">Fehler beim Auslesen der Funktionswerte ('.htmlspecialchars($line).')</p>';
									$functionRemain[]				= $line;
								}
							}
						}
					}
					$functionValues									= implode("\n", $functionRemain);
					break;
			}
		}
		
		// Get several value lists
		$romanizations			= getRomanizations();
		$languages				= getLanguages();
		$scripts				= getScripts();
	endif;

	$types						= getTypes();
	$subdivisions				= array('No type' => array());
	$noType						=& $subdivisions['No type'];
	$noTypeCounts				= array('iso' => 0, 'other' => 0);
	$isoSubdivisions			=
	$nonIsoSubdivisions			= 0;
	$subdivisionResult			= mysql_query('SELECT `country_subdivisions`.* FROM `country_subdivisions` WHERE `country_numeric` = '.COUNTRY.' ORDER BY NULL') or die(mysql_error());
	if ($subdivisionResult && mysql_num_rows($subdivisionResult)) {
		while($subdivision = mysql_fetch_assoc($subdivisionResult)) {
			$subdivisionType							= $subdivision['type'];
			if ($subdivisionType) {
				if (!array_key_exists($subdivisionType, $subdivisions)) {
					$subdivisions[$subdivisionType]	= array();
				}
				$subdivisions[$subdivisionType]
					[$subdivision['subdivision']]	= $subdivision;
			} else {
				$noType[$subdivision['subdivision']]	= $subdivision;
				++$noTypeCounts[($subdivision['subdivision_code'] === null) ? 'other' : 'iso'];
			}
			if ($subdivision['subdivision_code'] === null) {
				++$nonIsoSubdivisions;
			} else {
				++$isoSubdivisions;
			}
		}
	}
	if (!count($noType)) {
		unset($subdivisions['No type']);
	}
	$labels											= array(
		'subdivision'								=> 'ID',
		'subdivision_code'							=> 'ISO 3166-2 Code',
		'abbreviation'								=> 'Abbreviation',
	);
	
		?><h1><?php echo $country['name_english']; ?> (<?php echo $country['name_local']; ?>)</h1>
			<table class="layout">
				<tr>
					<td>
						<table class="subdivision-header-table">
							<tr>
								<th>ISO-3166-1 numeric</th>
								<td><?php echo $country['country_numeric']; ?></td>
							</tr>
							<tr>
								<th>ISO-3166-1 ALPHA-2</th>
								<td><?php echo $country['country_alpha_2']; ?></td>
							</tr>
							<tr>
								<th>ISO-3166-1 ALPHA-3</th>
								<td><?php echo $country['country_alpha_3']; ?></td>
							</tr>
						</table>
					</td>
					<td>
						<table class="subdivision-header-table">
							<tr>
								<th>Subdivisions</td>
								<th>Total</td>
								<th>Untyped</td>
							</tr>
							<tr>
								<th>ISO-3166-2</th>
								<td><?php echo $isoSubdivisions; ?></td>
								<td><?php echo $noTypeCounts['iso']; ?></td>
							</tr>
							<tr>
								<th>Others</th>
								<td><?php echo $nonIsoSubdivisions; ?></td>
								<td><?php echo $noTypeCounts['other']; ?></td>
							</tr>
							<tr>
								<th>Total</th>
								<td><?php echo $isoSubdivisions + $nonIsoSubdivisions; ?></td>
								<td><?php echo array_sum($noTypeCounts); ?></td>
							</tr>
						</table>
					</td>
					<td>
						<table class="subdivision-header-table">
							<?php
							
	foreach ($subdivisions as $type => $typeSubdivisions):
							
							?><tr>
								<th><a href="#<?php echo $types[$type]; ?>"><?php echo $types[$type]; ?></a></th>
								<td><?php echo count($typeSubdivisions); ?></td>
							</tr><?php						

	endforeach;
	
							?>
						</table>
					</td>
				</tr>
			</table><?php
			
	foreach ($subdivisions as $type => $typeSubdivisions):
		
		// Get subdivision names
		$typeIds							= array_keys($typeSubdivisions);
		$typeLabels							= array();
		if (count($typeIds)) {
			$typeLabelResult				= mysql_query('SELECT * FROM `country_subdivision_names` WHERE `subdivision` IN ("'.implode('","', $typeIds).'")') or die(mysql_error());
			while($typeLabel = mysql_fetch_assoc($typeLabelResult)) {
				if (!array_key_exists($typeLabel['priority'], $typeLabels)) {
					$typeLabels[$typeLabel['priority']]		= array(
						'subdivisions'		=> array(),
						'language'			=> $typeLabel['language'],
						'romanization'		=> $typeLabel['romanization'],
						'script'			=> $typeLabel['script'],
					);
				}
				$typeLabels[$typeLabel['priority']]['subdivisions'][$typeLabel['subdivision']] = $typeLabel['name'];
			}
			ksort($typeLabels, SORT_NUMERIC);
		}
		
			?><form method="post" class="subdivision-type" id="<?php echo $types[$type]; ?>"><?php
					
		if (EXTENDED):
					
				?><input type="hidden" name="type" value="<?php echo ($type == 'No type') ? '' : $type; ?>"/>
				<input type="hidden" name="priority" value=""/><?php
					
		endif;
					
				?><div class="subdivision-controls"><?php
					
		if (EXTENDED):
					
					?><input type="submit" name="do[type]" value="Set type"/><select name="set-type"><option>---</option><?php
						
			foreach ($types as $typeValue => $typeLabel):
			
				?><option value="<?php echo $typeValue; ?>"><?php echo $typeLabel; ?></option><?php
			
			endforeach;
						
					?></select><input type="submit" name="do[abbreviation]" value="Compute abbreviation"/><input type="button" name="unselect-all" value="Unselect all"/><input type="button" name="select-all" value="Select all"/><?php
					
		endif;
					
					?><h3 class="subdivision-table"><?php echo $types[$type]; ?> (<?php echo count($typeSubdivisions); ?>)</h3>
				</div>
				<table class="subdivision-table<?php if ($type == 'No type') echo " no-type"; ?>"><?php 
					?><tr class="header"><?php
					
		if (EXTENDED):
					
						?><th class="checkbox">&nbsp;</th><?php
		
		endif;
		
		?><th class="code"><?php echo implode('</th><th class="code">', $labels); ?></th><?php 
				
		foreach ($typeLabels as $priority => $labelData):
		
						?><th><?php
		
			if (EXTENDED):
				if ($priority):
						
							?><button type="submit" name="swap[<?php echo ($priority - 1).'-'.$priority; ?>]" class="swap" title="Priorität erhöhen">◄</button><?php
			
				endif;

							?><select name="language[<?php echo $priority; ?>]" class="language" data-priority="<?php echo $priority; ?>">
								<option value=""<?php if (!$labelData['language']) echo ' selected="selected"'; ?>>-?-</option>
								<optgroup label="ISO 639-2" title="ISO 639-2 (T)"><?php

				$selectedLanguage		= null;
				foreach ($languages as $languageValue => $languageLabel):
							
									?><option value="<?php echo $languageValue; ?>"<?php if ($languageValue == $labelData['language']) { $selectedLanguage = $languageValue; echo ' selected="selected"'; } ?>><?php echo $languageLabel; ?></option><?php
						
				endforeach;
				if (($selectedLanguage === null) && $labelData['language']):

								?></optgroup>
								<optgroup label="ISO 639-3">
									<option value="<?php echo $labelData['language']; ?>" selected="selected"><?php echo $labelData['language']; ?></option><?php
				endif;

								?></optgroup>
							</select><select name="script[<?php echo $priority; ?>]" class="script" data-priority="<?php echo $priority; ?>">
								<option value=""<?php if (!$labelData['script']) echo ' selected="selected"'; ?>>---</option><?php
						
				foreach ($scripts as $scriptValue => $scriptLabel):

								?><option value="<?php echo $scriptValue; ?>"<?php if ($labelData['script'] == $scriptValue) echo ' selected="selected"'; ?>><?php echo $scriptLabel; ?></option><?php
				
				endforeach;
						
							?></select><select name="romanization[<?php echo $priority; ?>]" class="romanization" data-priority="<?php echo $priority; ?>"><option value=""<?php if (!$labelData['romanization']) echo ' selected="selected"'; ?>>---</option><?php
	
				foreach ($romanizations as $romanizationValue => $romanizationLabel):

								?><option value="<?php echo $romanizationValue; ?>"<?php if ($labelData['romanization'] == $romanizationValue) echo ' selected="selected"'; ?>><?php echo $romanizationLabel; ?></option><?php
				
				endforeach;
				
							?></select><?php

				if ($priority < (count($typeLabels) - 1)):

							?><button type="submit" name="swap[<?php echo $priority.'-'.($priority + 1); ?>]" class="swap" title="Priorität reduzieren">►</button><?php
				
				endif;
			endif;	

						?></th><?php
		
		endforeach;
				
					?></tr><?php
					
		foreach ($typeSubdivisions as $typeSubdivision):

					?><tr><?php
		
			if (EXTENDED):
		
						?><td class="checkbox"><input type="checkbox" name="subdivision[]" value="<?php echo $typeSubdivision['subdivision']; ?>" /></td><?php
			
			endif;
			
						?><td class="code"><?php echo implode('</td><td class="code">', array_intersect_key($typeSubdivision, $labels)); ?></td><?php 
				
			foreach ($typeLabels as $priority => $labelData):

						?><td class="name"><?php echo $labelData['subdivisions'][$typeSubdivision['subdivision']]; ?></td><?php
			
			endforeach;
				
				?></tr><?php			
			
		endforeach;
			
			?></table>
		</form><?php
		
	endforeach;
	
	if (EXTENDED):
	
		?><form method="post" class="functions">
			<h2>Functions</h2>
			<table class="subdivision-header-table">
				<tr>
					<th>Command</th>
					<td>
						<select name="do[function]">
							<option value="label">Set subdivision name</option>
							<option value="abbreviation">Set subdivision abbreviation</option>
							<option value="subdivision_code">Set ISO 3166-2 code</option>
						</select>
						<label>Priority level <input type="text" name="do[priority]" size="2" value="<?php echo $functionPriority; ?>"/></label>
						<label>Language <select name="do[language]"><option value="">---</option><?php 

		foreach ($languages as $languageValue => $languageLabel):
		
							?><option value="<?php echo $languageValue; ?>"><?php echo $languageLabel; ?></option><?php
		
		endforeach;
						
						?></select></label>
						<label>Script <select name="do[script]"><option value="">---</option><?php 

		foreach ($scripts as $scriptValue => $scriptLabel):
		
							?><option value="<?php echo $scriptValue; ?>"><?php echo $scriptLabel; ?></option><?php
		
		endforeach;
						
						?></select></label>
						<input type="submit" value="Run command"/>
					</td>
				</tr>
				<tr>
					<th>Values</th>
					<td><textarea id="function" name="function"><?php echo htmlspecialchars($functionValues); ?></textarea></td>
				</tr>
			</table>
		</form><?php	

	endif;
endif;
	
	?></body>
</html>