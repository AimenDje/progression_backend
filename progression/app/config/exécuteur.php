<?
return [
    'url' => env('COMPILEBOX_URL', "http://compilebox:12380/compile"),
    'image' => env("COMPILEBOX_IMAGE_EXECUTEUR", 'registry.gitlab.com/projet-progression/compilebox/remotecompiler:latest'),
];
