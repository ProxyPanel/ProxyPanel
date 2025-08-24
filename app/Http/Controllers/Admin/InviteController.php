<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invite;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Log;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Str;

class InviteController extends Controller
{
    public function index(): View
    { // 邀请码列表
        return view('admin.aff.invite', ['inviteList' => Invite::with(['invitee:id,username', 'inviter:id,username'])->orderBy('status')->orderByDesc('id')->paginate(15)->appends(request('page'))]);
    }

    public function generate(): JsonResponse
    { // 生成邀请码
        $invites = [];
        $expirationDate = date('Y-m-d H:i:s', strtotime(sysConfig('admin_invite_days').' days'));

        for ($i = 0; $i < 10; $i++) {
            $invites[] = [
                'code' => strtoupper(substr(md5(microtime().Str::random(6)), 8, 12)),
                'dateline' => $expirationDate,
            ];
        }

        Invite::insert($invites);

        return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.generate')])]);
    }

    public function export(): void
    { // 导出邀请码
        $inviteList = Invite::whereStatus(0)->select(['code', 'dateline'])->get();

        $filename = trans('user.invite.attribute').'_'.date('Ymd').'.xlsx';

        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator('ProxyPanel')
            ->setLastModifiedBy('ProxyPanel')
            ->setTitle(trans('user.invite.attribute'))
            ->setSubject(trans('user.invite.attribute'));

        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(trans('user.invite.attribute'));
        $sheet->fromArray([trans('user.invite.attribute'), trans('common.available_date')]);

        foreach ($inviteList as $k => $vo) {
            $sheet->fromArray([$vo->code, $vo->dateline], null, 'A'.($k + 2));
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        try {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.export'), 'attribute' => trans('user.invite.attribute')]).': '.$e->getMessage());
        }
    }
}
