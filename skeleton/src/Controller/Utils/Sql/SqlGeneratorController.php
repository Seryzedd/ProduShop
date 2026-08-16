<?php

namespace App\Controller\Utils\Sql;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Utils\SqlGenerator;
use App\Entity\Utils\selector;
use App\Entity\Utils\condition;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\Utils\SqlGeneratorRepository;
use App\Form\Utils\Sql\ConfigurationType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Form;
use App\Form\Utils\Sql\SelectorType;
use App\Service\Sql\SqlRequestGenerator;
use App\Service\EntityBuilder\EntityMetaDatas;

#[Route('/utils/sql/generator')]
final class SqlGeneratorController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager, private SqlRequestGenerator $requestGenerator) {}

    #[Route('/list', name: 'app_utils_sql_generator_list')]
    public function index(): Response
    {
        return $this->render('utils/sql/sql_generator/index.html.twig', [
        ]);
    }

    #[Route('/create', name: 'app_utils_sql_generator_new')]
    public function createConfiguration(Request $request): Response
    {
        $config = new SqlGenerator($this->getUser());

        $form = $this->createForm(ConfigurationType::class, $config);
        
        $form->handleRequest($request);

        $save = $this->saveDatas($form);

        if($save) {
            return $save;
        }

        return $this->renderTemplate($form);
    }

    #[Route('/update/{id}', name: 'app_utils_sql_generator_update')]
    public function updateConfiguration(SqlGenerator $config, Request $request): Response
    {
        $form = $this->createForm(ConfigurationType::class, $config);

        $form->handleRequest($request);

        $save = $this->saveDatas($form);

        if($save) {
            return $save;
        }

        $datas = [];
        $error = null;

        try{
            $datas = $this->requestGenerator->getDatas($config);
        } catch(\Exception $e) {
            $error = $e->getMessage();
        }

        return $this->renderTemplate($form, $datas, $error);
    }

    private function renderTemplate(Form $form, array $datas = [], ?string $error = ''): Response
    {

        return $this->render('utils/sql/sql_generator/manage.html.twig', [
            'form' => $form,
            'datas' => $datas,
            'error' => $error
        ]);
    }

    #[Route('/request/{class}/{formType}', name: 'app_utils_sql_entity_request')]
    public function asyncRequestEntityParameters(string $class, EntityMetaDatas $metadatas): JsonResponse
    {
        if (!isset(SqlGenerator::CLASSELIST[$class])) {
            throw $this->createNotFoundException('Entité inconnue.');
        }

        $entityClass = SqlGenerator::CLASSELIST[$class];
        $choices = $metadatas->buildDefaults($entityClass);

        $form = $this->createForm(CollectionType::class, [new Selector()], [
                'entry_type' => SelectorType::class,
                'row_attr' => ['class' => 'col-6'],
                'label' => 'Select',
                'entry_options' => [
                    'fields_options' => $metadatas->buildDefaults(SqlGenerator::getClassNamespace($class)),
                    'label_attr'      => ['class' => ''],
                    'sources' => SqlGenerator::CLASSELIST,
                    'selected_source' => SqlGenerator::getClassNamespace($class)
                ],
                'label_attr' => ['class' => ''],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true
            ]);

        $html = $this->renderView('form/collectionType.html.twig', [
            'collection' => $form->createView()
        ]);

        return new JsonResponse(['form' => $html]);
    }

    private function saveDatas(Form $form)
    {

        if ($form->isSubmitted() && $form->isValid()) {
            $configuration = $form->getData();

            $this->entityManager->persist($configuration);
            $this->entityManager->flush();

            $this->addFlash('success', 'Datas request configuration created.');

            return $this->redirectToRoute('app_utils_sql_generator_update', ['id' => $configuration->getId()]);
        }

        return null;
    }
}
