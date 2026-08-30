<?php

namespace App\Controller\Admin\Helper;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\Helper\CategoryRepository;
use App\Entity\Helper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Helper\HelperType;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Route('admin/helper')]
final class HelperController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route('/', name: 'app_admin_helper_index')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $form = $this->createFormBuilder(new Helper\Category(), ['attr' => ['class' => 'd-inline-flex']])
            ->setAction($this->generateUrl('app_admin_category_helper_create'))
            ->add('name',
                TextType::class,
                [
                    'label' => false,
                    'attr' => [
                        'class' => 'mx-2',
                        'placeholder' => 'New category name'
                    ],
                    
                ]
            )
            ->add('save', SubmitType::class, ['label' => 'Create Task'])
            ->getForm()
        ;

        return $this->render('admin/helper/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
            'form' => $form->createView()
        ]);
    }

    #[Route('/category/new', name: 'app_admin_category_helper_create')]
    public function createCategory(Request $request)
    {
        $name = $request->request->all('form')['name'];

        if(!$name) {
            $this->addFlash('danger', 'No category name provided.');
        } else {
            $category = new Helper\Category();
            $category->setName($name);

            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $this->addFlash('success', 'Category created.');
        }

        return $this->redirectToRoute('app_admin_helper_index');
    }

    #[Route('/helper/update/{documentation}', name: 'app_admin_helper_update')]
    public function updateHelper(Request $request, Helper\Documentation $documentation)
    {
        $form = $this->getForm($documentation, $request);

        if($form instanceof RedirectResponse) {
            return $form;
        }

        return $this->render('admin/helper/HelperManagement.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/helper/new/{id}', name: 'app_admin_helper_create')]
    public function createNewHelper(Helper\Category $id, Request $request)
    {
        $documentation = new Helper\Documentation();

        $documentation->setCategory($id);
        
        $form = $this->getForm($documentation, $request);

        if($form instanceof RedirectResponse) {
            return $form;
        }

        return $this->render('admin/helper/HelperManagement.html.twig', [
            'form' => $form
        ]);
    }

    private function getForm(Helper\Documentation $documentation, Request $request)
    {
        $form = $this->createForm(HelperType::class, $documentation);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $shelf = $form->getData();

            $this->entityManager->persist($documentation);
            $this->entityManager->flush();

            $this->addFlash('success', new TranslatableMessage('Documentation "%name%" saved.', ['%name%' => $documentation->getName()]));

            return $this->redirectToRoute('app_admin_helper_update', [
                'documentation' => $documentation->getId(),
                'request' => $request
            ]);
        }

        return $form;
    }
}
