<a name="top" />Инструкция по интеграцию
===

- [Шаг 0: Введение.](#step-0)
    - [Шаг 1: Создавайте репозиторий адаптеров файловой системы.](#step-1)
    - [Шаг 2: Создавайте репозиторий неймеров.](#step-2)
    - [Шаг 4: Реализуйте `Atom\Uploader\Event\IEventDispatcher`.](#step-3)
    - [Шаг 4: Создавайте репозиторий метаданных.](#step-4)
    - [Шаг 5: Создавайте обработчика загрузки.](#step-5)
    - [Шаг 6: Создавайте загрузчика.](#step-6)
    - [Шаг 7: Интегрируйте c хранилища данных.](#step-7)


<a name="step-0" />Шаг 0: Введение.
---

В этом инструкции сервисы будут создаваться с использованием оператора ```new```, <br />
но вместо этого вы должны зарегистрировать их в вашем Фреймворке.

<a name="step-1" />Шаг 1: Создавайте репозиторий адаптеров файловой системы.
---

```php
    $filesystemMap = [
        // 'fs_adapter' в дальнейшем используется при создании метаданных.
        // $filesystemAdapter должен быть экземпляром Atom\Uploader\Filesystem\IFilesystemAdapter
        'fs_adapter' => $filesystemAdapter,
        ...
    ];

    $filesystemAdapterRepo = new Atom\Uploader\Filesystem\FilesystemAdapterRepo($filesystemMap);
```


> В качестве `$filesystemAdapter` можно использовать [готовых адаптеров и/или создать свое][fs-adapters].

<a name="step-2" />Шаг 2: Создавайте репозиторий неймеров.
---

```php
    $namerMap = [
        // 'naming_strategy' в дальнейшем можно использовать при создании метаданных.
        // $namer должен быть экземпляром интерфейса Atom\Uploader\Naming\INamer
        'naming_strategy' => $namer,
        ...
    ];

    $namerRepo = new Atom\Uploader\Naming\NamerRepo($namerMap);
```

> В качестве `$namer` можно использовать [готовых неймеров и/или создать свое][namers].

<a name="step-3" />Шаг 3: Реализуйте [<sub>`Atom\Uploader\Event\IEventDispatcher`.</sub>][IEventDispatcher] <sub>[на верх](#top)</sub>
---

> Это обертка над диспетчером событий вашего Фреймворка.

Сначала реализуйте интерфейс [`Atom\Uploader\Event\IUploadEvent`][IUploadEvent]. <br />
Для этого достаточно использовать следующий трейт [`Atom\Uploader\Event\UploadEvent`][UploadEvent].

Пример реализации для `symfony`:

```php
<?php
# src/Acme/Event/UploadEvent

namespace Acme\Event;

use Atom\Uploader\Event\IUploadEvent;
use Symfony\Component\EventDispatcher\Event;

class UploadEvent extends Event implements IUploadEvent
{
    use \Atom\Uploader\Event\UploadEvent;
}
```

Теперь можно реализовать [`Atom\Uploader\Event\IEventDispatcher`][IEventDispatcher].

Пример реализации для `symfony`:

```php
<?php
# src/Acme/Event/EventDispatcher

namespace Acme\Event;

use Atom\Uploader\Event\IUploadEvent;
use Atom\Uploader\Metadata\FileMetadata;
use Acme\Event\UploadEvent;
use Atom\Uploader\Event\IEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventDispatcher implements IEventDispatcher
{

    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param string       $eventName
     *
     * @param object       $fileReference
     * @param FileMetadata $metadata
     *
     * @return IUploadEvent
     */
    public function dispatch($eventName, $fileReference, FileMetadata $metadata) {
        return $this->dispatcher->dispatch($eventName, new UploadEvent($fileReference, $metadata));
    }
}
```

Затем создавайте экземпляр этого класса:

```php
    $dispatcher = new Acme\Event\EventDispatcher();
```

<a name="step-4" />Шаг 4: Создавайте репозиторий метаданных. <sub>[на верх](#top)</sub>
---

> Сначала подготовьте метаданные,
  для этого нужно создать экземпляры класса [`Atom\Uploader\Metadata\FileMetadata`][FileMetadata]
  с нужными вам параметрами. [Подробнее о метаданных][metadata].

```php
    $metadataNames = [
        'Atom\Uploader\Model\Embeddable\FileReference' => 'metadata-name'
    ];

    $metadataMap = [
        // $metadata должен быть экземпляром Atom\Uploader\Metadata\FileMetadata
        'metadata-name' => $metadata,
        ...
    ]

    $metadataRepo = new MetadataRepo($metadataNames, $metadataMap);
```

<a name="step-5" />Шаг 5: Создавайте обработчика загрузки. <sub>[на верх](#top)</sub>
---

Сначала реализуйте [`Atom\Uploader\LazyLoad\IFilesystemAdapterRepoLazyLoader`][IFilesystemAdapterRepoLazyLoader].

Пример реализации для `symfony`:

```php
<?php
# src/Acme/LazyLoad/FilesystemAdapterRepoLazyLoader.php

namespace Acme\LazyLoad;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Atom\Uploader\LazyLoad\IFilesystemAdapterRepoLazyLoader;

class FilesystemAdapterRepoLazyLoader implements IFilesystemAdapterRepoLazyLoader {

    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getFilesystemAdapterRepo() {
        return $this->container->get('...');
    }
}
```

Затем создавайте обработчика загрузки.

```php
    $adapterRepoLoader = new Acme\LazyLoad\FilesystemAdapterRepoLazyLoader($container);

    $uploadHandler = new Atom\Uploader\Handler\UploadHandler(
        $metadataRepo,
        $propertyHandler,
        $adapterRepoLoader,
        $namerRepo,
        $dispatcher
    );
```

<a name="step-6" />Шаг 6: Создавайте загрузчика. <sub>[на верх](#top)</sub>
---

Сначала реализуйте [`Atom\Uploader\LazyLoad\IUploadHandlerLazyLoader`][IUploadHandlerLazyLoader].

Пример реализации для `symfony`:

```php
<?php
# src/Acme/LazyLoad/UploadHandlerLazyLoader.php

namespace Acme\LazyLoad;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Atom\Uploader\LazyLoad\IUploadHandlerLazyLoader;

class UploadHandlerLazyLoader implements IUploadHandlerLazyLoader {

    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getUploadHandler() {
        return $this->container->get('...');
    }
}
```

Затем создавайте загрузчика.

```php
    $uploadHandlerLazyLoader = new Acme\LazyLoad\UploadHandlerLazyLoader($container);
    $eventHandler = new Atom\Uploader\Handler\EventHandler($uploadHandlerLazyLoader);
```

<a name="step-7" />Шаг 7: Интегрируйте c хранилища данных. <sub>[на верх](#top)</sub>
---

- [Готовая интеграция с сущностями doctrine][integration-with-doctrine-entities]
- [Готовая интеграция с встраиваемых объектов doctrine][integration-with-doctrine-embeddables]

> Или интегрируйте со своим слоем хранение данных используя загрузчика. <br />
  См. [как использовать загрузчика?][how-to-use-the-uploader]

[datastore-listeners]: datastore-listeners.md
[how-to-use-the-uploader]: how-to-use-the-uploader.md
[fs-adapters]: fs-adapters.md
[namers]: namers.md
[IEventDispatcher]: ../../src/Event/IEventDispatcher.php
[IUploadEvent]: ../../src/Event/IUploadEvent.php
[UploadEvent]: ../../src/Event/UploadEvent.php
[FileMetadata]: ../../src/Metadata/FileMetadata.php
[metadata]: metadata.md
[IFilesystemAdapterRepoLazyLoader]: ../../src/LazyLoad/IFilesystemAdapterRepoLazyLoader.php
[IUploadHandlerLazyLoader]: ../../src/LazyLoad/IUploadHandlerLazyLoader.php
[integration-with-doctrine-entities]: integration-with-doctrine-entities.md
[integration-with-doctrine-embeddables]: integration-with-doctrine-embeddables.md