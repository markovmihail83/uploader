Метаданные
==========

Создавайте экземпляр класса [`Atom\Uploader\Metadata\FileMetadata`](../../src/Metadata/FileMetadata.php) со следующими параметрами:
---

- `$fileSetter` <br >

    Свойства загружаемого объекта для записи относительный путь файла.

    Пример: `file`

- `$fileGetter` <br >

    Свойства загружаемого объекта для чтение прикрепленного файла,
    если файл является экземпляром \SplFileInfo, to оно будет обработано.

    Пример: `file`

- `$uriSetter` <br >

    Свойства загружаемого объекта для записи URI.

    Пример: `uri`

- `$fileInfoSetter` <br >

    Свойства загружаемого объекта для записи информацию о файле.

    Пример: `fileInfo`

- `$fsPrefix` <br >

    Префикс файловой системы, используется адаптером файловой системы.

    Пример: `/project/web/uploads`
    > Разные адаптеры могут иметь разный формат префикса, прочитайте [доступные адаптеры файловой системы](available-fs-adapters.md)

- `$uriPrefix` <br >

    Шаблон URI, используется при внедрении URI.

    Пример: `/uploads/%s`
    > "%s" будут заменено с относительным путем файла.

- `$fsAdapter` <br >

    Название адаптера файловой системы.

    Пример: `local_adapter`
    > Название присваивается [при создание репозиторий адаптеров.](integration.md#step-1).

- `$namingStrategy` <br >

    Стратегия нейминга.

    Пример: `unique_id`

    > Название неймера присваивается [при создание репозиторий неймеров.](integration.md#step-2).

- `$deleteOldFile` <br >

    Если `true` то после обновлении данных старый файл будет удалено.

- `$deleteOnRemove` <br >

    Если `true` то после удалении данных файл тоже будет удалено.

- `$injectUriOnLoad` <br >

    Если `true` то после загрузки данных внедряется URI к данным.

- `$injectFileInfoOnLoad` <br >

    Если `true` то после загрузки данных внедряется информация о файле к данным.

```php
$metadata = new \Atom\Uploader\Metadata\FileMetadata(
    $fileSetter,
    $fileGetter,
    $uriSetter,
    $fileInfoSetter,
    $fsPrefix,
    $uriPrefix,
    $fsAdapter,
    $namingStrategy,
    $deleteOldFile,
    $deleteOnRemove,
    $injectUriOnLoad,
    $injectFileInfoOnLoad
);
```
