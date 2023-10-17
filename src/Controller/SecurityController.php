<?php

namespace App\Controller;

use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/profile', name: 'app_profile')]
    public function profile(Security $security, Request $request, EntityManagerInterface $manager): Response
    {
        $user = $security->getUser();
        $registrationForm = $this->createForm(RegistrationFormType::class, $user);
        $registrationForm->handleRequest($request);
        if($registrationForm->isSubmitted() && $registrationForm->isValid()){
            $manager->persist($user);
            $manager->flush();
            return $this->redirectToRoute('app_profile');
        }
        return $this->render('security/profile.html.twig', [
            'user' => $user,
            'registrationForm' => $registrationForm
        ]);
    }
}
