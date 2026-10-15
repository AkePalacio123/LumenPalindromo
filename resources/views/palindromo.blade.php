<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detector de Palíndromos</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;

            display: flex;
            justify-content: center;
            align-items: center;

            min-height: 100vh;
            margin: 0;
        }

        .container {
            background-color: white;

            padding: 30px;

            border-radius: 10px;

            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);

            text-align: center;

            width: 100%;
            max-width: 500px;
        }

        h1 {
            color: #333;
        }

        p {
            color: #666;
        }

        input[type="text"] {
            width: 90%;

            padding: 12px;

            border: 1px solid #ddd;

            border-radius: 5px;

            font-size: 16px;

            margin-bottom: 15px;
        }

        button {
            background-color: #007bff;

            color: white;

            padding: 12px 25px;

            border: none;

            border-radius: 5px;

            cursor: pointer;

            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .resultado {
            margin-top: 25px;

            padding: 15px;

            border-radius: 5px;

            font-size: 18px;

            font-weight: bold;
        }

        .palindromo {
            background-color: #d4edda;

            color: #155724;
        }

        .no-palindromo {
            background-color: #f8d7da;

            color: #721c24;
        }
    </style>
</head>

<body>

    <div class="container">

        <h1>Detector de Palíndromos</h1>

        <p>
            Introduce una palabra o frase para comprobar si es un palíndromo.
        </p>

        <form action="{{ url('palindromo') }}" method="POST">
            @csrf

            <input
                type="text"
                name="texto"
                placeholder="Escribe aquí..."
                value="{{ $textoOriginal ?? '' }}"
                required
            >

            <br>

            <button type="submit">
                Comprobar
            </button>

        </form>

        @if(!empty($errors))
            <div class="resultado no-palindromo">{{ implode(' ', $errors) }}</div>
        @endif

        @isset($esPalindromo)

            @if ($esPalindromo)

                <div class="resultado palindromo">

                    "{{ $textoOriginal }}" es un palíndromo.

                </div>

            @else

                <div class="resultado no-palindromo">

                    "{{ $textoOriginal }}" no es un palíndromo.

                </div>

            @endif

        @endisset

    </div>

</body>

</html>
