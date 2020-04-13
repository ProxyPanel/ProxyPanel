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
		return $this->view('emails.nodeCrashWarning')->subject('节点离线警告');
	}

	// 发件失败处理
	public function failed(Exception $e)
	{
		NotificationLog::query()->where('id', $this->id)->update(['status' => -1, 'error' => $e->getMessage()]);
	}
}
