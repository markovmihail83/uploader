<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>.
 */

namespace ExampleApp\Command\ORMEmbeddable;

use ExampleApp\Command\Base\ORMEmbeddableCommand;

class RemoveCommand extends ORMEmbeddableCommand
{
    protected function doConfigure()
    {
        $this->addIdArgument();
    }

    protected function doExecute()
    {
        $this->em->remove($this->getEntity());
        $this->view();
    }
}
