<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class Authenticator
{
    use TargetPathTrait;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var CsrfTokenManagerInterface
     */
    private $csrfTokenManager;

    /**
     * @var UserPasswordHasherInterface
     */
    private $passwordEncoder;


    /**
     * @param EntityManagerInterface       $entityManager    parameter
     * @param RouterInterface              $router           parameter
     * @param CsrfTokenManagerInterface    $csrfTokenManager parameter
     * @param UserPasswordHasherInterface $passwordEncoder  parameter
     */
    public function __construct(EntityManagerInterface $entityManager, RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordHasherInterface $passwordEncoder)
    {

        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }


    /**
     * @param Request $request parameter
     *
     * @return bool
     */
    public function supports(Request $request): bool
    {

        if ($request->attributes->get('_route') === 'login' && $request->isMethod('POST')) {
            return true;
        }

        return false;
    }


    /**
     * @param Request $request parameter
     *
     * @return array
     */
    public function getCredentials(Request $request): array
    {

        $credentials
            = [
            'username'      => $request->request->get('username'),
            'password'   => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['username']
        );

        return $credentials;
    }


    /**
     * @param array                 $credentials  parameter
     * @param UserProviderInterface $userProvider parameter
     *
     * @return mixed|object|UserInterface|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {

        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $credentials['username']]);

        if (!$user) {
            throw new CustomUserMessageAuthenticationException('Utilisateur non trouvé.');
        }

        return $user;
    }


    /**
     * @param array         $credentials parameter
     * @param PasswordAuthenticatedUserInterface $user        parameter
     *
     * @return bool
     */
    public function checkCredentials($credentials, PasswordAuthenticatedUserInterface $user): bool
    {

        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }


    /**
     * @param Request        $request     parameter
     * @param TokenInterface $token       parameter
     * @param string         $providerKey parameter
     *
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): RedirectResponse
    {

        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->router->generate('homepage'));
    }


    /**
     * @return string
     */
    protected function getLoginUrl(): string
    {

        return $this->router->generate('login');
    }
}
