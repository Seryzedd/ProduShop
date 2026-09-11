<?php

namespace App\Controller\Company;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\User\ProfessionalRepository;
use App\Repository\Product\ProductRepository;
use App\Entity\User\Professional;
use App\Entity\User\Client;
use App\Service\Seo\Analyzer;
use App\Form\Seo\SeoKeywordType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/company')]
final class CompanyController extends AbstractController
{
    #[Route('/adress/{adressId}', name: 'app_companies', defaults: ['adressId' => null])]
    public function index(ProductRepository $productRepository, Request $request, ?int $adressId): Response
    {
        $radius = $request->query->get('radius') ?? 20;
        $user = $this->getUser();

        $adress = null;
        if($user) {
            $adress = $this->getUser()->getPostalAdress();
            
            if($user instanceof Client && $adressId) {
                $foundAdress = $user->getAdressById($adressId);
                if ($foundAdress) {
                    $adress = $foundAdress;
                }
            }
            
            if($adress) {
                $products = [];
                try {
                    $products = $productRepository->findWithinRadius($adress, $radius);
                } catch (\Throwable $th) {
                    $this->addFlash('danger', $th->getMessage());
                }
                
            } else {
                $products = $productRepository->findAll();
            }
        } else {
            $products = $productRepository->findAll();
        }

        return $this->render('company/company/index.html.twig', [
            'products' => $products,
            'radius' => $radius,
            'adress' => $adress
        ]);
    }

    #[Route('/{id}', name: 'app_company')]
    public function view(Professional $professional, Request $request): Response
    {
        
        return $this->render('company/company/view.html.twig', [
            'products' => $professional->getProducts(),
            'professional' => $professional,
            'radius' => $request->query->get('radius') ?? 20
        ]);
    }

    #[Route('/seo/analyze', name: 'app_company_seo')]
    public function seoProfessionalView(Analyzer $seoAnalyzer, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if(!$user) {
            return $this->redirectToRoute('app_home_index');
        }

        $keyWords = $user->getSeoKeywords();

        $form = $this->createForm(CollectionType::class, $keyWords, [
            'entry_type' => SeoKeywordType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'label' => 'keywords'
        ]);

        $form->handleRequest($request);

        $html = $this->view($this->getUser(), $request)->getContent();

        if($form->isSubmitted() && $form->isValid()) {
            
            foreach($form->getData() as $keyword) {
                $keyword->setUser($user);

                $entityManager->persist($keyword);
            }
            
            $entityManager->flush();

            $this->addFlash('success', 'Your account is updated.');
        }

        $report = $seoAnalyzer->analyze($html)->analyzeKeywords($keyWords, $html);

        return $this->render('company/company/seoView.html.twig', [
            'html' => $html,
            'report' => $report,
            'form' => $form->createView()
        ]);
    }
}
