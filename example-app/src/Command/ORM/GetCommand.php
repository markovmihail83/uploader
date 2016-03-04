<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Command\ORM;


use ExampleApp\Command\Base\ORMCommand;

class GetCommand extends ORMCommand
{
    protected function doConfigure()
    {
        $this->addIdArgument();
    }

    protected function doExecute()
    {
        $this->view($this->getId(), $this->getEntity()->toArray());
    }
}