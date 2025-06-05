<?php

// Definieer hier het pad naar de map die je wilt doorzoeken.
// Voorbeelden:
// $path = './components';    // Zoekt in de 'components' map in de huidige directory
// $path = '../components';   // Zoekt in de 'components' map één niveau hoger dan de huidige directory
// $path = '/var/www/html/components'; // Een absoluut pad
$path = './components/user'; // Pas dit pad aan naar de gewenste map!

$outputFileName = 'map_inhoud.txt'; // De naam van het bestand waar de inhoud wordt opgeslagen

// Controleer of het opgegeven pad een geldige map is
if (!is_dir($path)) {
    die("Fout: Het opgegeven pad '$path' is geen geldige map of bestaat niet.");
}

// Open het uitvoerbestand om te schrijven (of maak het aan als het niet bestaat)
$outputFile = fopen($outputFileName, 'w') or die("Kan het bestand '$outputFileName' niet openen om te schrijven!");

// Scan de opgegeven map
$files = scandir($path);

foreach ($files as $file) {
    // Sla '.' en '..' over, dit zijn verwijzingen naar de huidige en bovenliggende map
    if ($file === '.' || $file === '..') {
        continue;
    }

    $filePath = $path . DIRECTORY_SEPARATOR . $file;

    // Controleer of het een regulier bestand is (geen map)
    if (is_file($filePath)) {
        // Schrijf de bestandsnaam als titel
        fwrite($outputFile, "--- " . $file . " ---\n");

        // Lees de inhoud van het bestand
        $content = file_get_contents($filePath);
        if ($content === false) {
            fwrite($outputFile, "Kon de inhoud van het bestand '$file' niet lezen.\n\n");
        } else {
            // Schrijf de inhoud naar het uitvoerbestand
            fwrite($outputFile, $content . "\n\n");
        }
    }
}

// Sluit het uitvoerbestand
fclose($outputFile);

echo "Alle bestanden uit '$path' zijn succesvol samengevoegd in '$outputFileName'.\n";

?>
