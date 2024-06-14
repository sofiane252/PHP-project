<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\EditProfileFormType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserController extends AbstractController {
    
    // #[Route('/users', name: 'user_list')]
    // public function getUsers(UserRepository $userRepository): Response {
        
    //     $users = $userRepository->findAll();

    //     return $this->render('user/users.html.twig', [
    //         'users' => $users, 
    //     ]);
    // }

    // #[Route('/users/{id<\d+>}', name: 'user_by_id')]
    // public function getUserById(UserRepository $userRepository, int $id): Response {
        
    //     $user = $userRepository->find($id);

    //     if (!$user) {
    //         throw $this->createNotFoundException('L\'utilisateur n\'existe pas.');
    //     }

    //     return $this->render('user/userById.html.twig', [
    //         'user' => $user, 
    //     ]);
    // }

    // #[Route('/user/greet', name: 'user_greet')]
    // public function greet(Request $request): Response
    // {
    //     $name = $request->query->get('name', 'Guest');
    //     return new Response('Hello ' . $name);
    // }


    // #[Route('/user/new', name: 'user_new')]
    // public function new(Request $request, ManagerRegistry $doctrine): Response
    // {
    //     $user = new User();
    //     $form = $this->createForm(UserType::class, $user);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $entityManager = $doctrine->getManager();
    //         $entityManager->persist($user);
    //         $entityManager->flush();

    //         $this->addFlash('success', 'User created successfully!');

    //         return $this->redirectToRoute('user_success');
    //     }

    //     return $this->render('user/new.html.twig', [
    //         'form' => $form->createView(),
    //     ]);
    // }


    // #[Route('/user/success', name: 'user_success')]
    // public function success(): Response
    // {
    //     return $this->render('user/success.html.twig');
    // }

    #[Route('/change-password', name: 'edit_password')]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('You must be logged in to change your password.');
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $oldPassword = $form->get('oldPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();

            if (!$passwordHasher->isPasswordValid($user, $oldPassword)) {
                $this->addFlash('error', 'Current password is incorrect.');
            } else {
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $newPassword
                    )
                );

                $entityManager->flush();

                $this->addFlash('success', 'Password changed successfully.');

                return $this->redirectToRoute('profil');
            }
        }

        return $this->render('user/change_password.html.twig', [
            'changePasswordForm' => $form->createView(),
        ]);
    }

    #[Route('/edit-profile', name: 'edit_profil')]
    public function editProfile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('You must be logged in to edit your profile.');
        }

        $form = $this->createForm(EditProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
            }
            catch (\Exception $e){
                $this->addFlash('danger', 'Email déjà utilisé');
                return $this->render('user/edit_profil.html.twig', [
                    'editProfileForm' => $form->createView()
                ]);
            }

            $this->addFlash('success', 'Profile updated successfully.');

            return $this->redirectToRoute('profil');
        }

        return $this->render('user/edit_profil.html.twig', [
            'editProfileForm' => $form->createView(),
        ]);
    }
}
