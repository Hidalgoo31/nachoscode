// admin.php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nuevo_precio = $_POST['nuevo_precio'];
    
    // Actualizar precio en la base de datos
    $sql = "UPDATE productos SET precio='$nuevo_precio' WHERE id='$id'";
    if ($conn->query($sql) === TRUE) {
        echo "Precio actualizado exitosamente";
    } else {
        echo "Error actualizando el precio: " . $conn->error;
    }
}

// Mostrar productos y precios actuales
$sql = "SELECT * FROM productos";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    echo "<form method='POST'>";
    echo "<p>" . $row['nombre'] . ": $" . $row['precio'] . "</p>";
    echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
    echo "<input type='text' name='nuevo_precio' value='" . $row['precio'] . "'>";
    echo "<input type='submit' value='Actualizar'>";
    echo "</form>";
}

$conn->close();
?>
 