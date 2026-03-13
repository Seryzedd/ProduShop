<?php

namespace App\Controller\Merchant;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\User\ScheduleType;
use App\Entity\User\OpeningSchedule;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/merchant')]
final class ScheduleController extends AbstractController
{
    #[Route('/schedule', name: 'app_merchant_schedule')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $schedule = $user->getOpeningSchedule();
        if (!$schedule) {
            $schedule = new OpeningSchedule();
            $schedule->setUser($user);
        }

        $entityManager->persist($schedule);

        $form = $this->createForm(ScheduleType::class, $schedule, []);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($schedule);
            $entityManager->flush();

            $this->addFlash('success', 'Schedules updated.');

            return $this->redirectToRoute('app_account_informations');
        }

        return $this->render('merchant/schedule/index.html.twig', [
            'form' => $form
        ]);
    }
}
