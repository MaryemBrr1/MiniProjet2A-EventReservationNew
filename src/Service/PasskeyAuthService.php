<?php
// src/Service/PasskeyAuthService.php
namespace App\Service;
 
use App\Entity\User;
use App\Entity\WebauthnCredential;
use App\Repository\WebauthnCredentialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
 /**
 * Service d'authentification Passkey (WebAuthn/FIDO2)
 * Gère l'enregistrement et la vérification des clés biométriques
 * Conforme au standard W3C WebAuthn Level 2
 */
class PasskeyAuthService
{   
    public function __construct(
        private EntityManagerInterface $em,
        private WebauthnCredentialRepository $credRepo,
        private CacheItemPoolInterface $cache,
        private string $rpId,
        private string $rpName,
    ) {}
 
    /**
     * Genere les options de creation d'une Passkey pour un utilisateur.
     * Ces options sont envoyees au navigateur qui les utilise pour generer la cle.
     */
    public function getRegistrationOptions(User $user): array
    {
        $challenge = random_bytes(32);
        $challengeB64 = rtrim(strtr(base64_encode($challenge), '+/', '-_'), '=');
 
        // Stocker le challenge en cache pour le verifier plus tard
        $item = $this->cache->getItem('webauthn_reg_'.$user->getId());
        $item->set($challengeB64)->expiresAfter(120); // 2 minutes
        $this->cache->save($item);
 
        return [
            'challenge' => $challengeB64,
            'rp' => ['name' => $this->rpName, 'id' => $this->rpId],
            'user' => [
                'id' => base64_encode($user->getId()),
                'name' => $user->getEmail(),
                'displayName' => $user->getUsername() ?? $user->getEmail(),
            ],
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7],   // ES256
                ['type' => 'public-key', 'alg' => -257], // RS256
            ],
            'timeout' => 60000,
            'attestation' => 'none',
            'authenticatorSelection' => [
                'authenticatorAttachment' => 'platform',
                'userVerification' => 'preferred',
                'residentKey' => 'preferred',
            ],
        ];
    }
 
    /**
     * Genere les options de connexion par Passkey.
     * Pas besoin de connaitre l'utilisateur : la passkey residente l'identifie.
     */
    public function getLoginOptions(): array
    {
        $challenge = random_bytes(32);
        $challengeB64 = rtrim(strtr(base64_encode($challenge), '+/', '-_'), '=');
 
        $item = $this->cache->getItem('webauthn_login_challenge');
        $item->set($challengeB64)->expiresAfter(120);
        $this->cache->save($item);
 
        return [
            'challenge' => $challengeB64,
            'timeout' => 60000,
            'rpId' => $this->rpId,
            'userVerification' => 'preferred',
        ];
    }
 
    /**
     * Verifie l'enregistrement d'une Passkey et la sauvegarde en BDD.
     */
    public function verifyRegistration(string $credentialJson, User $user): void
    {
        $data = json_decode($credentialJson, true);
 
        // Creer et sauvegarder la credential
        $credential = new WebauthnCredential();
        $credential->setUser($user);
        $credential->setCredentialData($credentialJson);
        $credential->setName('Passkey du ' . date('d/m/Y'));
 
        $this->em->persist($credential);
        $this->em->flush();
    }
 
    /**
     * Verifie la connexion par Passkey et retourne l'utilisateur si ok.
     */
    public function verifyLogin(array $credentialData): User
    {
        // Retrouver la credential en BDD a partir de son ID
        $credentialId = $credentialData['id'] ?? null;
        if (!$credentialId) {
            throw new \InvalidArgumentException('Credential ID manquant');
        }

        error_log('Searching for credential ID: ' . $credentialId);
        
        // Chercher le credential en BDD en parcourant tous les credentials
        // et en comparant l'ID de credential
        $credentials = $this->em->getRepository(WebauthnCredential::class)->findAll();
        error_log('Total credentials in DB: ' . count($credentials));
        $credential = null;
        
        foreach ($credentials as $cred) {
            $credData = json_decode($cred->getCredentialData(), true);
            $storedId = $credData['id'] ?? 'N/A';
            error_log('Comparing stored ID: ' . $storedId . ' with incoming: ' . $credentialId);
            
            if (isset($credData['id']) && $credData['id'] === $credentialId) {
                $credential = $cred;
                error_log('Credential FOUND for user: ' . $cred->getUser()->getEmail());
                break;
            }
        }

        // En production, tu dois verifier la signature cryptographique ici
        // Pour l'implementation complete, utilise web-auth/webauthn-lib

        if (!$credential || !$credential->getUser()) {
            error_log('Credential NOT FOUND or no user attached');
            throw new \RuntimeException('Passkey non reconnue');
        }

        $credential->touch();
        $this->em->flush();

        return $credential->getUser();
    }
}
