<?php
session_start();
require_once "../config/db.php";

if(!isset($_SESSION['user'])){
    header("Location: ../public/login.php");
    exit();
}

$postId = (int)($_GET['post'] ?? 0);
$userId = $_SESSION['user']['id'];

if($postId === 0){
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

// Toggle: se já curtiu, descurte
$existing = $conn->prepare("SELECT id FROM curtidas WHERE id_postagem = ? AND id_usuario = ?");
$existing->execute([$postId, $userId]);

if($existing->fetch()){
    // Descurtir
    $conn->prepare("DELETE FROM curtidas WHERE id_postagem = ? AND id_usuario = ?")->execute([$postId, $userId]);
} else {
    // Curtir
    $conn->prepare("INSERT INTO curtidas(id_postagem, id_usuario) VALUES (?,?)")->execute([$postId, $userId]);
}

header("Location: ../public/index.php#post-{$postId}");
exit();
