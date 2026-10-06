# SecotrustSabreDavBundle #

> **This repository is archived and no longer maintained.**
> Development continues as [`lawcloud/sabredav-bundle`](https://gitlab.com/lawcloud/sabredav-bundle) on GitLab.

## Moving to `lawcloud/sabredav-bundle` ##

The bundle lives on under a new package name, starting at version 4.0:

```console
composer remove secotrust/sabredav-bundle
composer require lawcloud/sabredav-bundle:^4.0
```

Only the package name changed: the PHP namespace `Secotrust\Bundle\SabreDavBundle`, the bundle class
`SecotrustSabreDavBundle`, the `@SecotrustSabreDavBundle` resource prefix and the `secotrust_sabre_dav`
configuration key stay the same.

Version 4.0 itself has breaking changes, such as importing the routes from `config/routing.php` instead of
`config/routing.xml`, removed plugins, and PHP 8.3+ with Symfony 6.4, 7.x or 8.x. Read its
[UPGRADE.md](https://gitlab.com/lawcloud/sabredav-bundle/-/blob/main/UPGRADE.md) before upgrading.

The versions released from this repository, up to 3.0.0, remain available here, but receive no further
changes.

## About ##

This bundle integrates the [SabreDAV](https://sabre.io/) library into Symfony. It is a fork of
[secotrust/SecotrustSabreDavBundle](https://github.com/secotrust/SecotrustSabreDavBundle) without the
dependency on FOSUserBundle.

## License ##

See [LICENSE](LICENSE).
