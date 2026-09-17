<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])

@livewireStyles

<script>
    (() => {
        const stored = localStorage.getItem('stockpilot-theme');

        const dark =
            stored === 'dark'
            ||
            (
                stored !== 'light'
                &&
                window.matchMedia(
                    '(prefers-color-scheme: dark)'
                ).matches
            );

        document.documentElement.classList.toggle(
            'dark',
            dark
        );
    })();
</script>
