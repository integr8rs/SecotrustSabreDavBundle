<?php

declare(strict_types=1);

namespace Secotrust\Bundle\SabreDavBundle\Tests\DependencyInjection;

use Secotrust\Bundle\SabreDavBundle\DependencyInjection\SecotrustSabreDavExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class SecotrustSabreDavExtensionTest extends TestCase
{
    private SecotrustSabreDavExtension $extension;
    private Container $container;

    protected function setUp(): void
    {
        $this->extension = new SecotrustSabreDavExtension();
        $this->container = new ContainerBuilder(
            new ParameterBag(['kernel.debug' => false])
        );
    }

    protected function tearDown(): void
    {
    }

    /**
     * @covers SecotrustSabreDavExtension::load
     */
    public function testLoadDefaultConfiguration(): void
    {
        $this->extension->load([[]], $this->container);

        $this->assertTrue($this->container->hasDefinition('secotrust.sabredav.server'));
        $this->assertTrue($this->container->hasDefinition('secotrust.sabredav.server.inner'));
        $this->assertTrue($this->container->hasDefinition('secotrust.sabredav.exceptionHandler'));

        $this->assertTrue($this->container->hasParameter('secotrust.sabredav.controller.class'));
        $this->assertTrue($this->container->hasParameter('secotrust.sabredav.exceptionHandler.class'));
        $this->assertTrue($this->container->hasParameter('secotrust.sabredav.server.inner.class'));
        $this->assertTrue($this->container->hasParameter('secotrust.sabredav.server.class'));
        $this->assertTrue($this->container->hasParameter('secotrust.sabredav.use_symfony_exception_handler'));
        $this->assertTrue($this->container->hasParameter('secotrust.sabredav.server.nodes'));
    }
}
