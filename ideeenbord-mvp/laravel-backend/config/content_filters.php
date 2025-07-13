<?php

return [

    /*
     |---------------------------------------------------------------
     | Regex-patronen om spam, promo of ongepaste content te blokkeren
     |---------------------------------------------------------------
     |  ➜ Elke waarde is een regex-pattern (delimiter “/” al aanwezig)
     |  ➜ Voeg zelf regels toe of wijzig naar wens
     */
    'patterns' => [

        // 1. LINKS  ────────────────────────────────────────────────
        //    http:// https:// ftp://  of  www.  of  domein.tld
        'url_http' => '/\b(?:https?|ftp):\/\/\S+/i',
        'url_www' => '/\bwww\.\S+/i',
        'url_domain' => '/\b[0-9A-Za-z.-]+\.[A-Za-z]{2,}\b/',

        // 2. E-MAILADRESSEN
        'email' => '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i',

        // 3. TELEFOONNUMMERS  ─────────────────────────────────────
        //    06xxxxxxxx  of  0xx-xxxxxxx  of >6 cijfers aaneen
        'phone_06' => '/\b06[-\s]?\d{8}\b/',
        'phone_nl_city' => '/\b0\d{2,3}[-\s]?\d{6,8}\b/',
        'long_digits' => '/\d{7,}/',

        // 4. PRIJZEN (€ of $ of £)  ───────────────────────────────
        'currency' => '/[€$£]\s*\d+(?:[.,]\d{1,2})?/',

        // 5. >10x hetzelfde karakter (spam / keyboard smash)
        'repeat_char' => '/(.)\1{9,}/',

    ],
];
