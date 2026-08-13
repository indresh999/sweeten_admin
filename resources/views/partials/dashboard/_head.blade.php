

<!-- Favicon -->
<link rel="shortcut icon" href="{{asset('images/favicon.ico')}}" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="{{asset('css/libs.min.css')}}">
<link rel="stylesheet" href="{{asset('css/hope-ui.css?v=1.1.0')}}">
<link rel="stylesheet" href="{{asset('css/custom.css?v=1.1.0')}}">
<link rel="stylesheet" href="{{asset('css/dark.css?v=1.1.0')}}">
<link rel="stylesheet" href="{{asset('css/rtl.css?v=1.1.0')}}">
<link rel="stylesheet" href="{{asset('css/customizer.css?v=1.1.0')}}">

<!-- Fullcalender CSS -->
<link rel="stylesheet" href="{{ asset('vendor/flatpickr/dist/flatpickr.min.css') }}">
<link rel='stylesheet' href="{{asset('vendor/fullcalendar/core/main.css')}}" />
<link rel='stylesheet' href="{{asset('vendor/fullcalendar/daygrid/main.css')}}" />
<link rel='stylesheet' href="{{asset('vendor/fullcalendar/timegrid/main.css')}}" />
<link rel='stylesheet' href="{{asset('vendor/fullcalendar/list/main.css')}}" />
<link rel="stylesheet" href="{{asset('vendor/Leaflet/leaflet.css')}}" />

<link rel="stylesheet" href="{{asset('vendor/aos/dist/aos.css')}}" />
<style>
    th.hide-search input{
       display: none;
    }
 </style>
@stack('styles')
