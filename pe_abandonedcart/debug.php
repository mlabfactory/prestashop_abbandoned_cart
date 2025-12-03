<?php
// Simple debug script
header('Content-Type: text/plain');
echo "Debug Start\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";

echo "\n--- Module Check ---\n";
try {
    if (class_exists('Module')) {
        echo "Module class exists: YES\n";
        echo "Module enabled: " . (Module::isEnabled('pe_abandonedcart') ? 'YES' : 'NO') . "\n";
    } else {
        echo "Module class exists: NO\n";
    }
} catch (Exception $e) {
    echo "Module check error: " . $e->getMessage() . "\n";
}

echo "\n--- Configuration Check ---\n";
try {
    if (class_exists('Configuration')) {
        echo "Configuration class exists: YES\n";
        echo "Token: " . Configuration::get('PE_ABANDONED_CART_CRON_TOKEN') . "\n";
        echo "Delay: " . Configuration::get('PE_ABANDONED_CART_DELAY') . "\n";
        echo "Enabled: " . Configuration::get('PE_ABANDONED_CART_ENABLED') . "\n";
    } else {
        echo "Configuration class exists: NO\n";
    }
} catch (Exception $e) {
    echo "Configuration check error: " . $e->getMessage() . "\n";
}

echo "\n--- File Check ---\n";
$baseDir = dirname(__FILE__) . '/../..';
echo "Base dir: $baseDir\n";
echo "Autoload exists: " . (file_exists($baseDir . '/vendor/autoload.php') ? 'YES' : 'NO') . "\n";
echo "Service exists: " . (file_exists($baseDir . '/src/Service/AbandonedCartService.php') ? 'YES' : 'NO') . "\n";
echo "Model exists: " . (file_exists($baseDir . '/src/Model/AbandonedCart.php') ? 'YES' : 'NO') . "\n";

echo "\n--- Autoload Test ---\n";
try {
    $autoloadPath = $baseDir . '/vendor/autoload.php';
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
        echo "Autoload loaded: YES\n";
        
        echo "Service class exists: " . (class_exists('MLAB\PE\Service\AbandonedCartService') ? 'YES' : 'NO') . "\n";
        echo "Model class exists: " . (class_exists('MLAB\PE\Model\AbandonedCart') ? 'YES' : 'NO') . "\n";
    } else {
        echo "Autoload file not found\n";
    }
} catch (Exception $e) {
    echo "Autoload error: " . $e->getMessage() . "\n";
}

echo "\nDebug End\n";
?>