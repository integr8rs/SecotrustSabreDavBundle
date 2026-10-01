<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('secotrust.sabredav.controller.class', \Secotrust\Bundle\SabreDavBundle\Controller\SabreDavController::class);
    $parameters->set('secotrust.sabredav.exceptionHandler.class', \Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer::class);
    $parameters->set('secotrust.sabredav.server.inner.class', \Sabre\DAV\Server::class);
    $parameters->set('secotrust.sabredav.server.class', \Secotrust\Bundle\SabreDavBundle\SabreDav\Server::class);
    $parameters->set('secotrust.sabredav.use_symfony_exception_handler', 1);
    $parameters->set('secotrust.sabredav.server.nodes', ['', '', '']);

    $services->set('secotrust.sabredav.server', '%secotrust.sabredav.server.class%')
        ->args([
            service('secotrust.sabredav.server.inner'),
            service('secotrust.sabredav.exceptionHandler'),
            service('router'),
            '%secotrust.sabredav.use_symfony_exception_handler%',
        ]);

    $services->set('secotrust.sabredav.server.inner', '%secotrust.sabredav.server.inner.class%')
        ->args(['']);

    $services->set('secotrust.sabredav.exceptionHandler', '%secotrust.sabredav.exceptionHandler.class%')
        ->args(['$debug' => true]);

    $services->set('secotrust.sabredav.controller', '%secotrust.sabredav.controller.class%')
        ->args([service('secotrust.sabredav.server')])
        ->tag('controller.service_arguments');
};
