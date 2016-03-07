<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\DependencyInjection;


use Atom\Uploader\Handler\UploadHandler;
use Atom\Uploader\LazyLoad\IFilesystemFactoryLazyLoader;
use Atom\Uploader\LazyLoad\IUploadHandlerLazyLoader;
use Atom\Uploader\Listener\ORM\ORMListener;
use Atom\Uploader\Listener\ORMEmbeddable\ORMEmbeddableListener;
use Atom\Uploader\Filesystem\FilesystemFactory;
use ExampleApp\Event\EventDispatcher;

class AppContainer implements IAppContainer, IUploadHandlerLazyLoader, IFilesystemFactoryLazyLoader
{
    private $filesystemFactory;

    private $handler;

    private $ormListener;

    private $ormEmbeddableListener;

    private $dispatcher;

    /**
     * @param FilesystemFactory $filesystemFactory
     */
    public function setFilesystemFactory(FilesystemFactory $filesystemFactory)
    {
        $this->filesystemFactory = $filesystemFactory;
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
     * @return FilesystemFactory
     */
    public function getFilesystemFactory()
    {
        return $this->filesystemFactory;
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