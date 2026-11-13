<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    @yield('head')
</head>
<body style="margin:0; min-height:100vh; background:#FAF7F2; font-family:Arial,sans-serif; color:#49382D;">
<div style="display:flex; min-height:100vh;">
    @include('dashboard.partials.sidebar', ['currentPage' => $currentPage])

    <main style="margin-left:230px; flex:1; min-width:0;">
        @include('dashboard.partials.header')

        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>
