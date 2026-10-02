<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ActionResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CertRequest;
use App\Models\NodeCertificate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CertController extends Controller
{
    use ActionResponse;

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
        return $this->actionResponse('common.delete', 'model.node_cert.attribute', fn () => $cert->delete());
    }
}
