<?php
// 1. Recibir los datos de MDirector
// Usamos el operador null coalescing (??) por seguridad si algún campo viene vacío
$nombre           = $_POST['nombre'] ?? '';
$apellido_paterno = $_POST['apellido_paterno'] ?? '';
$apellido_materno = $_POST['apellido_materno'] ?? '';
$email            = $_POST['email'] ?? '';
$celular          = $_POST['celular'] ?? '';
$documento_tipo   = $_POST['documento'] ?? ''; // CC o CE
$documento_numero = $_POST['cedula'] ?? '';
$monto            = (int)($_POST['monto'] ?? 0); // Lo convertimos a entero por seguridad
$terminos         = isset($_POST['terminos']) && $_POST['terminos'] === 'on' ? true : false;

// Nota: Como MDirector solo envía 'celular', lo clonaremos en 'phone' 
// ya que la API pide ambos como obligatorios.
$telefono_fijo    = $celular; 

// 2. Armar la estructura JSON para Opportunitex
$payload = [
    "record" => [
        "data" => [
            "system_id" => 2, // ID numérico para RTD Colombia
            "user" => [
                "names" => $nombre,
                "first_surname" => $apellido_paterno,
                "second_surname" => $apellido_materno,
                "email" => $email,
                "phone" => $telefono_fijo,
                "mobile" => $celular,
                "country" => "CO",
                "state" => "Bogota", // Puedes dejarlo fijo o pedirlo en el form si lo necesitas a futuro
                "postal_code" => "",
                "contact_by" => "EMAIL",
                "contact_by_wa" => true,
                "terms_conditions" => $terminos
            ],
            "debts" => [
                [
                    "borrower_institute" => "Por definir", // Ajusta según necesites
                    "debt_amount" => $monto,
                    "months_behind" => "",
                    "credit_type" => ""
                ]
            ],
            "mkt" => [
                "landing" => "https://tulanding.com", // Cambia esto por la URL de tu landing
                "utm_source" => $_POST['utm_source'] ?? 'mdirector',
                "utm_medium" => "api",
                // Aquí inyectamos los campos obligatorios directamente:
                "utm_term" => "bravo",
                "utm_flow" => "datacredito",
                "utm_assignment" => "datacredito" 
            ],
            "identity_metadata" => [
                "type" => $documento_tipo,
                "number" => $documento_numero
            ]
        ]
    ]
];

// Convertir el arreglo a formato JSON
$json_payload = json_encode($payload);

// 3. Enviar a la API mediante cURL
$endpoint = "https://opportunitex.sandbox.resuelve.io/api/records";

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($json_payload)
]);
// Descomentar la siguiente línea si el servidor presenta problemas con los certificados SSL locales
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// 4. Manejo de respuesta (Para depuración)
if ($http_status == 200) {
    // Éxito. Aquí puedes redirigir al usuario o imprimir un 'ok' para MDirector
    echo "Lead enviado exitosamente a Opportunitex. Respuesta: " . $response;
} else {
    // Fallo en la validación (Ej. 400 Bad Request) o error de conexión
    echo "Error enviando a Opportunitex. Código HTTP: $http_status. Error cURL: $curl_error. Respuesta: $response";
}
?>
