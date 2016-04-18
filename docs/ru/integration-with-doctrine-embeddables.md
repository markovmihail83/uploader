Интеграция с встраиваемыми объектами doctrine
===

```php
$evm = // экземпляр Doctrine/Common/EventManager
$uploader = // экземпляр Atom\Uploader\Handler\Uploader

// Свойств сущностей которые являются встраиваемыми объектами и имеют прикрепленные файлы.
$fileReferenceProperties = [
    'Acme\Entity\User' => [
        'avatar'
    ]
];

// Можете убрать ненужные событие,
// к примеру если не хотите удалит файлы после удаление сущность
// то уберите \Doctrine\ORM\Events::postRemove
$events = [
    \Doctrine\ORM\Events::prePersist,
    \Doctrine\ORM\Events::postPersist,

    \Doctrine\ORM\Events::preUpdate,
    \Doctrine\ORM\Events::postUpdate,

    \Doctrine\ORM\Events::postLoad,

    \Doctrine\ORM\Events::postRemove,
];

$subscriber = new Atom\Uploader\Listener\ORMEmbeddable\ORMEmbeddableListener($uploader, $fileReferenceProperties, $events);
$evm->addEventSubscriber($subscriber);
```

Использование
---

Создавайте встраиваемый объект
```php
# src/Entity/Avatar.php

use Doctrine\ORM\Mapping\Column;

class Avatar {

    /**
     * Если присвоите экземпляр \SplFileInfo к этому свойства
     * то при сохранение или обновлении сущность генерируется имя файла и сохраняется в файловой системы.
     * Сгенерированная имя файла является относительным путем к хранилище файлов.
     *
     * @Column()
     */
    private $file;

    /**
     * Эта необязательная свойства нужна для внедрение URI при загрузке сущность.
     * Этот функционал можно отключит.
     */
    private $uri;


    /**
     * Эта необязательная свойства нужна для внедрение информацию о файле(\SplFileInfo) при загрузке сущность.
     * Этот функционал можно отключит.
     */
    private $fileInfo;

    public function __construct(\SplFileInfo $file)
    {
        $this->file = $file;
    }
}
```

Встройте его в сущность
```php
# src/Entity/User.php

use Doctrine\ORM\Mapping\Embedded;

class User {
    ...

    /**
     * @Embedded(class="Acme\Entity\Avatar")
     */
    private $avatar;

    ...
}
```

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
    'Acme\Entity\Avatar' => 'acme_avatar'
    ...
];

$metadataMap = [
    ...
    'acme_avatar' => $metadata,
    ...
]

$metadataRepo = new MetadataRepo($metadataNames, $metadataMap);
```

Примеры
---

##### Сохранение загруженного файла:
```php
$file = // экземпляр \SplFileInfo
$em = // entity manager
$avatar = new Acme\Entity\Avatar($file);

$user = new Acme\Entity\User();
$user->setAvatar($avatar);

// Генерируется имя файла и сохраняется в файловой системы.
$em->persist($user);

// Если все хорошо то ничего не делается, иначе файл удаляется.
$em->flush();
```

##### Обновления:
```php
$file = // экземпляр \SplFileInfo
$user = // экземпляр Acme\Entity\User
$avatar = new Acme\Entity\Avatar($file);
$user->setAvatar($avatar);

// Генерируется имя файла и сохраняется в файловой системы.
// Удаляется старый файл если имя файла не совпадает с новым.
$em->flush();
```
##### Получение:
```php
// внедряется URI и информация о файле.
$user = $em->find('Acme\Entity\User', 1);
```

##### Удаление:
```php
$user = // экземпляр Acme\Entity\User

$em->setAvatar(null);
// или
$em->remove($user);

// Файл удаляется.
$em->flush();
```

[step-4]: integration.md#step-4
