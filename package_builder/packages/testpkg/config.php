<?php

return [
    'name' => 'Testpkg',
    'name_lower' => 'testpkg',
    'name_short' => 'tes',
    'version' => '1.0.0',
    'release' => 'alpha',
    
    'paths' => [
        'core' => 'core/components/testpkg/',
        'assets' => 'assets/components/testpkg/',
    ],
    
    'schema' => [
        'file' => 'model/schema/testpkg.mysql.schema.xml',
        'auto_generate_classes' => true,
        'update_tables' => true,
    ],
    
    'elements' => [
        'category' => 'Testpkg',
        'chunks' => 'elements/chunks.php',
        'snippets' => 'elements/snippets.php',
        'plugins' => 'elements/plugins.php',
        'templates' => 'elements/templates.php',
        'tvs' => 'elements/tvs.php',
        'settings' => 'elements/settings.php',
        'menus' => 'elements/menus.php',
    ],
    
    'static' => [
        'chunks' => true,
        'snippets' => true,
        'templates' => true,
    ],
    
    'tools' => [
        'phpCsFixer' => false,
        'eslint' => false,
    ],

    'build' => [
        'download' => false,
        'install' => false,
        'update' => [
            'chunks' => true,
            'snippets' => true,
            'settings' => false,
        ],
    ],
];
