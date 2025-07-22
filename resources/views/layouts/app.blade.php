<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Cleveridge')</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Arial:400,700&display=swap">
    <style>
        body { font-family: Arial, sans-serif; background: #f3f3f3; margin:0; }
        header { background: #6ac4f1; color: #fff; padding: 20px; text-align: center; }
        nav { background: #e0e0e0; padding: 10px; text-align: center; }
        nav a { margin: 0 15px; text-decoration: none; color: #004466; }
        .container { padding: 2rem; max-width: 1200px; margin: auto; }
        footer { background: #e0e0e0; color: #004466; text-align: center; padding: 10px; margin-top: 40px; }
    </style>
    @stack('styles')
</head>
<body>
<header>
    <h1>Cleveridge</h1>
    <p>Your Smart Fridge</p>
</header>
<nav>
    <a href="#">What's in your fridge</a>
    <a href="#">Produkte</a>
    <a href="#">Einkaufliste</a>
    <a href="#">Cleveridge Status</a>
    <a href="#">Ablaufwarnungen</a>
    <a href="https://play.google.com/store/apps/details?id=org.openfoodfacts.scanner" target="_blank">Barcode Scannen!</a>
</nav>
<div class="container">
    @yield('content')
</div>
<footer>
    Cleveridge &copy; {{ date('Y') }}
</footer>
@stack('scripts')
</body>
</html>
