<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NodeCertificate;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;

class CertController extends Controller
{
    // 域名证书列表
    public function index(Request $request)
    {
        $DvList = NodeCertificate::orderBy('id')->paginate(15)->appends($request->except('page'));
        foreach ($DvList as $Dv) {
            if ($Dv->pem) {
                $DvInfo = openssl_x509_parse($Dv->pem);
                if ($DvInfo) {
                    $Dv->issuer = $DvInfo['issuer']['O'] ?? null;
                    $Dv->from = date('Y-m-d', $DvInfo['validFrom_time_t']) ?: null;
                    $Dv->to = date('Y-m-d', $DvInfo['validTo_time_t']) ?: null;
                }
            }
        }
        $view['list'] = $DvList;

        return view('admin.node.cert.index', $view);
    }

    public function create()
    {
        return view('admin.node.cert.info');
    }

    // 添加域名证书
    public function store(Request $request): JsonResponse
    {
        $cert = new NodeCertificate();
        $cert->domain = $request->input('domain');
        $cert->key = str_replace(["\r", "\n"], '', $request->input('key'));
        $cert->pem = str_replace(["\r", "\n"], '', $request->input('pem'));
        $cert->save();

        if ($cert->id) {
            return Response::json(['status' => 'success', 'message' => '生成成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '生成失败']);
    }

    // 编辑域名证书
    public function edit($id)
    {
        $view['Dv'] = NodeCertificate::find($id);

        return view('admin.node.cert.info', $view);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $Dv = NodeCertificate::findOrFail($id);
        if ($Dv->update(['domain' => $request->input('domain'), 'key' => $request->input('key'), 'pem' => $request->input('pem')])) {
            return Response::json(['status' => 'success', 'message' => '修改成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '修改失败']);
    }

    // 删除域名证书
    public function destroy($id): JsonResponse
    {
        try {
            if (NodeCertificate::whereId($id)->delete()) {
                return Response::json(['status' => 'success', 'message' => '操作成功']);
            }
        } catch (Exception $e) {
            Log::error('删除域名证书失败：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '删除域名证书失败：'.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '删除域名证书失败']);
    }
}
