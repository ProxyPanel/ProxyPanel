<?php

namespace Tests\Unit\Http;

use App\Enums\ProxyType;
use App\Models\Node;
use App\Models\NodeAuth;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * 节点 userList 下发的凭据与限速单位：条目带全代理凭据，speed_limit 必须是字节/秒
 * （data_rate cast 里的 12.5 Mbps 对应 1562500 字节/秒），且只包含该节点可见的启用账号。
 */
class NodeUserListPayloadTest extends TestCase
{
    use DatabaseTransactions;

    public function test_ssr_user_list_carries_credentials_and_byte_speed_limit(): void
    {
        $node = $this->node(ProxyType::SHADOWSOCKSR, ['obfs_param' => 'obfs.example']);
        $user = $this->user(['speed_limit' => 12.5]);
        $disabled = $this->user(['enable' => 0]);

        $entries = collect($this->userList($node, 'ssr/v1')->json('data'))->keyBy('uid');

        $this->assertFalse($entries->has($disabled->id), 'enable=0 的账号不该出现在下发列表里');

        // 节点把每行严格 json.Unmarshal 进 UserInfo（int / uint64），下发字符串等于整批被拒，
        // 所以这几条按原样判类型，不做 (int) 折叠
        $entry = $entries->get($user->id);
        $this->assertNotNull($entry);
        $this->assertSame((int) $user->port, $entry['port']);
        $this->assertSame('ProxyPass!1', $entry['passwd']);
        $this->assertSame('aes-256-cfb', $entry['method']);
        $this->assertSame('origin', $entry['protocol']);
        $this->assertSame('plain', $entry['obfs']);
        $this->assertSame('obfs.example', $entry['obfs_param']);
        $this->assertSame(1562500, $entry['speed_limit'], '下发给节点的限速必须是整数字节/秒');
        $this->assertSame(1, $entry['enable']);

        $this->assertSame(12.5, $user->fresh()->speed_limit, 'cast 读回来的才是 Mbps 数值');
    }

    public function test_ss_user_list_speed_limit_is_bytes(): void
    {
        $node = $this->node(ProxyType::SHADOWSOCKS);
        $user = $this->user(['speed_limit' => 12.5]);

        $entry = collect($this->userList($node, 'ss/v1')->json('data'))->firstWhere('uid', $user->id);

        $this->assertNotNull($entry);
        $this->assertSame(['uid', 'port', 'passwd', 'speed_limit', 'enable'], array_keys($entry));
        $this->assertSame('ProxyPass!1', $entry['passwd']);
        $this->assertSame(1562500, $entry['speed_limit']);
    }

    /**
     * 四个协议的条目值都必须来自 Node::USER_COLUMNS 选中的列：少选一列不会报错，
     * 只会把 null 发给节点，所以这里逐端点断言键名形状与「每个值非 null」。
     */
    public function test_every_user_list_entry_is_backed_by_a_selected_column(): void
    {
        $node = $this->node(ProxyType::SHADOWSOCKSR, ['obfs_param' => 'obfs.example']);
        $user = $this->user([
            'speed_limit' => 12.5,
            'method' => 'aes-256-cfb',
            'protocol' => 'origin',
            'obfs' => 'plain',
            'vmess_id' => 'b0e3a1f2-7c6d-4e5f-9a0b-1c2d3e4f5a6b',
        ]);

        $expected = [
            'ss/v1' => ['uid', 'port', 'passwd', 'speed_limit', 'enable'],
            'ssr/v1' => ['uid', 'port', 'passwd', 'method', 'protocol', 'obfs', 'obfs_param', 'speed_limit', 'enable'],
            'v2ray/v1' => ['uid', 'vmess_uid', 'speed_limit'],
            'trojan/v1' => ['uid', 'password', 'speed_limit'],
        ];

        foreach ($expected as $prefix => $keys) {
            $entry = collect($this->userList($node, $prefix)->json('data'))->firstWhere('uid', $user->id);

            $this->assertNotNull($entry, $prefix.' 没下发该账号');
            $this->assertSame($keys, array_keys($entry), $prefix.' 的条目形状变了');

            foreach ($entry as $field => $value) {
                $this->assertNotNull($value, $prefix.' 的 '.$field.' 是 null，多半是 Node::USER_COLUMNS 少选了这列');
            }
        }
    }

    /** 带合法 key/timestamp 打端点并断言成功信封 */
    private function userList(Node $node, string $prefix): TestResponse
    {
        $auth = NodeAuth::firstOrCreate(['node_id' => $node->id], ['key' => 'abcdef0123456789', 'secret' => 'sec12345']);
        $host = parse_url(sysConfig('web_api_url') ?: sysConfig('website_url'), PHP_URL_HOST) ?: 'localhost';

        return $this->getJson('/api/'.$prefix.'/userList/'.$node->id, ['Host' => $host, 'key' => $auth->key, 'timestamp' => (string) time()])
            ->assertOk()
            ->assertJson(['status' => 'success']);
    }

    private function node(ProxyType $type, array $profile = []): Node
    {
        return Node::withoutEvents(fn () => Node::create([
            'name' => 'payload-'.uniqid(),
            'type' => $type->value,
            'profile' => $profile,
        ]));
    }

    private function user(array $attributes = []): User
    {
        return User::create(array_merge([
            'username' => 'payload-'.uniqid().'@example.com',
            'passwd' => 'ProxyPass!1',
            'port' => 30000 + random_int(1, 9999),
        ], $attributes));
    }
}
