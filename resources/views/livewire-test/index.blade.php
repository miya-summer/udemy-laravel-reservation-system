<html>
    <head>
        @livewireStyles
    </head>
    <body>
        livewireテスト
        @if (session()->has('message'))
            <div class="">
                {{ session('message') }}
            </div>
        @endif
        {{-- <livewire:counter /> --}}
        @livewire('counter')
        @livewireScripts
    </body>
</html>
