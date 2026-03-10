<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Mis Calificaciones BUAP</title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-sky-100 flex items-center justify-center min-h-screen">

<div class="bg-white shadow-xl rounded-2xl w-[900px] min-h-[500px] overflow-hidden flex">

    <div class="w-1/3 bg-sky-300 text-white flex flex-col items-center justify-center p-8">

        <img src="{{ asset('images/logo-buap.png') }}"
        alt="BUAP Logo"
        class="w-40 mb-6">

        <h2 class="text-xl font-semibold text-center">
            Mis Calificaciones BUAP
        </h2>

        <p class="text-sm text-white text-center mt-2">
            Sistema académico para consulta de calificaciones.
        </p>

    </div>

    <div class="w-2/3 flex flex-col justify-center items-center p-10">

        <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">
            Bienvenido
        </h1>

        <p class="text-gray-600 mb-10 text-center max-w-md">
            Selecciona tu tipo de usuario.
        </p>

        <div class="w-full max-w-sm flex flex-col gap-4">

            <a href="/login-estudiante"
            class="bg-blue-900 text-white py-3 rounded-lg text-center hover:bg-blue-800 transition">
            Iniciar sesión como Estudiante
            </a>

            <a href="/login-profesor"
            class="bg-sky-400 text-white py-3 rounded-lg text-center hover:bg-sky-500 transition">
            Iniciar sesión como Profesor
            </a>

        </div>

    </div>

</div>

</body>
</html>