<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>@yield('template_title')</title>@if(config('LaravelLogger.cssFramework') === 'tailwind')<link rel="stylesheet" href="/fixture-tailwind.css">@endif @yield('template_linked_css')@stack('template_linked_css')</head>
<body>@yield('content')@yield('footer_scripts')@stack('footer_scripts')</body></html>
