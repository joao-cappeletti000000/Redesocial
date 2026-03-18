<?php
session_start();
require_once "../config/db.php";

if(!isset($_SESSION['user'])){
    header("Location: ../public/login.php");
    exit();
}

// ── DEBUG: loga tudo que chegou ──────────────────────────────────────────────
$debugLog = __DIR__ . '/avatar_debug.log';
file_put_contents($debugLog, date('[Y-m-d H:i:s]') . "\n"
    . "POST keys: "    . implode(', ', array_keys($_POST))  . "\n"
    . "FILES: "        . print_r($_FILES, true)             . "\n"
    . "upload_foto set: " . (isset($_POST['upload_foto']) ? 'sim' : 'não') . "\n"
    . "foto set: "     . (isset($_FILES['foto'])  ? 'sim' : 'não')  . "\n"
    . "erro arquivo: " . ($_FILES['foto']['error'] ?? 'sem arquivo') . "\n"
    . str_repeat('-', 60) . "\n",
    FILE_APPEND
);
// ────────────────────────────────────────────────────────────────────────────

// Códigos de erro do PHP para $_FILES
$uploadErrors = [
    UPLOAD_ERR_INI_SIZE   => 'Arquivo maior que upload_max_filesize no php.ini',
    UPLOAD_ERR_FORM_SIZE  => 'Arquivo maior que MAX_FILE_SIZE do formulário',
    UPLOAD_ERR_PARTIAL    => 'Upload incompleto',
    UPLOAD_ERR_NO_FILE    => 'Nenhum arquivo enviado',
    UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária ausente',
    UPLOAD_ERR_CANT_WRITE => 'Falha ao gravar no disco',
    UPLOAD_ERR_EXTENSION  => 'Upload bloqueado por extensão PHP',
];

if(!isset($_POST['upload_foto']) || !isset($_FILES['foto'])){
    file_put_contents($debugLog, date('[Y-m-d H:i:s]') . " SAÍDA: POST ou FILES ausente\n", FILE_APPEND);
    header("Location: ../public/perfil.php?msg=upload_error");
    exit();
}

$fileError = $_FILES['foto']['error'];
if($fileError !== UPLOAD_ERR_OK){
    $errMsg = $uploadErrors[$fileError] ?? "Erro desconhecido ($fileError)";
    file_put_contents($debugLog, date('[Y-m-d H:i:s]') . " SAÍDA: Erro de upload — $errMsg\n", FILE_APPEND);
    header("Location: ../public/perfil.php?msg=upload_error&detail=" . urlencode($errMsg));
    exit();
}

$userId = (int)$_SESSION['user']['id'];
$file   = $_FILES['foto'];

// Valida extensão
$allowed = ['jpg','jpeg','png','gif','webp'];
$ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if(!in_array($ext, $allowed)){
    file_put_contents($debugLog, date('[Y-m-d H:i:s]') . " SAÍDA: Extensão inválida — $ext\n", FILE_APPEND);
    header("Location: ../public/perfil.php?msg=upload_error");
    exit();
}

// Valida tamanho (3 MB)
if($file['size'] > 3 * 1024 * 1024){
    file_put_contents($debugLog, date('[Y-m-d H:i:s]') . " SAÍDA: Arquivo grande demais — {$file['size']} bytes\n", FILE_APPEND);
    header("Location: ../public/perfil.php?msg=upload_error");
    exit();
}

// Valida MIME real do arquivo
$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
$allowedMimes = ['image/jpeg','image/png','image/gif','image/webp'];
if(!in_array($mimeType, $allowedMimes)){
    file_put_contents($debugLog, date('[Y-m-d H:i:s]') . " SAÍDA: MIME inválido — $mimeType\n", FILE_APPEND);
    header("Location: ../public/perfil.php?msg=upload_error");
    exit();
}

$uploadDir = __DIR__ . '/../uploads/fotos/';
if(!is_dir($uploadDir)){
    if(!mkdir($uploadDir, 0777, true)){
        file_put_contents($debugLog, date('[Y-m-d H:i:s]') . " SAÍDA: Falha ao criar pasta — $uploadDir\n", FILE_APPEND);
        header("Location: ../public/perfil.php?msg=upload_error");
        exit();
    }
}

// Tenta forçar permissão de gravação (Windows/Apache às vezes bloqueia)
if(!is_writable($uploadDir)){
    @chmod($uploadDir, 0777);
}

if(!is_writable($uploadDir)){
    $perm = is_dir($uploadDir) ? substr(sprintf('%o', fileperms($uploadDir)), -4) : 'NA';
    file_put_contents($debugLog, date('[Y-m-d H:i:s]') . " SAÍDA: Pasta sem permissão de escrita — $uploadDir (perms=$perm)\n", FILE_APPEND);
    header("Location: ../public/perfil.php?msg=upload_error&detail=" . urlencode("Pasta sem permissão: $uploadDir (perms=$perm)"));
    exit();
}

$filename    = 'avatar_' . $userId . '_' . time() . '.' . $ext;
$destination = $uploadDir . $filename;

if(!move_uploaded_file($file['tmp_name'], $destination)){
    file_put_contents($debugLog, date('[Y-m-d H:i:s]') . " SAÍDA: move_uploaded_file falhou — destino: $destination\n", FILE_APPEND);
    header("Location: ../public/perfil.php?msg=upload_error");
    exit();
}

try {
    // Remove foto anterior
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

    file_put_contents($debugLog, date('[Y-m-d H:i:s]') . " SUCESSO: foto salva — $filename\n" . str_repeat('-',60) . "\n", FILE_APPEND);
    header("Location: ../public/perfil.php?msg=success");
    exit();

} catch(PDOException $e){
    error_log('[avatarController] DB error: ' . $e->getMessage());
    file_put_contents($debugLog, date('[Y-m-d H:i:s]') . " SAÍDA: Erro DB — " . $e->getMessage() . "\n", FILE_APPEND);
    header("Location: ../public/perfil.php?msg=db_error");
    exit();
}
