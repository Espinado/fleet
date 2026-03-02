<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0066ff">
    <title>Offline — Driver App</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #f3f4f6; color: #1f2937; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; box-sizing: border-box; }
        .card { background: #fff; border-radius: 1rem; padding: 2rem; max-width: 20rem; text-align: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1); }
        h1 { font-size: 1.25rem; font-weight: 700; margin: 0 0 0.75rem; }
        p { font-size: 0.875rem; color: #6b7280; margin: 0; line-height: 1.5; }
        .icon { font-size: 3rem; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon" aria-hidden="true">📡</div>
        <h1>{{ __('app.driver.offline.title') }}</h1>
        <p>{{ __('app.driver.offline.message') }}</p>
    </div>
</body>
</html>
