<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;

use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use App\Repository\UtilisateurRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Security\CustomCredentials;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FaceAuthenticator extends AbstractAuthenticator
{
    private HttpClientInterface $httpClient;
    private UtilisateurRepository $userRepository;
    private string $apiKey;
    private string $apiSecret;
    private RequestStack $requestStack;

    public function __construct(HttpClientInterface $httpClient, UtilisateurRepository $userRepository, ParameterBagInterface $params, RequestStack $requestStack)
    {
        $this->httpClient = $httpClient;
        $this->userRepository = $userRepository;
        $this->apiKey = $params->get('face_api_key');
        $this->apiSecret = $params->get('face_api_secret');
        $this->requestStack = $requestStack;
        
    }

    public function supports(Request $request): bool
    {
        return $request->attributes->get('_route') === 'app_face_login';
    }

    public function authenticate(Request $request): Passport
    {
        $capturedImageBase64 = $request->get('captured_image');

        if (!$capturedImageBase64) {
            throw new AuthenticationException('Aucune image fournie.');
        }

        // Décoder l'image Base64
        $capturedImageBase64 = str_replace('data:image/jpeg;base64,', '', $capturedImageBase64);
        $imageData = base64_decode($capturedImageBase64);
        if ($imageData === false) {
            throw new AuthenticationException('Erreur lors du décodage de l\'image.');
        }

        // Sauvegarder temporairement l'image capturée
        $tempImagePath = sys_get_temp_dir() . '/captured_face_image.jpg';
        file_put_contents($tempImagePath, $imageData);

        // Récupérer tous les utilisateurs
        $users = $this->userRepository->findAll();
        $bestMatchUser = null;
        $bestConfidence = 0;

        foreach ($users as $user) {
            $userImagePath = 'uploads/' . $user->getImage();
            if (!file_exists($userImagePath)) {
                continue;
            }

           
            $userImageBase64 = base64_encode(file_get_contents($userImagePath));
            $userImageBase64 = str_replace('data:image/jpeg;base64,', '', $userImageBase64);

            // Appel API Face++
            $response = $this->httpClient->request('POST', 'https://api-us.faceplusplus.com/facepp/v3/compare', [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'body' => [
                    'api_key' => $this->apiKey,
                    'api_secret' => $this->apiSecret,
                    'image_base64_1' => $capturedImageBase64,
                    'image_base64_2' => $userImageBase64,
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                continue; 
            }

            $data = $response->toArray();
           //dd($data);
            if (empty($data['faces1']) || empty($data['faces2'])) {
               continue;
                // dd("faces not found"); 
            }

            // Vérification du score de confiance
            if (isset($data['confidence']) && $data['confidence'] > $bestConfidence) {
                $bestConfidence = $data['confidence'];
                $bestMatchUser = $user;
            }
        }
        //dd($bestMatchUser,$bestConfidence);

        // Vérification finale après avoir testé tous les utilisateurs
        if ($bestMatchUser !== null && $bestConfidence >= 40) {
            return new Passport(
                new UserBadge($bestMatchUser->getEmail()),
                new CustomCredentials(),
                [new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token'))]
            );
        }

        unlink($tempImagePath); 
        throw new AuthenticationException('Échec de l\'authentification - Aucun utilisateur correspondant trouvé.');
    }


    
    

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse('/utilisateur/home');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $session = $this->requestStack->getSession();

    
    if ($session instanceof FlashBagAwareSessionInterface) {
        $session->getFlashBag()->add('error', 'Échec de l\'authentification : ' . $exception->getMessage());
    }

       
        return new RedirectResponse('/login');
        #return new Response('Échec de l\'authentification : ' . $exception->getMessage(), Response::HTTP_UNAUTHORIZED);
    }
}
