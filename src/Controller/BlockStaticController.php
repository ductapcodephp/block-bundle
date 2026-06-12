<?php

namespace AmzsCMS\BlockBundle\Controller;


use AmzsCMS\BlockBundle\Constant\BlockStaticRoute;
use AmzsCMS\BlockBundle\DataTable\BlockStaticDataTable;
use AmzsCMS\BlockBundle\DataType\BlockDataType;
use AmzsCMS\BlockBundle\Entity\Block;
use AmzsCMS\BlockBundle\Form\AddBlockStaticForm;
use AmzsCMS\BlockBundle\Services\BlockService;
use AmzsCMS\PageBundle\Services\PageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class BlockStaticController extends AbstractController
{
    private $pageService;
    private $blockService;
    private $entityManager;
    public function __construct(
        PageService $pageService,
        BlockService $blockService,
        EntityManagerInterface $entityManager
    )
    {
        $this->pageService = $pageService;
        $this->blockService = $blockService;
        $this->entityManager = $entityManager;
    }

    public function index(Request $request): Response
    {
       return $this->render('@AmzsBlock/blockStatic/index.html.twig');
    }

    public function data(Request $request, BlockStaticDataTable $blockStaticDataTable): Response
    {
        return $this->json($blockStaticDataTable->getData($request));
    }

    public function edit(Request $request, int $id): Response
    {
        throw new \Exception('Not implemented');
    }

    public function delete(Request $request, int $id): Response
    {
        throw new \Exception('Not implemented');
    }

    public function add(Request $request): Response
    {
        $block = new Block();
        $block->setKind(BlockDataType::KIND_STATIC);
        $form = $this->createForm(AddBlockStaticForm::class, $block, [
            'action' => $this->generateUrl(BlockStaticRoute::ADD),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($block);
            $this->entityManager->flush();
            return new JsonResponse(['message' => 'Block added successfully!']);
        }
        $form = $form->createView();
        return $this->render('@AmzsBlock/blockStatic/add_modal.html.twig', compact('form'));
    }
}