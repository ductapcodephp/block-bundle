<?php

namespace AmzsCMS\BlockBundle\Form;

use AmzsCMS\CoreBundle\Traits\Form\FormButtonsTrait;
use AmzsCMS\PageBundle\Entity\Page;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InsertStaticBlockForm extends AbstractType
{
    use FormButtonsTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('blocks', InsertPostStaticBlockType::class);

        $this->addActionButtons($builder, [
            'submit_label' => 'Add',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}