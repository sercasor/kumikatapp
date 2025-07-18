<?php

include_once "bbdd.php";

//realizado por Sergio Castillo Ortiz


/* Función para crear una conexión con Prepared Statements */

function crearConexionPrepare() {

    //crea la conexión
    $conexion = new mysqli(HOST, USER, PASSWORD, DATABASE);

    //Comprobamos la conexión y mostramos mensaje de error si no funciona
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
    return $conexion;
    //Después de esto tocaría el prepare & bind, set parameters & execute, y cerrar el statement y la conexión (mirar docu de w3schools de prepared statements)
}

/* --------------------------------------- */
/* CLASES */
/* --------------------------------------- */
/* En terminos prácticos, Persona es un sinónimo de Usuario */

class Persona {

    protected $usuario;
    protected $telefono;
    protected $dni;
    protected $nombre;
    protected $apellido;
    protected $email;
    protected $contrasenya;
    protected $edad;
    protected $cif;
    protected $activo; //boolean para comprobar si el usuario está activo o no

    /**
     * Usaremos métodos estáticos para poder alimentar los parámetros del constructor tras una consulta a la BD para evitar creaciones de objetos innecesarias. 
     * @param type $datos Es un  array asociativo resultado de un SELECT que hacemos previamente con el método obtenerPersona
     * 
     */
    public function __construct($datos) {
        $this->usuario = $datos['usuario'];
        $this->dni = $datos['dni_persona'];
        $this->nombre = $datos['nombre'];
        $this->apellido = $datos['apellido'];
        $this->email = $datos['email'];
        $this->contrasenya = $datos['contrasenya'];
        $this->edad = $datos['edad'];
        $this->cif = $datos['cif_escuela'];
        $this->telefono = $datos['telefono'];
        $this->activo = $datos['activo'];
    }

    /* Comprueba si la contraseña introducida coincide con la registrada en la BD. Se usa para el login */

    public static function comprobarContrasenyaExistente($usuario, $contrasenya) {
        $db = crearConexionPrepare();

        $stmt = $db->prepare("SELECT * FROM persona WHERE usuario = ? AND contrasenya = ?");
        $stmt->bind_param("ss", $usuario, $contrasenya);

        $stmt->execute();
        $result = $stmt->get_result();

        $esValida = ($result->num_rows > 0);

        $stmt->close();
        $db->close();

        return $esValida;
    }

    /* Función para cancelar el valor de mi cookie con una fecha anterior a hoy para desloguear al usuario. Borra también la sesión actual con sus variables */

    public static function borrarCookieYSesionLogin($nombreCookie) {
        if (isset($_COOKIE[$nombreCookie])) {
            setcookie($nombreCookie, "", time() - 3600, "/"); //borra la cookie al poner un tiempo anterior a ahora
            unset($_COOKIE[$nombreCookie]); //por si acaso
            // Borrar sesión
            if (isset($_SESSION["persona"])) {
                unset($_SESSION["persona"]);
            }

            session_destroy(); // elimina completamente la sesión


            echo'Te has desconectado con éxito, volver a <a href="index.php">inicio</a>';
            exit;
        } else {
            throw new Exception("Error al borrar cookie: No existe ninguna cookie con ese nombre");
        }
    }

    /* Función para recuperar el valor de mi cookie con el nombre del usuario los valores de la cookie como variables. Devuelve el nombre del usuario sin instanciar ningún objeto */

    public static function getNombreUsuarioCookie() {
        if (isset($_COOKIE['cookieBrownie'])) {
            return $_COOKIE['cookieBrownie'];
        } else {
            return null;
        }
    }

    /**
     * Funcion que crea un objeto Persona si el usuario se ha logueado ya. Nos servirá en todas las páginas para sólo mostrar el contenido al usuario ya logueado. Devuelve el objeto como variable de sesión si no estaba ya definida.
     */
    public static function obtenerPersonaSiLogueado() {
        //si no hay un objeto Persona correcto, lo creamos y lo usamos de variable de sesión
        if (!isset($_SESSION["persona"])) {
            $nombreUsuario = Persona::getNombreUsuarioCookie();
            $persona = Persona::obtenerPersonaSiExiste($nombreUsuario); // Este método debe devolver un objeto Persona o false

            if ($persona) {
                $_SESSION["persona"] = $persona;
            }
        }

        // Usamos el objeto de sesión si existe
        if (!isset($_SESSION["persona"])) {
            die('No te has logueado o registrado, puedes hacerlo en el <a href="login.php">registro</a>');
        } else {
            return $_SESSION["persona"];
        }
    }

    /**
     * Método que comprueba si tenemos un usuario logueado sin devolver objetos y que tiene en cuenta tanto la cookie como la variable de sesion. Es una alternativa sin isntanciación del método obtenerPersonaSiLogueado.
     * @return type
     */
    public static function hayUsuarioLogueado() {
        if (isset($_SESSION["persona"]) || isset($_COOKIE["cookieBrownie"])) {
            return true;
        } else {
            throw new Exception('Error: No te has logueado o registrado. puedes hacerlo en el <a href="login.php">registro</a>');
        }
    }

    /**
     * Método que devuelve un objeto donde ya se aplica el constructor. Usa un array asociativo 
     * @param type $usuario
     * @return \Persona|null
     */
    public static function obtenerPersonaSiExiste($nombreUsuario) {
        $db = crearConexionPrepare();

        $stmt = $db->prepare("SELECT * FROM persona WHERE usuario = ?");
        $stmt->bind_param("s", $nombreUsuario);

        $stmt->execute();
        $res = $stmt->get_result();

        $stmt->close();
        $db->close();

        if ($res->num_rows > 0) {
            return new Persona($res->fetch_assoc());
        } else {
            return false;
        }
    }

    public static function borrarPersonaBD($nombreUsuario) {
        $mensaje;
        $usuario = self::obtenerPersonaSiExiste($nombreUsuario);
        if (!$usuario) {
            throw new Exception("Error: no existe una persona con ese usuario.");
        }

        $db = crearConexionPrepare();
        $stmt = $db->prepare("UPDATE `persona` SET `activo`=false WHERE usuario=?");
        $stmt->bind_param("s", $nombreUsuario);

        if ($stmt->execute()) {
            $mensaje = "Persona borrada correctamente.";
        } else {
            $mensaje = "Error al borrar la persona: " . $stmt->error;
            throw new Exception($mensaje);
        }

        $stmt->close();
        $db->close();
        return $mensaje;
    }

    /**
     * Método que usaremos para realizar un INSERT sin crear un objeto. El procedimiento normal será usar este método estático y luego obtener el objeto 
     * @param type $usuario
     * @param type $contrasenya
     * @param type $cif_escuela
     * @param type $dni_persona
     * @param type $nombre
     * @param type $apellido
     * @param type $email
     * @param type $edad
     * @return string
     */
    public static function crearPersonaBD($usuario, $contrasenya, $cif_escuela, $dni_persona, $nombre, $apellido, $email, $edad, $tlfo, $activo = true) {
        $mensaje;
        if (self::obtenerPersonaSiExiste($usuario)) {
            return "Error: ya existe una persona con ese usuario.";
        }

        $db = crearConexionPrepare();
        $stmt = $db->prepare("INSERT INTO `persona`(`usuario`, `contrasenya`, `cif_escuela`, `dni_persona`, `nombre`, `apellido`, `email`, `edad`, `telefono`, `activo`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?)");
        $stmt->bind_param("sssssssiss", $usuario, $contrasenya, $cif_escuela, $dni_persona, $nombre, $apellido, $email, $edad, $tlfo, $activo);

        if ($stmt->execute()) {
            $mensaje = "Persona creada correctamente.";
        } else {
            $mensaje = "Error al crear la persona: " . $stmt->error;
        }

        $stmt->close();
        $db->close();
        return $mensaje;
    }

    /**
     * Método para renovar variable de sesión que tiene un objeto Persona que representa al usuario logueado. Si no está creada ya, se crea desde cero. 
     */
    public static function renovarPersonaSesion() {
        if (!isset($_SESSION["persona"])) {
            $_SESSION["persona"] = Persona::obtenerPersonaSiExiste(Persona::getNombreUsuarioCookie());
        }
        return $_SESSION["persona"];
    }

    /**
     * Método que usaremos para realizar un UPDATE de una persona. El procedimiento normal será usar este método estático y luego obtener el objeto 
     * @param type $usuario
     * @param type $contrasenya
     * @param type $cif_escuela
     * @param type $dni_persona
     * @param type $nombre
     * @param type $apellido
     * @param type $email
     * @param type $edad
     * @return string
     */
    public function modificarDatosPersonalesBD(Persona $persona) {
        $mensaje = "";

        // Obtener los valores desde el objeto Persona
        $dni = $persona->getDni();
        $nombre = $persona->getNombre();
        $apellido = $persona->getApellido();
        $email = $persona->getEmail();
        $edad = $persona->getEdad();
        $usuario = $persona->getUsuario(); // clave para buscar en el WHERE
        $contrasenya = $persona->getContrasenya();
        $cif = $persona->getCif();
        $telefono = $persona->getTelefono();
        // Comprobamos si el usuario existe
        if (!self::obtenerPersonaSiExiste($usuario)) {
            die("No existe una persona con ese usuario.");
        }

        // Conexión y sentencia UPDATE
        $db = crearConexionPrepare();
        $stmt = $db->prepare("UPDATE persona SET contrasenya = ?, cif_escuela = ?, dni_persona = ?, nombre = ?, apellido = ?, email = ?, edad = ?, telefono = ? WHERE usuario = ?");
        $stmt->bind_param("ssssssiss", $contrasenya, $cif, $dni, $nombre, $apellido, $email, $edad, $telefono, $usuario);

        // Ejecutamos la sentencia
        if ($stmt->execute()) {
            $mensaje = "Datos modificados correctamente.";
        } else {
            $mensaje = "Error al modificar los datos de usuario: " . $stmt->error;
        }

        // Cerramos recursos
        $stmt->close();
        $db->close();

        return $mensaje;
    }

    /**
     * Función que sirve para apuntar al alumno a una clase concreta tras comprobar si ya está apuntado, caso en el que dará un error.
     * @param type $idClase
     * @return string
     */
    //Estos métodos son aplicables a alumno y a profe (pero con código diferente) para ser sobrecargados en sus respectivas clases
    protected function claseYaReservada($idClase) {
        //metodo sobrecargado en las clases heredadas
    }

    function reservarClase($idClase) {
        //metodo sobrecargado en las clases heredadas
    }

    public function cancelarAsistencia($idClase) {
        //metodo sobrecargado en las clases heredadas
    }

    /* GETTERS */

    public function getUsuario() {
        return $this->usuario;
    }

    public function getTelefono() {
        return $this->telefono;
    }

    public function getDni() {
        return $this->dni;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getCif() {
        return $this->cif;
    }

    public function getApellido() {
        return $this->apellido;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getContrasenya() {
        return $this->contrasenya;
    }

    public function getEdad() {
        return $this->edad;
    }

    public function getActivo() {
        return $this->activo;
    }

    /* SETTERS */

    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    public function setTelefono($telefono) {
        $this->telefono = $telefono;
    }

    public function setDni($dni) {
        $this->dni = $dni;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setApellido($apellido) {
        $this->apellido = $apellido;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setContrasenya($contrasenya) {
        $this->contrasenya = $contrasenya;
    }

    public function setEdad($edad) {
        $this->edad = $edad;
    }

    public function setCif($cif) {
        $this->cif = $cif;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }
}

final class Alumno extends Persona {

    private $fechaMatricula;
    private $antiguedad;
    private $companyero; //objeto tipo alumno
    private $rango; //objeto de tipo Rango
    private $luchasRealizadas;

    /**
     * Usaremos métodos estáticos para poder alimentar los parámetros del constructor tras una consulta a la BD para evitar creaciones de objetos innecesarias. 
     * @param type $datos Es un array asociativo resultado de un SELECT que hacemos previamente con el método obtenerPersona
     * 
     */
    public function __construct($datos, Persona $persona) {

        /* atributos de superclase Persona */
        /* ------------------- */
        $this->usuario = $persona->getUsuario();
        $this->dni = $persona->getDni();
        $this->nombre = $persona->getNombre();
        $this->apellido = $persona->getApellido();
        $this->email = $persona->getEmail();
        $this->contrasenya = $persona->getContrasenya();
        $this->edad = $persona->getEdad();
        $this->telefono = $persona->getTelefono();
        $this->cif = $persona->getCif();
        $this->activo = $persona->getActivo();
        /* atributos de alumno */
        /* ------------------- */
        $this->fechaMatricula = $datos['fecha_matricula'];
        $hoy = new DateTime();
        $fechaMatricula = new DateTime($datos['fecha_matricula']);
        //Comprobaciones previas para la antiguedad. diff requiere objetos del tipo DateTime.
        $diferenciaFechas = date_diff($fechaMatricula, $hoy);
        if ($diferenciaFechas->days > 0) {
            $this->antiguedad = $diferenciaFechas;
        } else {
            die("Error, la fecha de matrícula no puede ser posterior a la fecha de hoy");
        }

        /* atributos de rango que pertenecen al alumno */
        /* ------------------- */


        $idRango = Rango::obtenerIdRangoAlumno($persona->getUsuario());
        if (!$idRango) {
            throw new Exception("Error: El alumno no tiene ningún rango asignado");
        }
        $rango = Rango::obtenerRangoSiExiste($idRango['id_rango']);

        $this->rango = $rango;
    }

    public static function validarCredenciales($usuario, $contrasenya) {
        $db = crearConexionPrepare();
        $stmt = $db->prepare("SELECT * FROM persona WHERE usuario = ? AND contrasenya = ?");
        $stmt->bind_param("ss", $usuario, $contrasenya);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $db->close();
        return $result->num_rows > 0;
    }

    /**
     * Método que devuelve un array asociativo para luego usarlo como parámetro en el constructor de la clase (ya incorporado en el método)
     * @param type $usuario
     * @return \Persona|null
     */
    public static function obtenerAlumno($usuario) {
        $db = crearConexionPrepare();

        //sacar datos de alumno
        $stmt = $db->prepare("SELECT * FROM alumno WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resAlumno = $stmt->get_result(); //datos del alumno
        $stmt->close();

        if ($resAlumno->num_rows === 0) {
            return false;
        }

        $datosAlumno = $resAlumno->fetch_assoc();

        //obtenemos objeto Persona con datos usuario
        // Obtener datos de persona
        $persona = Persona::obtenerPersonaSiExiste($usuario);
        if (!$persona) {
            return false;
        }

        // Obtener el id del rango
        $stmt = $db->prepare("SELECT id_rango FROM rango_alumno WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resRango = $stmt->get_result();

        $idRango = null;
        if ($resRango->num_rows > 0) {
            $filaRango = $resRango->fetch_assoc();
            $idRango = $filaRango['id_rango'];
        }
        $stmt->close();
        $db->close();
        return new Alumno($datosAlumno, $persona);
    }

    /**
     * Método que devuelve un array asociativo con todos los alumnos de la escuela del usuario 
     * @param type $usuario
     * @return \Persona|null
     */
    public static function obtenerTodosAlumnos() {
        $db = crearConexionPrepare();

        $usuario = Persona::obtenerPersonaSiExiste(Persona::getNombreUsuarioCookie());
        $cifEscuela = $usuario->getCif();
        //sacar datos de alumno
        $stmt = $db->prepare("SELECT * FROM persona WHERE cif_escuela = ?");
        $stmt->bind_param("s", $cifEscuela);
        $stmt->execute();
        $resAlumno = $stmt->get_result();
        $stmt->close();
        $db->close();

        if ($resAlumno->num_rows === 0) {
            $db->close();
            return false;
        }


        //en el controller recorremos las mensualidades (cada una es un array asociativo) con foreach para sacar la info que queramos
        $alumnos = [];
        while ($fila = $resAlumno->fetch_assoc()) {
            $alumnos[] = new Persona($fila);
        }
        return $alumnos;
    }

    /**
     * Método que usaremos para realizar un INSERT sin crear un objeto. El procedimiento normal será usar este método estático y luego obtener el objeto 
     * @param type $usuario
     * @param type $fecha_matricula
     * @param type $companyero
     * @return string
     */
    public static function crearAlumnoBD($usuario, $fecha_matricula, $companyero = null, $idRango) {
        $mensaje;
        if (self::obtenerAlumno($usuario)) {
            throw new Exception("Error: ya existe un alumno con ese usuario.");
        }

        $db = crearConexionPrepare();
        $stmt = $db->prepare("INSERT INTO `alumno`(`usuario`, `fecha_matricula`, `usuario_alu_companiero`) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $usuario, $fecha_matricula, $companyero);

        if ($stmt->execute()) {
            $mensaje = "Alumno creado correctamente.";
        } else {
            $mensajeErrorPersona = "Error al crear el alumno" . $stmt->error;
            throw new Exception($mensajeErrorPersona);
        }
        $stmt->close();
        $stmt = $db->prepare("INSERT INTO `rango_alumno`(`usuario`, `id_rango`) VALUES (?, ?)");
        $stmt->bind_param("si", $usuario, $idRango);

        if ($stmt->execute()) {
            $mensaje = "Persona y rango creados correctamente.";
        } else {
            $mensajeErrorPersona = "Error al crear el rango:" . $stmt->error;
            throw new Exception($mensajeErrorPersona);
        }

        $stmt->close();
        $db->close();
        return $mensaje;
    }

    protected function claseYaReservada($idClase) {
        $db = crearConexionPrepare();
        $stmt = $db->prepare("SELECT * FROM alumno_clase WHERE usuario = ? AND id_clase = ?");
        $stmt->bind_param("si", $this->usuario, $idClase);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        $db->close();

        if ($res->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Función que sirve para apuntar al alumno a una clase concreta tras comprobar si ya está apuntado, caso en el que dará un error.
     * @param type $idClase
     * @return string
     */
    function reservarClase($idClase) {
        $resultado;
        //comprobamos si ya está reservada la clase por el alumno
        if ($this->claseYaReservada($idClase)) {
            exit("Se está intentando reservar una clase ya reservada");
        }
        $db = crearConexionPrepare();
        $usuario = $this->usuario;
        $stmt = $db->prepare("INSERT INTO `alumno_clase`(`usuario`, `id_clase`) VALUES (?,?)");
        $stmt->bind_param("si", $usuario, $idClase);

        if ($stmt->execute()) {
            $resultado = "Clase reservada con éxito";
        } else {
            $resultado = "Error al reservar la clase: " . $stmt->error;
        }

        $stmt->close();
        $db->close();

        return $resultado;
    }

    function cancelarAsistencia($idClase) {
        $resultado;
        //comprobamos si ya está reservada la clase por el alumno
        if (!$this->claseYaReservada($idClase)) {
            exit("Error: el usuario no ha reservado esta clase, no puede cancelarse la asistencia.");
        }
        $db = crearConexionPrepare();
        $usuario = $this->usuario;
        $stmt = $db->prepare("DELETE FROM `alumno_clase` WHERE usuario = ? AND id_clase = ?");
        $stmt->bind_param("si", $usuario, $idClase);

        if ($stmt->execute()) {
            $resultado = "Asistencia a clase cancelada con éxito";
        } else {
            $resultado = "Error al reservar la clase: " . $stmt->error;
        }

        $stmt->close();
        $db->close();

        return $resultado;
    }


    function emparejar(Alumno $companyero) {
        $this->companyero = $companyero;
        $companyero->companyero = $this->getUsuario();
    }

    /* GETTERS */

    function getAntiguedad() {
        return $this->antiguedad;
    }

    function getCompanyero() {
        return $this->companyero;
    }

    function getFechaMatricula() {
        return $this->fechaMatricula;
    }

    function getRango() {
        return $this->rango;
    }

    function getLuchasRealizadas() {
        return $this->luchasRealizadas;
    }

    /* SETTERS */

    function setAntiguedad($antiguedad) {
        $this->antiguedad = $antiguedad;
    }

    function setCompanyero($companyero) {
        $this->companyero = $companyero;
    }

    function setFechaMatricula($fechaMatricula) {
        $this->fechaMatricula = $fechaMatricula;
    }

    function setRango(Rango $id_rango) {
        $this->rango = $id_rango;
    }

    function setLuchasRealizadas($luchasRealizadas) {
        $this->rango = $luchasRealizadas;
    }
}

final class Profesor extends Persona {

    private $fechacontratacion;
    private $salario;

    /**
     * Usaremos métodos estáticos para poder alimentar los parámetros del constructor tras una consulta a la BD para evitar creaciones de objetos innecesarias. 
     * @param type $datos Es un array asociativo resultado de un SELECT que hacemos previamente con el método obtenerPersona
     * 
     */
    public function __construct($datos, Persona $persona) {

        /* atributos de superclase Persona */
        /* ------------------- */
        $this->usuario = $persona->getUsuario();
        $this->dni = $persona->getDni();
        $this->nombre = $persona->getNombre();
        $this->apellido = $persona->getApellido();
        $this->email = $persona->getEmail();
        $this->contrasenya = $persona->getContrasenya();
        $this->edad = $persona->getEdad();
        $this->activo = $persona->getActivo();
        //la funcion explode nos sirve para hacer un array a partir de un string con un delimitador si nos hace falta más de un tlfo
        $this->telefono = $persona->getTelefono();

        /* atributos de profesor */
        /* ------------------- */
        $this->fechacontratacion = $datos['fecha_contratacion'];
        $this->salario = $datos['salario'];
    }

    /**
     * Método que devuelve un array asociativo para luego usarlo como parámetro en el constructor de la clase
     * @param type $usuario
     * @return \Persona|null
     */
    public static function obtenerProfesor($usuario) {
        $db = crearConexionPrepare();
        $stmt = $db->prepare("SELECT * FROM profesor WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resProfesor = $stmt->get_result(); //datos del profesor
        $stmt->close();
        $db->close();

        if ($resProfesor->num_rows === 0) {
            return false;
        }
        $datosProfesor = $resProfesor->fetch_assoc();

        // Obtener datos de persona
        $persona = Persona::obtenerPersonaSiExiste($usuario);
        if (!$persona) {
            return false;
        }

        return new Profesor($datosProfesor, $persona);
    }

    /**
     * Método que usaremos para realizar un INSERT sin crear un objeto. El procedimiento normal será usar este método estático y luego obtener el objeto 
     * @param type $usuario
     * @param type $fecha_matricula
     * @param type $companyero
     * @return string
     */
    public static function crearProfesorBD($usuario, $fechaContratacion, $salario) {
        if (self::obtenerProfesor($usuario)) {
            $mensajeErrorPersona = "Error: ya existe un profesor con ese usuario." . $stmt->error;
            throw new Exception($mensajeErrorProfeExiste);
        }

        $db = crearConexionPrepare();
        $stmt = $db->prepare("INSERT INTO `profesor`(`usuario`, `fecha_contratacion`, `salario`)  VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $usuario, $fechaContratacion, $salario);

        if ($stmt->execute()) {
            $mensaje = "Profesor creado correctamente.";
        } else {
            $mensaje = "Error al crear el profesor: " . $stmt->error;
            $mensajeErrorPersona = "Error al crear el profesor en la base de datos: " . $stmt->error;
            throw new Exception($mensajeErrorProfe);
        }

        $stmt->close();
        $db->close();
        return $mensaje;
    }

    protected function claseYaReservada($idClase) {
        $db = crearConexionPrepare();
        $stmt = $db->prepare("SELECT * FROM clase WHERE usuario = ? AND id_clase = ?");
        $stmt->bind_param("si", $this->usuario, $idClase);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        $db->close();

        if ($res->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Función que sirve para apuntar al alumno a una clase concreta tras comprobar si ya está apuntado, caso en el que dará un error.
     * @param type $idClase
     * @return string
     */
    function reservarClase($idClase) {
        $resultado = "";
        //comprobamos si ya está reservada la clase por el alumno
        if ($this->claseYaReservada($idClase)) {
            exit("Se está intentando reservar una clase ya reservada");
        }
        $db = crearConexionPrepare();
        $usuario = $this->usuario;

        $stmt = $db->prepare("UPDATE `clase` SET `usuario`=? WHERE `id_clase`=? ");
        $stmt->bind_param("si", $usuario, $idClase);

        if ($stmt->execute()) {
            $resultado = "Clase reservada con éxito";
        } else {
            $resultado = "Error al reservar la clase: " . $stmt->error;
        }

        $stmt->close();
        $db->close();

        return $resultado;
    }

    function cancelarAsistencia($idClase) {
        $mensajeExito = "";
        //comprobamos si ya está reservada la clase por el alumno
        if (!$this->claseYaReservada($idClase)) {
            exit("Error: el usuario no ha reservado esta clase, no puede cancelarse la asistencia.");
        }
        $db = crearConexionPrepare();
        //ponemos el usuario como null porque debe haber un profesor para cada clase, simplemente lo sustituimos por otro
        $stmt = $db->prepare("UPDATE clase SET usuario = NULL WHERE id_clase = ?");
        $stmt->bind_param("i", $idClase);

        if ($stmt->execute()) {
            $mensajeExito = "Clase cancelada con éxito";
        } else {
            $mensajeExito = "Error al reservar la clase: " . $stmt->error;
        }

        $stmt->close();
        $db->close();

        return $mensajeExito;
    }

    /* GETTERS */

    function getFechacontratacion() {
        return $this->fechacontratacion;
    }

    function getFechaMatricula() {
        return $this->salario;
    }

    /* SETTERS */

    function setFechacontratacion($fechacontratacion) {
        $this->fechacontratacion = $fechacontratacion;
    }

    function setFechaMatricula($fechaMatricula) {
        $this->fechaMatricula = $fechaMatricula;
    }
}

class Escuela {

    private $nombre;
    private $telefono;
    private $cif;
    private $direccion;
    private $email;

    /**
     * Usaremos métodos estáticos para poder alimentar los parámetros del constructor tras una consulta a la BD para evitar creaciones de objetos innecesarias. 
     * Los atributos se definirán en el constructor de la clase hijo
     * @param type $datos
     */
    public function __construct($datos) {
        $this->cif = $datos['cif_escuela'];
        $this->nombre = $datos['nombre'];
        $this->direccion = $datos['direccion'];
        $this->telefono = $datos['tlfo'];
        $this->email = $datos['email'];
    }

    public static function existeEscuela($cif) {
        $db = crearConexionPrepare();
        $stmt = $db->prepare("SELECT * FROM escuela WHERE cif_escuela = ?");
        $stmt->bind_param("s", $cif);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        $db->close();

        if ($res->num_rows > 0) {
            return new Escuela($res->fetch_assoc());
        } else {
            return false;
        }
    }

    public static function crearEscuelaBD($cif, $nombre, $direccion, $email, $telefono) {
        $mensaje;
        $escuela = self::existeEscuela($cif);
        if ($escuela) {
            return "Error: ya existe una escuela con ese CIF.";
        }

        $db = crearConexionPrepare();
        $stmt = $db->prepare("INSERT INTO escuela (cif_escuela, nombre, direccion, email, tlfo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $cif, $nombre, $direccion, $email, $telefono);

        if ($stmt->execute()) {
            $mensaje = "Escuela creada correctamente.";
        } else {
            $mensaje = "Error al crear la escuela: " . $stmt->error;
        }

        $stmt->close();
        $db->close();
        return $mensaje;
    }

    /* GETTERS */

    function getCif() {
        return $this->cif;
    }

    function getDireccion() {
        return $this->direccion;
    }

    function getEmail() {
        return $this->email;
    }

    function getNombre() {
        return $this->nombre;
    }

    function getTelefono() {
        return $this->telefono;
    }

    /* SETTERS */

    function setCif($cif) {
        $this->cif = $cif;
    }

    function setDireccion($direccion) {
        $this->direccion = $direccion;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function setTelefono($telefono) {
        $this->telefono = $telefono;
    }
}

class Rango {

    private $cinturon;
    private $idRango;
    private $nivel;
    private $tiempoMinimoMeses;

    /**
     * Usaremos métodos estáticos para poder alimentar los parámetros del constructor tras una consulta a la BD para evitar creaciones de objetos innecesarias. 
     * Los atributos se definirán en el constructor de la clase hijo
     * @param type $datos
     */
    public function __construct($datos) {
        $this->cinturon = $datos['cinturon'];
        $this->idRango = $datos['id_rango'];
        $this->nivel = $datos['nivel'];
        $this->tiempoMinimoMeses = $datos['tiempo_minimo_meses'];
    }

    public function asignarRangoAlumnoBD($usuario, $idRango) {

        $db = crearConexionPrepare();
        $stmt = $db->prepare("INSERT INTO `rango_alumno`(`usuario`, `id_rango`) VALUES (?, ?)");
        $stmt->bind_param("si", $usuario, $idRango);

        if ($stmt->execute()) {
            $mensaje = "Rango asignado correctamente.";
        } else {
            $mensaje = "Error al asignar el rango al alumno: " . $stmt->error;
        }

        $stmt->close();
        $db->close();
    }

    public static function obtenerIdRangoAlumno($nombreUsuario) {
        $db = crearConexionPrepare();
        $stmt = $db->prepare("SELECT * FROM rango_alumno WHERE usuario  = ?");
//        $stmt = $db->prepare("
//    SELECT r.*, ra.usuario 
//    FROM rango_alumno ra 
//    JOIN rango r ON ra.id_rango = r.id_rango 
//    WHERE ra.usuario = ?
//");

        $stmt->bind_param("s", $nombreUsuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        $db->close();

        if ($res->num_rows > 0) {
            return $res->fetch_assoc();
        } else {
            return false;
        }
    }

    public static function obtenerRangoSiExiste($idRango) {
        $db = crearConexionPrepare();
        $stmt = $db->prepare("SELECT * FROM rango WHERE id_rango = ?");
        $stmt->bind_param("s", $idRango);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        $db->close();

        if ($res->num_rows > 0) {
            return new Rango($res->fetch_assoc());
        } else {
            return false;
        }
    }

    //Obtenemos todos los rangos en forma de array asociativo para trabajarlos luego
    public static function obtenerTodosRangos() {
        $db = crearConexionPrepare();
        $stmt = $db->prepare("SELECT * FROM rango");
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        $db->close();

        //en el controller recorremos los rangos (cada uno es un array asociativo) con foreach para sacar la info que queramos
        $rangos = [];
        while ($fila = $res->fetch_assoc()) {
            $rangos[] = $fila;
        }
        return $rangos;
    }

    public static function crearRangoBD($cinturon, $idRango, $nivel, $tiempoMinimoMeses) {
        $mensaje;
        $rango = self::obtenerRangoSiExiste($idRango);
        if ($rango) {
            return "Error: ya existe una rango con ese id.";
        }

        $db = crearConexionPrepare();
        $stmt = $db->prepare("INSERT INTO rango (`id_rango`, `cinturon`, `tiempo_minimo_meses`, `nivel`) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $idRango, $cinturon, $tiempoMinimoMeses, $nivel);

        if ($stmt->execute()) {
            $mensaje = "Rango creado correctamente.";
        } else {
            $mensaje = "Error al crear el rango: " . $stmt->error;
        }

        $stmt->close();
        $db->close();
        return $mensaje;
    }

    public function getAlumnosConRango($idRango) {
        $db = crearConexionPrepare();
        $stmt = $db->prepare("SELECT * FROM rango_alumno WHERE id_rango = ?");
        $stmt->bind_param("s", $idRango);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        $db->close();

        //en el controller recorremos los alumnos con foreach para sacar la info que queramos
        $alumnos = [];
        while ($fila = $res->fetch_assoc()) {
            $alumnos[] = $fila;
        }
        return $alumnos;
    }

    /* GETTERS */

    function getCinturon() {
        return $this->cinturon;
    }

    function getRango() {
        return $this->idRango;
    }

    function getNivel() {
        return $this->nivel;
    }

    function getTiempoMinimoMeses() {
        return $this->tiempoMinimoMeses;
    }

    /* SETTERS */

    function setCinturon($cinturon) {
        $this->cinturon = $cinturon;
    }

    function setIdRango($idRango) {
        $this->idRango = $idRango;
    }

    function setNivel($nivel) {
        $this->nivel = $nivel;
    }

    function setTiempoMinimoMeses($tiempoMinimoMeses) {
        $this->tiempoMinimoMeses = $tiempoMinimoMeses;
    }
}

class Clase {

    private $idClase;
    private $fechaHora;
    private $duracion;
    private $usuarioProfesor;
    private $tiempoRestante;
    private $calentamiento = "";
    private $rondasLuchas;

    /**
     * Usaremos métodos estáticos para poder alimentar los parámetros del constructor tras una consulta a la BD para evitar creaciones de objetos innecesarias. 
     * @param type $datos
     */
    public function __construct($datos) {
        $this->idClase = $datos['id_clase'];
        $this->fechaHora = $datos['fecha_hora'];
        $this->duracion = $datos['duracion'];
        $this->usuarioProfesor = $datos['usuario'];
        $this->tiempoRestante = $datos['duracion'];
    }

    public static function obtenerClase($idClase) {
        $db = crearConexionPrepare();
        $stmt = $db->prepare("SELECT * FROM clase WHERE id_clase  = ?");
        $stmt->bind_param("s", $idClase);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        $db->close();

        if ($res->num_rows > 0) {
            return new Clase($res->fetch_assoc());
        } else {
            return false;
        }
    }

    public static function obtenerTodasClasesFuturas() {
        $db = crearConexionPrepare();
        $stmt = $db->prepare("SELECT * FROM clase WHERE fecha_hora >= NOW() ORDER BY fecha_hora ASC;");
//        $stmt->bind_param("s", $idClase); //no es necesario porque no hay parámetros
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        $db->close();

        if ($res->num_rows === 0) {
            return false;
        }


        //en el controller recorremos las clases (cada una es un array asociativo) con foreach para sacar la info que queramos
        $clases = [];
        while ($fila = $res->fetch_assoc()) {
            $clases[] = new Clase($fila);
        }
        return $clases;
    }

    public static function crearClaseBD($fechaHora, $duracion, $usuarioProfesor) {
        $mensaje;
        

        $db = crearConexionPrepare();
        $stmt = $db->prepare("INSERT INTO `clase`(`fecha_hora`, `duracion`, `usuario`) VALUES (?, ?, ?)");
        $stmt->bind_param("sis",  $fechaHora, $duracion, $usuarioProfesor);

        if ($stmt->execute()) {
            $mensaje = "Clase creada correctamente.";
        } else {
            $mensaje = "Error al crear la clase: " . $stmt->error;
        }

        $stmt->close();
        $db->close();
        return $mensaje;
    }

    public static function cancelarClaseBD($idClase) {
        $mensaje;

        // Verificamos si la clase existe antes de intentar borrarla
        if (!self::obtenerClase($idClase)) {
            return "Error: no existe ninguna clase con ese id.";
        }

        $db = crearConexionPrepare();
        $stmt = $db->prepare("DELETE FROM `clase` WHERE `id_clase` = ?");
        $stmt->bind_param("i", $idClase);

        if ($stmt->execute()) {
            $mensaje = "Clase cancelada correctamente.";
        } else {
            $mensaje = "Error al cancelar la clase: " . $stmt->error;
        }

        $stmt->close();
        $db->close();
        return $mensaje;
    }

    public function asignarTecnicaBD(Tecnica $tecnica, $minutos) {
        $mensaje = "";

        $this->comprobarTiempoRestante($minutos);

        $this->tiempoRestante -= $minutos;
        $mensaje = "<p>Técnica asignadada a la clase correctamente. La técnica se practicará durante $minutos minutos, quedan $this->tiempoRestante minutos restantes en la clase</p>";

        return $mensaje;
    }

    /**
     * 
     * @param type $calentamiento
     * @param type $minutos
     * @return type
     */
    public function crearCalentamiento($calentamiento, $minutos) {
        $mensaje = "";

        $this->comprobarTiempoRestante($minutos);
        $this->tiempoRestante -= $minutos;
        $mensaje = "<p>Calentamiento asignadado a la clase correctamente. Se practicará durante $minutos minutos, quedan $this->tiempoRestante minutos restantes en la clase</p>";
        $this->setCalentamiento($calentamiento);

        return $mensaje;
    }

    public function asignarLuchas($rondas, $minutos) {
        $mensaje = "";

        $this->comprobarTiempoRestante($minutos);
        $this->tiempoRestante -= $rondas * $minutos;
        $this->setRondasLuchas($rondas);
        $mensaje = "<p>Tiempo de luchas asignadado a la clase correctamente. Se practicarán $rondas rondas de $minutos minutos, quedan $this->tiempoRestante minutos restantes en la clase</p>";

        return $mensaje;
    }

    private function comprobarTiempoRestante($tiempo) {
        $tiempoRestante = $this->tiempoRestante;
        if ($tiempoRestante - $tiempo < 0) {
            throw new Exception("Tiempo restante superado, asigna menos tiempo a esta actividad. Tiempo de la clase: $this->tiempoRestante minutos");
        } else {
            return true;
        }
    }

    private function esCompatiblePareja(Alumno $alumno1, Alumno $alumno2) {


        if ($alumno1->getCompanyero() !== null || $alumno2->getCompanyero() !== null) {
            throw new Exception("Alguno de los alumnos ya tiene pareja.");
        }

        return true;
    }

    public function crearParejas($alumnos) {
        $parejas = [];

        //reinicio de los compañeros de cada alumno
        foreach ($alumnos as $alumno) {
            $alumno->setCompanyero(null);
        }

        //cambiamos el ordend el array para que en cada ejecución sean parejas lo más diferentes posible
        shuffle($alumnos);

        //si tenemos un número impar de alumnos lo dejamos fuera indicándolo
        if (count($alumnos) % 2 !== 0) {
            $ultimo = $alumnos[count($alumnos) - 1];
            if ($ultimo->getCompanyero() === null) {
                echo '<p>Alumno sin pareja: ' . $ultimo->getNombre() . '</p>';
                $ultimo->setCompanyero("sin pareja, descansa");
            }
        }

        // incrementamos de dos en dos el contador, porque en cada vuelta tratamos una pareja 
        for ($i = 0; $i < count($alumnos) - 1; $i += 2) {
            $alumno1 = $alumnos[$i];
            $alumno2 = $alumnos[$i + 1];

            if ($alumno1->getCompanyero() === null && $alumno2->getCompanyero() === null) {
                $alumno1->setCompanyero($alumno2);
                $alumno2->setCompanyero($alumno1);
                $parejas[] = [$alumno1, $alumno2]; //hacemos un array por cada pareja que luego recorremos con un foreach en el controller donde cada posición es una pareja y sacamos individualmente el valor de cada alumno de ese "subarray"
            }
        }



        return $parejas;
    }

    /* GETTERS */

    function getIdClase() {
        return $this->idClase;
    }

    function getFechaHora() {
        return $this->fechaHora;
    }

    function getDuracion() {
        return $this->duracion;
    }

    function getUsuarioProfesor() {
        return $this->usuarioProfesor;
    }

    function getCalentamiento() {
        return $this->calentamiento;
    }

    function getRondasLuchas() {
        return $this->rondasLuchas;
    }

    public function getAlumnosClase($idClase) {
        $alumnos = [];
        $db = crearConexionPrepare();

        $stmt = $db->prepare("SELECT * FROM alumno_clase WHERE id_clase = ?");
        $stmt->bind_param("i", $idClase);

        //sacamos los usuarios de los alumnos
        $stmt->execute();
        $res = $stmt->get_result();

        //usamos los ids de los alumnos para crear los objetos Alumno y meterlos en el array
        while ($fila = $res->fetch_assoc()) {
            $usuario = $fila['usuario'];

            $alumnos[] = Alumno::obtenerAlumno($usuario);
        }


        $stmt->close();
        $db->close();
        return $alumnos;
    }

    /* SETTERS */

    function setIdClase($idClase) {
        $this->idClase = $idClase;
    }

    function setFechaHora($fechaHora) {
        $this->fechaHora = $fechaHora;
    }

    function setDuracion($duracion) {
        $this->duracion = $duracion;
    }

    function setUsuarioProfesor($usuarioProfesor) {
        $this->usuarioProfesor = $usuarioProfesor;
    }

    function setCalentamiento($calentamiento) {
        $this->calentamiento = $calentamiento;
    }

    function setRondasLuchas($rondasLuchas) {
        $this->rondasLuchas = $rondasLuchas;
    }
}

class Tecnica {

    private $idTecnica;
    private $tipo;
    private $dificultad;
    private $nombre;
    private $descripcion;

    /**
     * Usaremos métodos estáticos para poder alimentar los parámetros del constructor tras una consulta a la BD para evitar creaciones de objetos innecesarias. 
     * Los atributos se definirán en el constructor de la clase hijo
     * @param type $datos
     */
    public function __construct($datos) {
        $this->idTecnica = $datos['id_tecnica'];
        $this->tipo = $datos['tipo'];
        $this->dificultad = $datos['dificultad'];
        $this->nombre = $datos['nombre'];
        $this->descripcion = $datos['descripcion'];
    }

    public static function obtenerTecnicaSiExiste($idTecnica) {
        $db = crearConexionPrepare();
        $stmt = $db->prepare("SELECT * FROM tecnica WHERE id_tecnica = ?");
        $stmt->bind_param("s", $idTecnica);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        $db->close();

        if ($res->num_rows > 0) {
            return new Tecnica($res->fetch_assoc());
        } else {
            return false;
        }
    }

    //Obtenemos todas las mensualidades en forma de array asociativo para trabajarlas luego
    public static function obtenerTodasTecnicas() {
        $db = crearConexionPrepare();
        $stmt = $db->prepare("SELECT * FROM tecnica");
//        $stmt->bind_param("s", $cifEscuela); no necesario por no haber parámetros
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        $db->close();

        //en el controller recorremos las mensualidades (cada una es un array asociativo) con foreach para sacar la info que queramos
        $tecnicas = [];
        while ($fila = $res->fetch_assoc()) {
            $tecnicas[] = new Tecnica($fila);
        }
        return $tecnicas;
    }

    public static function crearTecnicaBD($idTecnica, $tipo, $dificultad, $nombre, $descripcion) {
        $mensaje;
        $tecnica = self::obtenerTecnicaSiExiste($idTecnica);
        if ($tecnica) {
            die("Error: ya existe una técnica con ese id.");
        }

        $db = crearConexionPrepare();
        $stmt = $db->prepare("INSERT INTO `tecnica`(`id_tecnica`, `nombre`, `tipo`, `dificultad`, `descripcion`) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $idTecnica, $tipo, $dificultad, $nombre, $descripcion);

        if ($stmt->execute()) {
            $mensaje = "Técnica creada correctamente.";
        } else {
            $mensaje = "Error al crear la técnica: " . $stmt->error;
        }

        $stmt->close();
        $db->close();
        return $mensaje;
    }

    /* GETTERS */

    function getIdTecnica() {
        return $this->idTecnica;
    }

    function getTipo() {
        return $this->tipo;
    }

    function getDificultad() {
        return $this->dificultad;
    }

    function getNombre() {
        return $this->nombre;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    /* SETTERS */

    function setIdTecnica($idTecnica) {
        $this->idTecnica = $idTecnica;
    }

    function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    function setDificultad($dificultad) {
        $this->dificultad = $dificultad;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }
}

class Pago {

    private $idPago;
    private $usuario;
    private $idMensualidad;
    private $fechaPago;
    private $metodoPago;

    /**
     * Usaremos métodos estáticos para poder alimentar los parámetros del constructor tras una consulta a la BD para evitar creaciones de objetos innecesarias. 
     * Los atributos se definirán en el constructor de la clase hijo
     * @param type $datos
     */
    public function __construct($datos) {
        $this->usuario = $datos['usuario'];
        $this->idPago = $datos['id_pago'];
        $this->idMensualidad = $datos['id_mensualidad'];
        $this->fechaPago = $datos['fecha_pago'];
        $this->metodoPago = $datos['metodo_pago'];
    }

    /**/

    public static function obtenerPago($idPago, $usuario) {
        $db = crearConexionPrepare();
        $stmt = $db->prepare("SELECT * FROM pago WHERE id_pago  = ? AND usuario=?");
        $stmt->bind_param("is", $idPago, $usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        $db->close();

        if ($res->num_rows > 0) {
            return new Pago($res->fetch_assoc());
        } else {
            return false;
        }
    }

    /**/

    public static function crearPagoBD($usuario, $idMensualidad, $fechaPago, $metodoPago) {
        $mensaje;

        //comentario: $idPago es un autoincrement, no lo ponemos en el INSERT
        $db = crearConexionPrepare();
        $stmt = $db->prepare("INSERT INTO `pago`(`usuario`,  `id_mensualidad`, `fecha_pago`, `metodo_pago`)  VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $usuario, $idMensualidad, $fechaPago, $metodoPago);

        if ($stmt->execute()) {
            $mensaje = "Pago  creado correctamente.";
        } else {
            $mensaje = "Error al crear el pago: " . $stmt->error;
        }

        $stmt->close();
        $db->close();
        return $mensaje;
    }

    public static function borrarPagoBD($idPago, $usuario) {
        $mensaje = "";
        // Verificamos si existe el pago antes de intentar borrarlo
        if (!self::obtenerPago($idPago, $usuario)) {
            throw new Exception("Error: no existe un pago para este usuario con el id seleccionado.");
        }

        $db = crearConexionPrepare();
        $stmt = $db->prepare("DELETE FROM `pago` WHERE `id_pago` = ? AND usuario=?");
        $stmt->bind_param("is", $idPago, $usuario);

        if ($stmt->execute()) {
            $mensaje = "Pago borrado correctamente.";
        } else {
            $mensaje = "Error al borrar el pago: " . $stmt->error;
        }

        $stmt->close();
        $db->close();

        return $mensaje;
    }

    public static function obtenerTodosPagos($usuario) {
        $db = crearConexionPrepare();
        $stmt = $db->prepare("SELECT * FROM pago WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        $db->close();

        //en el controller recorremos las mensualidades (cada una es un array asociativo) con foreach para sacar la info que queramos
        $pagos = [];
        while ($fila = $res->fetch_assoc()) {
            $pagos[] = new Pago($fila);
        }
        return $pagos;
    }

    /* GETTERS */

    function getIdPago() {
        return $this->idPago;
    }

    function getUsuario() {
        return $this->usuario;
    }

    function getidMensualidad() {
        return $this->idMensualidad;
    }

    function getFechaPago() {
        return $this->fechaPago;
    }

    function getMetodoPago() {
        return $this->metodoPago;
    }

    /* SETTERS */

    function setIdPago($idPago) {
        $this->idPago = $idPago;
    }

    function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    function setIdMensualidad($idMensualidad) {
        $this->idMensualidad = $idMensualidad;
    }

    function setFechaPago($fechaPago) {
        $this->fechaPago = $fechaPago;
    }

    function setMetodoPago($metodoPago) {
        $this->metodoPago = $metodoPago;
    }
}

class Mensualidad {

    private $idMensualidad;
    private $nombre;
    private $tipo;
    private $monto;
    private $cifEscuela;
    private $activa;

    /**
     * Usaremos métodos estáticos para poder alimentar los parámetros del constructor tras una consulta a la BD para evitar creaciones de objetos innecesarias. 
     * Los atributos se definirán en el constructor de la clase hijo
     * @param type $datos
     */
    public function __construct($datos) {

        $this->idMensualidad = $datos['id_mensualidad'];
        $this->nombre = $datos['nombre'];
        $this->tipo = $datos['tipo'];
        $this->monto = $datos['monto'];
        $this->cifEscuela = $datos['cif_escuela'];
        $this->activa = $datos['activa'];
    }

    //Obtenemos todas las mensualidades en forma de array asociativo para trabajarlas luego
    public static function obtenerTodasMensualidades($cifEscuela) {
        $db = crearConexionPrepare();
        $stmt = $db->prepare("SELECT * FROM mensualidad WHERE cif_escuela = ?");
        $stmt->bind_param("s", $cifEscuela);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        $db->close();

        //en el controller recorremos las mensualidades (cada una es un array asociativo) con foreach para sacar la info que queramos
        $mensualidades = [];
        while ($fila = $res->fetch_assoc()) {
            $mensualidades[] = new Mensualidad($fila);
        }
        return $mensualidades;
    }

    public static function obtenerMensualidad($idMensualidad) {
        $db = crearConexionPrepare();
        $stmt = $db->prepare("SELECT * FROM mensualidad WHERE id_mensualidad  = ?");
        $stmt->bind_param("s", $idMensualidad);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        $db->close();

        if ($res->num_rows > 0) {
            return new Mensualidad($res->fetch_assoc());
        } else {
            return false;
        }
    }

    public static function existeMensualidad($nombre, $cifEscuela) {
        $db = crearConexionPrepare();
        $stmt = $db->prepare("SELECT * FROM mensualidad WHERE nombre = ? AND cif_escuela = ?");
        $stmt->bind_param("ss", $nombre, $cifEscuela);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        $db->close();

        if ($res->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function crearMensualidadBD($nombre, $tipo, $monto, $cifEscuela, $activa = true) {
        $mensaje;
        $mensualidad = self::existeMensualidad($nombre, $cifEscuela);
        if ($mensualidad) {
            throw new Exception("Error: ya existe una mensualidad con ese nombre.");
        }

        $db = crearConexionPrepare();
        $stmt = $db->prepare("INSERT INTO `mensualidad`( `nombre`, `tipo`, `monto`,`cif_escuela`, `activa`) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdss", $nombre, $tipo, $monto, $cifEscuela, $activa);

        if ($stmt->execute()) {
            $mensaje = "Mensualidad  creada correctamente.";
        } else {
            $mensaje = "Error al crear la mensualidad: " . $stmt->error;
            throw new Exception($mensaje);
        }

        $stmt->close();
        $db->close();
        return $mensaje;
    }

    /**
     * Método auxiliar para usar en el borrado de mensualidades
     * @param type $idMensualidad
     * @param type $cifEscuela
     * @return type
     */
    public static function existeMensualidadPorIdYCif($idMensualidad, $cifEscuela) {
        $db = crearConexionPrepare();
        //Podemos también hacer un "SELECT 1" que devuelve el número 1 por cada fila que coincida con la condición del WHERE
        $stmt = $db->prepare("SELECT * FROM mensualidad WHERE id_mensualidad = ? AND cif_escuela = ?");
        $stmt->bind_param("is", $idMensualidad, $cifEscuela);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        $db->close();

        return $res->num_rows > 0;
    }

    public static function borrarMensualidadBD($idMensualidad) {
        $mensaje = "";

        $desactivada = false;
        $persona = Persona::renovarPersonaSesion();
        $cif_escuela = $persona->getCif();

        if (!self::existeMensualidadPorIdYCif($idMensualidad, $cif_escuela)) {
            throw new Exception("Error: No existe una mensualidad con ese ID en tu escuela.");
        }

        $db = crearConexionPrepare();
        $stmt = $db->prepare("UPDATE  mensualidad SET `activa` = ? WHERE `id_mensualidad` = ? AND cif_escuela = ?");
        $stmt->bind_param("sis", $desactivada, $idMensualidad, $cif_escuela);

        if ($stmt->execute()) {
            $mensaje = "Mensualidad borrada correctamente.";
        } else {
            $mensaje = "Error al borrar la mensualidad: " . $stmt->error;
        }

        $stmt->close();
        $db->close();

        return $mensaje;
    }

    /* GETTERS */

    function getIdMensualidad() {
        return $this->idMensualidad;
    }

    function getNombre() {
        return $this->nombre;
    }

    function getTipo() {
        return $this->tipo;
    }

    function getMonto() {
        return $this->monto;
    }

    function getCifEscuela() {
        return $this->cifEscuela;
    }

    public function getPrecioFinal($porcentajeIVA = 21) {
        return $this->monto * (1 + $porcentajeIVA / 100);
    }

    function getActiva() {
        return $this->activa;
    }

    /* SETTERS */

    function setIdMensualidad($idMensualidad) {
        $this->idMensualidad = $idMensualidad;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    function setMonto($monto) {
        $this->monto = $monto;
    }

    function setCifEscuela($cifEscuela) {
        $this->cifEscuela = $cifEscuela;
    }

    function setActiva($activa) {
        $this->activa = $activa;
    }
}
