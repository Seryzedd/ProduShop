<?php

namespace App\Service\String;

class Normalizer
{
    public function stringToList(string $value): array
    {
        // 1. Découpe sur virgule OU espace(s), en respectant les groupes déjà entre quotes
        preg_match_all("/'([^']*)'|\"([^\"]*)\"|([^,\s]+)/", $value, $matches, PREG_SET_ORDER);

        $items = [];
        foreach ($matches as $match) {
            // Récupère la partie non vide (quote simple, quote double, ou mot brut)
            $value = $match[1] !== '' ? $match[1] : ($match[2] !== '' ? $match[2] : $match[3]);
            $value = trim($value);
            if ($value !== '') {
                $items[] = $value;
            }
        }

        return $items;
    }
}