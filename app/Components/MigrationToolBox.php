<?php

namespace App\Components;

use DB;

class MigrationToolBox
{
    public function versionCheck(): bool
    {
        $dbVersion = DB::select('select version()')[0]->{'version()'};
        $dbType = strpos($dbVersion, 'Maria');
        $dbVersion = mb_substr($dbVersion, 0, 6);

        return ($dbType !== false && version_compare($dbVersion, '10.2.7', '>=')) || ($dbType === false && version_compare($dbVersion, '5.7.8', '>='));
    }
}
