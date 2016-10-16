<?php

header('Content-Type: text/plain');
header('Content-Disposition: attachment;filename="former-postal-counties.sql"');
$subdivisions			= array();
$subdivisionNames		= array();
$countyNames			= array();
$postalCounties			= file('former-postal-counties.txt');

foreach ($postalCounties as $line => $postalCounty) {
	$postalCounty							= trim($postalCounty);
	if (strlen($postalCounty) && empty($countyNames[$postalCounty])) {
		$countyNames[$postalCounty]			= true;
		$id									= strtoupper(substr(sha1($postalCounty), 0, 5));
		if (array_key_exists($id, $subdivisions)) {
			die($line.': '.$postalCounty);
		} else {
			$subdivisions[$id]				= '("'.implode('","', array(
				'GB-'.$id,
				826,
				$id,
				97
			)).'")';
			$subdivisionNames[$id]			= '("'.implode('","', array(
					'GB-'.$id,
					0,
					1824,
					215,
					$postalCounty
			)).'")';
		}
	}
}

if (count($subdivisions)) {
	echo 'REPLACE INTO `country_subdivisions` (`subdivision`, `country_numeric`, `abbreviation`, `type`) VALUES 
'.implode(",\n", $subdivisions).";\n\n";
	echo 'REPLACE INTO `country_subdivision_names` (`subdivision`, `priority`, `language`, `script`, `name`) VALUES 
'.implode(",\n", $subdivisionNames).';';
}

?>