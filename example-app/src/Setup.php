<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp;

use Atom\Uploader\Filesystem\FilesystemAdapterRepo;
use Atom\Uploader\Filesystem\FlysystemAdapter;
use Atom\Uploader\Filesystem\LocalAdapter;
use Atom\Uploader\Handler\PropertyHandler;
use Atom\Uploader\Handler\Uploader;
use Atom\Uploader\Handler\UploadHandler;
use Atom\Uploader\Listener\ORM\ORMListener;
use Atom\Uploader\Listener\ORMEmbeddable\ORMEmbeddableListener;
use Atom\Uploader\Metadata\FileMetadata;
use Atom\Uploader\Metadata\MetadataRepo;
use Atom\Uploader\Naming\BasenameNamer;
use Atom\Uploader\Naming\NamerRepo;
use Atom\Uploader\Naming\UniqueNamer;
use Atom\Uploader\ThirdParty\FlysystemStreamWrapper;
use Doctrine\ORM\Events;
use ExampleApp\Command\ORM\GetCommand;
use ExampleApp\Command\ORM\RemoveCommand;
use ExampleApp\Command\ORM\UpdateCommand;
use ExampleApp\Command\ORM\UploadCommand;
use ExampleApp\Command\ORMEmbeddable;
use ExampleApp\Command\DBAL;
use ExampleApp\DependencyInjection\AppContainer;
use ExampleApp\Entity\ORM\UploadableEntity;
use ExampleApp\Entity\ORMEmbeddable\EntityHasEmbeddedFile;
use ExampleApp\Event\EventDispatcher;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Symfony\Component\Console\Application;
use Symfony\Component\Yaml\Yaml;

class Setup
{
    final private function __construct()
    {
    }

    public static function setup(Application $app)
    {
        $container = new AppContainer();
        $filesystemAdapterRepo = self::createFilesystemAdapterRepo();
        $container->setFilesystemAdapterRepo($filesystemAdapterRepo);
        $namerRepo = self::createNamer();
        $propertyHandler = new PropertyHandler();
        $dispatcher = new EventDispatcher();
        $container->setDispatcher($dispatcher);

        $uploader = new Uploader($container);

        $container->setUploader($uploader);

        $ormListener = self::createOrmListener($uploader);
        $container->setOrmListener($ormListener);

        $ormEmbeddableListener = self::createOrmEmbeddableListener($uploader);
        $container->setOrmEmbeddableListener($ormEmbeddableListener);

        $mappings = self::getMappingsFromConfig();
        $extraMappings = self::getExtraMappings();
        $metadataRepo = self::createMetadataRepo($mappings, $extraMappings);

        $uploadHandler = new UploadHandler(
            $metadataRepo,
            $propertyHandler,
            $container,
            $namerRepo,
            $dispatcher
        );

        $container->setUploadHandler($uploadHandler);

        self::registerCommands($container, $app);

        return $container;
    }

    private static function createFilesystemAdapterRepo()
    {
        $localAdapter = new Local(__DIR__ . '/Resources/public/uploads');
        $localFilesystem = new Filesystem($localAdapter);
        $mountManager = new MountManager();
        $mountManager->mountFilesystem('embeddableFs', $localFilesystem);

        $flysystemAdapter = new FlysystemAdapter($mountManager, new FlysystemStreamWrapper());
        $localAdapter = new LocalAdapter();

        $filesystemAdapterRepo = new FilesystemAdapterRepo([
            'flysystem' => $flysystemAdapter,
            'local' => $localAdapter,
        ]);

        return $filesystemAdapterRepo;
    }

    private static function createNamer()
    {
        $uniqueNamer = new UniqueNamer();
        $basenameNamer = new BasenameNamer();
        $namerRepo = new NamerRepo([
            'unique_id' => $uniqueNamer,
            'basename' => $basenameNamer,
        ]);

        return $namerRepo;
    }

    private static function createOrmListener(Uploader $handler)
    {
        $fileReferenceEntities = [
            UploadableEntity::class => UploadableEntity::class,
        ];

        return new ORMListener($handler, $fileReferenceEntities, self::getDoctrineEvents());
    }

    private static function getDoctrineEvents()
    {
        return [
            Events::prePersist,
            Events::postPersist,
            Events::preUpdate,
            Events::postUpdate,
            Events::postLoad,
            Events::postRemove,
            Events::postFlush,
        ];
    }

    private static function createOrmEmbeddableListener(Uploader $handler)
    {
        $fileReferenceProperties = [
            EntityHasEmbeddedFile::class => [
                'fileReference',
            ],
        ];

        return new ORMEmbeddableListener($handler, $fileReferenceProperties, self::getDoctrineEvents());
    }

    private static function getMappingsFromConfig()
    {
        $mappingsPath = __DIR__ . '/Resources/config/mappings.yml';

        if (!file_exists($mappingsPath)) {
            return [];
        }

        return Yaml::parse(file_get_contents($mappingsPath));
    }

    private static function getExtraMappings()
    {
        $path = getenv('EXTRA_MAPPINGS') ?: __DIR__ . '/../var/tmp/extra-mappings.yml';

        if (!$path || !file_exists($path)) {
            return [];
        }

        return Yaml::parse(file_get_contents($path));
    }

    private static function createMetadataRepo()
    {
        $fileReferenceClasses = [];
        $metadataMap = [];

        foreach (func_get_args() as $argument) {
            foreach ($argument as $fileReferenceClass => $mapping) {
                $defaults = self::getDefaultMapping();

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
                $metadata['fs_prefix'],
                $metadata['uri_prefix'],
                $metadata['fs_adapter'],
                $metadata['naming_strategy'],
                $metadata['delete_old_file'],
                $metadata['delete_on_remove'],
                $metadata['inject_uri_on_load'],
                $metadata['inject_file_info_on_load']
            );
        }

        return new MetadataRepo($fileReferenceClasses, $metadataMap);
    }

    private static function getDefaultMapping()
    {
        return [
            'file_setter' => 'file',
            'file_getter' => 'file',
            'uri_setter' => 'uri',
            'file_info_setter' => 'fileInfo',
            'fs_prefix' => __DIR__ . '/Resources/public/uploads',
            'uri_prefix' => '/uploads/%s',
            'fs_adapter' => 'local',
            'naming_strategy' => 'unique_id',
            'delete_old_file' => true,
            'delete_on_remove' => true,
            'inject_uri_on_load' => true,
            'inject_file_info_on_load' => true,
        ];
    }

    private static function registerCommands(AppContainer $container, Application $app)
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

                new DBAL\UploadCommand('dbal:upload', $container),
                new DBAL\GetCommand('dbal:get', $container),
                new DBAL\RemoveCommand('dbal:remove', $container),
                new DBAL\UpdateCommand('dbal:update', $container),
            ]
        );
    }
}
