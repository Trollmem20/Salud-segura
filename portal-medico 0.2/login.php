<?php
require_once 'config.php';
require_once 'rut_validator.php';
$error = '';

// Verificar si se envió un formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // --- Lógica para el Login del DOCTOR ---
    if (isset($_POST['login_type']) && $_POST['login_type'] == 'doctor') {
        $username = $conn->real_escape_string($_POST['username']);
        $sql = "SELECT * FROM usuarios WHERE username = '$username' AND role = 'doctor'";
        $result = $conn->query($sql);

        if ($result && $result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($_POST['password'], $user['password_hash'])) {
                // Login exitoso
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['nombre_completo'] = $user['nombre_completo'];
                header("location: doctor_dashboard.php");
                exit;
            }
        }
        $error = "Usuario o contraseña de Doctor incorrectos.";
    }

    // --- Login del PACIENTE ---
    elseif (isset($_POST['login_type']) && $_POST['login_type'] == 'paciente') {
        $rut = $_POST['rut'];
        if (validarRUT($rut)) {
            $rut_db = $conn->real_escape_string($rut);
            // Excepción
            if ($rut === '11.111.111-1') {
                // Crear una sesión de "invitado" o buscar un usuario demo
                // Aquí, por simplicidad, lo dejaremos pasar sin buscar en DB
                $_SESSION['role'] = 'paciente';
                $_SESSION['nombre_completo'] = 'Paciente de Demostración';
                $_SESSION['user_id'] = 0; // ID especial para demo
                header("location: patient_dashboard.php");
                exit;
            } else {
                $sql = "SELECT * FROM usuarios WHERE rut = '$rut_db' AND role = 'paciente'";
                $result = $conn->query($sql);
                if ($result && $result->num_rows == 1) {
                    $user = $result->fetch_assoc();
                    // Login exitoso
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['nombre_completo'] = $user['nombre_completo'];
                    header("location: patient_dashboard.php");
                    exit;
                } else {
                    $error = "RUT no encontrado en nuestros registros de pacientes.";
                }
            }
        } else {
            $error = "El RUT ingresado no es válido.";
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Portal Médico</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f0f2f5; margin: 0; }
        .login-container { width: 350px; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 8px; overflow: hidden; }
        .tabs { display: flex; background-color: #eee; }
        .tab-link { flex: 1; padding: 15px; text-align: center; cursor: pointer; font-weight: bold; color: #555; border-bottom: 3px solid transparent; }
        .tab-link.active { color: #007bff; border-bottom: 3px solid #007bff; }
        .form-content { padding: 2rem; }
        .form-section { display: none; }
        .form-section.active { display: block; }
        input { display: block; margin-bottom: 1rem; padding: 10px; width: calc(100% - 20px); border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 1rem; width: 100%; background-color: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px; font-size: 16px; }
        .error-message { color: #d93025; text-align: center; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="tabs">
            <div class="tab-link active" onclick="showForm('paciente')">Soy Paciente</div>
            <div class="tab-link" onclick="showForm('doctor')">Soy Doctor</div>
        </div>

        <div class="form-content">
            <div id="paciente-form" class="form-section active">
                <h3 style="text-align:center;">Acceso Paciente</h3>
                <form action="login.php" method="post">
                    <input type="hidden" name="login_type" value="paciente">
                    <input type="text" name="rut" placeholder="Ingrese su RUT (ej: 12.345.678-9)" required>
                    <button type="submit">Ingresar con RUT</button>
                </form>
            </div>

            <div id="doctor-form" class="form-section">
                <h3 style="text-align:center;">Acceso Doctor</h3>
                <form action="login.php" method="post">
                    <input type="hidden" name="login_type" value="doctor">
                    <input type="text" name="username" placeholder="Nombre de usuario" required>
                    <input type="password" name="password" placeholder="Contraseña" required>
                    <button type="submit">Ingresar</button>
                </form>
            </div>

            <?php if($error): ?><p class="error-message"><?php echo $error; ?></p><?php endif; ?>
        </div>
    </div>

    <script>
        function showForm(formName) {
            document.getElementById('paciente-form').classList.remove('active');
            document.getElementById('doctor-form').classList.remove('active');
            document.querySelector('.tab-link.active').classList.remove('active');

            document.getElementById(formName + '-form').classList.add('active');
            event.currentTarget.classList.add('active');
        }
    </script>
</body>
</html>