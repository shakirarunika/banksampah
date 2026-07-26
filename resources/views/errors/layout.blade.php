<!DOCTYPE html>
{{-- Halaman error: sengaja standalone (CSS inline, tanpa Vite/DB) supaya tetap tampil walau asset/server lagi bermasalah --}}
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') — {{ config('app.name', 'Bank Sampah') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Figtree', 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(160deg, #f8fafc 0%, #ecfdf5 60%, #d1fae5 100%);
            color: #0f172a;
            overflow-x: hidden;
        }

        /* Daun & ikon daur ulang melayang di background */
        .leaves span {
            position: fixed;
            bottom: -4rem;
            opacity: .16;
            animation: rise linear infinite;
        }

        @keyframes rise {
            to { transform: translateY(-115vh) rotate(360deg); }
        }

        .wrap {
            text-align: center;
            padding: 2rem;
            position: relative;
            z-index: 1;
            max-width: 34rem;
        }

        .emoji {
            font-size: 3.25rem;
            display: inline-block;
            animation: bounce 2.4s ease-in-out infinite;
            filter: drop-shadow(0 12px 16px rgba(5, 150, 105, .18));
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-14px); }
        }

        .code {
            font-size: clamp(4.5rem, 14vmin, 7.5rem);
            font-weight: 900;
            letter-spacing: -.05em;
            line-height: 1.1;
            margin: .25rem 0 .75rem;
        }

        .code span {
            display: inline-block;
            background: linear-gradient(135deg, #059669, #0f766e);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: bob 1.8s ease-in-out infinite;
        }

        .code span:nth-child(2) { animation-delay: .15s; }
        .code span:nth-child(3) { animation-delay: .3s; }

        @keyframes bob {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }

        h1 {
            font-size: .95rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .3em;
            color: #334155;
            margin-bottom: .9rem;
        }

        p {
            font-size: .85rem;
            font-weight: 600;
            color: #64748b;
            line-height: 1.8;
            margin-bottom: 2.25rem;
        }

        .btns {
            display: flex;
            gap: .75rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: .95rem 1.9rem;
            border-radius: 1rem;
            border: 0;
            font: inherit;
            font-size: .68rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .2em;
            text-decoration: none;
            cursor: pointer;
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .btn:active { transform: scale(.95); }

        .btn-primary {
            background: #059669;
            color: #fff;
            box-shadow: 0 14px 28px -10px rgba(5, 150, 105, .5);
        }

        .btn-primary:hover { background: #047857; transform: translateY(-2px); }

        .btn-ghost {
            background: #fff;
            color: #475569;
            border: 1px solid #e2e8f0;
            box-shadow: 0 6px 16px -8px rgba(15, 23, 42, .15);
        }

        .btn-ghost:hover { background: #f8fafc; transform: translateY(-2px); }

        @media (prefers-reduced-motion: reduce) {
            * { animation: none !important; transition: none !important; }
        }
    </style>
</head>

<body>
    <div class="leaves" aria-hidden="true">
        <span style="left:6%;  font-size:1.6rem; animation-duration:16s;">♻️</span>
        <span style="left:18%; font-size:1.1rem; animation-duration:22s; animation-delay:3s;">🍃</span>
        <span style="left:34%; font-size:1.4rem; animation-duration:19s; animation-delay:7s;">🌿</span>
        <span style="left:52%; font-size:1rem;   animation-duration:24s; animation-delay:1s;">♻️</span>
        <span style="left:68%; font-size:1.5rem; animation-duration:17s; animation-delay:5s;">🍃</span>
        <span style="left:82%; font-size:1.2rem; animation-duration:21s; animation-delay:9s;">♻️</span>
        <span style="left:93%; font-size:1.3rem; animation-duration:18s; animation-delay:4s;">🌿</span>
    </div>

    <main class="wrap">
        <div class="emoji">@yield('emoji')</div>

        <div class="code" role="img" aria-label="Error @yield('code')">
            @foreach (str_split(trim($__env->yieldContent('code'))) as $digit)
                <span>{{ $digit }}</span>
            @endforeach
        </div>

        <h1>@yield('title')</h1>
        <p>@yield('message')</p>

        <div class="btns">
            <a class="btn btn-primary" href="{{ url('/') }}">🏠 Balik ke Beranda</a>
            @hasSection('retry')
                <button class="btn btn-ghost" onclick="location.reload()">🔄 Muat Ulang</button>
            @endif
        </div>
    </main>
</body>

</html>
