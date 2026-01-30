@props([
    'title',
    'thead_style' => 'default',
    'theads' => [],
    'count' => null,
    'pagination' => null,
    'grid' => '',
    'filters' => null,
    'body' => null,
    'actions' => null,
    'thead' => null,
    'tbody' => null,
    'deleteConfig' => null,
])

<div class="panel" {!! $deleteConfig ? 'data-delete-config=' . json_encode($deleteConfig, JSON_THROW_ON_ERROR) : '' !!}>
    <div class="panel-heading">
        <h1 class="panel-title">{!! $title !!}</h1>
        @if ($actions)
            <div class="panel-actions">{{ $actions }}</div>
        @endif
    </div>
    <div class="panel-body {{ $grid ? 'row' : '' }}">
        @if ($filters)
            <form class="form-row {{ $grid ? 'col-12' : '' }}">
                {!! $filters !!}
                <div class="form-group btn-group col-auto">
                    <button class="btn btn-primary" type="submit">{{ trans('common.search') }}</button>
                    <button class="btn btn-danger" type="button" onclick="resetSearchForm()">{{ trans('common.reset') }}</button>
                </div>
            </form>
        @endif
        {!! $body !!}

        {!! $grid ? "<div class='$grid'>" : '' !!}
        <table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
            <thead class="thead-{{ $thead_style }}">
                <tr>
                    @forelse($theads as $key => $value)
                        <th>
                            @if (is_string($key))
                                @sortablelink($key, $value)
                            @else
                                {{ $value }}
                            @endif
                        </th>
                    @empty
                        {!! $thead !!}
                    @endforelse
                </tr>
            </thead>
            <tbody>
                {!! $tbody !!}
            </tbody>
        </table>

        {!! $grid ? '</div>' : '' !!}
    </div>

    @if ($count && $pagination)
        <div class="panel-footer d-flex flex-column flex-lg-row justify-content-between align-items-center">
            <div class="mb-3 mb-lg-0">
                {!! $count !!}
            </div>
            {!! $pagination !!}
        </div>
    @elseif($count)
        <div class="panel-footer d-flex justify-content-start">
            {!! $count !!}
        </div>
    @elseif($pagination)
        <div class="panel-footer d-flex justify-content-end">
            {!! $pagination !!}
        </div>
    @endif
</div>
