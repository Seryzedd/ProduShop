<?php

namespace App\Controller\Error;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

#[Route('/error')]
final class ErrorController extends AbstractController
{
    #[Route('/', name: 'app_error')]
    public function index(FlattenException $exception, ?DebugLoggerInterface $logger = null): Response
    {
        $statusCode = $exception->getStatusCode();

        return $this->render('error/error/index.html.twig', [
            'status_text'    => $exception->getStatusText(),
            'status_code'    => $statusCode,
            'exception'      => $exception,
        ], new Response('', $statusCode));
    }
}
