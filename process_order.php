<?php
require('fpdf/fpdf.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '256M');

// Conexión a la base de datos
$host = 'localhost';
$user = 'root';
$password = '1234567890';
$dbname = 'nachos';

$conn = new mysqli($host, $user, $password, $dbname);

// Comprobar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener los precios dinámicos desde la base de datos
$precios = [];
$sql = "SELECT nombre, precio FROM productos";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $precios[$row['nombre']] = $row['precio'];
    }
} else {
    die("No se encontraron productos.");
}

// Obtener datos del formulario
$name = isset($_POST['name']) ? $_POST['name'] : 'Cliente Anónimo';
$order = isset($_POST['order']) ? $_POST['order'] : 'Sin orden';
$nachosComments = isset($_POST['nachosComments']) ? $_POST['nachosComments'] : 'Ninguno';
$elotesComments = isset($_POST['elotesComments']) ? $_POST['elotesComments'] : 'Ninguno';
$total = 0;

// Ingredientes opcionales
$carne = isset($_POST['carne']) ? 1 : 0;
$queso = isset($_POST['queso']) ? 1 : 0;
$escabeche = isset($_POST['escabeche']) ? 1 : 0;
$jalapenos = isset($_POST['jalapenos']) ? 1 : 0;
$cebolla = isset($_POST['cebolla']) ? 1 : 0;
$elotes = isset($_POST['elotes']) ? (int)$_POST['elotes'] : 0;

// Precios base (obtenidos de la base de datos)
$precio_base = isset($precios['nachos']) ? $precios['nachos'] : 1.5;
$precio_carne = isset($precios['carne']) ? $precios['carne'] : 0.25;
$precio_queso = isset($precios['queso']) ? $precios['queso'] : 0.25;
$precio_escabeche = isset($precios['escabeche']) ? $precios['escabeche'] : 0.25;
$precio_jalapenos = isset($precios['jalapenos']) ? $precios['jalapenos'] : 0.25;
$precio_cebolla = isset($precios['cebolla']) ? $precios['cebolla'] : 0.25;
$precio_elote = isset($precios['elote']) ? $precios['elote'] : 1;

// Calcular el total
$total = $precio_base;
$total += $carne ? $precio_carne : 0;
$total += $queso ? $precio_queso : 0;
$total += $escabeche ? $precio_escabeche : 0;
$total += $jalapenos ? $precio_jalapenos : 0;
$total += $cebolla ? $precio_cebolla : 0;
$total += $elotes * $precio_elote;

// Número de factura autoincremental
$numero_factura = 0;
$factura_file = 'factura_numero.txt';
if (file_exists($factura_file)) {
    $numero_factura = (int) file_get_contents($factura_file);
    $numero_factura++;
} else {
    $numero_factura = 1;
}
file_put_contents($factura_file, $numero_factura);

// Crear PDF de la factura
$pdf = new FPDF();
$pdf->AddPage();

// Estilo de colores
$color_borde = [173, 216, 230];
$color_fondo_celda = [240, 255, 255];
$color_texto = [44, 62, 80];

// Agregar logo de la empresa
// Agregar logo de la empresa (ajustamos el tamaño a 20 de ancho)
$pdf->Image('img/LogoNACHOS-01.png', 10, 6, 20); // El tercer parámetro es el nuevo ancho

$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor($color_texto[0], $color_texto[1], $color_texto[2]);
$pdf->Cell(80); // Espaciado al centro
$pdf->Cell(30, 10, 'Factura de Orden', 0, 1, 'C');
$pdf->Ln(10);

// Detalles de la factura
$pdf->SetFont('Arial', '', 12);
$pdf->SetFillColor($color_fondo_celda[0], $color_fondo_celda[1], $color_fondo_celda[2]);
$pdf->Cell(0, 10, utf8_decode("Factura N°: $numero_factura"), 0, 1, 'L', true);
$pdf->Cell(0, 10, utf8_decode("Nombre del Cliente: $name"), 0, 1, 'L', true);

$pdf->Ln(5);

// Ingredientes
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Detalles del pedido', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode("Con Carne: " . ($carne ? "Sí" : "No")), 0, 1, 'L', true);
$pdf->Cell(0, 10, utf8_decode("Con Queso: " . ($queso ? "Sí" : "No")), 0, 1, 'L', true);
$pdf->Cell(0, 10, utf8_decode("Con Escabeche: " . ($escabeche ? "Sí" : "No")), 0, 1, 'L', true);
$pdf->Cell(0, 10, utf8_decode("Con Chile Jalapeño: " . ($jalapenos ? "Sí" : "No")), 0, 1, 'L', true);
$pdf->Cell(0, 10, utf8_decode("Con Cebolla: " . ($cebolla ? "Sí" : "No")), 0, 1, 'L', true);
$pdf->Cell(0, 10, utf8_decode("Cantidad de Elotes Locos: $elotes"), 0, 1, 'L', true);
$pdf->Cell(0, 10, utf8_decode("Comentarios Nachos: $nachosComments"), 0, 1, 'L', true);
$pdf->Cell(0, 10, utf8_decode("Comentarios Elotes: $elotesComments"), 0, 1, 'L', true);

// Precio total
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(34, 139, 34); // Verde para el total
$pdf->Cell(0, 10, utf8_decode("Total: $" . number_format($total, 2)), 0, 1, 'C');

// Redes sociales
$pdf->Ln(20);
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor($color_texto[0], $color_texto[1], $color_texto[2]);
$pdf->Cell(0, 10, utf8_decode("Síguenos en nuestras redes sociales"), 0, 1, 'C');
$pdf->Ln(5);
$pdf->Image('img/facebook.png', 60, 250, 10); // Logo Facebook
$pdf->Cell(0, 10, utf8_decode("Facebook: @nombrePagina"), 0, 1, 'C');
$pdf->Image('img/instagram.png', 90, 250, 10); // Logo Instagram
$pdf->Cell(0, 10, utf8_decode("Instagram: @nombreUsuario"), 0, 1, 'C');

// Establecer la zona horaria para El Salvador
date_default_timezone_set('America/El_Salvador');

// Obtener la fecha y hora actual
$fecha_actual = date("Y-m-d H:i:s"); // Formato: Año-Mes-Día Hora:Minuto:Segundo

// Agregar la fecha y hora a la factura
$pdf->Ln(10);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 10, utf8_decode("Fecha y Hora: $fecha_actual"), 0, 1, 'C');

// Guardar PDF
$pdf_filename = "factura_$numero_factura.pdf";
$pdf->Output('F', $pdf_filename);

// Mostrar confirmación con estilos
echo "
    <div style='text-align: center; background-color: #f0f8ff; padding: 20px; border-radius: 10px; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #2c3e50;'>Orden enviada exitosamente</h2>
        <p style='font-size: 18px;'>Gracias por tu orden, <strong>$name</strong>. El total de tu orden es <strong>$" . number_format($total, 2) . "</strong>.</p>
        <p>Tu factura ha sido generada con el número <strong>$numero_factura</strong>. Puedes descargarla aquí:</p>
        <a href='$pdf_filename' download style='background-color: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Descargar Factura</a>
    </div>
";
?>
