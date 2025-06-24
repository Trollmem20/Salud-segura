<?php
require_once 'config.php';
// Proteger la página: solo para doctores
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("location: login.php");
    exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
// Consulta para obtener las consultas con el nombre del paciente
$sql = "SELECT c.*, u.nombre_completo FROM consultas c JOIN usuarios u ON c.id_paciente = u.id ORDER BY c.fecha_consulta DESC";
$consultas = $conn->query($sql);
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Portal del Doctor</title>
    <style>
        body { font-family: sans-serif; padding: 2rem; }
        .consulta { border: 1px solid #ccc; padding: 1rem; margin-bottom: 1rem; border-radius: 8px; }
        .analisis { background-color: #eef; padding: 0.5rem; margin-top: 0.5rem; border-left: 3px solid #007bff; }
    </style>
</head>
<body>
    <h1>Bienvenida, <?php echo htmlspecialchars($_SESSION['nombre_completo']); ?></h1>
    <a href="logout.php">Cerrar sesión</a>
    <hr>
    <h3>Consultas de Pacientes Recientes</h3>
    <?php if ($consultas->num_rows > 0): ?>
        <?php while($row = $consultas->fetch_assoc()): ?>
            <div class="consulta">
                <p><strong>Paciente:</strong> <?php echo htmlspecialchars($row['nombre_completo']); ?></p>
                <p><strong>Fecha:</strong> <?php echo $row['fecha_consulta']; ?></p>
                <p><strong>Síntomas reportados:</strong><br><?php echo nl2br(htmlspecialchars($row['sintomas_paciente'])); ?></p>
                <div class="analisis">
                    <strong>Análisis de Watsonx:</strong><br>
                    <?php echo nl2br(htmlspecialchars($row['analisis_watsonx'])); ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No hay consultas nuevas.</p>
    <?php endif; ?>
</body>
</html>