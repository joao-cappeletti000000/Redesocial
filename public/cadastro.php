<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/conecta-escola/assets/css/style.css">
    <title>Cadastro | Conecta Escola</title>
</head>
<body class="auth-page">

<div class="auth-box">
    <div class="auth-brand">Conecta Escola</div>
    <p class="auth-subtitle">Crie sua conta e faça parte da rede</p>

    <?php if(isset($_GET['erro'])): ?>
        <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($_GET['erro']); ?></div>
    <?php endif; ?>

    <form class="auth-form" action="../controllers/authController.php" method="POST">
        <input name="nome" type="text" placeholder="Nome completo" required autocomplete="name">
        <input name="email" type="email" placeholder="E-mail" required autocomplete="email">
        <input name="handle" type="text" placeholder="@nome_de_usuario" required autocomplete="username">
        <small style="font-size:.85rem; color:#677488; display:block; margin-bottom:10px;">Será mostrado como @nome_de_usuario no seu perfil.</small>
        <input type="password" name="senha" placeholder="Crie uma senha" required autocomplete="new-password">
        <button name="cadastro" type="submit">Criar conta →</button>
    </form>

    <div class="auth-footer">
        Já tem conta? <a href="login.php">Fazer login</a>
    </div>
</div>

</body>
</html>
