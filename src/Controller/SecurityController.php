<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\User;
use App\Form\AddressFormType;
use App\Form\PasswordUserFormType;
use App\Form\UpdateUserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
    public function profile(Request $request, EntityManagerInterface $manager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $profile = $user->getProfile();
        $updateUserForm = $this->createForm(UpdateUserFormType::class, $user);
        $updateUserForm->handleRequest($request);
        if ($updateUserForm->isSubmitted() && $updateUserForm->isValid()) {
            $manager->persist($user);
            $manager->flush();
            return $this->redirectToRoute('app_profile');
        }

        $passwordUserForm = $this->createForm(PasswordUserFormType::class, $user, [
            'action' => $this->generateUrl('app_profile_password_change')
        ]);
        $passwordUserForm->handleRequest($request);
        $deliveryAddress = $profile->getDeliveryAddress() ?: new Address();
        $deliveryAddressForm = $this->createForm(AddressFormType::class, $deliveryAddress, [
            'action' => $this->generateUrl('app_profile_address_change', [
                    'type' => 'delivery',
                    'address' => $deliveryAddress ? $deliveryAddress->getId() : null
                ])
        ]);
        $billingAddress = $profile->getBillingAddress() ?: new Address();

        $billingAddressForm = $this->createForm(AddressFormType::class, $billingAddress, [
            'action' => $this->generateUrl('app_profile_address_change', [
                'type' => 'billing',
                'address' => $billingAddress ? $billingAddress->getId() : null
            ])
        ]);

        return $this->render('security/profile.html.twig', [
            'user' => $user,
            'updateUserForm' => $updateUserForm,
            'passwordUserForm' => $passwordUserForm,
            'deliveryAddressForm' => $deliveryAddressForm,
            'billingAddressForm' => $billingAddressForm
        ]);
    }

    #[Route(path: '/profile/password-change', name: 'app_profile_password_change')]
    public function resetPassword(
        Request                     $request,
        UserPasswordHasherInterface $passwordEncoder,
        EntityManagerInterface      $manager
    ): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = $request->request->all('password_user_form');

        $oldPassword = $data['old_password'];
        $password = $data['password']['first'];
        $passwordConfirmation = $data['password']['second'];

        // Check if the old password matches the user's current password
        if ($password !== $passwordConfirmation || !$passwordEncoder->isPasswordValid($user, $oldPassword)) {
            $this->addFlash('error', 'Invalid current password.');
            return $this->redirectToRoute('app_profile');
        }

        $user->setPassword($passwordEncoder->hashPassword($user, $password));

        $manager->persist($user);
        $manager->flush();
        return $this->redirectToRoute('app_profile');
    }

    #[Route(path: '/profile/address-change/{type}/{address}', name: 'app_profile_address_change')]
    public function changeAddress(Request $request, string $type, EntityManagerInterface $entityManager, ?Address $address = null): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $address ??= new Address();
        /** @var User $user */
        $user = $this->getUser();
        $profile = $user->getProfile();

        $addressFormType = $this->createForm(AddressFormType::class, $address);
        $addressFormType->handleRequest($request);
        if (!$addressFormType->isValid()) {
            $this->addFlash('error', 'Invalid address.');
            return $this->redirectToRoute('app_profile');
        }
        $address->setType($type);
        $profile->addAddress($address);
        $entityManager->persist($address);
        $entityManager->flush();
        return $this->redirectToRoute('app_profile');
    }
}
