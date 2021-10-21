<?php

declare(strict_types=1);

namespace SymfonyBundle\UIBundle\Query;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use SymfonyBundle\UIBundle\Foundation\UIFoundationBundle;
use SymfonyBundles\BundleDependency\BundleDependency;
use SymfonyBundles\BundleDependency\BundleDependencyInterface;

class UIQueryBundle extends Bundle implements BundleDependencyInterface
{
    use BundleDependency;

    public function getBundleDependencies(): array
    {
        return [
            UIFoundationBundle::class
        ];
    }
}
