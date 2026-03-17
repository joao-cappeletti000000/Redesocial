<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/conecta-escola/assets/css/style.css">
    <title>Login | NetFriends</title>
</head>
<body class="auth-page">

<div class="auth-box">
    <div class="auth-brand">NetFriends</div>    
    <p class="auth-subtitle">Acesse sua conta para continuar</p>

    <?php if(isset($_GET['erro'])): ?>
        <div class="alert alert-error">⚠️ Email ou senha incorretos. Tente novamente.</div>
    <?php endif; ?>

    <form action="../controllers/authController.php" method="POST" class="auth-form">
        <input name="email" type="email" placeholder="Seu e-mail" required autocomplete="email">
        <input name="senha" type="password" placeholder="Sua senha" required autocomplete="current-password">
        <button name="login" type="submit">Entrar →</button>
    </form>

    <div class="auth-footer">
        Não tem conta? <a href="cadastro.php">Cadastre-se grátis</a>
    </div>
</div>

</body>
</html>
