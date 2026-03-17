<?php
session_start();
require_once "../config/db.php";

if(!isset($_SESSION['user'])){
    header("Location: ../public/login.php");
    exit();
}

$texto  = trim($_POST['texto'] ?? '');
$postId = (int)($_POST['post'] ?? 0);
$userId = $_SESSION['user']['id'];

if($texto === '' || $postId === 0){
    header("Location: ../public/index.php");
    exit();
}

if(mb_strlen($texto) > 500){
    header("Location: ../public/index.php");
    exit();
}

// Verifica se o post existe
$check = $conn->prepare("SELECT id FROM postagens WHERE id = ?");
$check->execute([$postId]);
if(!$check->fetch()){
    header("Location: ../public/index.php");
    exit();
}

$sql = $conn->prepare("INSERT INTO comentarios(id_postagem, id_usuario, texto) VALUES (?,?,?)");
$sql->execute([$postId, $userId, $texto]);

header("Location: ../public/index.php#post-{$postId}");
exit();
