<?php
// watsonx_api.php - VERSIÓN FINAL CON EXTRACCIÓN DE SÍNTOMAS

// La función getIAMToken no cambia. Se encarga de obtener el permiso de IBM.
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
        error_log("cURL Error en getIAMToken: " . $err);
        return null;
    }

    $decoded = json_decode($response, true);

    if (!isset($decoded['access_token'])) {
        error_log("Respuesta de IAM no contiene access_token: " . $response);
        return null;
    }

    return $decoded['access_token'];
}

function analizarSintomasConWatsonx($sintomas) {
    $token = getIAMToken(IBM_API_KEY);
    if (!$token) {
        return "Error Crítico: No se pudo obtener el token de autenticación de IBM.";
    }

    // LA PLANTILLA DE PROMPT MAESTRA (NUESTRO "ENTRENAMIENTO")
    // Aquí le damos el rol, las instrucciones y los ejemplos a la IA.
    $promptTemplate = <<<PROMPT
Eres un asistente médico experto en Procesamiento de Lenguaje Natural.
Tu tarea es leer el siguiente texto proporcionado por un paciente y extraer únicamente los síntomas clínicos mencionados. Formatea la salida como una lista simple con guiones. Si el paciente no menciona síntomas claros o solo pide información administrativa, responde con 'No se reportan síntomas específicos.'. Normaliza los términos coloquiales a términos clínicos (ej. 'dolor de guata' a 'Dolor de estómago').
---
Texto del Paciente: "me duele la guata y la cabeza."
Resultado Esperado:
- Dolor de estómago
- Dolor de cabeza
---
Texto del Paciente: "No he tenido fiebre, pero llevo dos días con tos seca y me siento muy cansado, sobre todo por la tarde. También me pican un poco los ojos."
Resultado Esperado:
- Tos seca
- Fatiga
- Picazón de ojos
---
Texto del Paciente: "Solo llamo para agendar una hora de control."
Resultado Esperado:
No se reportan síntomas específicos.
---
Texto del Paciente: "{$sintomas}"
Resultado Esperado:
PROMPT;

    // LA CONFIGURACIÓN DE LA LLAMADA A LA API
    $payload = [
        // Hemos cambiado a un modelo más adecuado para seguir instrucciones.
        'model_id' => 'google/flan-t5-xxl',
        'input' => $promptTemplate, // Usamos nuestra nueva plantilla como entrada
        'parameters' => [
            'decoding_method' => 'greedy',
            'max_new_tokens' => 100, // Una lista de síntomas no necesita ser muy larga
            'repetition_penalty' => 1.5 // Ayuda a que la IA sea más concisa
        ],
        'project_id' => WATSONX_PROJECT_ID
    ];

    $apiUrl = WATSONX_URL . '/ml/v1-beta/generation/text?version=2023-05-29';

    // 3. LA EJECUCIÓN DE LA LLAMADA (Sin cambios aquí)
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

    if (isset($decoded['results'][0]['generated_text'])) {
        return trim($decoded['results'][0]['generated_text']);
    } else {
        $errorMessage = "Respuesta de Error de Watsonx: " . json_encode($decoded);
        error_log($errorMessage);
        return $errorMessage;
    }
}
?>