<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title', $raffle->name)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --accent: #2563eb;
            --accent-hover: #1d4ed8;
            --danger: #dc2626;
            --success: #059669;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            background: var(--bg);
            color: var(--text);
            font-family: Inter, system-ui, -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .card {
            width: 100%;
            max-width: 480px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 12px 32px rgba(15, 23, 42, .06);
            overflow: hidden;
        }

        .card-header { padding: 32px 32px 0; }
        .card-body { padding: 24px 32px 32px; }

        .eyebrow {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--accent);
        }

        h1 { margin: 6px 0 8px; font-size: 24px; line-height: 1.25; letter-spacing: -.01em; }
        p { margin: 0; color: var(--muted); font-size: 15px; line-height: 1.6; }

        .prize {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-top: 20px;
            padding: 14px 16px;
            border-radius: 12px;
            background: #eff6ff;
            color: #1e3a8a;
            font-size: 14px;
        }
        .prize strong { display: block; font-size: 15px; color: #1e40af; }

        label.field { display: block; margin-bottom: 16px; }
        .label { display: block; margin-bottom: 6px; font-size: 14px; font-weight: 500; }
        .optional { color: var(--muted); font-weight: 400; }

        input[type=text], input[type=email], input[type=tel], input[type=number], select {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font: inherit;
            font-size: 15px;
            color: var(--text);
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
        }
        input:focus, select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .15);
        }
        .has-error input, .has-error select { border-color: var(--danger); }

        .check { display: flex; gap: 10px; align-items: flex-start; margin-bottom: 16px; font-size: 14px; cursor: pointer; }
        .check input { margin-top: 3px; width: 16px; height: 16px; accent-color: var(--accent); }

        .error { margin-top: 6px; font-size: 13px; color: var(--danger); }
        .alert { margin-bottom: 16px; padding: 12px 14px; border-radius: 10px; background: #fef2f2; color: var(--danger); font-size: 14px; }

        button {
            width: 100%;
            padding: 13px 16px;
            border: 0;
            border-radius: 10px;
            background: var(--accent);
            color: #fff;
            font: inherit;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s;
        }
        button:hover { background: var(--accent-hover); }
        button:disabled { opacity: .6; cursor: wait; }

        .number {
            margin: 24px 0 8px;
            text-align: center;
        }
        .number span {
            display: inline-block;
            padding: 16px 28px;
            border-radius: 14px;
            background: var(--text);
            color: #fff;
            font-size: 44px;
            font-weight: 700;
            letter-spacing: .12em;
            font-variant-numeric: tabular-nums;
        }
        .center { text-align: center; }
        .footer { margin-top: 24px; font-size: 13px; color: var(--muted); text-align: center; }
    </style>
</head>
<body>
    <main class="card">
        @yield('content')
    </main>
</body>
</html>
