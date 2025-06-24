<?php
require_once 'config.php';
// Proteger la página: solo para pacientes logueados
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'paciente') {
    header("location: login.php");
    exit;
}

$mensaje_exito = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sintomas'])) {
    require_once 'watsonx_api.php';
    
    $sintomas = $_POST['sintomas'];
    $analisis = analizarSintomasConWatsonx($sintomas);
    
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $stmt = $conn->prepare("INSERT INTO consultas (id_paciente, sintomas_paciente, analisis_watsonx) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $_SESSION['user_id'], $sintomas, $analisis);
    
    if($stmt->execute()){
        $mensaje_exito = "Sus síntomas han sido enviados y analizados correctamente. El médico revisará los resultados.";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Portal del Paciente</title>
    <style> body { font-family: sans-serif; padding: 2rem; } </style>
</head>
<body>
    <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_completo']); ?></h1>
    <a href="logout.php">Cerrar sesión</a>
    <hr>
    <h3>Describa sus síntomas</h3>
    <form action="" method="post">
        <textarea name="sintomas" rows="10" cols="80" required></textarea><br>
        <button type="submit">Enviar síntomas para análisis</button>
    </form>
    <?php if($mensaje_exito): ?><p style="color:green;"><?php echo $mensaje_exito; ?></p><?php endif; ?>
</body>
</html>