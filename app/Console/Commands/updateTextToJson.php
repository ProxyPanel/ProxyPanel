<?php

namespace App\Console\Commands;

use App\Models\ReferralApply;
use App\Models\RuleGroup;
use App\Models\UserGroup;
use Illuminate\Console\Command;
use Log;

class updateTextToJson extends Command {
	protected $signature = 'updateTextToJson';
	protected $description = '转换原有数列至新数列';

	public function handle(): void {
		Log::info('----------------------------【数据转换】开始----------------------------');
		foreach(ReferralApply::all() as $referralApply){
			$referralApply->link_logs = $this->convertToJson($referralApply->getRawOriginal ('link_logs'));
			$referralApply->save();
		}
		Log::info('转换返利表完成');
		foreach(UserGroup::all() as $userGroup){
			$userGroup->nodes = $this->convertToJson($userGroup->getRawOriginal ('nodes'));
			$userGroup->save();
		}
		Log::info('转换用户分组表完成');
		foreach(RuleGroup::all() as $ruleGroup){
			$ruleGroup->rules = $this->convertToJson($ruleGroup->getRawOriginal ('rules'));
			$ruleGroup->nodes = $this->convertToJson($ruleGroup->getRawOriginal ('nodes'));
			$ruleGroup->save();
		}
		Log::info('转换审核规则表完成');
		Log::info('----------------------------【数据转换】结束----------------------------');
	}

	private function convertToJson($string): array {
		return explode(',', $string);
	}
}
