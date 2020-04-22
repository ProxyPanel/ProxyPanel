<?php

namespace App\Mail;

use App\Http\Models\NotificationLog;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class nodeCrashWarning extends Mailable implements ShouldQueue
{
	use Queueable, SerializesModels;

	protected $id; // 邮件记录ID

	public function __construct($id)
	{
		$this->id = $id;

	}

	public function build()
	{
		$view['content'] = NotificationLog::query()->whereId($this->id)->first()->content;

		return $this->view('emails.nodeCrashWarning', $view)->subject('节点阻断警告');
	}

	// 发件失败处理
	public function failed(Exception $e)
	{
		NotificationLog::query()->whereId($this->id)->update(['status' => -1, 'error' => $e->getMessage()]);
	}
}
