<?php
// Affiche les fichiers et dossiers à chaque niveau pour diagnostiquer le chemin de connexion

function listDir($path) {
    echo "Contenu de $path :\n";
    if (is_dir($path)) {
        foreach (scandir($path) as $file) {
            if ($file !== '.' && $file !== '..') {
                echo "- $file\n";
            }
        }
    } else {
        echo "(dossier introuvable)\n";
    }
    echo "\n";
}

listDir(__DIR__);
listDir(dirname(__DIR__));
listDir(dirname(dirname(__DIR__)));
listDir(dirname(dirname(dirname(__DIR__))));
listDir(dirname(dirname(dirname(__DIR__))) . '/src/database');
listDir(dirname(dirname(dirname(__DIR__))) . '/db');
