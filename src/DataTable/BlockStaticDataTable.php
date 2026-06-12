<?php

namespace AmzsCMS\BlockBundle\DataTable;

use AmzsCMS\BlockBundle\DataType\BlockDataType;
use AmzsCMS\BlockBundle\Entity\Block;
use AmzsCMS\BlockBundle\Repository\BlockRepository;
use AmzsCMS\CoreBundle\Service\Datatable\BaseDataTable;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class BlockStaticDataTable extends BaseDataTable
{
    protected $entityAlias = 'block_static';
    public function __construct(BlockRepository $repository)
    {
        parent::__construct($repository);
    }

    // ================== Tùy chỉnh QueryBuilder từ đầu (nếu cần JOIN) ==================
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('block_static')
            ->where('block_static.kind = :kind')
            ->setParameter('kind', BlockDataType::KIND_STATIC)
            // ->leftJoin('e.category', 'c')
            // ->addSelect('c');
            ;
    }

    protected function applyCustomFilters(QueryBuilder $qb, Request $request): void
    {

    }

    protected function getColumnMap(): array
    {
        return [
            0 => 'id',
//            1 => 'name',
//            2 => 'url',
//            3 => 'language',
        ];
    }

    protected function getSearchableFields(): array
    {
        return [];
    }

    protected function formatData(array $entities): array
    {
        $data = [];
        /** @var Block $block */
        foreach ($entities as $index => $block) {
            $data[] = [
                'index'      => $index + 1,
                'id'         => $block->getId(),
                'title'      => $block->getTitle(),
                'sort'       => $block->getSortOrder(),
                'type'       => $block->getType(),
                'created_at' => $block->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $block->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }
        return $data;
    }
}