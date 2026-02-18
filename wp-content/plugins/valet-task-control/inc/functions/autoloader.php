<?php
// Simple PSR-4-like autoloader for Valet\Services
spl_autoload_register(function ($class) {
	$namespace = 'ValetTasks\\';

	// Only load classes from our namespace
	if (strpos($class, $namespace) !== 0) {
		return;
	}

	// Base directory for the namespace prefix
	$base_dir = plugin_dir_path(__DIR__);

	// Get the relative class name
	$relative_class = substr($class, strlen($namespace));

	// Replace namespace separators with directory separators
	$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

	if (file_exists($file)) {
		require_once $file;
	}
});