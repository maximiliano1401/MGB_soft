
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contacto | MGB Software</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
    </style>
</head>
<body>

    <header>
        <div class="logo-container">
            <img src="IMG/logo.png" alt="Logo MGB">
        </div>
        <h1>Contacto</h1>
        <p>¿Tienes preguntas o deseas una cotización? Contáctanos, estamos para ayudarte</p>
    <nav>
      <a href="index.html">Inicio</a>
      <a href="servicios.html">Servicios</a>
      <a href="paquetes.html">Paquetes</a>
      <a href="contacto.php">Contacto</a>
      <a href="acerca_de.html">Acerca de</a>
    </nav>
    </header>

    <main class="contacto">
        <h2>Envíanos un mensaje</h2>
        <form action="procesar.php" method="post">
            <label for="nombre">Nombre completo</label>
            <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" required>

            <label for="correo">Correo electrónico</label>
            <input type="email" id="correo" name="correo" placeholder="tucorreo@ejemplo.com" required>

            <label for="mensaje">Mensaje</label>
            <textarea id="mensaje" name="mensaje" rows="6" placeholder="Escribe tu mensaje aquí..." required></textarea>

            <button type="submit">Enviar mensaje</button>
        </form>

        <div class="info-contacto">
            <h3>También puedes contactarnos a través de:</h3>
            <p><strong>Teléfono:</strong> +52 999 123 4567</p>
            <p><strong>Email:</strong> contacto@mgbsoftware.com</p>
            <p><strong>Dirección:</strong> Calle 123, Col. Centro, Mérida, Yucatán</p>
        </div>
    </main>

    <footer>
        &copy; 2025 MGB Software. Todos los derechos reservados.
    </footer>

</body>
</html>