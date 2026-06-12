<?php

namespace AmzsCMS\BlockBundle\Services;

use AmzsCMS\BlockBundle\DataType\BlockDataType;
use AmzsCMS\BlockBundle\Entity\Block;
use AmzsCMS\BlockBundle\Repository\BlockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class BlockService
{
    private $blockRepository;
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager,BlockRepository $blockRepository)
    {
        $this->blockRepository = $blockRepository;
        $this->entityManager = $entityManager;
    }

    public function find($id, $lockMode = null,  $lockVersion = null)
    {
        return $this->blockRepository->find($id, $lockMode, $lockVersion);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->blockRepository->findBy($criteria, $orderBy, $limit, $offset);
    }

    // khi luu thong cua khoi "static" thi lay tat ca cac khoi co cùng type đồng bộ hết những data khối static có
    public function syncSameTypeBlocks(Block $parentBlock)
    {
        if ($parentBlock->getKind() != BlockDataType::KIND_STATIC) {
            return;
        }

        $reflectionClass = new \ReflectionClass($parentBlock);
        $data = [];

        foreach ($reflectionClass->getProperties() as $property) {
            // Cho phép truy cập vào thuộc tính private/protected
            $property->setAccessible(true);
            $data[$property->getName()] = $property->getValue($parentBlock);
        }

        $excludeProps = ['id', 'createdAt', 'post', 'slug', 'kind', 'isArchived', 'type', 'button'];

        // 1. Tạo thẳng QueryBuilder Update từ Repository
        $qb = $this->blockRepository->createQueryBuilder('b')->update();

        // 2. Thiết lập điều kiện: Cùng Type nhưng khác ID (không tự update chính nó)
        $qb->where('b.type = :type')
            ->andWhere('b.id != :currentId')
            ->setParameter('type', $parentBlock->getType())
            ->setParameter('currentId', $parentBlock->getId());

        // 3. Set các giá trị cần update
        foreach ($data as $column => $value) {
            if (in_array($column, $excludeProps)) {
                continue;
            }

            // SỬA LỖI 3: Dùng alias 'b' thay vì 'block' cho khớp với createQueryBuilder('b')
            $qb->set("b.$column", ":$column")
                ->setParameter($column, $value);
        }

        // 4. Thực thi
        $qb->getQuery()->execute();
    }

    public function findOneById(int $id)
    {
        return $this->blockRepository->find($id);
    }

    public function findOneByType(string $type, $extra = [])
    {
        return $this->blockRepository->findOneBy(array_merge([
            'type' => $type,
            'isDeleted' => null,
        ], $extra));
    }
    public function findAllByType(string $type)
    {
        return $this->blockRepository->findBy([
            'type' => $type,
            'kind' => BlockDataType::KIND_STATIC,
            'isDeleted' => null,
        ]);
    }
    public function addDefaultListingItem(Block $block, string $keyItems = 'listingItem'): string
    {
        $content = json_decode($block->getContent(), true) ?: [];

        $uuid = Uuid::v4()->toRfc4122();
        $newItem = [
            'uuid' => $uuid,
            'title' => 'Enter title',
            'subTitle' => 'Enter subtitle',
            'description' => 'Click here to edit',
            'customUrl' => '#demo',
            'button' => '',
            'videoUrl' => '#',
            'image' => '',
            'background' => '',
            'gallery_id' => null,
        ];

        if (!isset($content[$keyItems])) {
            $content[$keyItems] = [];
        }
        $content[$keyItems][$uuid] = $newItem;

        $block->setContent(json_encode($content));
        $this->entityManager->flush();

        return $uuid;
    }
}