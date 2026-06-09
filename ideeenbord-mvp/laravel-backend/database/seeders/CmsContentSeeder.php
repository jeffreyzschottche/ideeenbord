<?php

namespace Database\Seeders;

use App\Models\CmsField;
use App\Models\CmsPage;
use Illuminate\Database\Seeder;

/**
 * Rewrites the key CMS copy in one consistent tone of voice that makes clear
 * what Ideeënbord is. Idempotent: run safely with
 *   php artisan db:seed --class=CmsContentSeeder
 *
 * Only the listed fields are touched; everything else (images, stats numbers,
 * other pages) is left untouched.
 */
class CmsContentSeeder extends Seeder
{
    public function run(): void
    {
        $home = [
            'home-tagline' => 'Jouw stem telt bij merken',
            'home-title' => 'Deel je idee. Merken luisteren.',
            'home-description' =>
                'Ideeënbord is hét platform waar je ideeën, wensen en verbeterpunten '
                .'deelt met de merken die jij gebruikt. Plaats een idee, stem op dat van '
                .'anderen en bepaal samen wat merken morgen maken.',
            'home-cta' => 'Plaats jouw idee',

            'motivation-title' => 'Voel je gehoord — en zie er resultaat van',
            'motivation-description' =>
                'Te vaak gaan goede ideeën verloren omdat klanten geen podium hebben. '
                .'Op Ideeënbord komt jouw idee rechtstreeks bij het merk, krijg je '
                .'reacties en zie je wat ermee gebeurt.',

            'howto-step1-title' => '1. Plaats je idee',
            'howto-step1-description' =>
                'Kies een merk en deel je idee, wens of verbeterpunt — in een paar zinnen.',
            'howto-step2-title' => '2. Verzamel steun',
            'howto-step2-description' =>
                'Andere gebruikers stemmen op je idee. Hoe meer steun, hoe groter het bereik.',
            'howto-step3-title' => '3. Merken gaan ermee aan de slag',
            'howto-step3-description' =>
                'Het merk reageert op populaire ideeën en houdt je op de hoogte van wat er gebeurt.',

            'brandslider-title' => 'Merken die luisteren naar hun klanten',
            'options-title' => 'Voor iedereen die invloed wil',
            'options-intro' =>
                'Of je nu consument bent of een merk vertegenwoordigt — op Ideeënbord '
                .'breng je vraag en aanbod samen rond échte ideeën.',
        ];

        $about = [
            'about-title' => 'Wat is Ideeënbord?',
            'about-description' =>
                'Ideeënbord verbindt consumenten en merken rond échte ideeën. '
                .'Gebruikers delen wensen, verbeterpunten en nieuwe productideeën; '
                .'merken luisteren, reageren en halen waardevolle inzichten uit de data. '
                .'Zo krijgt iedereen een stem — en maken merken producten waar mensen op zitten te wachten.',
        ];

        $this->upsert('home', $home);
        $this->upsert('about', $about);
    }

    private function upsert(string $pageTitle, array $fields): void
    {
        $page = CmsPage::firstOrCreate(['title' => $pageTitle]);

        foreach ($fields as $key => $value) {
            $field = CmsField::firstOrNew(['page_id' => $page->id, 'key' => $key]);
            // Only set label/type for brand-new fields; keep existing admin config.
            if (! $field->exists) {
                $field->type = 'text';
                $field->label = $this->labelFor($key);
            }
            $field->value = $value;
            $field->save();
        }
    }

    private function labelFor(string $key): string
    {
        return ucfirst(str_replace('-', ' ', $key));
    }
}
