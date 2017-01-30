Как использовать загрузчика?
===

Примеры использование загрузчика с [doctrine dbal][dbal]:
---

#### Настройка

Создавайте метаданные:

```php
// Название свойства или метода для получение файла.
$fileSetter = 'file';

// Название свойства или метода для записи относительный путь к файлу.
$fileGetter = 'file';

// Название свойства или метода для записи URI
$uriSetter = 'uri';

// Название свойства или метода для записи информацию о файле(\SplFileInfo)
$fileInfoSetter = 'fileInfo';

// Название адаптера файловой системы.
$fsAdapter = 'local';

// Префикс файловой системы.
// Значение зависит от того какой адаптер файловой системы используется.
$fsPrefix = __DIR__ . '/../../web/uploads';

// Префикс URI.
// %s будет заменено на относительный путь к файлу.
$uriPrefix = '/uploads/%s';

// Название стратегии именирование.
$namingStrategy = 'unique_id';

// Если true то после обновления данных старый файл удаляется.
$deleteOldFile = true;

// Если true то после удаления данных файл тоже удаляется.
$deleteOnRemove = true;

// Если true то при загрузке данных внедряется URI
$injectUriOnLoad = true;

// Если true то при загрузке данных внедряется информация о файле (\SplFileInfo)
$injectFileInfoOnLoad = true;

$metadata = new Atom\Uploader\Metadata\FileMetadata(
    $fileSetter,
    $fileSetter,
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

Дополняйте [шаг 4][step-4]

```php
$metadataNames = [
    ...
    'uploader' => 'dbal_uploader'
    ...
];

$metadataMap = [
    ...
    'dbal_uploader' => $metadata,
    ...
]

$metadataRepo = new MetadataRepo($metadataNames, $metadataMap);
```

База данных:
```sql
CREATE TABLE IF NOT EXISTS uploadable (
  id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  file VARCHAR(255)
);
```

##### Сохранение загруженного файла
```php
$file = // Экземпляр \SplFileInfo;
$conn = // экземпляр Doctrine\DBAL\Connection
$uploader = // экземпляр Atom\Uploader\Handler\Uploader

// Любое уникальное для этой транзакции значение.
$transactionId = uniqid();

$uploadable = compact('file');

// Перед сохранением данных сохраняется прикрепленные файлы.
// Название файла будет сгенирировано автоматически согласно мэппингу,
// т.е. значение $uploadable['file'] будут изменено на автоматически сгенерированное название(относительный путь).
$uploader->persist($transactionId, $uploadable, 'uploadable' /* для массивов нужно явно указат название мэппинга */);

try {
    $uploadable['id'] = $conn->insert('uploadable', ['file' => $uploadable['file']]);

    // После успешного сохранение данных нужно уведомлять об этом.
    $uploader->saved($transactionId);
} catch (\Exception $e) {
    // do nothing
} finally {
    // Если данные успешно сохранены то ничего не делается иначе файл удаляется.
    $uploader->flush();
}
```

##### Обновление
```php
$id = // идентификатор данных.
$file = // Экземпляр \SplFileInfo;
$conn = // экземпляр Doctrine\DBAL\Connection
$uploader = // экземпляр Atom\Uploader\Handler\Uploader

$statement = $conn->prepare('SELECT id, file FROM uploadable t WHERE t.id = :id');
$statement->bindValue('id', $id);
$statement->execute();

$oldUploadable = $statement->fetch();
$newUploadable = array_merge($oldUploadable, ['file' => $file]);

// Любое уникальное для этой транзакции значение.
$transactionId = uniqid();

// Перед обновлением новый файл(если есть) сохраняется.
$uploader->update($transactionId, $newUploadable, $oldUploadable, 'uploadable');

try {
    $conn->update('uploadable', ['file' => $newUploadable['file']], compact('id'));

    // После успешного обновления старый файл удаляется.
    $uploader->updated($identity);
} catch (\Exception $e) {
    // do nothing
} finally {
    // Если обновления прошло успешно то ничего не делается иначе новый файл удаляется.
    $uploader->flush();
}
```

##### Получение
```php
$id = // идентификатор данных.
$conn = // экземпляр Doctrine\DBAL\Connection
$uploader = // экземпляр Atom\Uploader\Handler\Uploader

$statement = $conn->prepare('SELECT id, file FROM uploadable t WHERE t.id = :id');
$statement->bindValue('id', $id);
$statement->execute();

$uploadable = $statement->fetch();

// Внедряется URI и информация о файле(\SplFileInfo)
$uploader->loaded($uploadable, 'uploadable');
```

##### Удаление

```php
$id = // идентификатор данных.
$conn = // экземпляр Doctrine\DBAL\Connection
$uploader = // экземпляр Atom\Uploader\Handler\Uploader

$statement = $conn->prepare('SELECT id, file FROM uploadable t WHERE t.id = :id');
$statement->bindValue('id', $id);
$statement->execute();

$uploadable = $statement->fetch();

$conn->delete('uploadable', ['id' => $this->getId()]);

// После удаления данных файл тоже удаляется.
$uploader->removed($uploadable, 'uploadable');
```

Автоматизация
---
Выше приведенные примеры лучше использовать в событиях хранилища данных(если такое есть), см [ORMListener].

[dbal]: http://www.doctrine-project.org/projects/dbal.html
[ORMListener]: https://github.com/atom-php/uploader/blob/master/src/Listener/ORM/ORMListener.php
[step-4]: integration.md#step-4
