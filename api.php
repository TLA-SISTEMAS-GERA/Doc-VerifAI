<?php
require_once 'chat.php';

$mensaje = $_POST['messages'] ?? '';
$vertexStream = new Chat();
$vertexStream->vertexStream($mensaje);
exit;