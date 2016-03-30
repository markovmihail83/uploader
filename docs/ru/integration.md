<a name="top" />Инструкция по интеграцию
========================================

- [Шаг 0: Введение.](#step-0)
    - [Шаг 1: Создавайте репозиторий адаптеров файловой системы.](#step-1)
    - [Шаг 2: Создавайте репозиторий неймеров.](#step-2)
    - [Шаг 3: Реализуйте `Atom\Uploader\Handler\IPropertyHandler`.](#step-3)
    - [Шаг 4: Реализуйте `Atom\Uploader\Event\IEventDispatcher`.](#step-4)
    - [Шаг 5: Создавайте репозиторий метаданных.](#step-5)
    - [Шаг 6: Создавайте обработчика загрузки.](#step-6)
    - [Шаг 7: Создавайте обработчика событий.](#step-7)
    - [Шаг 8: Интегрируйте со слоем хранение данных.](#step-8)


<a name="step-0" />Шаг 0: Введение.
-----------------------------------

В этом инструкции сервисы будут создаваться с использованием оператора ```new```, <br />
но вместо этого вы должны зарегистрировать их в вашем Фреймворке.

<a name="step-1" />Шаг 1: Создавайте репозиторий адаптеров файловой системы. <sub>[на верх](#top)</sub>
-------------------------------------------------------------------------------------------------------

```php
    $filesystemMap = [
        // 'fs_adapter' в дальнейшем можно использовать при создании метаданных.
        // $filesystemAdapter должен быть экземпляром интерфейса Atom\Uploader\Filesystem\IFilesystemAdapter
        'fs_adapter' => $filesystemAdapter,
        ...
    ];

    $filesystemAdapterRepo = new Atom\Uploader\Filesystem\FilesystemAdapterRepo($filesystemMap);
```


> В качестве `$filesystemAdapter` можно использовать [готовых адаптеров](available-fs-adapters.md),
  и/или [создать свое](custom-filesystem.md).

<a name="step-2" />Шаг 2: Создавайте репозиторий неймеров. <sub>[на верх](#top)</sub>
-------------------------------------------------------------------------------------

```php
    $namerMap = [
        // 'naming_strategy' в дальнейшем можно использовать при создании метаданных.
        // $namer должен быть экземпляром интерфейса Atom\Uploader\Naming\INamer
        'naming_strategy' => $namer,
        ...
    ];

    $namerRepo = new Atom\Uploader\Naming\NamerRepo($namerMap);
```

> В качестве `$namer` можно использовать [готовых неймеров](available-namers.md)
  и/или [создать свое](custom-namer.md).

<a name="step-3" />Шаг 3: Реализуйте [<sub>`Atom\Uploader\Handler\IPropertyHandler`.</sub>](../../src/Handler/IPropertyHandler.php) <sub>[на верх](#top)</sub>
---------------------------------------------------------------------------------------------------------------------------------------------------------

> Это нужно для чтение и запись свойств загружаемого объекта. <br />

Пример реализации с использованием `symfony/property-access` [`ExampleApp\Handler\PropertyHandler`](../../example-app/src/Handler/PropertyHandler.php):

```php
    $propertyHandler = ...;
```


<a name="step-4" />Шаг 4: Реализуйте [<sub>`Atom\Uploader\Event\IEventDispatcher`.</sub>](../../src/Event/IEventDispatcher.php) <sub>[на верх](#top)</sub>
-----------------------------------------------------------------------------------------------------------------------------------------------------

> Это обертка над диспетчером событий вашего Фреймворка.

Сначала реализуйте интерфейс [`Atom\Uploader\Event\IUploadEvent`](../../src/Event/IUploadEvent.php). <br />
Для этого достаточно использовать следующий трейт [`Atom\Uploader\Event\UploadEvent`](../../src/Event/UploadEvent.php).

> Почему это сделано так?
>- для того чтобы оставить возможность наследоваться от базового события вашего диспетчера событий.

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

Теперь можно реализовать [`Atom\Uploader\Event\IEventDispatcher`](../../src/Event/IEventDispatcher.php).

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

<a name="step-5" />Шаг 5: Создавайте репозиторий метаданных. <sub>[на верх](#top)</sub>
---------------------------------------------------------------------------------------

> Сначала подготовьте метаданные,
  для этого нужно создать экземпляры класса [`Atom\Uploader\Metadata\FileMetadata`](../../src/Metadata/FileMetadata.php)
  с нужными вам параметрами. [Подробнее о метаданных](metadata.md).

```php
    $classNamesForMetadata = [
        'Atom\Uploader\Model\Embeddable\FileReference' => 'metadata-name'
    ];

    $metadataMap = [
        // $metadata должен быть экземпляром Atom\Uploader\Metadata\FileMetadata
        'metadata-name' => $metadata,
        ...
    ]

    $metadataRepo = new MetadataRepo($classNamesForMetadata, $metadataMap);
```

> `$classNamesForMetadata` должен содержат хэш типа: `['имя загружаемого класса' => 'имя метаданных']`. <br />

<a name="step-6" />Шаг 6: Создавайте обработчика загрузки. <sub>[на верх](#top)</sub>
--------------------------------------------------------------------------------------

Сначала реализуйте [`Atom\Uploader\LazyLoad\IFilesystemAdapterRepoLazyLoader`](../../src/LazyLoad/IFilesystemAdapterRepoLazyLoader.php).

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

Затем создавайте экземпляр этого класса:

```php
    $adapterRepoLoader = new Acme\LazyLoad\FilesystemAdapterRepoLazyLoader($container);
```

Затем создавайте обработчика загрузки.

```php
    $uploadHandler = new Atom\Uploader\Handler\UploadHandler(
        $metadataRepo,
        $propertyHandler,
        $adapterRepoLoader,
        $namerRepo,
        $dispatcher
    );
```

<a name="step-7" />Шаг 7: Создавайте обработчика событий. <sub>[на верх](#top)</sub>
-------------------------------------------------------------------------------------

Сначала реализуйте [`Atom\Uploader\LazyLoad\IUploadHandlerLazyLoader`](../../src/LazyLoad/IUploadHandlerLazyLoader.php).

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

Затем создавайте экземпляр этого класса:

```php
    $uploadHandlerLazyLoader = new Acme\LazyLoad\UploadHandlerLazyLoader($container);
```

Затем создавайте обработчика событий.

```php
    $eventHandler = new Atom\Uploader\Handler\EventHandler($uploadHandlerLazyLoader);
```

<a name="step-8" />Шаг 8: Интегрируйте со слоем хранение данных. <sub>[на верх](#top)</sub>
-------------------------------------------------------------------------------------------

Используйте [готовых слушателей](db-persistance-layer-listeners.md) для популярных библиотек, таких как `doctrine` и т.д.

> Или интегрируйте со своим слоем хранение данных используя обработчика событий. <br />
  Для этого прочитайте [как использовать обработчика событий](how-to-use-the-event-handler.md).
