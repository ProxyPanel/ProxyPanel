@props(['href' => '#', 'color' => 'primary', 'icon', 'title', 'value', 'tag' => false])

<div class="col-xxl-3 col-xl-4 col-md-6">
    <a class="card card-shadow" href="{{ $href }}">
        <div class="card-block bg-white">
            <button class="btn btn-floating btn-sm btn-{{ $color }}" type="button">
                <i class="icon {{ $icon }}"></i>
            </button>
            <span class="ml-15 font-weight-400">{{ $title }}</span>
            <div class="content-text text-center mb-0">
                <span class="font-size-40 font-weight-100">{{ $value }}</span>
                @if ($tag)
                    <span class="badge badge-success badge-round up font-size-20 m-0" style="top:-20px">
                        <i class="icon wb-triangle-up" aria-hidden="true"></i> {{ $tag }}
                    </span>
                @endif
            </div>
        </div>
    </a>
</div>
