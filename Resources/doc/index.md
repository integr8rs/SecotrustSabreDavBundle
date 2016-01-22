# SecotrustSabreDavBundle #

This bundle is still WIP.

## Setup
First add this bundle to your composer dependencies:

`> composer require secotrust/sabredav-bundle dev-master`

Then register it in your AppKernel.php.

```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Secotrust\Bundle\SabreDavBundle\SecotrustSabreDavBundle(),
            // ...
```

Add DAV routes.

```yaml
# app/config/routing.yml
dav:
    resource: "@SecotrustSabreDavBundle/Resources/config/routing.xml"
    prefix: dav
```

Define a service to use for file access tagged with `secotrust.sabredav.collection`.

```yaml
# app/config/services.yml
services:
    gaufrette.adapter:
        class: Gaufrette\Adapter\Local
        arguments:
            - "%kernel.root_dir%/../var/uploads"
    gaufrette.filesystem:
        class: Gaufrette\Filesystem
        arguments:
            - @gaufrette.adapter
    gaufrette.dav.collection:
        class: Secotrust\Bundle\SabreDavBundle\SabreDav\Gaufrette\Collection
        arguments:
            - @gaufrette.filesystem
        tags:
            - { name: secotrust.sabredav.collection }
```

## Add principal Collection

```yaml
# app/config/config.yml
secotrust_sabre_dav:
    plugins:
        #...
        principal: true
    settings:
        principals_class: Symfony\Component\Security\Core\User\User
```
