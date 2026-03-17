<?php
session_start();
require_once "../config/db.php";

// ── CADASTRO ──────────────────────────────────────────
if(isset($_POST['cadastro'])){
    $nome   = trim($_POST['nome']   ?? '');
    $email  = trim($_POST['email']  ?? '');
    $handle = trim($_POST['handle'] ?? '');
    $senha  = $_POST['senha'] ?? '';

    // Validações básicas
    if(!$nome || !$email || !$handle || !$senha){
        header("Location: ../public/cadastro.php?erro=" . urlencode("Preencha todos os campos."));
        exit();
    }
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        header("Location: ../public/cadastro.php?erro=" . urlencode("E-mail inválido."));
        exit();
    }
    if(strlen($senha) < 6){
        header("Location: ../public/cadastro.php?erro=" . urlencode("A senha deve ter ao menos 6 caracteres."));
        exit();
    }

    // Verifica e-mail duplicado
    $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $check->execute([$email]);
    if($check->fetch()){
        header("Location: ../public/cadastro.php?erro=" . urlencode("E-mail já cadastrado."));
        exit();
    }

    // Ajuste do handle para formar @nome
    $handleFormatado = ltrim($handle, '@');
    $handleFormatado = '@' . preg_replace('/[^a-zA-Z0-9_]/', '', strtolower($handleFormatado));

    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $sql  = $conn->prepare("INSERT INTO usuarios(nome, email, senha, turma) VALUES (?,?,?,?)");
    $sql->execute([$nome, $email, $hash, $handleFormatado]);

    header("Location: ../public/login.php?msg=cadastro_ok");
    exit();
}

// ── LOGIN ─────────────────────────────────────────────
if(isset($_POST['login'])){
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if(!$email || !$senha){
        header("Location: ../public/login.php?erro=campos");
        exit();
    }

    $sql = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
    $sql->execute([$email]);
    $user = $sql->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($senha, $user['senha'])){
        // Não guarda a senha na sessão
        unset($user['senha']);
        $_SESSION['user'] = $user;
        header("Location: ../public/index.php");
        exit();
    }

    header("Location: ../public/login.php?erro=invalido");
    exit();
}

header("Location: ../public/login.php");
exit();
