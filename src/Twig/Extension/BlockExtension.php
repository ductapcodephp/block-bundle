<?php

namespace AmzsCMS\BlockBundle\Twig\Extension;

use AmzsCMS\BlockBundle\Utils\AssetUtil;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BlockExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_path_block_cms_asset', [AssetUtil::class, 'getPrefixBundle']),
        ];
    }

}
