<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp;


use Atom\Uploader\Metadata\FileMetadata;
use Atom\Uploader\Naming\BasenameNamer;
use Atom\Uploader\Storage\LocalStorage;
use ExampleApp\Command\ORM\GetCommand;
use ExampleApp\Command\ORM\RemoveCommand;
use ExampleApp\Command\ORM\UpdateCommand;
use ExampleApp\Command\ORM\UploadCommand;
use ExampleApp\DependencyInjection\AppContainer;
use ExampleApp\DependencyInjection\IAppContainer;
use ExampleApp\Entity\ORM\UploadableEntity;
use ExampleApp\Entity\ORMEmbeddable\EntityHasEmbeddedFile;
use ExampleApp\Event\EventDispatcher;
use ExampleApp\Handler\PropertyHandler;
use Atom\Uploader\Handler\ListenerHandler;
use Atom\Uploader\Handler\UploadHandler;
use Atom\Uploader\Listener\ORM\ORMListener;
use Atom\Uploader\Listener\ORMEmbeddable\ORMEmbeddableListener;
use Atom\Uploader\Metadata\MetadataFactory;
use Atom\Uploader\Naming\NamerFactory;
use Atom\Uploader\Naming\UniqueNamer;
use Atom\Uploader\Storage\FlysystemStorage;
use Atom\Uploader\Storage\StorageFactory;
use Atom\Uploader\ThirdParty\FlysystemStreamWrapper;
use Doctrine\ORM\Events;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Symfony\Component\Console\Application;
use ExampleApp\Command\ORMEmbeddable;
use Symfony\Component\Yaml\Yaml;

class Setup
{
    const DOCTRINE_EVENTS = [
        Events::prePersist,
        Events::postPersist,
        Events::preUpdate,
        Events::postUpdate,
        Events::postLoad,
        Events::postRemove,
        Events::postFlush,
    ];

    const DEFAULT_MAPPING = [
        'file_setter' => 'file',
        'file_getter' => 'file',
        'uri_setter' => 'uri',
        'file_info_setter' => 'fileInfo',
        'filesystem_prefix' => __DIR__ . '/Resources/public/uploads',
        'uri_prefix' => '/uploads/%s',
        'storage_type' => 'local',
        'naming_strategy' => 'unique_id',
        'delete_old_file' => true,
        'delete_on_remove' => true,
        'inject_uri_on_load' => true,
        'inject_file_info_on_load' => true,
    ];

    public static function setup(Application $app)
    {
        $container = new AppContainer();
        $storageFactory = self::createStorageFactory();
        $container->setStorageFactory($storageFactory);
        $namerFactory = self::createNamerFactory();
        $propertyHandler = new PropertyHandler();
        $dispatcher = new EventDispatcher();
        $container->setDispatcher($dispatcher);

        $listenerHandler = new ListenerHandler($container);

        $ormListener = self::createOrmListener($listenerHandler);
        $container->setOrmListener($ormListener);

        $ormEmbeddableListener = self::createOrmEmbeddableListener($listenerHandler);
        $container->setOrmEmbeddableListener($ormEmbeddableListener);

        $mappings = self::getMappingsFromConfig();
        $extraMappings = self::getExtraMappings();
        $metadataFactory = self::createMetadataFactory($mappings, $extraMappings);

        $uploadHandler = new UploadHandler(
            $metadataFactory,
            $propertyHandler,
            $container,
            $namerFactory,
            $dispatcher
        );

        $container->setUploadHandler($uploadHandler);

        self::registerCommands($container, $app);

        return $container;
    }

    private static function getExtraMappings()
    {
        $path = getenv('EXTRA_MAPPINGS') ?: __DIR__ . '/../var/tmp/extra-mappings.yml';

        if (!$path || !file_exists($path)) {
            return [];
        }

        return Yaml::parse(file_get_contents($path));
    }

    private static function getMappingsFromConfig()
    {
        $mappingsPath = __DIR__ . '/Resources/config/mappings.yml';

        if (!file_exists($mappingsPath)) {
            return [];
        }

        return Yaml::parse(file_get_contents($mappingsPath));
    }

    private static function registerCommands(IAppContainer $container, Application $app)
    {
        $app->addCommands(
            [
                new UploadCommand('orm:upload', $container),
                new RemoveCommand('orm:remove', $container),
                new UpdateCommand('orm:update', $container),
                new GetCommand('orm:get', $container),

                new ORMEmbeddable\UploadCommand('orm_embeddable:upload', $container),
                new ORMEmbeddable\RemoveCommand('orm_embeddable:remove', $container),
                new ORMEmbeddable\UpdateCommand('orm_embeddable:update', $container),
                new ORMEmbeddable\GetCommand('orm_embeddable:get', $container),
            ]
        );
    }

    private static function createStorageFactory()
    {
        $localAdapter = new Local(__DIR__ . '/Resources/public/uploads');
        $localFilesystem = new Filesystem($localAdapter);
        $mountManager = new MountManager();
        $mountManager->mountFilesystem('embeddableFs', $localFilesystem);

        $flysystemStorage = new FlysystemStorage($mountManager, new FlysystemStreamWrapper());
        $localStorage = new LocalStorage();

        $storageFactory = new StorageFactory([
            'flysystem' => $flysystemStorage,
            'local' => $localStorage
        ]);

        return $storageFactory;
    }

    private static function createNamerFactory()
    {
        $uniqueNamer = new UniqueNamer();
        $basenameNamer = new BasenameNamer();
        $namerFactory = new NamerFactory([
            'unique_id' => $uniqueNamer,
            'basename' => $basenameNamer,
        ]);

        return $namerFactory;
    }

    private static function createMetadataFactory()
    {
        $fileReferenceClasses = [];
        $metadataMap = [];

        foreach (func_get_args() as $argument) {
            foreach ($argument as $fileReferenceClass => $mapping) {
                $defaults = self::DEFAULT_MAPPING;

                if (isset($fileReferenceClasses[$fileReferenceClass])) {
                    $defaults = $metadataMap[$fileReferenceClasses[$fileReferenceClass]];
                }

                $metadata = array_merge($defaults, $mapping);
                $metadataIndex = array_search($metadata, $metadataMap);

                if (false === $metadataIndex) {
                    $metadataIndex = array_push($metadataMap, $metadata) - 1;
                }

                $fileReferenceClasses[$fileReferenceClass] = $metadataIndex;
            }
        }

        $diff = array_diff(array_keys($metadataMap), array_values($fileReferenceClasses));

        foreach ($diff as $unusedMetadataId) {
            unset($metadataMap[$unusedMetadataId]);
        }

        foreach ($metadataMap as &$metadata) {
            $metadata = new FileMetadata(
                $metadata['file_setter'],
                $metadata['file_getter'],
                $metadata['uri_setter'],
                $metadata['file_info_setter'],
                $metadata['filesystem_prefix'],
                $metadata['uri_prefix'],
                $metadata['storage_type'],
                $metadata['naming_strategy'],
                $metadata['delete_old_file'],
                $metadata['delete_on_remove'],
                $metadata['inject_uri_on_load'],
                $metadata['inject_file_info_on_load']
            );
        }

        return new MetadataFactory($fileReferenceClasses, $metadataMap);
    }

    private static function createOrmListener(ListenerHandler $handler)
    {
        $fileReferenceEntities = [
            UploadableEntity::class => UploadableEntity::class
        ];

        return new ORMListener($handler, $fileReferenceEntities, self::DOCTRINE_EVENTS);
    }

    private static function createOrmEmbeddableListener(ListenerHandler $handler)
    {
        $fileReferenceProperties = [
            EntityHasEmbeddedFile::class => [
                'fileReference'
            ]
        ];

        return new ORMEmbeddableListener($handler, $fileReferenceProperties, self::DOCTRINE_EVENTS);
    }

    final private function __construct()
    {
    }
}