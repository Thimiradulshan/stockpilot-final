@if (session('success'))
    <div
        x-data
        x-init="window.StockPilotSwal?.toast('success', @js(session('success')))"
    ></div>
@endif

@if (session('error'))
    <div
        x-data
        x-init="window.StockPilotSwal?.error('Action failed', @js(session('error')))"
    ></div>
@endif

@if ($errors->any())
    <div
        x-data
        x-init="window.StockPilotSwal?.error('Validation failed', @js($errors->first()))"
    ></div>
@endif
