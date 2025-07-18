<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Kumikatapp - App de gestión eficiente de escuelas de artes marciales</title>
        <meta name="description" content="App de gestión para escuelas de artes marciales con la planificación de entrenamientos más detallada. Ahorra tiempo y dedícalo a lo que amas: enseñar">
        <link rel="stylesheet" href="stylesheet.css">

        <!--realizado por Sergio Castillo Ortiz, amante del deporte-->
    </head>
    <body>

        <?php
        require_once 'controller.php';
        showHeader();
        
        ?>
        <main>
            <h1>Gestiona tu escuela de la forma más eficiente</h1>
            <a href="login.php">Registro/login</a>
            <h2>¿Cómo funciona Kumikatapp?</h2>
            <p>Es muy fácil, te registras en la web usando el <a href="login.php">login</a> y nos llamas o escribes indicando tu usuario, los datos de tu escuela (cif imprescindible) y nosotros nos encargamos de registrar la escuela y añadirte como profesor a ti y a todos los uaurios que quieras.</p>
            <p>Una vez esté establecida, simplemente es cuestión de registrar los alumnos (pueden hacerlo ellos mismos) e ir añadiendo al calendario las clases para que los estudiantes se apunten. Puedes usar el planificador de clases para organizar el tiempo de la clase una vez esté creada y tus alumnos se hayan apuntado. </p>
            <p>Para crear los planes de pago, sólo tienes que ir a la sección de mensualidades y crear las que desees. Los pagos los puedes registrar en pagos una vez que el alumno haya pagado</p>
        </main>

        <?php
        showFooter();
        
        ?>
    </body>
</html>
