<?php

namespace App\Controller\Admin\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User\AbstractUser;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use App\Form\Admin\User\RolesManagerType;
use Symfony\Component\HttpFoundation\Request;

#[Route('/admin/users')]
final class UsersController extends AbstractController
{
    #[Route('/', name: 'app_admin_users')]
    public function index(EntityManagerInterface $entityManager, UserRepository $repository): Response
    {
        //$users = $entityManager->getRepository(AbstractUser::class)->findBy([], ['userType' => 'ASC']);

        $users = $repository->findAllByType();
        
        return $this->render('admin/user/users/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user')]
    public function view(AbstractUser $user, Request $request)
    {
        $form = $this
            ->createForm(RolesManagerType::class, $user)
        ;

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

        }

        return $this->render('admin/user/users/view.html.twig', [
            'user' => $user,
            'form' => $form
        ]);
    }
}
