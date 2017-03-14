<?php
/**
 * Copyright © 2017 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Command\ODM;

use ExampleApp\Command\Base\ODMCommand;

class GetCommand extends ODMCommand
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
