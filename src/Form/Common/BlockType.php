<?php

declare(strict_types=1);

namespace AmzsCMS\BlockBundle\Form\Common;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockType extends AbstractType
{
    private ParameterBagInterface $parameterBag;
    public function __construct(
        ParameterBagInterface $parameterBag
    )
    {
        $this->parameterBag = $parameterBag;
    }
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    private function getBlocks(): array
    {
        $results = array();
        $blocks = $this->parameterBag->get('blocks_type');

        foreach ($blocks as $type => $block) {
            $results[$block['name']] = $type;
        }

        return $results;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->getBlocks(),
            'attr' => [
                'class' => 'form-select form-select-sm',
                'data-control' => 'select2',
                'data-dropdown-parent' => '#amzs-modal',
            ],
            'placeholder' => '-- Select option --',
            'multiple' => false,

        ]);

    }
}
