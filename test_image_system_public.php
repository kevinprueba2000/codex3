<?php
/**
 * Test del Sistema de Imágenes - Versión Pública
 * AlquimiaTechnologic
 * 
 * Este script verifica y corrige problemas con el sistema de imágenes
 * sin requerir autenticación de administrador
 */

require_once 'config/config.php';
require_once 'classes/Product.php';
require_once 'classes/Category.php';

echo "<h1>🧪 Test del Sistema de Imágenes - AlquimiaTechnologic (Público)</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    .info { color: blue; }
    .debug { background: #f5f5f5; padding: 10px; margin: 10px 0; border-left: 4px solid #007bff; }
    .fixed { background-color: #d4edda; }
    .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #e9ecef; border-radius: 5px; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; background: white; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

echo "<div class='container'>";

try {
    $database = new Database();
    $db = $database->getConnection();
    $product = new Product();
    $category = new Category();
    
    echo "<div class='section'>";
    echo "<h2>🔧 Corrección de Datos de Imágenes</h2>";
    
    // Obtener todos los productos
    $stmt = $db->query("SELECT id, name, slug, images FROM products ORDER BY id");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $fixedCount = 0;
    $totalProducts = count($products);
    
    echo "<table>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Estado Original</th><th>Acción</th><th>Estado Final</th></tr>";
    
    foreach ($products as $productData) {
        $id = $productData['id'];
        $name = $productData['name'];
        $images = $productData['images'];
        $originalStatus = "OK";
        $action = "Ninguna";
        $finalStatus = "OK";
        
        // Verificar si las imágenes están vacías o son null
        if (empty($images) || $images === 'null' || $images === '[]') {
            $originalStatus = "<span class='error'>❌ Sin imágenes</span>";
            
            // Crear estructura JSON válida con placeholder
            $defaultImages = json_encode([
                'assets/images/placeholder.jpg'
            ]);
            
            // Actualizar en la base de datos
            $updateStmt = $db->prepare("UPDATE products SET images = ? WHERE id = ?");
            $updateStmt->execute([$defaultImages, $id]);
            
            $action = "<span class='success'>✅ Corregido</span>";
            $finalStatus = "<span class='success'>✅ Con placeholder</span>";
            $fixedCount++;
            
        } else {
            // Verificar si es JSON válido
            $decoded = json_decode($images, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $originalStatus = "<span class='error'>❌ JSON inválido</span>";
                
                // Intentar corregir el JSON
                $correctedImages = json_encode(['assets/images/placeholder.jpg']);
                $updateStmt = $db->prepare("UPDATE products SET images = ? WHERE id = ?");
                $updateStmt->execute([$correctedImages, $id]);
                
                $action = "<span class='success'>✅ JSON corregido</span>";
                $finalStatus = "<span class='success'>✅ Válido</span>";
                $fixedCount++;
            }
        }
        
        echo "<tr>";
        echo "<td>$id</td>";
        echo "<td>$name</td>";
        echo "<td>$originalStatus</td>";
        echo "<td>$action</td>";
        echo "<td>$finalStatus</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<div class='debug'>";
    echo "<h3>📊 Resumen de Correcciones</h3>";
    echo "<p><strong>Total de productos:</strong> $totalProducts</p>";
    echo "<p class='success'><strong>Productos corregidos:</strong> $fixedCount</p>";
    echo "<p class='info'><strong>Productos sin cambios:</strong> " . ($totalProducts - $fixedCount) . "</p>";
    echo "</div>";
    echo "</div>";
    
    // Verificar categorías
    echo "<div class='section'>";
    echo "<h2>📂 Verificación de Categorías</h2>";
    
    $stmt = $db->query("SELECT id, name, image FROM categories ORDER BY id");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $categoryFixedCount = 0;
    $totalCategories = count($categories);
    
    echo "<table>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Imagen</th><th>Estado</th></tr>";
    
    foreach ($categories as $categoryData) {
        $id = $categoryData['id'];
        $name = $categoryData['name'];
        $image = $categoryData['image'];
        
        if (empty($image) || $image === 'null') {
            // Actualizar con imagen por defecto
            $updateStmt = $db->prepare("UPDATE categories SET image = ? WHERE id = ?");
            $updateStmt->execute(['assets/images/placeholder.jpg', $id]);
            
            echo "<tr class='fixed'>";
            echo "<td>$id</td>";
            echo "<td>$name</td>";
            echo "<td>assets/images/placeholder.jpg</td>";
            echo "<td><span class='success'>✅ Corregido</span></td>";
            echo "</tr>";
            $categoryFixedCount++;
        } else {
            echo "<tr>";
            echo "<td>$id</td>";
            echo "<td>$name</td>";
            echo "<td>$image</td>";
            echo "<td><span class='success'>✅ OK</span></td>";
            echo "</tr>";
        }
    }
    
    echo "</table>";
    
    echo "<div class='debug'>";
    echo "<h3>📊 Resumen de Categorías</h3>";
    echo "<p><strong>Total de categorías:</strong> $totalCategories</p>";
    echo "<p class='success'><strong>Categorías corregidas:</strong> $categoryFixedCount</p>";
    echo "</div>";
    echo "</div>";
    
    // Verificar configuración del sitio
    echo "<div class='section'>";
    echo "<h2>⚙️ Verificación de Configuración del Sitio</h2>";
    
    $stmt = $db->query("SELECT * FROM site_settings WHERE setting_key IN ('logo', 'favicon')");
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Configuración</th><th>Valor</th><th>Estado</th></tr>";
    
    foreach ($settings as $setting) {
        $key = $setting['setting_key'];
        $value = $setting['setting_value'];
        
        if (empty($value) || $value === 'null') {
            echo "<tr class='fixed'>";
            echo "<td>$key</td>";
            echo "<td>Sin configurar</td>";
            echo "<td><span class='warning'>⚠️ Requiere configuración</span></td>";
            echo "</tr>";
        } else {
            echo "<tr>";
            echo "<td>$key</td>";
            echo "<td>$value</td>";
            echo "<td><span class='success'>✅ Configurado</span></td>";
            echo "</tr>";
        }
    }
    
    echo "</table>";
    echo "</div>";
    
    // Verificar archivos de imágenes
    echo "<div class='section'>";
    echo "<h2>🖼️ Verificación de Archivos de Imágenes</h2>";
    
    $imageDirs = [
        'assets/images',
        'assets/images/products',
        'assets/images/categories',
        'assets/images/settings'
    ];
    
    foreach ($imageDirs as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
            echo "<p class='success'>✅ Directorio creado: $dir</p>";
        } else {
            echo "<p class='info'>ℹ️ Directorio existe: $dir</p>";
        }
    }
    
    // Crear imagen placeholder si no existe
    if (!file_exists('assets/images/placeholder.jpg')) {
        $placeholderContent = file_get_contents('https://via.placeholder.com/300x300/cccccc/666666?text=Sin+Imagen');
        if ($placeholderContent) {
            file_put_contents('assets/images/placeholder.jpg', $placeholderContent);
            echo "<p class='success'>✅ Imagen placeholder creada</p>";
        } else {
            echo "<p class='warning'>⚠️ No se pudo crear la imagen placeholder</p>";
        }
    } else {
        echo "<p class='info'>ℹ️ Imagen placeholder existe</p>";
    }
    
    echo "</div>";
    
    // Resumen final
    echo "<div class='section'>";
    echo "<h2>🎉 Resumen Final</h2>";
    
    $totalFixed = $fixedCount + $categoryFixedCount;
    
    echo "<div class='debug'>";
    echo "<h3>✅ Correcciones Aplicadas</h3>";
    echo "<ul>";
    echo "<li><strong>Productos corregidos:</strong> $fixedCount</li>";
    echo "<li><strong>Categorías corregidas:</strong> $categoryFixedCount</li>";
    echo "<li><strong>Total de correcciones:</strong> $totalFixed</li>";
    echo "</ul>";
    
    echo "<h3>🔗 Enlaces Útiles</h3>";
    echo "<ul>";
    echo "<li><a href='debug_images_public.php' target='_blank'>🔍 Verificar estado actual</a></li>";
    echo "<li><a href='admin/dashboard.php' target='_blank'>📊 Panel de Administración</a></li>";
    echo "<li><a href='index.php' target='_blank'>🏠 Página Principal</a></li>";
    echo "<li><a href='products.php' target='_blank'>📦 Productos</a></li>";
    echo "</ul>";
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section'>";
    echo "<h2>❌ Error</h2>";
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</div>";
echo "<h2>✅ Test del Sistema de Imágenes Completado</h2>";
echo "<p>Se han aplicado las correcciones necesarias al sistema de imágenes.</p>";
?> 