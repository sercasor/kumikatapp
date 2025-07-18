<!DOCTYPE html>
<html>
    <head>
        <title>Kumikatapp - Contacto</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="App de gestión para escuelas de artes marciales con la planificación de entrenamientos más detallada. Ahorra tiempo y dedícalo a lo que amas: enseñar">
        <link rel="stylesheet" href="stylesheet.css">
        <!--realizado por Sergio Castillo Ortiz, amante del deporte-->
    </head>
    <body>
        
        <?php
        require_once 'controller.php';
        session_start();
        showHeader();
        $usuario = Persona::getNombreUsuarioCookie();
        $persona = Persona::renovarPersonaSesion();
        
     
        ?>
        <main>

        <h1>Contacto</h1>

        <p>¿Tienes una duda o te gustaría registrar tu escuela? Puedes hacerlo a través de nuestro  <a href="mailto:info@kumikatapp.es">correo</a> o llamándonos al <a href="tel:655124578">655124578</a> indicando los datos de tu escuela y los profesores.</p>











        </main>
        <?php
            showFooter();
        ?>
        
        
    </body>
</html>
