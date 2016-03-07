<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\DependencyInjection;


use Atom\Uploader\Handler\UploadHandler;
use Atom\Uploader\LazyLoad\IStorageFactoryLazyLoader;
use Atom\Uploader\LazyLoad\IUploadHandlerLazyLoader;
use Atom\Uploader\Listener\ORM\ORMListener;
use Atom\Uploader\Listener\ORMEmbeddable\ORMEmbeddableListener;
use Atom\Uploader\Storage\StorageFactory;
use ExampleApp\Event\EventDispatcher;

class AppContainer implements IAppContainer, IUploadHandlerLazyLoader, IStorageFactoryLazyLoader
{
    private $storageFactory;

    private $handler;

    private $ormListener;

    private $ormEmbeddableListener;

    private $dispatcher;

    /**
     * @param StorageFactory $storageFactory
     */
    public function setStorageFactory(StorageFactory $storageFactory)
    {
        $this->storageFactory = $storageFactory;
    }

    /**
     * @return ORMListener
     */
    public function getOrmListener()
    {
        return $this->ormListener;
    }

    /**
     * @param ORMListener $ormListener
     */
    public function setOrmListener($ormListener)
    {
        $this->ormListener = $ormListener;
    }

    /**
     * @return ORMEmbeddableListener
     */
    public function getOrmEmbeddableListener()
    {
        return $this->ormEmbeddableListener;
    }

    /**
     * @param ORMEmbeddableListener $ormEmbeddableListener
     */
    public function setOrmEmbeddableListener($ormEmbeddableListener)
    {
        $this->ormEmbeddableListener = $ormEmbeddableListener;
    }

    /**
     * @param UploadHandler $handler
     */
    public function setUploadHandler($handler)
    {
        $this->handler = $handler;
    }

    /**
     * @return UploadHandler
     */
    public function getUploadHandler()
    {
        return $this->handler;
    }

    /**
     * @return StorageFactory
     */
    public function getStorageFactory()
    {
        return $this->storageFactory;
    }

    /**
     * @return EventDispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @param EventDispatcher $dispatcher
     */
    public function setDispatcher(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
}