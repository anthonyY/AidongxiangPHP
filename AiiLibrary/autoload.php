<?php
spl_autoload_register(function ($class) {
    if ($class) {
        $file = str_replace('\\', '/', $class);
        $file = str_replace('AiiLibrary/', '', $file);
        $file = __DIR__ . '/' . $file . '.php';
        if (file_exists($file)) {
            include $file;
        }
    }
});