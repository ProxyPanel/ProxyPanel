<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ActionResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RuleGroupRequest;
use App\Models\Rule;
use App\Models\RuleGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class RuleGroupController extends Controller
{
    use ActionResponse;

    public function index(): View
    {
        return view('admin.rule.group.index', ['ruleGroups' => RuleGroup::paginate(15)->appends(request('page'))]);
    }

    public function store(RuleGroupRequest $request): RedirectResponse
    {
        return $this->redirectAction('common.add', 'model.rule_group.attribute', function () use ($request) {
            if ($group = RuleGroup::create($request->only('name', 'type'))) {
                $rules = $request->input('rules');
                if (! empty($rules)) {
                    $group->rules()->attach($rules);
                }

                return redirect(route('admin.rule.group.edit', $group))->with('successMsg', trans('common.success_item', ['attribute' => trans('common.add')]));
            }

            return null;
        });
    }

    public function create(): View
    {
        return view('admin.rule.group.info', ['rules' => Rule::pluck('name', 'id')]);
    }

    public function edit(RuleGroup $group): View
    {
        $group->load('rules:id');

        return view('admin.rule.group.info', [
            'ruleGroup' => array_merge($group->toArray(), ['rules' => $group->rules->pluck('id')->map('strval')->toArray()]),
            'rules' => Rule::pluck('name', 'id'),
        ]);
    }

    public function update(RuleGroupRequest $request, RuleGroup $group): RedirectResponse
    {
        return $this->redirectAction('common.edit', 'model.rule_group.attribute', function () use ($request, $group) {
            if ($group->update($request->only(['name', 'type']))) {
                $group->rules()->sync($request->input('rules', []));

                return redirect()->back()->with('successMsg', trans('common.success_item', ['attribute' => trans('common.edit')]));
            }

            return null;
        });
    }

    public function destroy(RuleGroup $group): JsonResponse
    {
        return $this->actionResponse('common.delete', 'model.rule_group.attribute', fn () => $group->delete());
    }
}
