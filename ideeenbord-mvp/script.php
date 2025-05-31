<?php

// Naam van de map die we willen doorzoeken
// Zorg ervoor dat dit pad correct is ten opzichte van waar je dit script uitvoert.
// Bijvoorbeeld, als ideeenbord een directe submap is, is 'ideeenbord' correct.
$targetDirectory = 'ideeenbord';

// Naam van het output bestand waar de resultaten in worden opgeslagen
$outputFile = 'gevonden_bestanden.txt';

// Woorden en patronen waar we naar zoeken in de bestandsinhoud
// Deze zijn hoofdlettergevoelig, dus 'fetch(' is anders dan 'Fetch('.
// Voeg varianten toe indien nodig (bijv. 'try{' zonder spatie).
$searchPatterns = [
    'fetch(',
    'apiFetch(',
    'try {',
    'catch ('
];

// Mappen die overgeslagen moeten worden tijdens de zoektocht
// Dit voorkomt dat we irrelevante bestanden in build- of dependency-mappen doorzoeken.
$excludedDirectories = [
    'node_modules',
    '.nuxt',
    '.output',
    '.git', // Vaak handig om ook over te slaan
    'vendor' // Voor PHP Composer projecten
];

// Toegestane bestandsextensies om te doorzoeken
// Voeg hier alle extensies toe waarvan je verwacht dat de code erin staat.
$allowedExtensions = ['php', 'js', 'json', 'vue', 'ts', 'jsx', 'tsx'];

$foundFiles = []; // Array om de paden van gevonden bestanden in op te slaan

echo "Starten met zoeken in de map: '{$targetDirectory}'...\n";

// --- Controleer of de doelmap bestaat ---
if (!is_dir($targetDirectory)) {
    die("Fout: De map '{$targetDirectory}' is niet gevonden in de huidige directory. Controleer het pad.\n");
}

/**
 * Recursief door mappen zoeken naar bestanden die aan de criteria voldoen.
 *
 * @param string $directory De huidige map die doorzocht wordt.
 * @param array $searchPatterns Array van strings om naar te zoeken.
 * @param array $allowedExtensions Array van toegestane bestandsextensies.
 * @param array $excludedDirectories Array van mappen die overgeslagen moeten worden.
 * @param array $foundFiles Referentie naar de array waarin gevonden bestanden worden opgeslagen.
 */
function searchDirectory(string $directory, array $searchPatterns, array $allowedExtensions, array $excludedDirectories, array &$foundFiles): void {
    $items = @scandir($directory); // Gebruik @ om waarschuwingen te onderdrukken bij onleesbare mappen

    if ($items === false) {
        echo "Waarschuwing: Kan map '{$directory}' niet lezen. Overslaan.\n";
        return;
    }

    foreach ($items as $item) {
        // Sla standaard '.' en '..' mappen over
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $directory . DIRECTORY_SEPARATOR . $item;

        if (is_dir($path)) {
            // Als het een map is, controleer of deze overgeslagen moet worden
            if (in_array($item, $excludedDirectories)) {
                echo "Map '{$item}' overgeslagen.\n";
                continue; // Ga naar het volgende item in de lus
            }
            // Doorzoek de submap recursief
            searchDirectory($path, $searchPatterns, $allowedExtensions, $excludedDirectories, $foundFiles);
        } elseif (is_file($path)) {
            // Als het een bestand is, controleer de extensie
            $fileExtension = pathinfo($path, PATHINFO_EXTENSION);
            if (in_array(strtolower($fileExtension), $allowedExtensions)) {
                $content = @file_get_contents($path); // Gebruik @ om waarschuwingen te onderdrukken bij onleesbare bestanden
                if ($content === false) {
                    echo "Waarschuwing: Kan bestand '{$path}' niet lezen. Overslaan.\n";
                    continue; // Ga naar het volgende item
                }

                // Zoek naar de gedefinieerde patronen in de inhoud van het bestand
                foreach ($searchPatterns as $pattern) {
                    if (strpos($content, $pattern) !== false) {
                        $foundFiles[] = $path; // Voeg het pad toe aan de lijst
                        break; // Stop met zoeken in dit bestand zodra een match is gevonden
                    }
                }
            }
        }
    }
}

// --- Start de zoektocht ---
searchDirectory($targetDirectory, $searchPatterns, $allowedExtensions, $excludedDirectories, $foundFiles);

// --- Verwijder duplicaten (voor het geval een bestand meerdere patronen bevat) ---
$foundFiles = array_unique($foundFiles);

// --- Schrijf de gevonden bestanden naar het output bestand ---
if (!empty($foundFiles)) {
    // Sorteer de bestandsnamen alfabetisch voor een nette output
    sort($foundFiles);
    $fileContent = implode("\n", $foundFiles); // Elk bestand op een nieuwe regel

    if (file_put_contents($outputFile, $fileContent) !== false) {
        echo "\nZoektocht voltooid. " . count($foundFiles) . " bestanden gevonden.\n";
        echo "De namen van de gevonden bestanden zijn opgeslagen in: '{$outputFile}'\n";
    } else {
        echo "\nFout: Kan het bestand '{$outputFile}' niet schrijven. Controleer de schrijfrechten.\n";
    }
} else {
    echo "\nGeen bestanden gevonden die voldoen aan de criteria.\n";
}

?>
