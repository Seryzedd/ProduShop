<?php

namespace App\Controller\Admin\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;
use App\Entity\User\AbstractUser;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use App\Form\Admin\User\RolesManagerType;
use Symfony\Component\HttpFoundation\Request;
use App\Service\Api\StripeService;
use App\Entity\User\Professional;
use App\Entity\User\Client;

#[Route('/admin/users')]
final class UsersController extends AbstractController
{
    public function __construct(private readonly StripeService $stripe) {}
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
    #[Route('/email/{email}', name: 'app_admin_email_user')]
    public function view(?string $id, ?string $email, Request $request, EntityManagerInterface $entityManager)
    {
        if ($id) {
            $user = $entityManager->getRepository(AbstractUser::class)->findOneBy(['id' => $id]);

            if(!$user) {
                $this->addFlash('danger', new TranslatableMessage('User with id "%id%" does not exist in database.', ['%id%' => $id]));

                return $this->redirectToRoute('app_admin_users');
            }
        } else {
            $user = $entityManager->getRepository(AbstractUser::class)->findOneBy(['email' => $email]);

            if(!$user) {
                $this->addFlash('danger', new TranslatableMessage('User with email "%email%" does not exist in database.', ['%email%' => $email]));

                return $this->redirectToRoute('app_admin_users');
            }
        }

        

        $paymentMethods = [];

        if($user instanceof Client) {
            $paymentMethods = $this->stripe->getPaymentMethods($user->getStripe()->getCustomerId());
        }

        $form = $this
            ->createForm(RolesManagerType::class, $user)
        ;

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', new TranslatableMessage('User with email "%username%" updated.', ['%username%' => $user->getEmail()]));
        }

        return $this->render('admin/user/users/view.html.twig', [
            'user' => $user,
            'form' => $form,
            'paymentMethods' => $paymentMethods
        ]);
    }
}
