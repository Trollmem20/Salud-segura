<?php
// Configuración de la Base de Datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); 
define('DB_PASS', '12345');     
define('DB_NAME', 'hospital_local');

// Configuración de IBM Watsonx
define('IBM_API_KEY', 'your_ibm_api_key_here');
define('WATSONX_PROJECT_ID', 'your_project_id_here');
define('WATSONX_URL', 'https://us-south.ml.cloud.ibm.com'); 

// Iniciar la sesión de PHP para rastrear al usuario logueado
session_start();
?>