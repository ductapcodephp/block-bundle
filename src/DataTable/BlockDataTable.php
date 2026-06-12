<?php

namespace AmzsCMS\BlockBundle\DataTable;

use AmzsCMS\BlockBundle\Entity\Block;
use AmzsCMS\BlockBundle\Repository\BlockRepository;
use AmzsCMS\CoreBundle\Service\Datatable\BaseDataTable;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class BlockDataTable extends BaseDataTable
{
    protected $entityAlias = 'block';
    private $pageId = null;

    public function getPageId(Request $request): int
    {
        $this->pageId = null;
        return $this->pageId;
    }
    public function setPageId(int $pageId): int
    {
        $this->pageId = $pageId;
        return $this->pageId;
    }
    public function __construct(BlockRepository $repository)
    {
        parent::__construct($repository);
    }

    // ================== Tùy chỉnh QueryBuilder từ đầu (nếu cần JOIN) ==================
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        $qb = $this->repository->createQueryBuilder('block')
            ->leftJoin('block.page', 'page');
        $qb->orderBy('block.sortOrder', 'ASC');
        if ($this->pageId !== null) {
            $qb->andWhere('page.id = :pageId')
                ->setParameter('pageId', $this->pageId);
        }

        return $qb;
    }

    protected function applyDefaultFilters(QueryBuilder $qb, Request $request): void
    {
    }
    protected function applyCustomFilters(QueryBuilder $qb, Request $request): void
    {

    }
    protected function getColumnMap(): array
    {
        return [
            0 => 'createdAt',
//            1 => 'name',
//            2 => 'url',
//            3 => 'language',
        ];
    }

    protected function getSearchableFields(): array
    {
        return ['name'];
    }

    protected function formatData(array $entities): array
    {
        $data = [];
        /** @var Block $block */
        foreach ($entities as $index => $block) {
            $data[] = [
                'index'       => $index + 1,
                'id'       => $block->getId(),
                'title'    => $block->getTitle(),
                'sort'    => $block->getSortOrder(),
                'type'     => $block->getType(),
                'created_at' => $block->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $block->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }
        return $data;
    }
}