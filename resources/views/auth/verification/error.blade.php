<!DOCTYPE html>
<html>
<head>
    <title>Email Verification Error</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            text-align: center;
            max-width: 24rem;
            width: 90%;
        }
        .icon {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1rem;
            background-color: #dc2626;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon svg {
            width: 2rem;
            height: 2rem;
            color: white;
        }
        h1 {
            color: #dc2626;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        p {
            color: #4b5563;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        .button {
            background-color: #dc2626;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .button:hover {
            background-color: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>
        <h1>Verification Failed</h1>
        <p>Sorry, we couldn't verify your email address. The verification link may be invalid or has expired.</p>
        <a href="#" class="button" onclick="window.close(); return false;">Close Window</a>
    </div>
    <script>
        setTimeout(function() {
            window.close();
        }, 3000);
    </script>
</body>
</html> 