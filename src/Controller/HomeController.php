<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/new-account', name: 'app_new_user_account')]
    public function new_user_account(
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        UserRepository $userRepository,
        UserPasswordHasherInterface $userPasswordHasherInterface
    ) {
        $user = new User;
        $user->setRoles(array('ROLE_USER'));
        $form = $this->createForm(UserFormType::class, $user)
        ->add('valider', SubmitType::class, [
            'label' => 'Créer un compte'
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            if($userRepository->findOneBy(['email' => $user->getEmail()]) == null) {
                $user->setPassword($userPasswordHasherInterface->hashPassword($user, $user->getPassword()));
                $entityManagerInterface->persist($user);
                $entityManagerInterface->flush();

                $this->addFlash('success', 'Votre compte a bien été créé, vous pouvez maintenant vous connecter !');
            } else {
                $this->addFlash('warning', 'Un compte avec votre adresse mail existe déjà. Utilisez la fonction "mot de passe oublié" ou saisissez une autre adresse.');
            }
            

            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/new_account.html.twig', [
            'form' => $form->createView()
        ]);

    }
}
