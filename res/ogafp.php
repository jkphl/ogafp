<?php

define('HOST', 'localhost');
define('USERNAME', 'ogafp');
define('PASSWORD', 'f@4UqO9$');
define('DBNAME', 'ogafp');

/**
 * Ermitteln aller verfügbaren Romanisierungsmethoden
 *
 * @return array            Verfügbare Romanisierungen
 */
function getRomanizations()
{
    $romanizations = array();
    $romanizationResult = mysql_query('SELECT * FROM `romanizations` ORDER BY `romanization_name`');
    while ($romanization = mysql_fetch_assoc($romanizationResult)) {
        $romanizations[$romanization['romanization']] = $romanization['romanization_name'];
    }
    asort($romanizations);
    return $romanizations;
}

/**
 * Ermitteln aller verfügbaren Sprachen
 *
 * @return array            Verfügbare Sprachen
 */
function getLanguages()
{
    $languages = array();
    $languageResult = mysql_query('SELECT * FROM `languages` WHERE NOT ISNULL(`language_iso_639_2_tac`) ORDER BY `language_iso_639_2_tac`');
    while ($language = mysql_fetch_assoc($languageResult)) {
        $languages[$language['language']] = $language['language_iso_639_2_tac'];
    }
    asort($languages);
    return $languages;
}

/**
 * Ermitteln aller verfügbaren Skripte
 *
 * @return array            Verfügbare Skripte
 */
function getScripts()
{
    $scripts = array();
    $scriptResult = mysql_query('SELECT * FROM `scripts` ORDER BY `script_code`');
    while ($script = mysql_fetch_assoc($scriptResult)) {
        $scripts[$script['script']] = $script['script_code'];
    }
    asort($scripts);
    return $scripts;
}

/**
 * Ermitteln aller verfügbaren Subdivisionstypen
 *
 * @return array            Verfügbare Subdivisionstypen
 */
function getTypes()
{
    $types = array();
    $typeResult = mysql_query('SELECT * FROM `country_subdivision_types` ORDER BY `type_name`');
    while ($type = mysql_fetch_assoc($typeResult)) {
        $types[$type['type']] = $type['type_name'];
    }
    asort($types);
    return $types;
}
