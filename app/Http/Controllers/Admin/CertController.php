<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CertRequest;
use App\Models\NodeCertificate;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Log;

class CertController extends Controller
{
    public function index(): View
    {
        return view('admin.node.cert.index', ['certs' => NodeCertificate::orderBy('id')->paginate()->appends(request('page'))]);
    }

    public function store(CertRequest $request): RedirectResponse
    {
        if ($cert = NodeCertificate::create($request->validated())) {
            return redirect(route('admin.node.cert.edit', $cert))->with('successMsg', trans('common.success_item', ['attribute' => trans('common.add')]));
        }

        return redirect()->back()->withInput()->withErrors(trans('common.failed_item', ['attribute' => trans('common.add')]));
    }

    public function create(): View
    {
        return view('admin.node.cert.info');
    }

    public function edit(NodeCertificate $cert): View
    {
        return view('admin.node.cert.info', compact('cert'));
    }

    public function update(CertRequest $request, NodeCertificate $cert): RedirectResponse
    {
        if ($cert->update($request->validated())) {
            return redirect()->back()->with('successMsg', trans('common.success_item', ['attribute' => trans('common.edit')]));
        }

        return redirect()->back()->withInput()->withErrors(trans('common.failed_item', ['attribute' => trans('common.edit')]));
    }

    public function destroy(NodeCertificate $cert): JsonResponse
    {
        try {
            if ($cert->delete()) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('model.node_cert.attribute')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')])]);
    }
}
