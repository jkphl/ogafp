<?php

header('Content-Type: text/plain');
header('Content-Disposition: attachment;filename="postal-towns.sql"');
$subdivisions			= array();
$subdivisionNames		= array();
$postalTowns			= file('postal-towns.txt');

foreach ($postalTowns as $line => $postalTown) {
	$postalTown								= trim($postalTown);
	if (strlen($postalTown)) {
		list($postalCode, $postalTown)		= explode("\t", $postalTown);
		$postalTownNormalized				= strtoupper(preg_replace("%[^a-z]%i", '', trim($postalTown)));
		$id									= strtoupper(substr(sha1($postalCode.$postalTownNormalized), 0, 5));
		if (array_key_exists($id, $subdivisions)) {
			die($line.': '.$postalCode.' '.$postalTown);
		} else {
			$subdivisions[$id]				= '("'.implode('","', array(
				'GB-'.$id,
				826,
				$id,
				96
			)).'")';
			$subdivisionNames[$id]			= '("'.implode('","', array(
					'GB-'.$id,
					0,
					1824,
					215,
					$postalTown
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