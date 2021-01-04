<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

class DropNodePing extends Migration
{
    public function up()
    {
        Schema::dropIfExists('node_ping');
        Permission::findByName('admin.node.pingLog')->delete();
    }

    public function down()
    {
        //
    }
}
