<?php

namespace AmzsCMS\BlockBundle\Controller;

use AmzsCMS\BlockBundle\Constant\BlockRoute;
use AmzsCMS\BlockBundle\DataTable\BlockDataTable;
use AmzsCMS\BlockBundle\DataType\BlockDataType;
use AmzsCMS\BlockBundle\DTO\BlockDTO;
use AmzsCMS\BlockBundle\Entity\Block;
use AmzsCMS\BlockBundle\Entity\Post;
use AmzsCMS\BlockBundle\Form\AddBlockForm;
use AmzsCMS\BlockBundle\Form\InsertStaticBlockForm;
use AmzsCMS\BlockBundle\Services\BlockService;
use AmzsCMS\PageBundle\Services\PageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class BlockController extends AbstractController
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


    public function listingOfPage(Request $request, $id): Response
    {
        $page = $this->pageService->findOneById((int)$id);
        return $this->render('@AmzsBlock/block/index.html.twig', compact('page'));
    }

    public function data(Request $request, BlockDataTable $dataTable, int $id): Response
    {
        $dataTable->setPageId($id);
        return $this->json($dataTable->getData($request));
    }


    public function index(Request $request): Response
    {
        throw new \Exception('Not implemented');
    }

    public function addByPage(Request $request, int $pageId): Response
    {
        $page = $this->pageService->findOneById($pageId);
        $block = new Block();

        $form = $this->createForm(AddBlockForm::class, $block, [
            'action' => $this->generateUrl(BlockRoute::ROUTE_ADD, ['pageId' => $pageId]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $block->setPage($page);
            $block->setKind(BlockDataType::KIND_DYNAMIC);
            $this->entityManager->persist($block);

            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Block and Post created successfully!']);
        }

        $form = $form->createView();
        return $this->render('@AmzsBlock/block/add_modal.html.twig', compact('form'));
    }

    public function edit(Request $request, int $id): Response
    {
        throw new \Exception('Not implemented');
    }

    public function delete(Request $request, int $id): Response
    {
        $csrfToken = $request->query->get('_csrf_token');
        if (!$this->isCsrfTokenValid('delete-block', $csrfToken))
            throw new AccessDeniedHttpException();

        $block = $this->blockService->findOneById($id);
        if(!$block instanceof Block) throw new NotFoundHttpException();
        $this->entityManager->remove($block);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Block deleted successfully!']);
    }

    public function add(Request $request): Response
    {
        throw new \Exception('Not implemented');
    }

    public function update(Request $request, SerializerInterface $serializer, int $id, BlockService $blockService): Response
    {
        $data = $request->request->all();

        $block = $this->blockService->findOneById($id);
        $jsonData = json_encode($data);
        $dto = $serializer->deserialize(
            $jsonData,
            BlockDTO::class,
            'json'   // dùng 'json' cho JSON body, hoặc bỏ qua nếu dùng array
        );
        $dto->run($block);

        $this->entityManager->flush();

//        $blockService->syncSameTypeBlocks($block);
        return new JsonResponse(['message' => 'Block updated successfully!','status' => 'success'   ]);
    }

    public function updateSort(
        Request $request,
        EntityManagerInterface $manager,
        BlockService  $blockService
    ): Response
    {
        $id = $request->request->get('id');
        /** @var Block $block */
        $block = $blockService->find($id);
        if(!$block instanceof Block) throw $this->createNotFoundException();
        $block->setSortOrder($request->request->getInt('sortOrder'));
        $manager->flush();

        return new JsonResponse([
            'message' => 'Block updated successfully',
            'success' => true,
        ]);
    }


    public function editAction(Request $request, int $id): Response
    {
        $block = $this->blockService->findOneById($id);
        $blocksType = $this->getParameter('blocks_type');
        $type = $blocksType[$block->getType()];

        return $this->forward($type['backend']['controller'], ['request' => $request, 'block' => $block]);
    }

    public function addStaticBlock(Request $request, int $pageId): Response
    {
        $page = $this->pageService->findOneById($pageId);

        $form = $this->createForm(InsertStaticBlockForm::class, null, [
            'action' => $this->generateUrl('amzs_admin_block_add_static_block', ['pageId' => $pageId]),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $blocks = $form->get('blocks')->get('blocks')->getData();

            foreach ($blocks as $block) {
                $newBlock = clone $block;
                $newBlock->setKind(BlockDataType::KIND_DYNAMIC);
                $newBlock->setPage($page);
                $this->entityManager->persist($newBlock);
            }

            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Static Block added to the Page successfully!']);
        }

        $form = $form->createView();

        return $this->render('@AmzsBlock/block/add_static_modal.html.twig', compact('form', 'page'));
    }

    public function deleteListingItem(Request $request, int $id): Response
    {
        $block = $this->blockService->findOneById($id);
        if (!$block instanceof Block) throw new NotFoundHttpException();

        $uuid     = $request->request->get('uuid');
        $keyItems = $request->request->get('key_items', 'listingItem');

        $content = json_decode($block->getContent(), true) ?? [];

        if (isset($content[$keyItems][$uuid])) {
            unset($content[$keyItems][$uuid]);
            $block->setContent(json_encode($content));
            $this->entityManager->flush();
        }

        return new JsonResponse(['status' => 'success', 'message' => 'Item deleted successfully!']);
    }
    public function addItem($id, Request $request): JsonResponse
    {
        $block = $this->entityManager->getRepository(Block::class)->find($id);
        if (!$block) {
            return new JsonResponse(['status' => 'error', 'message' => 'Không tìm thấy Block'], 404);
        }

        $keyItems = $request->request->get('key_items', 'listingItem');

        try {
            $uuid = $this->blockService->addDefaultListingItem($block, $keyItems);

            return new JsonResponse([
                'status' => 'success',
                'uuid' => $uuid,
                'message' => 'Item added successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Could not add item'
            ], 500);
        }
    }
    public function updateListingField(Request $request, int $id): Response
    {
        $block = $this->blockService->findOneById($id);
        if (!$block instanceof Block) throw new NotFoundHttpException();

        $uuid     = $request->request->get('uuid');
        $field    = $request->request->get('field');
        $value    = $request->request->get('value');
        $keyItems = $request->request->get('key_items', 'listingItem');
        $content = json_decode($block->getContent(), true) ?? [];

        if (!isset($content[$keyItems])) {
            $content[$keyItems] = [];
        }
        if (!isset($content[$keyItems][$uuid])) {
            $content[$keyItems][$uuid] = [];
        }

        $content[$keyItems][$uuid][$field] = $value;

        $block->setContent(json_encode($content));
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'success']);
    }
}