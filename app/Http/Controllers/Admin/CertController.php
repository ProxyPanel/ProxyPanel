<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CertRequest;
use App\Models\NodeCertificate;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Log;

class CertController extends Controller
{
    public function index()
    {
        $certs = NodeCertificate::orderBy('id')->paginate()->appends(request('page'));
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

    public function store(CertRequest $request)
    {
        if ($cert = NodeCertificate::create($request->validated())) {
            return redirect(route('admin.node.cert.edit', $cert))->with('successMsg', trans('common.generate_item', ['attribute' => trans('common.success')]));
        }

        return redirect()->back()->withInput()->withErrors('生成失败');
    }

    public function create()
    {
        return view('admin.node.cert.info');
    }

    public function edit(NodeCertificate $cert)
    {
        return view('admin.node.cert.info', compact('cert'));
    }

    public function update(CertRequest $request, NodeCertificate $cert): RedirectResponse
    {
        if ($cert->update($request->validated())) {
            return redirect()->back()->with('successMsg', trans('common.update_action', ['action' => trans('common.success')]));
        }

        return redirect()->back()->withInput()->withErrors(trans('common.update_action', ['action' => trans('common.failed')]));
    }

    public function destroy(NodeCertificate $cert): JsonResponse
    {
        try {
            if ($cert->delete()) {
                return response()->json(['status' => 'success', 'message' => '删除成功']);
            }
        } catch (Exception $e) {
            Log::error('删除域名证书失败：'.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => '删除错误：'.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => '删除失败']);
    }
}
