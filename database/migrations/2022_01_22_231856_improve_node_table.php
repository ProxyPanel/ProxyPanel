<?php

use App\Components\MigrationToolBox;
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
            Config::insertOrIgnore(['name' => $config]);
        }

        // 插入新字段
        Schema::table('node', function (Blueprint $table) {
            if ((new MigrationToolBox())->versionCheck()) {
                $table->json('profile')->comment('节点设置选项')->after('description');
            } else {
                $table->text('profile')->comment('节点设置选项')->after('description');
            }
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
            $node->update(['profile' => $profile]);

            if ($node->relay_server && $node->relay_port) { // 创建 中转线路
                $relayNodeData = [
                    'type'           => 0,
                    'name'           => $node->name.'↔️',
                    'country_code'   => $node->country_code,
                    'port'           => $node->relay_port,
                    'level'          => $node->level,
                    'rule_group_id'  => $node->rule_group_id,
                    'speed_limit'    => $node->speed_limit,
                    'client_limit'   => $node->client_limit,
                    'description'    => $node->description,
                    'geo'            => $node->geo,
                    'traffic_rate'   => $node->traffic_rate,
                    'relay_node_id'  => $node->id,
                    'is_udp'         => $node->is_udp,
                    'push_port'      => $node->push_port,
                    'detection_type' => $node->detection_type,
                    'sort'           => $node->sort,
                    'status'         => $node->status,
                ];

                if (filter_var($node->relay_server, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $relayNodeData['ip'] = $node->relay_server;
                } elseif (filter_var($node->relay_server, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    $relayNodeData['ipv6'] = $node->relay_server;
                } else {
                    $relayNodeData['server'] = $node->relay_server;
                    $ip = gethostbyname($node->relay_server);
                    if ($ip) {
                        $relayNodeData['ip'] = $ip;
                    }
                }
                Node::create($relayNodeData);
            }
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

        Schema::table('node', function (Blueprint $table) { // 回滚老字段
            $table->string('relay_server')->nullable()->comment('中转地址');
            $table->unsignedSmallInteger('relay_port')->nullable()->comment('中转端口');
            $table->string('method', 32)->default('aes-256-cfb')->comment('加密方式');
            $table->string('protocol', 64)->default('origin')->comment('协议');
            $table->string('protocol_param', 128)->nullable()->comment('协议参数');
            $table->string('obfs', 64)->default('plain')->comment('混淆');
            $table->string('obfs_param')->nullable()->comment('混淆参数');
            $table->boolean('compatible')->default(0)->comment('兼容SS');
            $table->boolean('single')->default(0)->comment('启用单端口功能：0-否、1-是');
            $table->string('passwd')->nullable()->comment('单端口的连接密码');
            $table->unsignedSmallInteger('v2_alter_id')->default(16)->comment('V2Ray额外ID');
            $table->string('v2_method', 32)->default('aes-128-gcm')->comment('V2Ray加密方式');
            $table->string('v2_net', 16)->default('tcp')->comment('V2Ray传输协议');
            $table->string('v2_type', 32)->default('none')->comment('V2Ray伪装类型');
            $table->string('v2_host')->nullable()->comment('V2Ray伪装的域名');
            $table->string('v2_path')->nullable()->comment('V2Ray的WS/H2路径');
            $table->boolean('v2_tls')->default(0)->comment('V2Ray连接TLS：0-未开启、1-开启');
            $table->string('v2_sni', 191)->nullable()->comment('V2Ray的SNI配置');
            $table->text('tls_provider')->nullable()->comment('V2Ray节点的TLS提供商授权信息');
            $table->boolean('is_relay')->default(0)->comment('是否中转节点：0-否、1-是');
        });

        foreach (Node::all() as $node) {
            if ($node->relay_node_id) { // 回滚中转节点
                $pNode = Node::find($node->relay_node_id);
                $pNode->is_relay = 1;
                $pNode->relay_server = $node->server ?: $node->ip;
                $pNode->relay_port = $node->port;
                $pNode->save();
                try {
                    $node->delete();
                } catch (Exception $e) {
                    Log::emergency('中转删除失败，请手动在数据库中删除; '.$e->getMessage());
                }
                continue;
            }
            switch ($node->type) { // 回滚节点配置
                case 0:
                    $node->method = $node->profile['method'];
                    break;
                case 2:
                    $node->v2_method = $node->profile['method'];
                    $node->v2_alter_id = $node->profile['v2_alter_id'];
                    $node->v2_net = $node->profile['v2_net'];
                    $node->v2_type = $node->profile['v2_type'];
                    $node->v2_host = $node->profile['v2_host'];
                    $node->v2_path = $node->profile['v2_path'];
                    $node->v2_tls = $node->profile['v2_tls'] ? 1 : 0;
                    $node->v2_sni = $node->profile['v2_sni'];
                    break;
                case 1:
                case 4:
                    $node->method = $node->profile['method'];
                    $node->protocol = $node->profile['protocol'];
                    $node->obfs = $node->profile['obfs'];
                    $node->obfs_param = $node->profile['obfs_param'];
                    $node->protocol_param = $node->profile['protocol_param'];
                    $node->single = $node->profile['passwd'] ? 1 : 0;
                    $node->passwd = $node->profile['passwd'];
                    break;
                default:
            }
            $node->save();
        }

        // 回滚新字段
        Schema::table('node', function (Blueprint $table) {
            $table->dropColumn('profile', 'relay_node_id');
        });
    }
}
