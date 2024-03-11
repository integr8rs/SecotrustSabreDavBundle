<?php

declare(strict_types=1);

namespace Secotrust\Bundle\SabreDavBundle\Tests;

use PHPUnit\Framework\TestCase;
use Secotrust\Bundle\SabreDavBundle\DependencyInjection\SecotrustSabreDavExtension;
use Secotrust\Bundle\SabreDavBundle\SecotrustSabreDavBundle;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class SecotrustSabreDavBundleTest extends TestCase
{
    private SecotrustSabreDavBundle $bundle;
    private Container $container;

    protected function setUp(): void
    {
        $this->bundle = new SecotrustSabreDavBundle();
        $this->container = new ContainerBuilder(
            new ParameterBag(['kernel.debug' => false])
        );
    }

    /**
     * @covers \Secotrust\Bundle\SabreDavBundle\SecotrustSabreDavBundle::build
     */
    public function testBuild(): void
    {
        $originalCount = count($this->container->getCompilerPassConfig()->getPasses());

        $this->bundle->build($this->container);

        $newCount = count($this->container->getCompilerPassConfig()->getPasses());

        $this->assertEquals(2,$newCount - $originalCount);
    }
}
