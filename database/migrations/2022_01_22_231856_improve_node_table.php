<?php

use App\Models\Config;
use App\Models\Node;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ImproveNodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    protected $configs = [
        'stripe_currency',
    ];

    public function up()
    {
        foreach ($this->configs as $config) {
            Config::insert(['name' => $config]);
        }

        // 插入新字段
        Schema::table('node', function (Blueprint $table) {
            $table->json('profile')->comment('节点设置选项')->after('description');
            $table->unsignedInteger('relay_node_id')->nullable()->comment('中转节点对接母节点, 默认NULL')->after('is_relay');
        });

        foreach (Node::all() as $node) {
            $profile = null;
            switch ($node->type) {
                case 0:
                    $profile = [
                        'method' => $node->method,
                    ];
                    break;
                case 2:
                    $profile = [
                        'method'      => $node->v2_method,
                        'v2_alter_id' => $node->v2_alter_id,
                        'v2_net'      => $node->v2_net,
                        'v2_type'     => $node->v2_type,
                        'v2_host'     => $node->v2_host,
                        'v2_path'     => $node->v2_path,
                        'v2_tls'      => $node->v2_tls ? 'tls' : '',
                        'v2_sni'      => $node->v2_sni,
                    ];
                    break;
                case 3:
                    $profile = [
                        'allow_insecure' => false,
                    ];
                    break;
                case 1:
                case 4:
                    $profile = [
                        'method'         => $node->method,
                        'protocol'       => $node->protocol,
                        'obfs'           => $node->obfs,
                        'obfs_param'     => $node->obfs_param,
                        'protocol_param' => $node->protocol_param,
                        'passwd'         => $node->passwd,
                    ];
                    break;
                default:
            }
            Node::whereId($node->id)->update(['profile' => $profile]);
        }

        // 销毁老字段
        Schema::table('node', function (Blueprint $table) {
            $table->dropColumn('relay_server', 'relay_port', 'method', 'protocol', 'protocol_param', 'obfs', 'obfs_param', 'compatible', 'single', 'passwd', 'v2_alter_id',
                'v2_method', 'v2_net', 'v2_type', 'v2_host', 'v2_path', 'v2_tls', 'v2_sni', 'tls_provider', 'is_relay');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Config::destroy($this->configs);
        // 太复杂了，无法逆转了
    }
}
