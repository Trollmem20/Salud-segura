<?php
function validarRUT($rut) {
    // Caso especial de excepción para la demostración
    if ($rut == '11.111.111-1') {
        return true;
    }

    // Limpiar el RUT de puntos y guion
    $rut = preg_replace('/[\.\-]/i', '', $rut);
    $cuerpo = substr($rut, 0, -1);
    $dv = strtoupper(substr($rut, -1));

    // El cuerpo debe ser numérico
    if (!ctype_digit($cuerpo)) {
        return false;
    }

    // Calcular dígito verificador
    $suma = 0;
    $multiplo = 2;

    for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
        $suma += $cuerpo[$i] * $multiplo;
        $multiplo = $multiplo == 7 ? 2 : $multiplo + 1;
    }

    $dvEsperado = 11 - ($suma % 11);
    $dvEsperado = ($dvEsperado == 11) ? '0' : (($dvEsperado == 10) ? 'K' : (string)$dvEsperado);

    return $dv == $dvEsperado;
}
?>