<?php


namespace App\Handlers;

class PictureHandler
{
    private const IMAGE_PATH_PREFIX = 'images/ressources/';

    public function getImageForCategory(string $category): string
    {
        switch ($category) {
            case 'CARCASSE':
                return self::IMAGE_PATH_PREFIX . 'carcasse.png';
            case 'MORCEAU':
                return self::IMAGE_PATH_PREFIX . 'Morceau.png';
            case 'DEMI-CARCASSE':
                return self::IMAGE_PATH_PREFIX . 'demiCarcasse.png';
            case 'PRODUIT':
                return self::IMAGE_PATH_PREFIX . 'steak.png';
            case 'ANIMAL':
                return self::IMAGE_PATH_PREFIX . 'vache.png';
            default:
                return self::IMAGE_PATH_PREFIX . 'default.png'; //need change
        }
    }
}
