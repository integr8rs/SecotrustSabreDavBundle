# Upgrade

## Upgrade to 3.1

### Supported PHP and Symfony versions

The bundle now requires PHP 8.2 or newer, and Symfony 6.4, 7.x or 8.x. Support for PHP 8.1 and Symfony 5.4,
which are both end-of-life, was dropped.

### Native return types added

To resolve the return-type deprecations reported by Symfony's `DebugClassLoader`, these methods now declare
native return types. If you extend one of these classes and override these methods, add the same return types
to your overrides:

- `SabreDav\Gaufrette\Collection`: `getChildren(): array`, `getChild(): INode`, `childExists(): bool`,
  `getName(): string`, `getLastModified(): ?int`
- `SabreDav\Gaufrette\File`: `getName(): string`, `getSize(): int`, `getLastModified(): ?int`,
  `put(): ?string`, `get(): mixed`
- `SabreDav\CardDavBackend`: `createAddressBook(): mixed`

### Routing configuration moved from XML to PHP

Symfony 7.4 deprecated the XML configuration format and Symfony 8.0 removes it, so the bundle's
configuration is now written in PHP.

`config/routing.xml` is deprecated in favour of `config/routing.php`. It still works, as it only imports
`routing.php`, but on Symfony 7.4 it triggers the XML deprecation notice, and it will be **removed in 4.0**.

Update the import in your routing configuration:

```diff
 # config/routes.yaml
 dav:
-    resource: "@SecotrustSabreDavBundle/config/routing.xml"
+    resource: "@SecotrustSabreDavBundle/config/routing.php"
     prefix: dav
```

The service definitions were converted as well. They are loaded by the bundle itself, so no change is needed
for those, unless you load or reference the bundle's `config/services/*.xml` files directly: those are gone,
use the `.php` files with the same name instead.
