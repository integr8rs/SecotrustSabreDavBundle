<?php

declare(strict_types=1);

namespace Secotrust\Bundle\SabreDavBundle\Tests\DependencyInjection;

use Secotrust\Bundle\SabreDavBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ConfigurationTest extends TestCase
{
    /**
     * @covers \Secotrust\Bundle\SabreDavBundle\DependencyInjection\Configuration::getConfigTreeBuilder
     */
    public function testGetConfigTreeBuilder()
    {
        $this->assertInstanceOf(TreeBuilder::class, (new Configuration())->getConfigTreeBuilder());
    }
}
