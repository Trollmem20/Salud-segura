<?php
// VERSIÓN DEPURADORA DE watsonx_api.php

function getIAMToken($apiKey) {
    $url = 'https://iam.cloud.ibm.com/identity/token';
    $data = 'grant_type=urn:ibm:params:oauth:grant-type:apikey&apikey=' . urlencode($apiKey);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        error_log("cURL Error en getIAMToken: " . $err); // Escribe en el log de errores de Apache
        return null;
    }

    $decoded = json_decode($response, true);

    if (!isset($decoded['access_token'])) {
        error_log("Respuesta de IAM no contiene access_token: " . $response); // Escribe en el log de errores de Apache
        return null;
    }

    return $decoded['access_token'];
}

function analizarSintomasConWatsonx($sintomas) {
    $token = getIAMToken(IBM_API_KEY);
    if (!$token) {
        return "Error Crítico: No se pudo obtener el token de autenticación de IBM. Revisa tu IBM_API_KEY en config.php y que la extensión cURL de PHP esté activada.";
    }

    $prompt = "Analiza los siguientes síntomas de un paciente desde una perspectiva clínica y proporciona un resumen conciso de posibles áreas de interés y preguntas de seguimiento relevantes para un médico. No des un diagnóstico. Síntomas: \n\n" . $sintomas;

    $payload = [
        'model_id' => 'google/flan-ul2',
        'input' => $prompt,
        'parameters' => [ 'max_new_tokens' => 200 ],
        'project_id' => WATSONX_PROJECT_ID
    ];

    $apiUrl = WATSONX_URL . '/ml/v1-beta/generation/text?version=2023-05-29';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return "Error de Conexión cURL a Watsonx: " . $err;
    }

    $decoded = json_decode($response, true);

    // --- Lógica de Depuración Mejorada ---
    if (isset($decoded['results'][0]['generated_text'])) {
        return trim($decoded['results'][0]['generated_text']);
    } else {
        // Si no hay resultado, devolvemos el error completo que nos dio IBM
        $errorMessage = "Respuesta de Error de Watsonx: " . json_encode($decoded);
        error_log($errorMessage); // También lo escribimos en el log de Apache
        return $errorMessage;
    }
}
?>