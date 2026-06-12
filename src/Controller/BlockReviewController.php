<?php

declare(strict_types=1);

namespace AmzsCMS\BlockBundle\Controller;

use AmzsCMS\BlockBundle\Services\BlockService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockReviewController extends AbstractController
{
    private $blockService;
    private $entityManager;
    private $parameterBag;

    public function __construct(
        BlockService $blockService,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag
    )
    {
        $this->blockService = $blockService;
        $this->parameterBag = $parameterBag;
        $this->entityManager = $entityManager;
    }

    public function preview(Request $request, int $id): Response
    {
        $block = $this->blockService->findOneById($id);

        if (!$block) {
            throw $this->createNotFoundException('Không tìm thấy Block này.');
        }

        $config = $this->parameterBag->get('blocks_type');
        $blockType = $config[$block->getType()];
        $templateReview = $blockType['backend']['view'] ;
        $contentData = json_decode((string) $block->getContent(), true) ?: [];
        return $this->render('@AmzsBlock/block/preview.html.twig', [
            'block' => $block,
            'templateReview' => $templateReview,
            'content_data' => $contentData
        ]);
    }
}