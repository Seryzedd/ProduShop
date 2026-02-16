<?php

namespace App\Service\Translation;

use \Locale;
use \IntlCalendar;

class Languages
{
    
    public function getLanguagesKeys(): array
    {
        $locales = IntlCalendar::getAvailableLocales();
        
        return $locales;
    }
}