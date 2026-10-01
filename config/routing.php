<?php

namespace Symfony\Component\Routing\Loader\Configurator;

return static function (RoutingConfigurator $routes) {
    $routes->add('secotrust_sabre_dav', '/{url}')
        ->methods(['HEAD', 'GET', 'POST', 'OPTIONS', 'PROPFIND', 'PROPPATCH', 'MKCOL', 'COPY', 'MOVE', 'DELETE', 'LOCK', 'UNLOCK', 'PUT', 'PATCH', 'REPORT'])
        ->controller('secotrust.sabredav.controller::execAction')
        ->defaults(['url' => ''])
        ->requirements(['url' => '/?.*'])
        ->options(['expose' => true]);
};
