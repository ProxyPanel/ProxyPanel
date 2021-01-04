<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

class DropNodePing extends Migration
{
    public function up()
    {
        Schema::dropIfExists('node_ping');
        $permission = Permission::where('name', 'admin.node.pingLog')->first();
        if ($permission) {
            $permission->delete();
        }
    }

    public function down()
    {
        //
    }
}
