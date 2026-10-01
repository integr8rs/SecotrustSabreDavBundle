<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('secotrust.sabredav.browser_plugin.class', \Secotrust\Bundle\SabreDavBundle\SabreDav\BrowserPlugin::class);
    $parameters->set('secotrust.sabredav.browser.config', ['browser_logo' => '%secotrust.sabredav.browser_plugin.logo%', 'favicon' => '%secotrust.sabredav.browser_plugin.favicon%']);

    $services->set('secotrust.sabredav_browser_plugin', '%secotrust.sabredav.browser_plugin.class%')
        ->private()
        ->args([false])
        ->call('setBrowserConfig', ['%secotrust.sabredav.browser.config%'])
        ->tag('secotrust.sabredav.plugin');
};
