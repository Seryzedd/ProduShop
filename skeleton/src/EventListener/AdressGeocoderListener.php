<?php
// src/EventListener/AdressGeocoderListener.php

namespace App\EventListener;

use App\Entity\User\PostalAdress\Adress;
use App\Service\GeocoderService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

/**
 * Géocode automatiquement une adresse avant chaque insertion ou mise à jour
 * si les coordonnées sont absentes ou si la rue/CP/pays a changé.
 */
#[AsEntityListener(event: Events::prePersist, entity: Adress::class)]
#[AsEntityListener(event: Events::preUpdate,  entity: Adress::class)]
class AdressGeocoderListener
{
    public function __construct(
        private readonly GeocoderService $geocoder,
    ) {}

    public function prePersist(Adress $adress): void
    {
        $this->fillCoordinates($adress);
    }

    public function preUpdate(Adress $adress): void
    {
        // Re-géocode seulement si l'adresse a changé ou si les coords sont vides
        if (!$adress->hasCoordinates()) {
            $this->fillCoordinates($adress);
        }
    }

    private function fillCoordinates(Adress $adress): void
    {
        $full = $adress->getFullAddress();
        if (empty($full)) return;

        $coords = $this->geocoder->geocode($full);
        if ($coords === null) return;

        [$lat, $lng] = $coords;
        $adress->setLatitude($lat);
        $adress->setLongitude($lng);
    }
}