<?php

namespace Tests\Unit\Http;

use App\Enums\ProxyType;
use App\Models\Node;
use App\Models\NodeAuth;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * webApi 中间件的鉴权判据：key 头必带且等于 node_auth.key，timestamp 与当前时间差必须小于 300 秒，
 * 通过后才进控制器；节点侧端点整组挂在 web_api_url/website_url 的域名下，Host 头必须匹配。
 */
class WebApiAuthTest extends TestCase
{
    use DatabaseTransactions;

    private Node $node;

    private string $key = 'abcdef0123456789';

    protected function setUp(): void
    {
        parent::setUp();

        $this->node = $this->makeNode();
        NodeAuth::create(['node_id' => $this->node->id, 'key' => $this->key, 'secret' => 'sec12345']);
    }

    public function test_missing_key_header_is_rejected(): void
    {
        $this->getJson($this->endpoint(), $this->nodeHeaders())
            ->assertStatus(400)
            ->assertJson(['status' => 'fail', 'code' => 400, 'message' => 'Your key is null!']);
    }

    public function test_mismatched_key_is_rejected(): void
    {
        $this->getJson($this->endpoint(), $this->nodeHeaders(['key' => 'ffffffffffffffff', 'timestamp' => (string) time()]))
            ->assertStatus(400)
            ->assertJson(['status' => 'fail', 'code' => 400, 'message' => '参数错误']);
    }

    public function test_node_without_auth_row_is_rejected(): void
    {
        $bare = $this->makeNode();

        $this->getJson('/api/ssr/v1/userList/'.$bare->id, $this->nodeHeaders(['key' => $this->key, 'timestamp' => (string) time()]))
            ->assertStatus(400)
            ->assertJson(['status' => 'fail', 'code' => 400, 'message' => '参数错误']);
    }

    /** 时差判据是 abs()：慢 400 秒和快 400 秒一样拒 */
    public function test_skewed_timestamp_is_rejected(): void
    {
        foreach ([time() - 400, time() + 400] as $skewed) {
            $this->getJson($this->endpoint(), $this->nodeHeaders(['key' => $this->key, 'timestamp' => (string) $skewed]))
                ->assertStatus(401)
                ->assertJson(['status' => 'fail', 'code' => 401, 'message' => '通信双方时钟不同步，或通信超时']);
        }
    }

    public function test_valid_key_and_timestamp_reach_the_controller(): void
    {
        $this->getJson($this->endpoint(), $this->nodeHeaders(['key' => $this->key, 'timestamp' => (string) time()]))
            ->assertOk()
            ->assertJson(['status' => 'success', 'code' => 200])
            ->assertJsonStructure(['status', 'code', 'data', 'message', 'updateTime']);
    }

    private function makeNode(): Node
    {
        return Node::withoutEvents(fn () => Node::create([
            'name' => 'webapi-'.uniqid(),
            'type' => ProxyType::SHADOWSOCKSR->value,
            'profile' => [],
        ]));
    }

    private function endpoint(): string
    {
        return '/api/ssr/v1/userList/'.$this->node->id;
    }

    private function nodeHeaders(array $extra = []): array
    {
        $host = parse_url(sysConfig('web_api_url') ?: sysConfig('website_url'), PHP_URL_HOST) ?: 'localhost';

        return array_merge(['Host' => $host], $extra);
    }
}
