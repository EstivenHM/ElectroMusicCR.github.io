<?php

// Genera el hash SHA-256 de 64 caracteres para '83044425'
$hash = hash('sha256', '83044426');

// El hash de 64 caracteres es SOLO los números y letras (0-9, a-f):
echo $hash;

// NOTA: '?>' NO es parte del hash, es la etiqueta de cierre de PHP.
// En PHP no hace falta poner '?>' al final del archivo.
