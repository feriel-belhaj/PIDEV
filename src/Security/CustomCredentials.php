<?php
namespace App\Security;

use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CredentialsInterface;

class CustomCredentials implements CredentialsInterface
{
    public function __construct()
    {
        // Pas besoin de validation ici pour l'authentification faciale
    }

    public function isResolved(): bool
    {
        // Indiquer que les credentials sont résolus, même si nous ne faisons rien ici
        return true;
    }
}
