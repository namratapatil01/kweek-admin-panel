@php
    $headerSections = $headerSections ?? collect();
    $activeHeaderSection = $activeHeaderSection ?? null;
    $fallbackImg = asset('images/kweek_icon.png');
@endphp

<div class="dropdown-service-list">
    <div class="row">
        @forelse($headerSections as $section)
            @php
                $isSelected = $activeHeaderSection && $activeHeaderSection->id === $section->id;
                $sectionImage = $section->sectionImage ?: $fallbackImg;
                $sectionRoute = url('/dashboard/' . $section->id . '/' . $section->serviceTypeFlag);
            @endphp
            <div class="col-md-4">
                <div class="service-list-box {{ $isSelected ? 'selected-section' : '' }}"
                    data-section-url="{{ $sectionRoute }}"
                    data-section-id="{{ $section->id }}"
                    data-section-type="{{ $section->serviceTypeFlag }}">
                    <img src="{{ $sectionImage }}"
                        onerror="this.onerror=null;this.src='{{ $fallbackImg }}'">
                    <h3>{{ $section->name }}</h3>
                    <p>{{ $section->serviceType }}</p>
                </div>
            </div>
        @empty
            <div class="col-md-12">
                <p class="text-muted mb-3">{{ trans('lang.no_record_found') }}</p>
            </div>
        @endforelse
        <div class="col-md-12">
            <div class="service-list-box"
                data-section-url="{{ route('sections.create') }}"
                data-section-id=""
                data-section-type="">
                <img src="{{ asset('images/add_more.png') }}">
                <h3>Add More</h3>
                <p>Expand by adding new modules as your business grows.</p>
            </div>
        </div>
    </div>
</div>
