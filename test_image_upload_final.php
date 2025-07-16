<?php
/**
 * Test Final del Sistema de Subida de Imágenes
 * Verifica que todo el flujo funcione correctamente
 */

echo "<h2>🧪 Test Final del Sistema de Subida de Imágenes</h2>";

// 1. Verificar configuración
echo "<h3>1. ✅ Configuración del Sistema</h3>";
echo "GD: " . (extension_loaded('gd') ? "✅ Habilitado" : "❌ Deshabilitado") . "<br>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";

// 2. Verificar carpetas
echo "<h3>2. ✅ Carpetas de Imágenes</h3>";
$folders = [
    'assets/images/',
    'assets/images/products/',
    'assets/images/categories/',
    'assets/images/settings/',
    'assets/images/test/'
];

foreach ($folders as $folder) {
    if (file_exists($folder)) {
        echo "✅ $folder existe";
        if (is_writable($folder)) {
            echo " y es escribible";
        } else {
            echo " pero NO es escribible";
        }
        echo "<br>";
    } else {
        echo "❌ $folder NO existe<br>";
    }
}

// 3. Verificar archivos de sistema
echo "<h3>3. ✅ Archivos del Sistema</h3>";
$files = [
    'admin/upload_handler.php',
    'assets/js/admin.js',
    'admin/products.php',
    'admin/categories.php',
    'admin/settings.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file existe<br>";
    } else {
        echo "❌ $file NO existe<br>";
    }
}

// 4. Test de subida
echo "<h3>4. 🧪 Test de Subida</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_image'])) {
    $file = $_FILES['test_image'];
    
    echo "📁 Archivo recibido: " . $file['name'] . "<br>";
    echo "📏 Tamaño: " . number_format($file['size']) . " bytes<br>";
    echo "🎨 Tipo: " . $file['type'] . "<br>";
    echo "⚠️ Error: " . $file['error'] . "<br>";
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'assets/images/test/';
        $fileName = 'test_final_' . uniqid() . '_' . basename($file['name']);
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            echo "✅ Archivo subido exitosamente a: $filePath<br>";
            echo "<img src='$filePath' style='max-width: 200px; border: 2px solid #28a745; border-radius: 8px;'><br>";
            
            // Verificar que se puede leer
            if (file_exists($filePath)) {
                echo "✅ Archivo existe y se puede leer<br>";
                echo "📏 Tamaño final: " . number_format(filesize($filePath)) . " bytes<br>";
            } else {
                echo "❌ Error: El archivo no se puede leer después de subir<br>";
            }
        } else {
            echo "❌ Error al mover el archivo<br>";
        }
    } else {
        echo "❌ Error en la subida: " . $file['error'] . "<br>";
    }
}

// 5. Instrucciones
echo "<h3>5. 📋 Instrucciones para Probar</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #007bff;'>";
echo "<strong>Para probar el sistema completo:</strong><br>";
echo "1. Ve a <a href='admin/products.php' target='_blank'>Productos</a> y crea un nuevo producto<br>";
echo "2. Ve a <a href='admin/categories.php' target='_blank'>Categorías</a> y crea una nueva categoría<br>";
echo "3. Ve a <a href='admin/settings.php' target='_blank'>Configuraciones</a> y sube un logo<br>";
echo "4. Abre la consola del navegador (F12) para ver los logs<br>";
echo "</div>";

// 6. Formulario de prueba
echo "<h3>6. 🧪 Formulario de Prueba Rápida</h3>";
?>
<form method="post" enctype="multipart/form-data" style="background: #e9ecef; padding: 20px; border-radius: 8px;">
    <div class="mb-3">
        <label class="form-label"><strong>Selecciona una imagen para probar:</strong></label>
        <input type="file" name="test_image" accept="image/*" required class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">🚀 Probar Subida</button>
</form>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #007bff; }
h3 { color: #28a745; margin-top: 30px; }
.btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
.btn-primary { background: #007bff; color: white; }
.form-control { padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 100%; }
.form-label { font-weight: bold; margin-bottom: 5px; display: block; }
.mb-3 { margin-bottom: 15px; }
</style> 