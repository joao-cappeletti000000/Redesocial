<?php
session_start();
require_once "../config/db.php";

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

$user   = $_SESSION['user'];
$userId = $user['id'];
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
    <title>Feed | Conecta Escola</title>
    <style>
        /* Like button states */
        .like-btn {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: .9rem; font-weight: 700;
            padding: 7px 16px;
            border-radius: var(--radius-pill);
            border: 1.5px solid var(--border);
            background: var(--surface-2);
            color: var(--ink-3);
            cursor: pointer; text-decoration: none;
            transition: all .2s var(--ease-out);
            user-select: none;
        }
        .like-btn:hover { background: #fff0f4; border-color: rgba(255,92,138,.3); color: var(--accent-2); transform: scale(1.04); text-decoration: none; }
        .like-btn.liked { background: #fff0f4; border-color: rgba(255,92,138,.5); color: var(--accent-2); }
        .like-btn .like-count {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 22px; height: 22px;
            background: rgba(255,92,138,.12);
            border-radius: var(--radius-pill);
            padding: 0 6px;
            font-size: .8rem; font-weight: 800;
            color: var(--accent-2);
            transition: transform .2s var(--ease);
        }
        .like-btn.liked .like-count { background: var(--accent-2); color: #fff; }
        .like-btn:hover .like-count, .like-btn.liked:hover .like-count { transform: scale(1.15); }

        /* Bounce animation on like */
        @keyframes likePop {
            0%   { transform: scale(1); }
            40%  { transform: scale(1.35); }
            70%  { transform: scale(.88); }
            100% { transform: scale(1); }
        }
        .like-btn.pop .like-count { animation: likePop .35s var(--ease) both; }

        /* Post anchor offset (navbar height) */
        .post { scroll-margin-top: 80px; }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="logo">Conecta Escola</div>
    <div class="menu">
        <a href="perfil.php">👤 Perfil</a>
        <a href="buscar.php">🔍 Buscar</a>
        <a href="logout.php">Sair</a>
    </div>
</nav>

<div class="container">

    <!-- CRIAR POST -->
    <div class="create-post">
        <form action="../controllers/postController.php" method="POST">
            <textarea name="conteudo" placeholder="Compartilhe algo com a turma..."></textarea>
            <button name="postar" type="submit">Publicar →</button>
        </form>
    </div>

    <div class="section-title">Feed</div>

    <?php
    $sql = $conn->query("
        SELECT postagens.*, usuarios.nome, usuarios.foto
        FROM postagens
        JOIN usuarios ON usuarios.id = postagens.id_usuario
        ORDER BY data_postagem DESC
    ");
    $posts = $sql->fetchAll(PDO::FETCH_ASSOC);

    if(empty($posts)):
    ?>
        <div class="empty-state">
            <div class="empty-icon">✨</div>
            <p>Nenhuma publicação ainda. Seja o primeiro!</p>
        </div>
    <?php else: foreach($posts as $post):

        // Contagem de curtidas
        $likeStmt = $conn->prepare("SELECT COUNT(*) FROM curtidas WHERE id_postagem = ?");
        $likeStmt->execute([$post['id']]);
        $likeCount = (int)$likeStmt->fetchColumn();

        // O usuário logado já curtiu?
        $likedStmt = $conn->prepare("SELECT 1 FROM curtidas WHERE id_postagem = ? AND id_usuario = ?");
        $likedStmt->execute([$post['id'], $userId]);
        $likedByMe = (bool)$likedStmt->fetch();
    ?>

        <div class="post" id="post-<?php echo (int)$post['id']; ?>">

            <!-- CABEÇALHO -->
            <div class="post-header">
                <div class="avatar">
                    <?php if(!empty($post['foto'])): ?>
                        <img src="/conecta-escola/uploads/fotos/<?php echo htmlspecialchars($post['foto']); ?>" alt="Avatar">
                    <?php else: ?>
                        <?php echo mb_strtoupper(mb_substr($post['nome'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div class="post-header-meta">
                    <b><?php echo htmlspecialchars($post['nome']); ?></b>
                    <span class="post-time">
                        <?php
                        $date = new DateTime($post['data_postagem']);
                        echo $date->format('d/m/Y · H:i');
                        ?>
                    </span>
                </div>
                <?php if((int)$post['id_usuario'] === $userId): ?>
                    <a href="../controllers/postController.php?excluir=<?php echo (int)$post['id']; ?>"
                       style="margin-left:auto;font-size:.8rem;color:var(--ink-3);opacity:.6;"
                       onclick="return confirm('Excluir este post?')">🗑</a>
                <?php endif; ?>
            </div>

            <!-- CONTEÚDO -->
            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post['conteudo'])); ?>
            </div>

            <!-- AÇÕES -->
            <div class="post-actions">
                <a  href="../controllers/likeController.php?post=<?php echo (int)$post['id']; ?>"
                    class="like-btn <?php echo $likedByMe ? 'liked' : ''; ?>"
                    data-post="<?php echo (int)$post['id']; ?>"
                    data-liked="<?php echo $likedByMe ? '1' : '0'; ?>"
                    onclick="handleLike(event, this)">
                    <?php echo $likedByMe ? '❤️' : '🤍'; ?>
                    <span class="like-count"><?php echo $likeCount; ?></span>
                    <?php echo $likedByMe ? 'Curtido' : 'Curtir'; ?>
                </a>
            </div>

            <!-- COMENTÁRIOS -->
            <div class="comments">
                <?php
                $coment = $conn->prepare("
                    SELECT comentarios.*, usuarios.nome, usuarios.foto
                    FROM comentarios
                    JOIN usuarios ON usuarios.id = comentarios.id_usuario
                    WHERE id_postagem = ?
                    ORDER BY comentarios.id ASC
                ");
                $coment->execute([$post['id']]);
                foreach($coment as $c):
                ?>
                    <div class="comment">
                        <div class="avatar" style="width:30px;height:30px;font-size:.75rem;flex-shrink:0;">
                            <?php if(!empty($c['foto'])): ?>
                                <img src="/conecta-escola/uploads/fotos/<?php echo htmlspecialchars($c['foto']); ?>" alt="">
                            <?php else: ?>
                                <?php echo mb_strtoupper(mb_substr($c['nome'], 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <b><?php echo htmlspecialchars($c['nome']); ?></b>
                            <span> <?php echo htmlspecialchars($c['texto']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- FORM COMENTAR -->
            <form class="comment-form" action="../controllers/commentController.php" method="POST">
                <input type="hidden" name="post" value="<?php echo (int)$post['id']; ?>">
                <input name="texto" placeholder="Escreva um comentário..." required>
                <button type="submit">Enviar</button>
            </form>

        </div>

    <?php endforeach; endif; ?>

</div>

<script>
/* Otimistic UI para curtidas — faz a animação e atualiza o contador
   sem esperar reload, mas ainda envia o request ao servidor. */
async function handleLike(e, btn){
    e.preventDefault();

    const postId  = btn.dataset.post;
    const liked   = btn.dataset.liked === '1';
    const countEl = btn.querySelector('.like-count');
    const newLiked = !liked;

    // Atualiza visualmente de imediato
    btn.dataset.liked = newLiked ? '1' : '0';
    btn.classList.toggle('liked', newLiked);
    btn.innerHTML = `${newLiked ? '❤️' : '🤍'} <span class="like-count">${parseInt(countEl.textContent) + (newLiked ? 1 : -1)}</span> ${newLiked ? 'Curtido' : 'Curtir'}`;
    btn.classList.add('pop');
    btn.addEventListener('animationend', () => btn.classList.remove('pop'), {once:true});

    // Envia ao servidor silenciosamente
    try {
        await fetch(`../controllers/likeController.php?post=${postId}`, {method:'GET',credentials:'same-origin'});
    } catch(err) {
        // Reverte se falhou
        btn.dataset.liked = liked ? '1' : '0';
    }
}
</script>

</body>
</html>
