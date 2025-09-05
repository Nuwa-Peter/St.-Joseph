<?php
// includes/view_loader.php

require_once __DIR__ . '/../../vendor/autoload.php';

// Define the path to the views directory.
$views_path = __DIR__ . '/../../resources/views';

// Set up the Twig loader, which tells Twig where to find our templates.
$loader = new \Twig\Loader\FilesystemLoader($views_path);

// Set up the main Twig environment.
$twig = new \Twig\Environment($loader, [
    // 'cache' => __DIR__ . '/../cache/twig', // Caching can be enabled in production for better performance.
    'debug' => true, // Enable debug mode for easier development.
]);

// Add the debug extension, which provides useful debugging functions like dump().
$twig->addExtension(new \Twig\Extension\DebugExtension());

// Add custom PHP functions to Twig so they can be called from templates.
$twig->addFunction(new \Twig\TwigFunction('url', 'url'));
$twig->addFunction(new \Twig\TwigFunction('csrf_input', 'csrf_input'));

// Return the configured Twig environment so it can be used globally.
return $twig;
