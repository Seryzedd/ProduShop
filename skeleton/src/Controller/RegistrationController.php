<?php

namespace App\Controller;

use App\Entity\User\AbstractUser;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use App\DTO\User\UserSignUp;
use App\Form\Step\RegistrationStepType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier) {}

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {

        $flow = $this->createForm(RegistrationStepType::class, new UserSignUp($userPasswordHasher))
            ->handleRequest($request);

        if ($flow->isSubmitted() && $flow->isValid() && $flow->isFinished()) {
            $data = $flow->getData();
            
            $user = $data->getUser();

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->sendEmailCheck($user);

            $this->addFlash('success', 'Account created. An email has been sent to your email adress');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $flow->getStepForm(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_login');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }

    #[Route('/request/verification/email', name: 'app_request_email_confirmation')]
    public function requestSecurityEmail(Request $request, UserRepository $userRepository)
    {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'help' => 'Enter your Lancana\'s account email address'
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findOneBy(['email' => $form->get('email')->getData()]);

            if($user) {
                $this->sendEmailCheck($user);
            }

            $this->addFlash('info', 'Email sent if user exist. Check your mailbox.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/request_confirmation.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function sendEmailCheck(AbstractUser $user)
    {
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('contact@producterShop.fr', 'Producter shop'))
                ->to((string) $user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
    }
}
