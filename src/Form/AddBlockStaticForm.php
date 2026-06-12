<?php

namespace AmzsCMS\BlockBundle\Form;


use AmzsCMS\BlockBundle\Entity\Block;
use AmzsCMS\BlockBundle\Form\Common\BlockType;
use AmzsCMS\CoreBundle\Traits\Form\FormButtonsTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddBlockStaticForm extends AbstractType
{
    use FormButtonsTrait;
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class, [
            'label' => 'Title',
            'label_attr' => ['class' => 'form-label my-2'],
            'attr' => ['class' => 'form-control fs-7'],
            'required' => false,
            'row_attr' => ['class' => 'mb-5'],
        ]);
        $builder->add('type', BlockType::class, [
            'required' => true,
            'constraints' => [
                new NotBlank(),
            ]
        ]);


        $blockStatic = $options['data'];
        $this->addActionButtons($builder, [
            'submit_label' => $blockStatic instanceof Block && is_integer($blockStatic->getId()) ? 'Edit' : 'Add',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Block::class,
        ]);
    }
}