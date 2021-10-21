<?php

declare(strict_types=1);

namespace SymfonyBundle\UIBundle\Query;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use SymfonyBundles\BundleDependency\BundleDependencyInterface;

class UIBundleQuery extends Bundle implements BundleDependencyInterface
{
    public function getBundleDependencies(): array
    {
        return [
            'SymfonyBundle\UIBundle\Foundation'
        ];
    }
}
