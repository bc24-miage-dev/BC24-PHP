<?php
namespace App\Handlers;

class RoleConversionWithBlockChainHandler

{
    public function convertRoleToBlockchainRole(string $role): string
    {
        switch ($role) {
            case 'ROLE_ELEVEUR':
                return 'BREEDER';
            case 'ROLE_EQUARRISSEUR':
                return 'SLAUGHTERER';
            case 'ROLE_USINE':
                return 'MANUFACTURER';
            case 'ROLE_ADMIN':
                return 'Admin';
            case 'ROLE_TRANSPORTEUR':
                return 'Transporteur';
            default:
                return 'Role inconnu';
        }
    }

    public function convertBlockchainRoleToRole(string $role): string
    {
        switch ($role) {
            case 'BREEDER':
                return 'ROLE_ELEVEUR';
            case 'SLAUGHTERER':
                return 'ROLE_EQUARRISSEUR';
            case 'MANUFACTURER':
                return 'ROLE_USINE';
            case 'Admin':
                return 'ROLE_ADMIN';
            case 'Transporteur':
                return 'ROLE_TRANSPORTEUR';
            default:
                return 'Role inconnu';
        }
    }
}

?>