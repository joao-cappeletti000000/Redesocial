<?php
session_start();
require_once "../config/db.php";

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

$sessionUser = $_SESSION['user'];
$id = $sessionUser['id'];

// Recarrega dados frescos do banco
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/conecta-escola/assets/css/style.css">
    <title>Perfil — <?php echo htmlspecialchars($user['nome']); ?> | Conecta Escola</title>
</head>
<body>

<nav class="navbar">
    <div class="logo">Conecta Escola</div>
    <div class="menu">
        <a href="index.php">🏠 Feed</a>
        <a href="buscar.php">🔍 Buscar</a>
        <a href="logout.php">Sair</a>
    </div>
</nav>

<div class="profile-page">

    <?php if(isset($_GET['msg'])): ?>
        <?php $msg = $_GET['msg']; ?>
        <?php if($msg === 'success'): ?>
            <div class="alert alert-success">✅ Foto atualizada com sucesso!</div>
        <?php elseif($msg === 'upload_error'): ?>
            <div class="alert alert-error">⚠️ Falha no upload. Verifique o arquivo e tente novamente.</div>
        <?php elseif($msg === 'db_error'): ?>
            <div class="alert alert-error">⚠️ Erro ao salvar no banco. Tente novamente.</div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- HEADER PERFIL -->
    <div class="profile-header">

        <!-- Avatar -->
        <div class="profile-avatar">
            <?php
            $avatar = !empty($user['foto'])
                ? '/conecta-escola/uploads/fotos/' . htmlspecialchars($user['foto'])
                : '/conecta-escola/assets/img/user.png';
            ?>
            <img src="<?php echo $avatar; ?>" alt="Foto de <?php echo htmlspecialchars($user['nome']); ?>">

            <form class="avatar-upload-form" action="../controllers/avatarController.php" method="POST" enctype="multipart/form-data">
                <label for="foto" class="avatar-upload-label">📷 Alterar foto</label>
                <input id="foto" type="file" name="foto" accept="image/*" required>
                <button type="submit" name="upload_foto">Enviar</button>
            </form>
        </div>

        <!-- Info -->
        <div class="profile-info">
            <h2><?php echo htmlspecialchars($user['nome']); ?></h2>
            <div class="turma">@<?php echo ltrim(htmlspecialchars($user['turma']), '@'); ?></div>

            <div class="profile-stats">
                <?php
                $totalPosts = $conn->prepare("SELECT COUNT(*) FROM postagens WHERE id_usuario = ?");
                $totalPosts->execute([$id]);
                $totalPosts = $totalPosts->fetchColumn();

                $totalCurtidas = $conn->prepare("
                    SELECT COUNT(*) FROM curtidas
                    JOIN postagens ON postagens.id = curtidas.id_postagem
                    WHERE postagens.id_usuario = ?
                ");
                $totalCurtidas->execute([$id]);
                $totalCurtidas = $totalCurtidas->fetchColumn();
                ?>
                <div>
                    <b><?php echo $totalPosts; ?></b>
                    <span>posts</span>
                </div>
                <div>
                    <b><?php echo $totalCurtidas; ?></b>
                    <span>curtidas</span>
                </div>
            </div>

            <button class="edit-btn">✏️ Editar Perfil</button>
        </div>

    </div><!-- /profile-header -->

    <!-- POSTS DO PERFIL -->
    <div class="section-title">Publicações</div>

    <?php
    $posts = $conn->prepare("
        SELECT * FROM postagens
        WHERE id_usuario = ?
        ORDER BY data_postagem DESC
    ");
    $posts->execute([$id]);
    $posts = $posts->fetchAll();
    ?>

    <?php if(empty($posts)): ?>
        <div class="empty-state">
            <div class="empty-icon">📝</div>
            <p>Você ainda não publicou nada. <a href="index.php">Criar um post</a></p>
        </div>
    <?php else: ?>
        <div class="profile-posts">
            <?php foreach($posts as $post): ?>
                <div class="post-grid">
                    <?php echo nl2br(htmlspecialchars($post['conteudo'])); ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div><!-- /profile-page -->
</body>
</html>
