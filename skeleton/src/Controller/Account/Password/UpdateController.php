<?php

namespace App\Controller\Account\Password;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\UpdatePasswordType;

#[Route('/account/password')]
final class UpdateController extends AbstractController
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher, private EntityManagerInterface $entityManager) {}

    #[Route('/update', name: 'app_account_password_update')]
    public function index(Request $request): Response
    {
        $form = $this
            ->createForm(UpdatePasswordType::class)
        ;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            $currentPassword = $form->get('currentPassword')->getNormData();
            $newPassword = $form->get('newPassword')->get('plainPassword')->getNormData();
            
            if($currentPassword === $newPassword) {
                $this->addFlash('warning', 'Your new password cannot be the same as the current one. Try to change password.');

                return $this->redirectToRoute('app_account_password_update');
            }

            $hashPassword = $this->userPasswordHasher->hashPassword(
                $user,
                $form->get('newPassword')->get('plainPassword')->getNormData()
            );
            
            $user->setPassword($hashPassword);
            
            $this->entityManager = $this->getDoctrine()->getManager();
            $this->entityManager->flush();

            $this->addFlash('success', 'Your password has been updated');

            return $this->redirectToRoute('app_account_informations');
        }
        return $this->render('account/password/update/index.html.twig', [
            'form' => $form
        ]);
    }
}
