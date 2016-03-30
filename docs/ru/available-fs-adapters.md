<a name="top"></a>Доступные адаптеры файловой системы.
======================================================

> Примечание: <br />
  В описаниях адаптера упоминается что должен быть в параметре 'fs_prefix'. <br />
  Этот параметр используется при обращении к методам адаптера, <br />
  и понадобиться при создании [метаданных](metadata.md).

- [Обертка над flysystem.](#flysystem)
- [Локальный адаптер.](#local)


<a name="flysystem"/></a>Обертка над [flysystem](https://github.com/thephpleague/flysystem). <sub>[на верх](#top)</sub>
------------------------------------------------------------------------------------------------------------------------

```php
    $mountManager = ...; // это должно быть экземпляром League\Flysystem\MountManager
    $streamWrapper = new Atom\Uploader\ThirdParty\FlysystemStreamWrapper();
    $flysystemAdapter = new Atom\Uploader\Filesystem\FlysystemAdapter($mountManager, $streamWrapper);
```
> 'fs_prefix' должен быть названием файловой системы `flysystem`.

> если используете [`twistor/flysystem-stream-wrapper`](https://github.com/twistor/flysystem-stream-wrapper), <br />
 то при внедрении информацию о файле, нужная файловая система автоматически монтируется.

<a name="local"></a>Локальный адаптер. <sub>[на верх](#top)</sub>
-----------------------------------------------------------------

```php
    $localAdapter = new Atom\Uploader\Filesystem\LocalAdapter();
```

> 'fs_prefix' должен быть абсолютным путем.
