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

## DAV-Clients

Full List of supported SabreDav Clients: [http://sabre.io/dav/clients/](http://sabre.io/dav/clients/)

### Thunderbird
+ SogoConnector: [http://www.sogo.nu/downloads/frontends.html](http://www.sogo.nu/downloads/frontends.html)

### Microsoft Outlook / Windows 8
+ [http://german.evomailserver.com/download.php](http://german.evomailserver.com/download.php): EVO Kollaborateur (Untested), 
+ [https://help.atmail.com/hc/en-us/articles/200907874-Syncing-Outlook-Contacts-and-Calendars-with-Atmail-DavSync](https://help.atmail.com/hc/en-us/articles/200907874-Syncing-Outlook-Contacts-and-Calendars-with-Atmail-DavSync)
+ [http://forum.xda-developers.com/showthread.php?t=2478215](http://forum.xda-developers.com/showthread.php?t=2478215)
+ [http://www.outlookdav.com/](http://www.outlookdav.com/): no freeware

### Mac OS X >= 10.8 / iOS >=7:
+ native implementation

### Android Smartphones
+ [http://dmfs.org/carddav/](http://dmfs.org/carddav/)
+ [http://dmfs.org/calcav/](http://dmfs.org/caldav/)

