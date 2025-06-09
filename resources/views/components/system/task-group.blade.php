@props(['type', 'items', 'units'])
<div class="col-12 row" id="{{ "tasks_$type" }}">
    <hr class="col-12" />
    @foreach ($items as $key => $duration)
        <x-system.input-unit type="{{ $type }}" :key="$key" :value="$duration['num']" :unit="$duration['unit']" :units="$units" />
    @endforeach
    <div class="col-12 text-center mt-md-15 mb-20">
        <button class="btn btn-primary w-p25" type="button" onclick="updateJson('{{ "tasks_$type" }}')">
            {{ trans('common.update') }}
        </button>
    </div>
</div>
