<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Smart Fan & LED Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-cream-1: #f6efe6;
            --bg-cream-2: #efe4d8;
            --bg-cream-3: #e7d8c7;

            --card-dark-1: #1e0f0a;
            --card-dark-2: #3b1d12;

            --accent: #c08a5a;

            --text-dark: #2b1a12;
            --text-light: #f6e9da;
            --text-muted: #d6c3ae;
        }

        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(
                180deg,
                var(--bg-cream-1) 0%,
                var(--bg-cream-2) 40%,
                var(--bg-cream-3) 100%
            );
            color: var(--text-dark);
        }

        header {
            text-align: center;
            padding: 30px 20px 10px;
        }

        header h1 {
            margin: 0;
            font-weight: 600;
        }

        header p {
            margin-top: 6px;
            color: #6b4a3a;
            font-size: 0.95em;
        }

        .container {
            padding: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
        }

        .card {
            position: relative;
            padding: 22px;
            border-radius: 22px;
            background: linear-gradient(160deg, var(--card-dark-1), var(--card-dark-2));
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,0.06),
                0 15px 40px rgba(0,0,0,0.4);
            color: var(--text-light);
        }

        .label {
            font-size: 0.85em;
            color: var(--text-muted);
            letter-spacing: 0.5px;
        }

        .value {
            margin-top: 10px;
            font-size: 2.4em;
            font-weight: 600;
            color: #fff;
        }

        .unit {
            font-size: 0.6em;
            margin-left: 6px;
            color: #f3dcc2;
            opacity: 0.9;
            text-shadow: 0 1px 2px rgba(0,0,0,0.6);
        }

        .status {
            margin-top: 14px;
            padding: 12px;
            border-radius: 14px;
            font-weight: 600;
            text-align: center;
            color: #fff;
        }

        /* FAN */
        .fan-off {
            background: linear-gradient(135deg, #6c6c6c, #3f3f3f);
        }

        .fan-sedang {
            background: linear-gradient(135deg, #d1a06a, #8a4f2a);
        }

        .fan-tinggi {
            background: linear-gradient(135deg, #e05a3f, #8b1e12);
        }

        /* LED */
        .led-hijau {
            background: linear-gradient(135deg, #2ecc71, #145a32);
        }

        .led-kuning {
            background: linear-gradient(135deg, #f1c40f, #9a7d0a);
        }

        .led-merah {
            background: linear-gradient(135deg, #e74c3c, #781f16);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 10px;
        }

        select {
            padding: 12px 14px;
            border-radius: 14px;
            border: none;
            background: linear-gradient(135deg, #2a140c, #3b1d12);
            color: #f6e9da;
            font-weight: 500;
            outline: none;
        }

        select option {
            background: #3b1d12;
            color: #f6e9da;
        }

        button {
            padding: 14px;
            border-radius: 16px;
            border: none;
            background: linear-gradient(135deg, var(--accent), #8a4f2a);
            color: #1b0d07;
            font-weight: 600;
            cursor: pointer;
            transition: transform .2s, box-shadow .2s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(192,138,90,0.35);
        }

        footer {
            text-align: center;
            padding: 20px;
            font-size: 0.85em;
            color: #6b4a3a;
            opacity: 0.7;
        }
    </style>
</head>
<body>

<header>
    <h1>Smart Fan & LED</h1>
    <p>IoT Dashboard</p>
</header>

<div class="container">

    <div class="card">
        <div class="label">Suhu Ruangan</div>
        <div class="value">
            {{ $data->temperature ?? '-' }}
            <span class="unit">°C</span>
        </div>
    </div>

    <div class="card">
        <div class="label">Kelembaban</div>
        <div class="value">
            {{ $data->humidity ?? '-' }}
            <span class="unit">%</span>
        </div>
    </div>

    <div class="card">
        <div class="label">Status Kipas</div>
        <div class="status
            @if(($data->fan_status ?? '') == 'OFF') fan-off
            @elseif(($data->fan_status ?? '') == 'SEDANG') fan-sedang
            @else fan-tinggi
            @endif">
            {{ $data->fan_status ?? '-' }}
        </div>
    </div>

    <div class="card">
        <div class="label">Status LED</div>
        <div class="status
            @if(($data->led_status ?? '') == 'HIJAU') led-hijau
            @elseif(($data->led_status ?? '') == 'KUNING') led-kuning
            @else led-merah
            @endif">
            {{ $data->led_status ?? '-' }}
        </div>
    </div>

    <div class="card">
        <div class="label">Kontrol Manual</div>
        <form method="POST" action="/manual">
            @csrf

            <select name="fan">
                <option value="OFF">OFF</option>
                <option value="SEDANG">SEDANG</option>
                <option value="TINGGI">TINGGI</option>
            </select>

            <select name="led">
                <option value="HIJAU">HIJAU</option>
                <option value="KUNING">KUNING</option>
                <option value="MERAH">MERAH</option>
            </select>

            <button>Kirim Kontrol</button>
        </form>
    </div>

</div>

<footer>
    © 2025 Smart IoT Project • Piranti Cerdas
</footer>

</body>
</html>
