<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\Product;
use App\Entity\User\Professional;

#[Route('/async')]
final class AsyncController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager, private UrlGeneratorInterface $urlGenerator) {}

    #[Route('/search', name: 'app_async', methods: ['POST'])]
    public function searchDatas(Request $request): JsonResponse
    {
        $research = $request->request->get('search');
        
        $products  = $this->entityManager->getRepository(Product\Product::class)
            ->createQueryBuilder('p')
            ->where('p.name LIKE :search')
            ->setParameter('search', '%' . $research . '%')
            ->getQuery()->getResult();

        $shelves   = $this->entityManager->getRepository(Product\Shelf::class)
            ->createQueryBuilder('s')
            ->where('s.name LIKE :search')
            ->setParameter('search', '%' . $research . '%')
            ->getQuery()->getResult();

        $merchants = $this->entityManager->getRepository(Professional::class)
            ->createQueryBuilder('m')
            ->where('m.companyName LIKE :search')
            ->setParameter('search', '%' . $research . '%')
            ->getQuery()->getResult();

        $results = [];

        foreach ($products as $product) {
            $results[] = [
                'type'  => 'product',
                'label' => $product->getName(),
                'id'    => $product->getId(),
                'url'   => $this->urlGenerator->generate('app_show_product', ['id' => $product->getId()]),
            ];
        }

        foreach ($shelves as $shelf) {
            $results[] = [
                'type'  => 'shelf',
                'label' => $shelf->getName(),
                'id'    => $shelf->getId(),
                'url'   => $this->urlGenerator->generate('app_shelf_product', ['shelf' => $shelf->getName()]),
            ];
        }

        foreach ($merchants as $merchant) {
            $results[] = [
                'type'  => 'merchant',
                'label' => $merchant->getCompanyName(),
                'id'    => $merchant->getId(),
                'url'   => $this->urlGenerator->generate('app_company', ['id' => $merchant->getId()]),
            ];
        }

        return new JsonResponse($results);
    }
}
