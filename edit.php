<?php

/**
 * Open Global Address Format Project: Address format editor
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
define ('COUNTRY', intval($_GET['country']));

$format						= array_key_exists('format', $_GET) ? $_GET['format'] : null;
$type						= array_key_exists('type', $_GET) ? $_GET['type'] : null;
$doc						= array_key_exists('doc', $_GET) ? $_GET['doc'] : (array_key_exists('doc', $_POST) ? $_POST['doc'] : null);
$field						= $format.'_'.$type;

$formats					= array(
	'Person Standard'		=> array('person', 'standard', ($format == 'person') && ($type == 'standard')),
	'Person natürlich'		=> array('person', 'natural', ($format == 'person') && ($type == 'natural')),
	'Person sortierbar'		=> array('person', 'sortable', ($format == 'person') && ($type == 'sortable')),
	'Adresse vollst.'		=> array('address', 'full', ($format == 'address') && ($type == 'full')),
	'Adresse kurz'			=> array('address', 'short', ($format == 'address') && ($type == 'short')),
);

$bit						= 0;
$formatValues				= array_values($formats);
foreach ($formatValues as $formatIndex => $formatType) {
	if ($formatType[2]) {
		$bit				= pow(2, $formatIndex);
		break;
	}
}

?><!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Country list [Open Global Address Format Project]</title>
        <link rel="stylesheet" type="text/css" href="css/common.css" media="all" />
        <link rel="stylesheet" type="text/css" href="css/menu.css" media="all" />
        <link rel="stylesheet" type="text/css" href="css/edit.css" media="all" />
        <script type="text/javascript" src="javascript/jquery-1.8.2.min.js"></script>
        <script type="text/javascript" src="javascript/jquery.caret.min.js"></script>
        <script type="text/javascript" src="javascript/edit.js"></script>
    </head><?php 

$link					= mysql_connect(HOST, USERNAME, PASSWORD);
if (!$link):
	?><body><p class="error">Could not connect to database. Exit.</p><?php
	exit;
elseif (!mysql_select_db(DBNAME, $link)):
	?><body><p class="error">Could not select database. Exit.</p><?php
else:
	?><body class="noscroll">
		<header><?php
		
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'res'.DIRECTORY_SEPARATOR.'preview.php';
	
	$countryQuery		= 'SELECT `countries`.*, `backend_editor_countries`.`favourite` FROM `countries` INNER JOIN `backend_editor_countries` USING (`country_numeric`) WHERE `countries`.`country_numeric` = '.COUNTRY;
	$countryResult		= mysql_query($countryQuery);
	$country			= mysql_fetch_assoc($countryResult);
	foreach ($formats as $header => $active): 
			?><a href="edit.php?country=<?php echo COUNTRY; ?>&format=<?php echo $active[0]; ?>&type=<?php echo $active[1]; if ($doc) echo '&doc='.$doc; ?>" class="<?php if ($active[2]) echo 'active'; if (!strlen(trim($country[$active[0].'_'.$active[1]]))) echo ' empty'; ?>"><?php echo $header; ?></a><?php
	endforeach;
		?></header>
		<form id="editor" method="post" action="edit.php?country=<?php echo COUNTRY; ?>&format=<?php echo $format; ?>&type=<?php echo $type; ?>">
			<input id="currentdoc" type="hidden" name="doc" value="<?php echo $doc; ?>"/><?php
		
		// Determine next format
		$nextURL						= 'edit.php?'.($doc ? 'doc='.$doc.'&' : '');
		$nextCountry					= mysql_query('SELECT * FROM `countries` WHERE `name_english` > "'.mysql_real_escape_string($country['name_english']).'" ORDER BY `name_english` ASC LIMIT 1');
		$nextCountry					= ($nextCountry && mysql_num_rows($nextCountry)) ? mysql_fetch_assoc($nextCountry) : null;
		$nextCountryURL					= $nextCountry ? $nextURL.'country='.$nextCountry['country_numeric'].'&format='.$format.'&type='.$type : 'index.php#'.COUNTRY;
		if ($formatIndex == (count($formatValues) - 1)) {
			if ($nextCountry) {
				$nextURL				.= 'country='.$nextCountry['country_numeric'].'&format='.$formatValues[0][0].'&type='.$formatValues[0][1];
			} else {
				$nextURL				= 'index.php#'.COUNTRY;
			}
		} else {
			$nextURL					.= 'country='.COUNTRY.'&format='.$formatValues[$formatIndex + 1][0].'&type='.$formatValues[$formatIndex + 1][1];
		}	
	
		// Save address alignment
		$align							= array_key_exists('align', $_POST);
		if ($align) {
			$country['address_align']	= intval($_POST['align']);
		}
		
		// Save postal box label
		$poboxLabel						= array_key_exists('pobox_label', $_POST);
		if ($poboxLabel) {
			$country['pobox_label']		= $_POST['pobox_label'];
		}
		
		// Save postal code format
		$postalCodeFormat				= array_key_exists('postal_code_format', $_POST);
		if ($postalCodeFormat) {
			$country['postal_code_format']		= $_POST['postal_code_format'];
		}
		
		// Save postal code regex
		$postalCodeRegex				= array_key_exists('postal_code_regex', $_POST);
		if ($postalCodeRegex) {
			$country['postal_code_regex']		= $_POST['postal_code_regex'];
		}
		
		// Save submitted value
		if (array_key_exists($field, $_POST)) {
			$country[$field]			= trim($_POST[$field]);
			$nextformat					= array_key_exists('savenextformat', $_POST);
			$nextcountry				= array_key_exists('savenextcountry', $_POST);
			if ($nextformat || $nextcountry || array_key_exists('save', $_POST)) {
				mysql_query('UPDATE `countries` SET `'.$field.'` = "'.mysql_real_escape_string($country[$field]).'"'.
					($align ? ', `address_align` = '.$country['address_align'] : '').
					($poboxLabel ? ', `pobox_label` = "'.mysql_real_escape_string($country['pobox_label']).'"' : '').
					($postalCodeFormat ? ', `postal_code_format` = "'.mysql_real_escape_string($country['postal_code_format']).'"' : '').
					($postalCodeRegex ? ', `postal_code_regex` = "'.mysql_real_escape_string($country['postal_code_regex']).'"' : '').
					' WHERE `country_numeric` = '.COUNTRY
				);
			}
			if ($nextformat || $nextcountry) {
				ob_end_clean();
				header('Location: '.($nextcountry ? $nextCountryURL : $nextURL));
			}
		}

		// Add favourite
		if (array_key_exists('favourite-add', $_POST) && is_array($_POST['favourite-add']) && count($_POST['favourite-add'])) {
			mysql_query('UPDATE `backend_editor_countries` SET `favourite` = `favourite` | '.intval($bit).' WHERE `country_numeric` = "'.mysql_real_escape_string(key($_POST['favourite-add'])).'"');
		}
		
		// Delete favourite
		if (array_key_exists('favourite-delete', $_POST) && is_array($_POST['favourite-delete']) && count($_POST['favourite-delete'])) {
			mysql_query('UPDATE `backend_editor_countries` SET `favourite` = `favourite` & ~'.intval($bit).' WHERE `country_numeric` = "'.mysql_real_escape_string(key($_POST['favourite-delete'])).'"');
		}
		
			?><p class="controls center"><button type="submit" id="savenextformat" name="savenextformat" class="save"><img src="img/save.png" width="16"/> Speichern ⇨</button><button type="submit" id="save" name="save" class="save"><img src="img/save.png" width="16"/> Speichern</button><button type="submit" id="savenextcountry" name="savenextcountry" class="save"><img src="img/save.png" width="16"/> Speichern ⇩</button></p>
			<p class="controls"><a href="<?php echo $nextURL; ?>">Weiter zum nächsten Format</a> · <a href="<?php echo $nextCountryURL; ?>">Weiter zum nächsten Land</a> · <a href="index.php#<?php echo $country['country_alpha_3']; ?>">Zurück zur Länderliste</a></p>
			<div id="favourites"><?php 
					
		$favourites				= mysql_query('SELECT `country_numeric`, `name_english`, `'.$field.'` FROM `countries` INNER JOIN `backend_editor_countries` USING (`country_numeric`) WHERE `backend_editor_countries`.`favourite` & '.$bit);
		if ($favourites && ($favouritesCount = mysql_num_rows($favourites))):
		
				?><div id="favourites-container">
					<p><?php echo $favouritesCount; ?> Favorit(en)</p>
					<ul><?php
				
			while ($favourite = mysql_fetch_assoc($favourites)):

					?><li data-favourite="<?php echo $favourite['country_numeric']; ?>">
						<input type="image" src="img/delete.png" name="favourite-delete[<?php echo $favourite['country_numeric']; ?>]" title="Aus Favoriten entfernen"/>
						<div class="favourite-label"><?php echo htmlspecialchars($favourite['name_english']); ?></div>
						<div class="favourite-format"><?php echo htmlspecialchars($favourite[$field]); ?></div>
					</li><?php
				
			endwhile;
					
					?></ul>
				</div><?php
				
		endif;
		
				?><input type="image" src="img/add.png" name="favourite-add[<?php echo COUNTRY; ?>]" title="Zu Favoriten hinzufügen"/>
			</div>
			<h1><?php echo $country['name_english']; ?></h1><?php
		
		$preview					= '<ul id="preview-options" data-url="preview.php?country='.COUNTRY.'&format='.$format.'&type='.$type.'"><li class="blind"><img src="img/preview.png"/></li>';
		if ($format == 'address'):
			$previewOptions				= array(
				'gender'				=> 'Anrede',
				'honorific-prefix'		=> 'Akad. Präfix',
				'given-name'			=> 'Vorname',
				'additional-name'		=> 'Zus. Namen',
				'family-name'			=> 'Nachname',
				'birth-name'			=> 'Geburtsname',
				'nickname'				=> 'Spitzname',
				'honorific-suffix'		=> 'Akad. Suffix',
				'company'				=> 'Firmenname',
				'department'			=> 'Abteilung',
				'co'					=> 'c/o',
				'street-address'		=> 'Adresse',
				'post-office'			=> 'Postamt',
				'postal-office-box'		=> 'Postfach',
				'postal-code'			=> 'Postleitzahl',
				'postal-address-code'	=> 'Adresscode',
				'locality'				=> 'Ort',
				'subdivision'			=> 'Subdivision',
				'country'				=> 'Land',
			);
			if (!array_key_exists('address_preview_options', $_SESSION)) {
				$_SESSION['address_preview_options'] =
				$activePreviewOptions	= array_fill_keys(array_keys($previewOptions), true);
				
			} else {
				$activePreviewOptions	= array_merge(array_fill_keys(array_keys($previewOptions), false), $_SESSION['address_preview_options']);
			}
			foreach ($previewOptions as $value => $label) {
				$preview				.= '<li class="option"><label><input type="checkbox" name="preview[]" value="'.$value.'"'.($activePreviewOptions[$value] ? ' checked="checked"' : '').'/> <span>'.htmlspecialchars($label).'</span></label></li>';
			}
		
			if ($type == 'full'):
				?><label id="pobox-label" class="half-width"><span>Postfachbezeichner</span><input type="text" name="pobox_label" value="<?php echo htmlspecialchars($country['pobox_label']); ?>"/></label><select name="align" id="align"><option value="0"<?php if (!intval($country['align'])) echo ' selected="selected"'; ?>>Links ausgerichtet</option><option value="1"<?php if (intval($country['align']) == 1) echo ' selected="selected"'; ?>>Mittig ausgerichtet</option><option value="2"<?php if (intval($country['align']) == 2) echo ' selected="selected"'; ?>>Rechts ausgerichtet</option></select>
				<div id="postal-code" class="full-width"><span>Postleitzahlenformat</span><input type="text" name="postal_code_format" value="<?php echo htmlspecialchars($country['postal_code_format']); ?>"/><input type="text" name="postal_code_regex" value="<?php echo htmlspecialchars($country['postal_code_regex']); ?>"/></div>
				<textarea name="<?php echo $field; ?>" id="format" dir="<?php echo (intval($country['align']) == 2) ? 'rtl' : 'ltr'; ?>" class="<?php echo (intval($country['align']) == 1) ? 'centered' : ''; ?>"><?php echo htmlspecialchars($country[$field]); ?></textarea>
				<fieldset id="preview" class="multiline"><legend>Vorschau</legend><?php echo $preview; ?></ul><address dir="<?php echo (intval($country['align']) == 2) ? 'rtl' : 'ltr'; ?>" class="<?php echo (intval($country['align']) == 1) ? 'centered' : ''; ?>"><?php echo formatPreview($country[$field], $activePreviewOptions, $country); ?></address></fieldset><?php
			elseif ($type == 'short'):
				?><label id="pobox-label" class="full-width"><span>Postfachbezeichner</span><input type="text" name="pobox_label" value="<?php echo htmlspecialchars($country['pobox_label']); ?>"/></label>
				<div id="postal-code" class="full-width"><span>Postleitzahlenformat</span><input type="text" name="postal_code_format" value="<?php echo htmlspecialchars($country['postal_code_format']); ?>"/><input type="text" name="postal_code_regex" value="<?php echo htmlspecialchars($country['postal_code_regex']); ?>"/></div>
				<input type="text" name="<?php echo $field; ?>" id="format" value="<?php echo htmlspecialchars($country[$field]); ?>"><fieldset id="preview"><legend>Vorschau</legend><?php echo $preview; ?></ul><address><?php echo formatPreview($country[$field], $activePreviewOptions, $country); ?></address></fieldset><?php
			endif;
			
				?><fieldset>
					<legend>Firma / Person</legend>
					<div class="fields">
					<input type="button" name="name-1" value="Firmenname 1" class="field"/>
					<input type="button" name="name-2" value="Firmenname 2" class="field"/>
					/
					<input type="button" name="gender" value="Anrede" class="field"/><input type="button" name="honorific-prefix" value="Akad. Präfix" class="field"/><input type="button" name="honorific-suffix" value="Akad. Suffix" class="field"/><br/>
					<input type="button" name="given-name" value="Vorname" class="field"/><input type="button" name="additional-name" value="Mittelname" class="field"/><input type="button" name="family-name" value="Nachname" class="field"/><input type="button" name="birth-name" value="Geburtsname" class="field"/><input type="button" name="nickname" value="Spitzname" class="field"/>
					</div>
				</fieldset>
				<fieldset>
					<legend>Standort / Adresse</legend>
					<div class="fields">
					<input type="button" name="department" value="Abteilung" class="field"/><input type="button" name="co" value="c/o" class="field"/>
					/
					<input type="button" name="street-address" value="Adresse" class="field"/><input type="button" name="postal-code" value="Postleitzahl" class="field"/><input type="button" name="postal-address-code" value="Adresscode" class="field"/><input type="button" name="locality" value="Ort" class="field"/><br/>
					<input type="button" name="post-office" value="Postamt" class="field"/><input type="button" name="postal-office-box" value="Postfach" class="field"/><input type="button" name="pobox-label" value="Postfach-Bezeichner" class="field"/>
					</div>
				</fieldset>
				<fieldset>
					<legend>Land</legend>
					<div class="fields">
					<input type="button" name="country-name" value="Land lokal" class="field" data-one-of="country"/><input type="button" name="country-intl" value="Land international" class="field" data-one-of="country"/><input type="button" name="country-iso" value="ISO-Nummer" class="field" data-one-of="country"/><input type="button" name="country-iso-2" value="ISO-2-Code" class="field" data-one-of="country"/><input type="button" name="country-iso-3" value="ISO-3-Code" class="field" data-one-of="country"/>
					</div>
				</fieldset><?php 
				
			$subdivisionQuery				= 'SELECT `country_subdivision_types`.`type_name` FROM `country_subdivisions` INNER JOIN `country_subdivision_types` USING (`type`) WHERE `country_subdivisions`.`country_numeric` = '.COUNTRY.' GROUP BY `country_subdivisions`.`type` ORDER BY `country_subdivision_types`.`type_name` ASC';
			$subdivisionResult				= mysql_query($subdivisionQuery);
			if ($subdivisionResult && mysql_num_rows($subdivisionResult)):
				
				?><fieldset>
					<legend>Subdivision</legend>
					<div class="fields"><?php
			
				while($subdivision = mysql_fetch_assoc($subdivisionResult)):
					
					?><input type="button" name="subdivision-<?php echo $subdivision['type_name']; ?>-name" value="<?php echo ucfirst(str_replace('-', ' ', $subdivision['type_name'])); ?>" class="field" data-one-of="subdivision"/><input type="button" name="subdivision-<?php echo $subdivision['type_name']; ?>-code" value="<?php echo ucfirst(str_replace('-', ' ', $subdivision['type_name'])); ?> (Code)" class="field" data-one-of="subdivision"/><input type="button" name="subdivision-<?php echo $subdivision['type_name']; ?>-abbr" value="<?php echo ucfirst(str_replace('-', ' ', $subdivision['type_name'])); ?> (Abk.)" class="field" data-one-of="subdivision"/><?php
					
				endwhile;
			
					?></div>
				</fieldset><?php 
			
			endif;
				
				?><fieldset>
					<legend>Makros</legend>
				<div class="fields">
				<input type="button" name="company" value="Vollständiger Firmenname" class="field"/><br/>
				<input type="button" name="person-standard" value="Person Standard" class="field" data-one-of="person"/><input type="button" name="person-natural" value="Person natürlich" class="field" data-one-of="person"/><input type="button" name="person-sortable" value="Person sortierbar" class="field" data-one-of="person"/><br/>
				<input type="button" name="country-full" value="Vollständiger Ländername (abgesetzt)" class="field" data-one-of="country"/><br/>
				<input type="button" name="««locality-full::?»»" value="locality-full" class="control" data-control-type="wrap" title="&lt;span label=&quot;locality-full&quot;/&gt;"/>
				</div>
			</fieldset><?php
		
		elseif ($format == 'person'):
	
			$previewOptions				= array(
				'gender'				=> 'Anrede',
				'honorific-prefix'		=> 'Akad. Präfix',
				'given-name'			=> 'Vorname',
				'additional-name'		=> 'Zus. Namen',
				'family-name'			=> 'Nachname',
				'birth-name'			=> 'Geburtsname',
				'nickname'				=> 'Spitzname',
				'honorific-suffix'		=> 'Akad. Suffix',
			);
			if (!array_key_exists('person_preview_options', $_SESSION)) {
				$_SESSION['person_preview_options'] =
				$activePreviewOptions	= array_fill_keys(array_keys($previewOptions), true);
					
			} else {
				$activePreviewOptions	= array_merge(array_fill_keys(array_keys($previewOptions), false), $_SESSION['person_preview_options']);
			}
			foreach ($previewOptions as $value => $label) {
				$preview				.= '<li class="option"><label><input type="checkbox" name="preview[]" value="'.$value.'"'.($activePreviewOptions[$value] ? ' checked="checked"' : '').'/> <span>'.htmlspecialchars($label).'</span></label></li>';
			}
				
			?><input type="text" name="<?php echo $field; ?>" id="format" value="<?php echo htmlspecialchars($country[$field]); ?>">
			<fieldset id="preview"><legend>Vorschau</legend><?php echo $preview; ?></ul><address><?php echo formatPreview($country[$field], $activePreviewOptions, $country); ?></address></fieldset>
			<fieldset>
				<legend>Person</legend>
				<div class="fields">
				<input type="button" name="gender" value="Anrede" class="field"/><input type="button" name="honorific-prefix" value="Akad. Präfix" class="field"/><input type="button" name="honorific-suffix" value="Akad. Suffix" class="field"/><br/>
				<input type="button" name="given-name" value="Vorname" class="field"/><input type="button" name="additional-name" value="Mittelname" class="field"/><input type="button" name="family-name" value="Nachname" class="field"/><input type="button" name="birth-name" value="Geburtsname" class="field"/><input type="button" name="nickname" value="Spitzname" class="field"/>
				</div>
			</fieldset><?php
		
		endif;
		
			?><fieldset>
				<legend>Steuerung</legend>
				<div class="fields">
				<input type="button" name="||" value="ODER" class="control" data-control-type="replace" title="&lt;or/&gt;"/><input type="button" name="((?))" value="GRUPPE" class="control" data-control-type="wrap" title="&lt;group/&gt;"/><input type="button" name="««MARKE::?»»" value="MARKE" class="control" data-control-type="wrap" title="&lt;span label=&quot;...&quot;/&gt;"/><input type="button" name="[[?]]" value="UMSCHLAG" class="control" data-control-type="wrap" title="&lt;trim/&gt;"/><input type="button" name="↗?↖" value="GROSS" class="control" data-control-type="wrap" title="&lt;trans type=&quot;upper&quot;/&gt;"/><input type="button" name="↘?↙" value="KLEIN" class="control" data-control-type="wrap" title="&lt;trans type=&quot;lower&quot;/&gt;"/><input type="button" name="--" value="LEERZEILE" class="control" data-control-type="replace" title="&lt;newline/&gt;"/>
				</div>
			</fieldset>
		</form>
		<div id="doc">
			<nav id="docnav"><?php
			
		$hasUPU						= @is_file('res'.DIRECTORY_SEPARATOR.'upu-pdf'.DIRECTORY_SEPARATOR.strtolower($country['country_alpha_3']).'En.pdf');
		$iframeUrls					= array();
		if ($hasUPU) {
			$iframeUrls['upu']		= 'res/upu-pdf/'.strtolower($country['country_alpha_3']).'En.pdf';
		}
		$iframeUrls['ad']			= 'res/addressdoctor.php?country='.$country['country_alpha_3'];
		$iframeUrls['col']			= 'http://www.columbia.edu/~fdc/postal/';
		$iframeUrls['wiki']			= 'http://en.wikipedia.org/wiki/ISO_3166-2:'.$country['country_alpha_2'];
		$iframeUrls['country']		= 'subdivision.php?country='.COUNTRY;
		$iframeURL					= $doc ? $iframeUrls[$doc] : current($iframeUrls);
		
		if ($hasUPU):
		
				?><a href="<?php echo $iframeUrls['upu']; ?>" target="docframe" onclick="$('#currentdoc')[0].value='upu'">UPU</a><?php
		
		endif;
		
				?><a href="<?php echo $iframeUrls['ad']; ?>" target="docframe" onclick="$('#currentdoc')[0].value='ad'">AddressDoctor</a>
				<a href="<?php echo $iframeUrls['col']; ?>" target="docframe" onclick="$('#currentdoc')[0].value='col'">Columbia</a><?php
				
		if ($country['country_alpha_2']):
		
				?><a href="<?php echo $iframeUrls['wiki']; ?>" target="docframe" onclick="$('#currentdoc')[0].value='wiki'">Wikipedia ISO 3166-2</a><?php
		
		endif;
				?><span><a href="<?php echo $iframeUrls['country']; ?>" target="docframe" onclick="$('#currentdoc')[0].value='country'">Subdivisionen</a>
				(<a href="subdivisions.php?country=<?php echo COUNTRY; ?>" target="_top" class="append">Bearbeiten</a>)</span>
			</nav>
			<div id="pdf"><iframe src="<?php echo $iframeURL ?>" name="docframe" id="docframe"></iframe></div>
		</div><?php
		
endif;

	?></body>
</html>