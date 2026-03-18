<?php
session_start();
require_once "../config/db.php";

// Garantir que tabela de seguidores exista
$conn->exec("CREATE TABLE IF NOT EXISTS seguidores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seguidor_id INT NOT NULL,
    seguido_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY ux_seguidores (seguidor_id, seguido_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

$sessionUser = $_SESSION['user'];
$viewerId = $sessionUser['id'];
$profileId = isset($_GET['id']) ? (int)$_GET['id'] : $viewerId;

// Recarrega dados frescos do banco
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$profileId]);
$user = $stmt->fetch();

if(!$user){
    header("Location: index.php");
    exit();
}

$isOwnProfile = ($viewerId === $profileId);

$followersStmt = $conn->prepare("SELECT COUNT(*) FROM seguidores WHERE seguido_id = ?");
$followersStmt->execute([$profileId]);
$followersCount = (int)$followersStmt->fetchColumn();

$followingStmt = $conn->prepare("SELECT COUNT(*) FROM seguidores WHERE seguidor_id = ?");
$followingStmt->execute([$profileId]);
$followingCount = (int)$followingStmt->fetchColumn();

$isFollowing = false;
if(!$isOwnProfile){
    $followedStmt = $conn->prepare("SELECT 1 FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
    $followedStmt->execute([$viewerId, $profileId]);
    $isFollowing = (bool)$followedStmt->fetchColumn();
}
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
    <title>Perfil — <?php echo htmlspecialchars($user['nome']); ?> | NetFriends</title>
</head>
<body>

<nav class="navbar">
    <div class="logo">NetFriends</div>
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
        <?php elseif($msg === 'upload_error'): ?>
            <div class="alert alert-error">⚠️ Falha no upload. Verifique o arquivo e tente novamente.
                <?php if(isset($_GET['detail'])): ?>
                    <br><small><?php echo htmlspecialchars($_GET['detail']); ?></small>
                <?php endif; ?>
            </div>
        <?php elseif($msg === 'followed'): ?>
            <div class="alert alert-success">✅ Você começou a seguir <?php echo htmlspecialchars($user['nome']); ?>.</div>
        <?php elseif($msg === 'unfollowed'): ?>
            <div class="alert alert-info">ℹ️ Você deixou de seguir <?php echo htmlspecialchars($user['nome']); ?>.</div>
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

            <?php if($isOwnProfile): ?>
            <form class="avatar-upload-form" action="../controllers/avatarController.php" method="POST" enctype="multipart/form-data" id="avatarForm">
                <label for="foto" class="avatar-upload-label" title="Clique para alterar a foto">📷 Alterar foto</label>
                <input id="foto" type="file" name="foto" accept="image/*" style="display:none;" onchange="document.getElementById('avatarForm').submit();">
                <input type="hidden" name="upload_foto" value="1">
            </form>
            <script>
                document.querySelector('.avatar-upload-label').addEventListener('click', function(e){
                    e.preventDefault();
                    document.getElementById('foto').click();
                });
            </script>
            <?php endif; ?>
        </div>

        <!-- Info -->
        <div class="profile-info">
            <h2><?php echo htmlspecialchars($user['nome']); ?></h2>
            <div class="turma">@<?php echo ltrim(htmlspecialchars($user['turma']), '@'); ?></div>

            <div class="profile-stats">
                <?php
                $totalPosts = $conn->prepare("SELECT COUNT(*) FROM postagens WHERE id_usuario = ?");
                $totalPosts->execute([$profileId]);
                $totalPosts = $totalPosts->fetchColumn();

                $totalCurtidas = $conn->prepare("
                    SELECT COUNT(*) FROM curtidas
                    JOIN postagens ON postagens.id = curtidas.id_postagem
                    WHERE postagens.id_usuario = ?
                ");
                $totalCurtidas->execute([$profileId]);
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
                <div>
                    <b><?php echo $followingCount; ?></b>
                    <span>seguindo</span>
                </div>
                <div>
                    <b><?php echo $followersCount; ?></b>
                    <span>seguidores</span>
                </div>
            </div>

            <?php if($isOwnProfile): ?>
                <button class="edit-btn">✏️ Editar Perfil</button>
            <?php else: ?>
                <form action="../controllers/followController.php" method="POST" style="display:inline;">
                    <input type="hidden" name="target_id" value="<?php echo $profileId; ?>">
                    <button type="submit" class="edit-btn" style="padding:8px 14px;">
                        <?php echo $isFollowing ? '✅ Seguindo' : '➕ Seguir'; ?>
                    </button>
                </form>
            <?php endif; ?>
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
    $posts->execute([$profileId]);
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