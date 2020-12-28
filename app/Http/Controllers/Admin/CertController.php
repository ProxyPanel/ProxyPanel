<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CertRequest;
use App\Models\NodeCertificate;
use Exception;
use Log;

class CertController extends Controller
{
    // 域名证书列表
    public function index()
    {
        $certs = NodeCertificate::orderBy('id')->paginate(15)->appends(request('page'));
        foreach ($certs as $cert) {
            if ($cert->pem) {
                $certInfo = openssl_x509_parse($cert->pem);
                if ($certInfo) {
                    $cert->issuer = $certInfo['issuer']['O'] ?? null;
                    $cert->from = date('Y-m-d', $certInfo['validFrom_time_t']) ?: null;
                    $cert->to = date('Y-m-d', $certInfo['validTo_time_t']) ?: null;
                }
            }
        }

        return view('admin.node.cert.index', ['certs' => $certs]);
    }

    public function create()
    {
        return view('admin.node.cert.info');
    }

    // 添加域名证书
    public function store(CertRequest $request)
    {
        if ($cert = NodeCertificate::create($request->validated())) {
            return redirect(route('admin.node.cert.update', $cert))->with('successMsg', '生成成功');
        }

        return redirect()->back()->withInput()->withErrors('生成失败');
    }

    // 编辑域名证书
    public function edit(NodeCertificate $cert)
    {
        return view('admin.node.cert.info', compact('cert'));
    }

    public function update(CertRequest $request, NodeCertificate $cert)
    {
        if ($cert->update($request->validated())) {
            return redirect()->back()->with('successMsg', '修改成功');
        }

        return redirect()->back()->withInput()->withErrors('修改失败');
    }

    // 删除域名证书
    public function destroy(NodeCertificate $cert)
    {
        try {
            if ($cert->delete()) {
                return response()->json(['status' => 'success', 'message' => '操作成功']);
            }
        } catch (Exception $e) {
            Log::error('删除域名证书失败：'.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => '删除域名证书错误：'.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => '删除域名证书失败']);
    }
}
