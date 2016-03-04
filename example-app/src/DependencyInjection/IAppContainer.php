<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\DependencyInjection;


use Atom\Uploader\DependencyInjection\IContainer;
use Atom\Uploader\Listener\ORM\ORMListener;
use Atom\Uploader\Listener\ORMEmbeddable\ORMEmbeddableListener;
use ExampleApp\Event\EventDispatcher;

interface IAppContainer extends IContainer
{
    /**
     * @return ORMListener
     */
    public function getOrmListener();

    /**
     * @return ORMEmbeddableListener
     */
    public function getOrmEmbeddableListener();

    /**
     * @return EventDispatcher
     */
    public function getDispatcher();
}