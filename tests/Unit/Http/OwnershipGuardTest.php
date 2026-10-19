<?php

namespace Tests\Unit\Http;

use App\Enums\ProxyType;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\User\InvoiceController;
use App\Http\Controllers\User\NodeController;
use App\Http\Controllers\User\TicketController;
use App\Models\Node;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

/**
 * 归属判据：别人的工单、订单、支付单、节点都读不到也改不动，本人的可以，超管在用户区同样不给后门。
 */
class OwnershipGuardTest extends TestCase
{
    use DatabaseTransactions;

    private User $owner;

    private User $intruder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = $this->user('owner@example.com');
        $this->intruder = $this->user('intruder@example.com');
    }

    public function test_foreign_ticket_cannot_be_opened_replied_or_closed(): void
    {
        $ticket = $this->ticket($this->owner);
        $this->actingAs($this->intruder);
        $controller = new TicketController;

        // 详情页直接 404，不给存在性线索
        $this->expectException(NotFoundHttpException::class);
        $controller->edit($ticket);
    }

    public function test_replying_to_a_foreign_ticket_is_refused_before_validation(): void
    {
        $ticket = $this->ticket($this->owner);
        $this->actingAs($this->intruder);

        $response = (new TicketController)->reply(Request::create('/user/ticket/reply', 'POST', ['content' => 'hi']), $ticket);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame('fail', $response->getData(true)['status']);
        $this->assertSame(0, $ticket->reply()->count());
    }

    public function test_closing_a_foreign_ticket_is_refused(): void
    {
        $ticket = $this->ticket($this->owner);
        $this->actingAs($this->intruder);

        $response = (new TicketController)->close($ticket);

        $this->assertSame('fail', $response->getData(true)['status']);
        $this->assertNotSame(3, (int) $ticket->fresh()->status, '别人的工单不该被关掉');
    }

    public function test_own_ticket_can_be_closed(): void
    {
        $ticket = $this->ticket($this->owner);
        $this->actingAs($this->owner);

        $response = (new TicketController)->close($ticket);

        $this->assertSame('success', $response->getData(true)['status']);
    }

    public function test_order_detail_is_scoped_to_the_logged_in_user(): void
    {
        $order = $this->order($this->owner);
        $this->actingAs($this->intruder);

        $this->expectException(ModelNotFoundException::class);
        (new InvoiceController)->show($order->sn);
    }

    public function test_own_order_detail_is_readable(): void
    {
        $order = $this->order($this->owner);
        $this->actingAs($this->owner);

        $view = (new InvoiceController)->show($order->sn);

        $this->assertSame($order->id, $view->getData()['order']->id);
    }

    public function test_foreign_order_cannot_be_closed(): void
    {
        $order = $this->order($this->owner);
        $this->actingAs($this->intruder);

        $response = (new PaymentController)->close($order);

        $this->assertSame('fail', $response->getData(true)['status']);
        $this->assertSame(0, (int) $order->fresh()->status, '越权关闭不能改动订单状态');
    }

    public function test_foreign_payment_detail_is_not_readable(): void
    {
        $payment = $this->payment($this->owner, $this->order($this->owner));
        $this->actingAs($this->intruder);

        $this->expectException(ModelNotFoundException::class);
        (new PaymentController)->detail($payment->trade_no);
    }

    public function test_own_payment_detail_is_readable(): void
    {
        $payment = $this->payment($this->owner, $this->order($this->owner));
        $this->actingAs($this->owner);

        $view = (new PaymentController)->detail($payment->trade_no);

        $this->assertSame($payment->id, $view->getData()['payment']->id);
    }

    public function test_polling_a_foreign_payment_reports_unknown_order(): void
    {
        $payment = $this->payment($this->owner, $this->order($this->owner));
        $this->actingAs($this->intruder);

        $response = PaymentController::getStatus(Request::create('/user/payment/status', 'POST', ['trade_no' => $payment->trade_no]));

        $this->assertSame('error', $response->getData(true)['status'], '别人的支付单要当成不存在');
    }

    public function test_polling_own_pending_payment_reaches_the_pending_branch(): void
    {
        $payment = $this->payment($this->owner, $this->order($this->owner));
        $this->actingAs($this->owner);

        $response = PaymentController::getStatus(Request::create('/user/payment/status', 'POST', ['trade_no' => $payment->trade_no]));

        $this->assertSame('fail', $response->getData(true)['status']);
        $this->assertSame(trans('common.status.payment_pending'), $response->getData(true)['message']);
    }

    public function test_node_detail_follows_the_users_visible_nodes(): void
    {
        $visible = $this->node(ProxyType::SHADOWSOCKSR->value, 0);
        $tooHigh = $this->node(ProxyType::SHADOWSOCKSR->value, 1);

        $this->actingAs($this->owner);

        $this->assertTrue(Gate::allows('view', $visible));
        $this->assertFalse(Gate::allows('view', $tooHigh), '等级不够的节点不能取详情');

        $response = (new NodeController)->show(Request::create('/user/node/'.$tooHigh->id, 'GET'), $tooHigh);
        $this->assertSame('fail', $response->getData(true)['status']);
    }

    public function test_super_admin_gets_no_backdoor_in_the_user_area(): void
    {
        $admin = $this->user('admin@example.com');
        $admin->assignRole(Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web'])->name);
        $ticket = $this->ticket($this->owner);

        $this->actingAs($admin);

        $response = (new TicketController)->close($ticket);

        $this->assertSame('fail', $response->getData(true)['status'], '带模型的归属判定不受超管短路放行');
        // 后台按路由名判权限走的仍是短路，否则超管面板会整片失效
        $this->assertTrue(Gate::allows('admin.index'));
    }

    private function user(string $username): User
    {
        return User::create(['username' => $username, 'passwd' => bcrypt('secret-123'), 'nickname' => 't']);
    }

    private function ticket(User $user): Ticket
    {
        // 带上 admin_id：TicketObserver 在没管理员时会给 user id 1 发通知，测试里不该依赖那颗种子数据
        return Ticket::create(['user_id' => $user->id, 'admin_id' => $user->id, 'title' => 't', 'content' => 'c', 'status' => 0]);
    }

    private function order(User $user): Order
    {
        return Order::create(['sn' => 'OWN'.strtoupper(substr(md5($user->username), 0, 10)), 'user_id' => $user->id, 'amount' => 1.5, 'status' => 0]);
    }

    private function payment(User $user, Order $order): Payment
    {
        return Payment::create(['trade_no' => 'PAY'.strtoupper(substr(md5($order->sn), 0, 12)), 'user_id' => $user->id, 'order_id' => $order->id, 'amount' => 150]);
    }

    private function node(int $type, int $level): Node
    {
        // 建节点会触发广播与 VNET/Hysteria2 任务，这里只要一行可查的节点数据
        return Node::withoutEvents(fn () => Node::create(['name' => 'n-'.$type.'-'.$level, 'type' => $type, 'level' => $level, 'profile' => []]));
    }
}
