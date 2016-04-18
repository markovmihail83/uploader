<a name="top"></a>Адаптеры файловой системы.
===

** Готовые адаптеры **

> В описаниях адаптера упоминается что должен быть в параметре 'fs_prefix'. <br />
  Этот параметр используется при обращении к методам адаптера, <br />
  и понадобиться при создании [метаданных][metadata].

- [Обертка над flysystem.](#flysystem)
- [Локальный адаптер.](#local)

** Создать свое **

Для создание адаптера, реализуйте интерфейс [<sub>`Atom\Uploader\Filesystem\IFilesystemAdapter`.</sub>][IFilesystemAdapter]

<a name="flysystem"/></a>Обертка над [flysystem].
---

```php
    $mountManager = ...; // это должно быть экземпляром League\Flysystem\MountManager
    $streamWrapper = new Atom\Uploader\ThirdParty\FlysystemStreamWrapper();
    $flysystemAdapter = new Atom\Uploader\Filesystem\FlysystemAdapter($mountManager, $streamWrapper);
```
> 'fs_prefix' должен быть названием файловой системы `flysystem`.

> если используете [`twistor/flysystem-stream-wrapper`][flysystem-stream-wrapper], <br />
 то при внедрении информацию о файле, нужная файловая система автоматически монтируется.

<a name="local"></a>Локальный адаптер.
---

```php
    $localAdapter = new Atom\Uploader\Filesystem\LocalAdapter();
```

> 'fs_prefix' должен быть абсолютным путем.

[IFilesystemAdapter]: ../../src/Filesystem/IFilesystemAdapter.php
[flysystem-stream-wrapper]: https://github.com/twistor/flysystem-stream-wrapper
[flysystem]: https://github.com/thephpleague/flysystem
[metadata]: metadata.md