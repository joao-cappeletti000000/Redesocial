<?php
session_start();
require_once "../config/db.php";

if(!isset($_SESSION['user'])){
    header("Location: ../public/login.php");
    exit();
}

if(!isset($_POST['upload_foto']) || !isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK){
    header("Location: ../public/perfil.php");
    exit();
}

$userId = (int)$_SESSION['user']['id'];
$file   = $_FILES['foto'];

// Valida extensão
$allowed = ['jpg','jpeg','png','gif','webp'];
$ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if(!in_array($ext, $allowed)){
    header("Location: ../public/perfil.php?msg=upload_error");
    exit();
}

// Valida tamanho (3 MB)
if($file['size'] > 3 * 1024 * 1024){
    header("Location: ../public/perfil.php?msg=upload_error");
    exit();
}

// Valida MIME real do arquivo (não apenas extensão)
$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
$allowedMimes = ['image/jpeg','image/png','image/gif','image/webp'];
if(!in_array($mimeType, $allowedMimes)){
    header("Location: ../public/perfil.php?msg=upload_error");
    exit();
}

$uploadDir = __DIR__ . '/../uploads/fotos/';
if(!is_dir($uploadDir)){
    mkdir($uploadDir, 0755, true);
}

$filename    = 'avatar_' . $userId . '_' . time() . '.' . $ext;
$destination = $uploadDir . $filename;

if(!move_uploaded_file($file['tmp_name'], $destination)){
    header("Location: ../public/perfil.php?msg=upload_error");
    exit();
}

try {
    // Remove foto anterior para não acumular arquivos
    $old = $conn->prepare("SELECT foto FROM usuarios WHERE id = ?");
    $old->execute([$userId]);
    $oldFoto = $old->fetchColumn();
    if($oldFoto){
        $oldPath = $uploadDir . $oldFoto;
        if(file_exists($oldPath) && is_file($oldPath)){
            @unlink($oldPath);
        }
    }

    $stmt = $conn->prepare("UPDATE usuarios SET foto = ? WHERE id = ?");
    $stmt->execute([$filename, $userId]);

    // Atualiza sessão
    $_SESSION['user']['foto'] = $filename;

    header("Location: ../public/perfil.php?msg=success");
    exit();

} catch(PDOException $e){
    error_log('[avatarController] DB error: ' . $e->getMessage());
    header("Location: ../public/perfil.php?msg=db_error");
    exit();
}
