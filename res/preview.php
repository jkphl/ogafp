<?php

set_include_path(get_include_path().PATH_SEPARATOR.dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'syslib');

/**
 * Formatieren einer Vorschauanzeige zu einem Anschriftenformat
 * 
 * @param string $str				Anschriftenformat
 * @param array $options			Vorschauoptionen
 * @param array $country			Länderdatensatz
 * @return string					Anschriftenvorschau
 */
function formatPreview($str, array $options, array $country) {
	
	// Ersetzen von Makros im Anschriftenformat
	$str						= strtr($str, array(
		'<<person-standard>>'	=> $country['person_standard'],	
		'<<person-natural>>'	=> $country['person_natural'],	
		'<<person-sortable>>'	=> $country['person_sortable'],
		'<<pobox-label>>'		=> $country['pobox_label'],
		'<<company>>'			=> "<<name-1>>\n<<name-2>>",	
	));
	
	require_once 'Firelike'.DIRECTORY_SEPARATOR.'Format'.DIRECTORY_SEPARATOR.'Address.php';
	require_once 'Firelike'.DIRECTORY_SEPARATOR.'Format'.DIRECTORY_SEPARATOR.'Locale.php';
	require_once 'Firelike'.DIRECTORY_SEPARATOR.'Exception.php';
	try {
		$formatDom				= Firelike_Format_Address::fromPlaintext($str);
	
		// Ersetzungen
		$formatReplace			= (array_key_exists('country', $options) && $options['country']) ? array(
			Firelike_Format_Locale::COUNTRY_NAME				=> $country['name_local'],
			Firelike_Format_Locale::COUNTRY_ISO					=> $country['country_numeric'],
			Firelike_Format_Locale::COUNTRY_ISO_2				=> $country['country_alpha_2'],
			Firelike_Format_Locale::COUNTRY_ISO_3				=> $country['country_alpha_3'],
			Firelike_Format_Locale::COUNTRY_INTL				=> $country['name_english'],
		) : array();
	
		// Firma
		if (array_key_exists('company', $options) && $options['company']) {
			$formatReplace[Firelike_Format_Locale::NAME_1]			= 'tollwerk® GmbH';
			$formatReplace[Firelike_Format_Locale::NAME_2]			= 'kommunikation . grafik . design';
		}
	
		foreach (array(
			Firelike_Format_Locale::DEPARTMENT					=> 'Personalabteilung',
			Firelike_Format_Locale::GENDER						=> 'Herr',
			Firelike_Format_Locale::GIVEN_NAME					=> 'Harald',
			Firelike_Format_Locale::ADDITIONAL_NAME				=> 'Franz',
			Firelike_Format_Locale::FAMILY_NAME					=> 'Schmidt',
			Firelike_Format_Locale::BIRTH_NAME					=> 'Schmidt',
			Firelike_Format_Locale::NICKNAME					=> 'Dirty Harry',
			Firelike_Format_Locale::HONORIFIC_PREFIX			=> 'Dr.',
			Firelike_Format_Locale::HONORIFIC_SUFFIX			=> 'jr.',
			Firelike_Format_Locale::CO							=> 'Hans Meiser',
			Firelike_Format_Locale::STREET_ADDRESS				=> 'Lindenaststraße 15',
			Firelike_Format_Locale::LOCALITY					=> 'Nürnberg',
			Firelike_Format_Locale::POSTAL_CODE					=> '90409',
			Firelike_Format_Locale::POSTAL_ADDRESS_CODE			=> '456769',
			Firelike_Format_Locale::POST_OFFICE					=> 'Postamt10',
			Firelike_Format_Locale::POST_OFFICE_BOX				=> '123',
		) as $option => $value) {
			if (array_key_exists($option, $options) && $options[$option]) {
				$formatReplace[$option]									= $value;
			}
		}
	
		if (array_key_exists('subdivision', $options) && $options['subdivision']) {
			$subdivisionResult	= mysql_query('SELECT `country_subdivisions`.`subdivision_code`, `country_subdivisions`.`abbreviation`,  `country_subdivision_names`.`name` FROM `country_subdivisions` INNER JOIN `country_subdivision_names` ON `country_subdivision_names`.`subdivision` = `country_subdivisions`.`subdivision` AND `country_subdivision_names`.`priority` = 0 WHERE `country_subdivisions`.`country_numeric` = '.$country['country_numeric'].' GROUP BY `country_subdivisions`.`country_numeric`');
			if ($subdivisionResult && mysql_num_rows($subdivisionResult)) {
				$subdivision											= mysql_fetch_assoc($subdivisionResult);
				$formatReplace[Firelike_Format_Locale::REGION_CODE]		= $subdivision['subdivision_code'];
				$formatReplace[Firelike_Format_Locale::REGION_ABBR]		= $subdivision['abbreviation'];
				$formatReplace[Firelike_Format_Locale::REGION_NAME]		= $subdivision['name'];
			}
		}
		
		$result					= $formatDom->format($formatReplace);
	
		// 	echo htmlspecialchars($formatDom->saveXML());
	} catch (Firelike_Exception $e) {
		$result					= '<span>Vorschau aufgrund folgender Fehler nicht möglich:</span><ul>';
		/* @var $error LibXMLError */
		foreach ($e->getMultiline() as $error) {
			$result				.= '<li>'.htmlspecialchars($error->message).'</li>';
		}
		$result					.= '</ul>';
	}
	
	return $result;
}