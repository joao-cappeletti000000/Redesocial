<?php
session_start();
require_once "../config/db.php";

if(!isset($_SESSION['user'])){
    header("Location: ../public/login.php");
    exit();
}

$userId = $_SESSION['user']['id'];

// ── CRIAR POST ────────────────────────────────────────
if(isset($_POST['postar'])){
    $conteudo = trim($_POST['conteudo'] ?? '');

    if($conteudo === ''){
        header("Location: ../public/index.php");
        exit();
    }
    if(mb_strlen($conteudo) > 1000){
        header("Location: ../public/index.php?erro=post_longo");
        exit();
    }

    $sql = $conn->prepare("INSERT INTO postagens(id_usuario, conteudo) VALUES (?,?)");
    $sql->execute([$userId, $conteudo]);

    header("Location: ../public/index.php");
    exit();
}

// ── EXCLUIR POST (somente dono) ───────────────────────
if(isset($_GET['excluir'])){
    $id = (int)$_GET['excluir'];

    // Confirma que o post pertence ao usuário logado
    $check = $conn->prepare("SELECT id FROM postagens WHERE id = ? AND id_usuario = ?");
    $check->execute([$id, $userId]);

    if($check->fetch()){
        $conn->prepare("DELETE FROM curtidas    WHERE id_postagem = ?")->execute([$id]);
        $conn->prepare("DELETE FROM comentarios WHERE id_postagem = ?")->execute([$id]);
        $conn->prepare("DELETE FROM postagens   WHERE id = ?")->execute([$id]);
    }

    header("Location: ../public/index.php");
    exit();
}

header("Location: ../public/index.php");
exit();
