<?php
require_once 'config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// -- Crear un paciente --
$username_paciente = 'juanperez';
$password_paciente = 'paciente123';
$hash_paciente = password_hash($password_paciente, PASSWORD_DEFAULT);
$nombre_paciente = 'Juan Pérez';
$sql_paciente = "INSERT INTO usuarios (username, password_hash, role, nombre_completo) VALUES ('$username_paciente', '$hash_paciente', 'paciente', '$nombre_paciente')";

if ($conn->query($sql_paciente) === TRUE) {
    echo "Usuario paciente creado exitosamente.<br>";
} else {
    echo "Error creando paciente: " . $conn->error . "<br>";
}

// -- Crear un doctor --
$username_doctor = 'dramartinez';
$password_doctor = 'doctor123';
$hash_doctor = password_hash($password_doctor, PASSWORD_DEFAULT);
$nombre_doctor = 'Dra. Ana Martínez';
$sql_doctor = "INSERT INTO usuarios (username, password_hash, role, nombre_completo) VALUES ('$username_doctor', '$hash_doctor', 'doctor', '$nombre_doctor')";

if ($conn->query($sql_doctor) === TRUE) {
    echo "Usuario doctor creado exitosamente.<br>";
} else {
    echo "Error creando doctor: " . $conn->error . "<br>";
}

$conn->close();
?>