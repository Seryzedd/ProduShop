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
use App\Repository\Product\ProductRepository;
use App\Entity\User\PostalAdress\Adress;

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

    #[Route('/admin-menu', name: 'admin_menu')]
    public function adminMenuSaver(Request $request)
    {
        $session = $request->getSession();
        dump((bool) $request->request->get('isChecked'));
        
        $session->set('admin-menu-expanded', (bool) $request->request->get('isChecked'));
        
        return new JsonResponse(true);
    }

    #[Route('/map/products/adress/{adress}/radius/{radius}', name: 'map_products_adress_list')]
    public function productsByAdress(Adress $adress, ProductRepository $productRepository, float $radius): JsonResponse
    {
        $products = $productRepository->findWithinRadiusToArray($adress, $radius);
 
        return new JsonResponse($this->buildMapResponse(
            $products,
            $radius,
            $adress->hasCoordinates() ? $adress->getLatitude() : null,
            $adress->hasCoordinates() ? $adress->getLongitude() : null,
        ));
    }

    #[Route('/map/products/coordinates/lat/{latitude}/lng/{longitude}/radius/{radius}', name: 'map_products_by_coordinates')]
    public function productsByCoordinates(ProductRepository $productRepository, float $latitude, float $longitude, float $radius): JsonResponse
    {
        $products = $productRepository->findByNumbersToArray($longitude, $latitude, $radius);

        dump($this->buildMapResponse($products, $radius, $latitude, $longitude));
 
        return new JsonResponse($this->buildMapResponse($products, $radius, $latitude, $longitude));
    }

    private function buildMapResponse(array $products, float $radius, ?float $lat, ?float $lng): array
    {
        $seenCompanies = [];
        $results = [];
 
        foreach ($products as $product) {
            $company = $product->getCompany();
 
            if (!$company || isset($seenCompanies[$company->getId()])) {
                continue;
            }
            $seenCompanies[$company->getId()] = true;
 
            $companyAdress = $company->getAdress();
 
            if (!$companyAdress || !$companyAdress->hasCoordinates()) {
                continue;
            }
 
            $results[] = [
                'company'     => $company->getCompanyName(),
                'companyLink' => $this->urlGenerator->generate('app_company', ['id' => $company->getId()]),
                'lat'         => $companyAdress->getLatitude(),
                'lng'         => $companyAdress->getLongitude(),
                'phone'       => $company->getPhone(),
                'address'     => sprintf('%s, %s %s', $companyAdress->getStreet(), $companyAdress->getZipCode(), $companyAdress->getCountry()),
                'open'        => method_exists($company, 'isOpen') ? $company->isOpen() : null,
            ];
        }
 
        return [
            'radiusKm' => $radius,
            'client'   => ['lat' => $lat, 'lng' => $lng],
            'products' => $results,
        ];
    }
}
