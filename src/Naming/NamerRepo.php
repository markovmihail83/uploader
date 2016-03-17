<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Naming;

use Atom\Uploader\Exception\NoSuchNamingException;

class NamerRepo
{
    private $namingMap;

    /**
     * @param INamer[] $namingMap
     */
    public function __construct(array $namingMap)
    {
        $this->namingMap = $namingMap;
    }

    /**
     * @param $strategy string
     *
     * @throws NoSuchNamingException
     *
     * @return INamer
     */
    public function getNamer($strategy)
    {
        if (!isset($this->namingMap[$strategy])) {
            throw new NoSuchNamingException(sprintf('The naming strategy "%s" does not exist', $strategy));
        }

        return $this->namingMap[$strategy];
    }
}
