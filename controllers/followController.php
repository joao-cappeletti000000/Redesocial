<?php
session_start();
require_once "../config/db.php";

if(!isset($_SESSION['user'])){
    header("Location: ../public/login.php");
    exit();
}

if(!isset($_POST['target_id'])){
    header("Location: ../public/index.php");
    exit();
}

$seguidorId = (int)$_SESSION['user']['id'];
$seguidoId  = (int)$_POST['target_id'];

if($seguidorId === $seguidoId){
    header("Location: ../public/index.php");
    exit();
}

try {
    // Garante que a tabela existe
    $conn->exec("CREATE TABLE IF NOT EXISTS seguidores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        seguidor_id INT NOT NULL,
        seguido_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY ux_seguidores (seguidor_id, seguido_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $check = $conn->prepare("SELECT 1 FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
    $check->execute([$seguidorId, $seguidoId]);
    $already = (bool)$check->fetchColumn();

    if($already){
        $del = $conn->prepare("DELETE FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
        $del->execute([$seguidorId, $seguidoId]);
        $action = 'unfollowed';
    } else {
        $ins = $conn->prepare("INSERT INTO seguidores (seguidor_id, seguido_id) VALUES (?, ?)");
        $ins->execute([$seguidorId, $seguidoId]);
        $action = 'followed';
    }

    header("Location: ../public/perfil.php?id=" . $seguidoId . "&msg=" . $action);
    exit();

} catch (PDOException $e) {
    error_log('[followController] ' . $e->getMessage());
    header("Location: ../public/index.php?follow=error");
    exit();
}
