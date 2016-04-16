<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Command\DBAL;


use ExampleApp\Command\Base\DBALCommand;

class RemoveCommand extends DBALCommand
{

    protected function doConfigure()
    {
        $this->addIdArgument();
    }

    protected function doExecute()
    {
        $uploadable = $this->getUploadable();
        $this->conn->delete('uploadable', ['id' => $this->getId()]);

        $this->uploader->removed($uploadable, 'dbal_uploadable');

        $this->view();
    }
}