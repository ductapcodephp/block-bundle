<?php

namespace AmzsCMS\BlockBundle\Form;


use AmzsCMS\BlockBundle\Entity\Block;
use AmzsCMS\BlockBundle\Form\Common\BlockType;
use AmzsCMS\CoreBundle\Traits\Form\FormButtonsTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddBlockForm extends AbstractType
{
    use FormButtonsTrait;
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class);
        $builder->add('type', BlockType::class);

        $block = $options['data'];
        $this->addActionButtons($builder, [
            'submit_label' => $block instanceof Block && !is_null($block->getId()) ? 'Edit' : "Add",
            // Bạn có thể tùy chỉnh thêm nếu muốn:
            // 'cancel_label' => 'Quay lại',
            // 'container_class' => 'd-flex gap-2 justify-content-end'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Block::class,
            'attr' => [
                'data-controller' => 'Admin--block-add'
            ]
        ]);
    }
}