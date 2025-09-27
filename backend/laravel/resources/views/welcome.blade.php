<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <style>
            body {
                font-family: 'Figtree', sans-serif;
                margin: 0;
                padding: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .container {
                text-align: center;
                background: white;
                padding: 3rem;
                border-radius: 1rem;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                max-width: 600px;
                margin: 2rem;
            }
            h1 {
                color: #333;
                margin-bottom: 1rem;
                font-size: 2.5rem;
            }
            .subtitle {
                color: #666;
                font-size: 1.2rem;
                margin-bottom: 2rem;
            }
            .status {
                background: #f8f9fa;
                padding: 1rem;
                border-radius: 0.5rem;
                margin: 1rem 0;
                border-left: 4px solid #28a745;
            }
            .api-links {
                margin-top: 2rem;
            }
            .api-links a {
                display: inline-block;
                margin: 0.5rem;
                padding: 0.75rem 1.5rem;
                background: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 0.5rem;
                transition: background 0.3s;
            }
            .api-links a:hover {
                background: #0056b3;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Laravel API</h1>
            <p class="subtitle">Your Laravel application is running successfully!</p>
            
            <div class="status">
                <strong>Status:</strong> ✅ Application is healthy and ready to serve requests
            </div>

            <div class="api-links">
                <a href="/api/health">Health Check</a>
                <a href="/api/status">API Status</a>
            </div>

            <p style="margin-top: 2rem; color: #666; font-size: 0.9rem;">
                Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
            </p>
        </div>
    </body>
</html>
