<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('secotrust.sabredav.lock_backend.class', \Sabre\DAV\Locks\Backend\File::class);
    $parameters->set('secotrust.sabredav.lock_plugin.class', \Sabre\DAV\Locks\Plugin::class);

    $services->set('secotrust.sabredav_lock_backend', '%secotrust.sabredav.lock_backend.class%')
        ->private()
        ->args(['%kernel.cache_dir%/sabredav/locks']);

    $services->set('secotrust.sabredav_lock_plugin', '%secotrust.sabredav.lock_plugin.class%')
        ->private()
        ->args([service('secotrust.sabredav_lock_backend')])
        ->tag('secotrust.sabredav.plugin');
};
