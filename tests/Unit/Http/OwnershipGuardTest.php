<?php

namespace Tests\Unit\Http;

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\User\InvoiceController;
use App\Http\Controllers\User\TicketController;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

/**
 * 归属判据：别人的工单、订单读不到也改不动，本人的可以。
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
}
