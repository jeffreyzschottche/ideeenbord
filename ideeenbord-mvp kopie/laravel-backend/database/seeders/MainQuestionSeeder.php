<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MainQuestion;

class MainQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            [
                'text' => 'Als je aan [merknaam] denkt, welk gevoel komt dan het eerst in je op?',
                'answers' => ['Ontspannen', 'Energiek', 'Vertrouwd', 'Nieuwsgierig', 'Geïnspireerd'],
            ],
            [
                'text' => 'Hoe vaak komt [merknaam] de laatste tijd in je gesprekken met anderen ter sprake?',
                'answers' => ['Nooit', 'Zelden', 'Soms', 'Regelmatig', 'Vaak'],
            ],
            [
                'text' => 'Stel dat [merknaam] een persoon zou zijn, welke drie woorden zouden die persoon het beste omschrijven?',
                'answers' => ['Antwoord in drie losse woorden'],
            ],
            [
                'text' => 'Op een schaal van 1 tot 5, waarbij 1 staat voor \'helemaal niet\' en 5 voor \'heel veel\', in hoeverre past [merknaam] bij jouw levensstijl of interesses?',
                'answers' => ['1', '2', '3', '4', '5'],
            ],
            [
                'text' => 'Welke andere [categorie, bijv. tv-series, voetbalclubs, supermarkten] ken je die vergelijkbaar is met [merknaam]?',
                'answers' => ['Geen', 'Eén of twee', 'Meer dan twee'],
            ],
            [
                'text' => 'Als je [merknaam] zou aanraden aan iemand, wat zou dan je belangrijkste reden zijn?',
                'answers' => ['Kwaliteit', 'Entertainment', 'Gemeenschap', 'Betrouwbaarheid', 'Anders'],
            ],
            [
                'text' => 'Hoe zou je de \'sfeer\' of \'uitstraling\' van [merknaam] omschrijven in één woord?',
                'answers' => ['Modern', 'Traditioneel', 'Spannend', 'Gemakkelijk', 'Gedreven'],
            ],
            [
                'text' => 'In vergelijking met andere [categorie] die je kent, waar zou je [merknaam] plaatsen op een populariteitsschaal?',
                'answers' => ['Laag', 'Gemiddeld', 'Hoog', 'Zeer hoog'],
            ],
            [
                'text' => 'Heb je recentelijk nog iets gezien of gehoord over [merknaam] dat je is bijgebleven?',
                'answers' => ['Ja', 'Nee'],
            ],
            [
                'text' => 'Als [merknaam] een kleur zou zijn, welke kleur zou dat dan zijn volgens jou?',
                'answers' => ['Antwoord in één kleur'],
            ],
        ];

        foreach ($questions as $q) {
            MainQuestion::create($q);
        }
    }
}


