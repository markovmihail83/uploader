<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Command\Base;


use Atom\Uploader\Handler\Uploader;
use Doctrine\DBAL\Connection;

abstract class DBALCommand extends Command
{
    /**
     * @var Connection
     */
    protected $conn;

    /**
     * @var Uploader
     */
    protected $uploader;

    final protected function registerDriver()
    {
        $this->conn = require __DIR__ . '/../../Resources/config/dbal/bootstrap.php';
        $this->uploader = $this->container->getUploader();
    }

    protected function view($id = null, array $fileReference = null)
    {
        if (isset($fileReference['fileInfo']) && $fileReference['fileInfo'] instanceof \SplFileInfo) {
            $fileReference['fileInfo'] = $fileReference['fileInfo']->getPathname();
        }

        parent::view($id, $fileReference);
    }

    protected function getUploadable()
    {
        $statement = $this->conn->prepare('SELECT id, file FROM uploadable t WHERE t.id = :id');
        $statement->bindValue('id', $this->getId());
        $statement->execute();

        $uploadable = $statement->fetch();
        $uploadable['uri'] = null;
        $uploadable['fileInfo'] = null;

        return $uploadable;
    }
}