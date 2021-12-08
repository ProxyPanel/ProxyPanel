<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class ChangeLogPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permssion = Permission::whereName('admin.log.viewer')->first();
        if ($permssion) {
            $permssion->name = 'log-viewer::dashboard,log-viewer::logs.*';
            $permssion->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permssion = Permission::whereName('log-viewer::dashboard,log-viewer::logs.*')->first();
        if ($permssion) {
            $permssion->name = 'admin.log.viewer';
            $permssion->save();
        }
    }
}
