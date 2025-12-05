
<?php

return [
    'default_paper_size' => 'A4',
    'default_font' => 'dejavu sans', // Utiliser une police qui supporte UTF-8
    'dpi' => 150, // Augmenter la résolution
    'enable_php' => false,
    'enable_javascript' => false,
    'enable_remote' => true, // Important : permettre les images
    'font_dir' => storage_path('fonts/'),
    'font_cache' => storage_path('fonts/'),
    'temp_dir' => storage_path('app/temp/'),
    'chroot' => realpath(base_path()),
    'log_output_file' => storage_path('logs/dompdf.html'),
    'default_media_type' => 'screen',
    'default_paper_orientation' => 'portrait',
    
    // Options de débogage (désactiver en production)
    'debug_png' => false,
    'debug_keep_temp' => false,
    'debug_css' => false,
    'debug_layout' => false,
    'debug_layout_lines' => false,
    'debug_layout_blocks' => false,
    'debug_layout_inline' => false,
    'debug_layout_padding_box' => false,
    
    // Options de performance
    'is_font_subsetting_enabled' => true,
    'is_html5_parser_enabled' => true,
];