
    <?php
        if($_SERVER["REQUEST_METHOD"] == "POST"){

        $tmp_dni = $_POST["dni"];
        $tmp_nombre = $_POST["nombre"];
        $tmp_apellidos = $_POST["apellidos"];
        $tmp_direccion = $_POST["direccion"];
        $tmp_telefono = $_POST["telefono"];
        $tmp_email = $_POST["email"];
        $tmp_contrasena = $_POST["contrasena"];
        $tmp_foto = $_POST["foto"];

        $correcto = true;

        /* VALIDACION DNI */

        $tmp_dni = htmlspecialchars($tmp_dni);
        $tmp_dni = trim($tmp_dni);

        if($tmp_dni == ""){
            $err_dni = "Inserta un DNI";
            $correcto = false;
        }elseif(!preg_match("/^[0-9]{8}[A-Za-z]$/",$tmp_dni)){
            $err_dni = "Formato de DNI incorrecto";
            $correcto = false;
        }else{
            $dni = $tmp_dni;
        }

        /* VALIDACION NOMBRE */

        $tmp_nombre = htmlspecialchars($tmp_nombre);
        $tmp_nombre = trim($tmp_nombre);

        if($tmp_nombre == ""){
            $err_nombre = "Inserta un nombre";
            $correcto = false;
        }else{
            $nombre = $tmp_nombre;
        }

        /* VALIDACION APELLIDOS */

        $tmp_apellidos = htmlspecialchars($tmp_apellidos);
        $tmp_apellidos = trim($tmp_apellidos);

        if($tmp_apellidos == ""){
            $err_apellidos = "Inserta los apellidos";
            $correcto = false;
        }else{
            $apellidos = $tmp_apellidos;
        }

        /* VALIDACION DIRECCION */

        $tmp_direccion = htmlspecialchars($tmp_direccion);
        $tmp_direccion = trim($tmp_direccion);

        if($tmp_direccion == ""){
            $err_direccion = "Inserta una dirección";
            $correcto = false;
        }else{
            $direccion = $tmp_direccion;
        }

        /* VALIDACION TELEFONO */

        $tmp_telefono = htmlspecialchars($tmp_telefono);
        $tmp_telefono = trim($tmp_telefono);

        if($tmp_telefono == ""){
            $err_telefono = "Inserta un teléfono";
            $correcto = false;
        }else{
            $telefono = $tmp_telefono;
        }

        /* VALIDACION EMAIL */

        $tmp_email = htmlspecialchars($tmp_email);
        $tmp_email = trim($tmp_email);

        if($tmp_email == ""){
            $err_email = "Inserta un email";
            $correcto = false;
        }elseif(!filter_var($tmp_email, FILTER_VALIDATE_EMAIL)){
            $err_email = "Email no válido";
            $correcto = false;
        }else{
            $email = $tmp_email;
        }

        /* VALIDACION CONTRASEÑA */

        $tmp_contrasena = htmlspecialchars($tmp_contrasena);
        $tmp_contrasena = trim($tmp_contrasena);

        if($tmp_contrasena == ""){
            $err_contrasena = "Inserta una contraseña";
            $correcto = false;
        }elseif(strlen($tmp_contrasena) < 8){
            $err_contrasena = "Debe tener al menos 8 caracteres";
            $correcto = false;
        }elseif(!preg_match("/[0-9]/",$tmp_contrasena)){
            $err_contrasena = "Debe contener al menos un número";
            $correcto = false;
        }else{
            $contrasena = $tmp_contrasena;
        }

        /* VALIDACION FOTO */

        $tmp_foto = htmlspecialchars($tmp_foto);
        $tmp_foto = trim($tmp_foto);

        if($tmp_foto == ""){
            $err_foto = "Inserta una foto";
            $correcto = false;
        }else{
            $foto = $tmp_foto;
        }


        /* INSERT */

        if($correcto){

        $contrasena_cifrada = password_hash($contrasena, PASSWORD_DEFAULT);

        $consulta = "INSERT INTO USUARIO 
        (DNI, Nombre, Apellidos, Direccion, Foto, Telefono, Email, Contrasena_encriptada) 
        VALUES 
        ('$dni','$nombre','$apellidos','$direccion','$foto','$telefono','$email','$contrasena_cifrada')";

        if($_conexion->query($consulta)){
        echo "<div class='alert alert-success'>Usuario registrado correctamente</div>";
        }else{
        echo "<div class='alert alert-danger'>Error al registrar usuario</div>";
        }

        }

        }
    ?>
 