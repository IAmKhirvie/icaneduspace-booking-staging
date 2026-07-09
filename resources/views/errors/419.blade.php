<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Expired | ICAN Eduspace</title>
    <style>
        :root {
            color-scheme: dark;
            --bg: #07111f;
            --panel: rgba(255, 255, 255, 0.06);
            --line: rgba(255, 255, 255, 0.14);
            --text: #ffffff;
            --muted: rgba(255, 255, 255, 0.72);
            --quiet: rgba(255, 255, 255, 0.48);
            --gold: #d7b46a;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--text);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        main {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 32px 18px;
        }

        section {
            width: min(100%, 600px);
            border: 1px solid var(--line);
            background: var(--panel);
            padding: clamp(24px, 5vw, 36px);
            box-shadow: 0 24px 80px rgba(0, 0, 0, 0.35);
        }

        .eyebrow {
            margin: 0 0 12px;
            color: var(--gold);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(36px, 8vw, 52px);
            font-weight: 500;
            line-height: 1.03;
        }

        p {
            margin: 20px 0 0;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.65;
        }

        .actions {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 28px;
        }

        a {
            min-height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--line);
            color: var(--text);
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        a.primary {
            border-color: var(--gold);
            background: var(--gold);
            color: var(--bg);
        }

        a:hover {
            border-color: var(--gold);
            color: var(--gold);
        }

        a.primary:hover {
            background: #ffffff;
            border-color: #ffffff;
            color: var(--bg);
        }

        .note {
            color: var(--quiet);
            font-size: 12px;
        }

        @media (max-width: 560px) {
            .actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <main>
        <section>
            <p class="eyebrow">Session expired</p>
            <h1>Please refresh and try again.</h1>
            <p>
                This usually happens when a form was open too long, the browser blocked cookies, or the page was opened from an old session.
                Your booking or registration was not submitted.
            </p>

            <div class="actions">
                <a class="primary" href="{{ route('register') }}">
                    Register
                </a>
                <a href="{{ route('login') }}">
                    Sign in
                </a>
                <a href="{{ url('/') }}">
                    Home
                </a>
            </div>

            <p class="note">
                If this keeps happening, reload the page, allow cookies for this site, and submit the form again.
            </p>
        </section>
    </main>
</body>
</html>
