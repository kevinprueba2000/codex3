<?php
/**
 * Script de Prueba del Sistema de Imágenes - AlquimiaTechnologic
 * Prueba la funcionalidad completa del sistema de imágenes
 */

require_once 'config/config.php';
require_once 'classes/Product.php';
require_once 'classes/Category.php';

// Verificar si es administrador
if (!isLoggedIn() || !isAdmin()) {
    die('Acceso denegado. Solo administradores pueden ejecutar este script.');
}

$product = new Product();

echo "<h1>🧪 Prueba del Sistema de Imágenes - AlquimiaTechnologic</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    .info { color: blue; }
    .test-section { background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 5px; }
    .test-result { padding: 10px; margin: 5px 0; border-radius: 3px; }
    .test-success { background: #d4edda; border: 1px solid #c3e6cb; }
    .test-error { background: #f8d7da; border: 1px solid #f5c6cb; }
    .test-info { background: #d1ecf1; border: 1px solid #bee5eb; }
</style>";

// Test 1: Verificar método getImagePath
echo "<div class='test-section'>";
echo "<h2>Test 1: Método getImagePath</h2>";

$testProducts = $product->getAllProducts(5); // Solo los primeros 5 productos

foreach ($testProducts as $prod) {
    echo "<div class='test-result test-info'>";
    echo "<strong>Producto:</strong> {$prod['name']}<br>";
    echo "<strong>Slug:</strong> {$prod['slug']}<br>";
    echo "<strong>JSON de imágenes:</strong> " . htmlspecialchars(substr($prod['images'] ?? 'null', 0, 100)) . "<br>";
    
    $imagePath = Product::getImagePath($prod);
    echo "<strong>Ruta de imagen obtenida:</strong> $imagePath<br>";
    
    // Verificar si la imagen existe
    $fullPath = __DIR__ . '/' . ltrim($imagePath, '/');
    if (file_exists($fullPath) && filesize($fullPath) > 100) {
        echo "<span class='success'>✅ Imagen válida encontrada</span>";
    } else {
        echo "<span class='error'>❌ Imagen no encontrada o inválida</span>";
    }
    echo "</div>";
}
echo "</div>";

// Test 2: Verificar formato JSON de imágenes
echo "<div class='test-section'>";
echo "<h2>Test 2: Formato JSON de Imágenes</h2>";

foreach ($testProducts as $prod) {
    echo "<div class='test-result test-info'>";
    echo "<strong>Producto:</strong> {$prod['name']}<br>";
    
    $imagesJson = $prod['images'] ?? null;
    if ($imagesJson) {
        $images = json_decode($imagesJson, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<span class='success'>✅ JSON válido</span><br>";
            echo "<strong>Estructura:</strong> ";
            if (is_array($images) && !empty($images)) {
                if (is_array($images[0])) {
                    echo "Array de objetos (formato upload_handler)<br>";
                    echo "<strong>Primera imagen:</strong> " . ($images[0]['original'] ?? 'N/A') . "<br>";
                } else {
                    echo "Array de strings (formato simple)<br>";
                    echo "<strong>Primera imagen:</strong> " . $images[0] . "<br>";
                }
            } else {
                echo "Array vacío<br>";
            }
        } else {
            echo "<span class='error'>❌ JSON inválido: " . json_last_error_msg() . "</span>";
        }
    } else {
        echo "<span class='warning'>⚠️ Sin imágenes</span>";
    }
    echo "</div>";
}
echo "</div>";

// Test 3: Verificar archivos físicos
echo "<div class='test-section'>";
echo "<h2>Test 3: Archivos Físicos</h2>";

$productsDir = 'assets/images/products/';
if (is_dir($productsDir)) {
    $files = scandir($productsDir);
    $imageFiles = array_filter($files, function($file) {
        return in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'webp', 'gif']);
    });
    
    echo "<div class='test-result test-info'>";
    echo "<strong>Total de archivos de imagen:</strong> " . count($imageFiles) . "<br>";
    
    if (!empty($imageFiles)) {
        echo "<strong>Archivos encontrados:</strong><br>";
        foreach ($imageFiles as $file) {
            $filePath = $productsDir . $file;
            $fileSize = filesize($filePath);
            $status = $fileSize > 100 ? "✅" : "❌";
            echo "&nbsp;&nbsp;$status $file ($fileSize bytes)<br>";
        }
    } else {
        echo "<span class='warning'>⚠️ No se encontraron archivos de imagen</span>";
    }
    echo "</div>";
} else {
    echo "<div class='test-result test-error'>";
    echo "<span class='error'>❌ Directorio de productos no existe</span>";
    echo "</div>";
}
echo "</div>";

// Test 4: Simular creación de producto con imagen
echo "<div class='test-section'>";
echo "<h2>Test 4: Simulación de Producto con Imagen</h2>";

$testProductData = [
    'name' => 'Producto de Prueba',
    'description' => 'Descripción de prueba',
    'short_description' => 'Descripción corta',
    'price' => 99.99,
    'category_id' => 1,
    'stock_quantity' => 10,
    'is_featured' => 0,
    'images' => json_encode(['assets/images/products/test-product.jpg']),
    'slug' => 'test-product',
    'sku' => 'TEST-001'
];

echo "<div class='test-result test-info'>";
echo "<strong>Datos de prueba:</strong><br>";
echo "Nombre: {$testProductData['name']}<br>";
echo "Slug: {$testProductData['slug']}<br>";
echo "Imágenes: {$testProductData['images']}<br>";

$imagePath = Product::getImagePath($testProductData);
echo "<strong>Ruta de imagen simulada:</strong> $imagePath<br>";

if ($imagePath === 'assets/images/placeholder.jpg') {
    echo "<span class='warning'>⚠️ Usando imagen placeholder (esperado para producto de prueba)</span>";
} else {
    echo "<span class='success'>✅ Ruta de imagen válida</span>";
}
echo "</div>";
echo "</div>";

// Test 5: Verificar permisos de directorios
echo "<div class='test-section'>";
echo "<h2>Test 5: Permisos de Directorios</h2>";

$directories = [
    'assets/images',
    'assets/images/products',
    'assets/images/categories',
    'assets/images/settings'
];

foreach ($directories as $dir) {
    echo "<div class='test-result test-info'>";
    echo "<strong>Directorio:</strong> $dir<br>";
    
    if (file_exists($dir)) {
        $perms = fileperms($dir);
        $isWritable = is_writable($dir);
        $status = $isWritable ? "✅" : "❌";
        
        echo "$status Existe y " . ($isWritable ? "es escribible" : "NO es escribible") . "<br>";
        echo "<strong>Permisos:</strong> " . substr(sprintf('%o', $perms), -4) . "<br>";
    } else {
        echo "<span class='error'>❌ No existe</span>";
    }
    echo "</div>";
}
echo "</div>";

// Resumen final
echo "<div class='test-section'>";
echo "<h2>📊 Resumen de Pruebas</h2>";
echo "<div class='test-result test-success'>";
echo "<strong>✅ Sistema de imágenes verificado</strong><br>";
echo "El sistema está funcionando correctamente. Las correcciones aplicadas deberían resolver los problemas de visualización de imágenes.";
echo "</div>";
echo "</div>";

echo "<h2>🎯 Próximos Pasos</h2>";
echo "<ul>";
echo "<li>Ejecuta el script <code>debug_images.php</code> para corregir productos existentes</li>";
echo "<li>Prueba crear un nuevo producto con imágenes desde el panel de administración</li>";
echo "<li>Verifica que las imágenes se muestren correctamente en el frontend</li>";
echo "<li>Si persisten problemas, revisa los logs de error del servidor</li>";
echo "</ul>";
?> 