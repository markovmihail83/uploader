<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>.
 */
$root = realpath(__DIR__.'/../../');

copy($root.'/devops/scripts/hooks/pre-commit', $root.'/.git/hooks/pre-commit');
