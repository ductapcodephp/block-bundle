<?php

namespace AmzsCMS\BlockBundle\Utils;

class AssetUtil
{
    private function __construct()
    {
    }

    public static function getPrefixBundle(): string
    {
        return 'bundles/amzsblock/';
    }
}