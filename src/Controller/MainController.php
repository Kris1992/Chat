<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Response, Cookie};
use Symfony\Component\Routing\Annotation\Route;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;

class MainController extends AbstractController
{
    /**
     * @return  Response
     * @Route("/", name="app_homepage", methods={"GET"})
     */
    public function index(): Response
    {   

        /** @var User $user */
        $user = $this->getUser();

        $response = $this->render('main/index.html.twig');
        /* We generate JWT token only to logged in users*/
        if ($user) {
        
            $token = (new Builder())
                ->withClaim('mercure', ['subscribe' => [sprintf('/%s', $user->getLogin())]])
                ->getToken(new Sha256(), new Key($this->getParameter('mercure_secret_key')));

            $cookie = Cookie::create('mercureAuthorization')
                ->withValue(strval($token))
                ->withPath('/.well-known/mercure')
                ->withExpires(new \DateTime('now +1 day'))
                ->withSecure(false)//true
                ->withHttpOnly(true)
                ->withSameSite('strict')
            ;
            $response->headers->setCookie($cookie);
        }

        return $response;

    }
}
