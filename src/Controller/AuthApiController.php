<?php
// src/Controller/AuthApiController.php
namespace App\Controller;
 
use App\Entity\User;
use App\Service\PasskeyAuthService;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
 /**
 * Contrôleur API d'authentification
 * Routes: /api/auth/register, /api/auth/passkey/*
 * Retourne des JWT signés RSA-4096
 */
#[Route('/api/auth')]
class AuthApiController extends AbstractController
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
        private EntityManagerInterface $em
    ) {}
 
    /**
     * POST /api/auth/register
     * Inscription classique par email + mot de passe
     */
    #[Route('/register', name:'api_register', methods:['POST'])]
    public function register(Request $req, UserPasswordHasherInterface $hasher): JsonResponse
    {
        $data = json_decode($req->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
 
        if (!$email || !$password) {
            return $this->json(['error' => 'Email et mot de passe requis'], 400);
        }
 
        // Verifier si l'email est deja utilise
        if ($this->em->getRepository(User::class)->findOneBy(['email' => $email])) {
            return $this->json(['error' => 'Email deja utilise'], 409);
        }
 
        $user = new User();
        $user->setEmail($email);
        // Hacher le mot de passe avant de le stocker
        $user->setPassword($hasher->hashPassword($user, $password));
 
        $this->em->persist($user);
        $this->em->flush();
 
        $jwt = $this->jwtManager->create($user);
        return $this->json(['success' => true, 'token' => $jwt, 'email' => $user->getEmail()], 201);
    }
 
    /**
     * POST /api/auth/passkey/register/options
     * Obtenir les options WebAuthn pour creer une passkey
     */
    #[Route('/passkey/register/options', name:'api_passkey_register_options', methods:['POST'])]
    public function passkeyRegisterOptions(Request $req, PasskeyAuthService $svc): JsonResponse
    {
        $data = json_decode($req->getContent(), true);
        $email = $data['email'] ?? null;
        if (!$email) return $this->json(['error' => 'Email requis'], 400);
 
        // Creer l'utilisateur s'il n'existe pas encore
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            $user = (new User())->setEmail($email)->setPassword('');
            $this->em->persist($user);
            $this->em->flush();
        }
 
        return $this->json($svc->getRegistrationOptions($user));
    }
 
    /**
     * POST /api/auth/passkey/register/verify
     * Verifier et sauvegarder la nouvelle passkey, puis retourner un JWT
     */
    #[Route('/passkey/register/verify', name:'api_passkey_register_verify', methods:['POST'])]
    public function passkeyRegisterVerify(Request $req, PasskeyAuthService $svc): JsonResponse
    {
        $data = json_decode($req->getContent(), true);
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $data['email'] ?? '']);
        if (!$user || !isset($data['credential'])) {
            return $this->json(['error' => 'Donnees invalides'], 400);
        }
 
        try {
            $svc->verifyRegistration(json_encode($data['credential']), $user);
            $jwt = $this->jwtManager->create($user);
            return $this->json(['success' => true, 'token' => $jwt]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
 
    /**
     * POST /api/auth/passkey/login/options
     */
    #[Route('/passkey/login/options', name:'api_passkey_login_options', methods:['POST'])]
    public function passkeyLoginOptions(PasskeyAuthService $svc): JsonResponse
    {
        return $this->json($svc->getLoginOptions());
    }
 
    /**
     * POST /api/auth/passkey/login/verify
     */
    #[Route('/passkey/login/verify', name:'api_passkey_login_verify', methods:['POST'])]
    public function passkeyLoginVerify(Request $req, PasskeyAuthService $svc): JsonResponse
    {
        $data = json_decode($req->getContent(), true);
        if (!isset($data['credential'])) return $this->json(['error' => 'Credential requis'], 400);
 
        $credentialId = $data['credential']['id'] ?? 'N/A';
        error_log('Passkey login - Credential ID: ' . $credentialId);
        
        try {
            $user = $svc->verifyLogin($data['credential']);
            $jwt = $this->jwtManager->create($user);
            return $this->json([
                'success' => true,
                'token' => $jwt,
                'user' => ['email' => $user->getEmail(), 'roles' => $user->getRoles()]
            ]);
        } catch (\Exception $e) {
            error_log('Passkey login error: ' . $e->getMessage());
            return $this->json(['error' => $e->getMessage()], 401);
        }
    }
 
    /**
     * GET /api/auth/me — Profil de l'utilisateur connecte (necessite JWT)
     */
    #[Route('/me', name:'api_me', methods:['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) return $this->json(['error' => 'Non authentifie'], 401);
        return $this->json([
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles()
        ]);
    }
}

