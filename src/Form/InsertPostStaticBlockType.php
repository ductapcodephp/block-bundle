<?php

declare(strict_types=1);

namespace AmzsCMS\BlockBundle\Form;


use AmzsCMS\BlockBundle\DataType\BlockDataType;
use AmzsCMS\BlockBundle\Entity\Block;
use AmzsCMS\BlockBundle\Form\Common\EntitySelect2Type;
use AmzsCMS\PageBundle\Entity\Page;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InsertPostStaticBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('blocks', EntitySelect2Type::class, [
            'class' => Block::class,
            'multiple' => true,
            'mapped' => false,
            'data-select2-dropdown-parent-value' => '.modal',
            'choice_label' => 'title',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('b')
                    ->where('b.deletedAt IS NULL')
                    ->andWhere('b.kind = :kind')
                    ->setParameter('kind', BlockDataType::KIND_STATIC);
            }
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}