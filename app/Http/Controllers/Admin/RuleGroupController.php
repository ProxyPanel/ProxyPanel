<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RuleGroupRequest;
use App\Models\Rule;
use App\Models\RuleGroup;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Log;

class RuleGroupController extends Controller
{
    public function index(): View
    {
        return view('admin.rule.group.index', ['ruleGroups' => RuleGroup::paginate(15)->appends(request('page'))]);
    }

    public function store(RuleGroupRequest $request): RedirectResponse
    {
        if ($group = RuleGroup::create($request->only('name', 'type'))) {
            $group->rules()->attach($request->input('rules'));

            return redirect(route('admin.rule.group.edit', $group))->with('successMsg', trans('common.success_item', ['attribute' => trans('common.add')]));
        }

        return redirect()->back()->withInput()->withErrors(trans('common.failed_item', ['attribute' => trans('common.add')]));
    }

    public function create(): View
    {
        return view('admin.rule.group.info', ['rules' => Rule::all()]);
    }

    public function edit(RuleGroup $group): View
    {
        return view('admin.rule.group.info', [
            'ruleGroup' => $group,
            'rules' => Rule::all(),
        ]);
    }

    public function update(RuleGroupRequest $request, RuleGroup $group): RedirectResponse
    {
        if ($group->update($request->only(['name', 'type']))) {
            $group->rules()->sync($request->input('rules'));

            return redirect()->back()->with('successMsg', trans('common.success_item', ['attribute' => trans('common.edit')]));
        }

        return redirect()->back()->withInput()->withErrors(trans('common.failed_item', ['attribute' => trans('common.edit')]));
    }

    public function destroy(RuleGroup $group): JsonResponse
    {
        try {
            if ($group->delete()) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('model.rule_group.attribute')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')])]);
    }
}
