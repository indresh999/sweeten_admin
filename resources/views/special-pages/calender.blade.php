<x-app-layout :assets="$assets ?? []">
    @push('scripts')
        <script src="{{ asset('vendor/fullcalendar/core/main.js') }}"></script>
        <script src="{{ asset('vendor/fullcalendar/daygrid/main.js') }}"></script>
        <script src="{{ asset('vendor/fullcalendar/timegrid/main.js') }}"></script>
        <script src="{{ asset('vendor/fullcalendar/list/main.js') }}"></script>
        <script src="{{ asset('vendor/fullcalendar/interaction/main.js') }}"></script>
    @endpush
    <div>
        <x-header-breadcrumb class="btn btn-primary" />
        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card  ">
                            <div class="card-body">
                                <div id="calendar1" class="calendar-s"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
