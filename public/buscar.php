<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/conecta-escola/assets/css/style.css">
    <title>Buscar | Conecta Escola</title>
    <style>
        .search-wrap { position: relative; }
        #live-results {
            position: absolute;
            top: calc(100% + 8px); left: 0; right: 0;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            z-index: 50; display: none;
            max-height: 400px; overflow-y: auto;
        }
        #live-results.open { display: block; animation: slideUp .18s var(--ease-out) both; }
        .live-item {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px; cursor: pointer;
            transition: background .15s;
            border-bottom: 1px solid var(--border);
            text-decoration: none; color: inherit;
        }
        .live-item:last-child { border-bottom: none; }
        .live-item:hover { background: var(--accent-pale); text-decoration: none; }
        .live-item .s-avatar {
            width: 42px; height: 42px; border-radius: 50%;
            background: linear-gradient(135deg,var(--accent),var(--accent-2));
            color:#fff; font-family:var(--font-display); font-weight:700; font-size:.95rem;
            display:flex; align-items:center; justify-content:center; flex-shrink:0; overflow:hidden;
        }
        .live-item .s-avatar img { width:100%; height:100%; object-fit:cover; }
        .live-item b { font-family:var(--font-display); font-weight:700; font-size:.93rem; display:block; }
        .live-item span { font-size:.8rem; color:var(--ink-3); }
        .live-badge {
            margin-left:auto; padding:3px 10px;
            background:var(--accent-pale); color:var(--accent);
            border-radius:var(--radius-pill); font-size:.75rem; font-weight:700; flex-shrink:0;
        }
        .live-empty { padding:22px 16px; text-align:center; color:var(--ink-3); font-size:.9rem; }
        .search-hint { font-size:.82rem; color:var(--ink-3); margin-top:10px; padding-left:4px; }
    </style>
</head>
<body>

<?php
session_start();
require_once "../config/db.php";

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

$busca = trim($_GET['q'] ?? '');
$resultados = [];

if($busca !== ''){
    $sql = $conn->prepare("SELECT * FROM usuarios WHERE nome LIKE ? ORDER BY nome ASC");
    $sql->execute(["%$busca%"]);
    $resultados = $sql->fetchAll(PDO::FETCH_ASSOC);
}

// Endpoint AJAX para live search
if(isset($_GET['ajax']) && $_GET['ajax'] === '1'){
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($resultados, JSON_UNESCAPED_UNICODE);
    exit();
}
?>

<nav class="navbar">
    <div class="logo">Conecta Escola</div>
    <div class="menu">
        <a href="index.php">🏠 Feed</a>
        <a href="perfil.php">👤 Perfil</a>
        <a href="logout.php">Sair</a>
    </div>
</nav>

<div class="search-page">

    <div class="section-title" style="margin-bottom:10px;">Buscar alunos</div>

    <form method="GET" autocomplete="off" onsubmit="doSearch(); return false;">
        <div class="search-bar search-wrap">
            <input
                id="search-input"
                name="q"
                placeholder="Digite o nome do aluno..."
                value="<?php echo htmlspecialchars($busca); ?>"
                autofocus
                autocomplete="off"
            >
            <button type="button" onclick="doSearch()">Buscar</button>
            <div id="live-results"></div>
        </div>
    </form>
    <p class="search-hint">✨ Os resultados aparecem enquanto você digita</p>

    <!-- Resultados estáticos -->
    <div id="static-results">
    <?php if($busca !== ''): ?>
        <p style="font-size:.85rem;color:var(--ink-3);margin-bottom:16px;margin-top:10px;">
            <strong><?php echo count($resultados); ?></strong> resultado(s) para
            "<strong><?php echo htmlspecialchars($busca); ?></strong>"
        </p>
        <?php if(!empty($resultados)): ?>
            <?php foreach($resultados as $u): ?>
                <a class="search-result-item" href="perfil.php?id=<?php echo (int)$u['id']; ?>">
                    <div class="s-avatar">
                        <?php if(!empty($u['foto'])): ?>
                            <img src="/conecta-escola/uploads/fotos/<?php echo htmlspecialchars($u['foto']); ?>" alt="">
                        <?php else: ?>
                            <?php echo mb_strtoupper(mb_substr($u['nome'], 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div class="search-result-info">
                        <b><?php echo htmlspecialchars($u['nome']); ?></b>
                        <span><?php echo htmlspecialchars($u['email']); ?></span>
                    </div>
                    <div class="search-result-badge">@<?php echo ltrim(htmlspecialchars($u['turma']), '@'); ?></div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state"><div class="empty-icon">🔍</div>
                <p>Nenhum aluno encontrado.</p></div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state"><div class="empty-icon">👥</div>
            <p>Comece a digitar para encontrar colegas.</p></div>
    <?php endif; ?>
    </div>

</div>

<script>
const input   = document.getElementById('search-input');
const liveBox = document.getElementById('live-results');
const staticBox = document.getElementById('static-results');
let timer;

input.addEventListener('input', () => {
    clearTimeout(timer);
    const q = input.value.trim();
    if(q.length < 1){ close(); return; }
    timer = setTimeout(() => fetchLive(q), 220);
});

input.addEventListener('keydown', e => { if(e.key==='Escape') close(); });
document.addEventListener('click', e => {
    if(!input.closest('.search-wrap').contains(e.target)) close();
});

function close(){ liveBox.innerHTML=''; liveBox.classList.remove('open'); staticBox.style.opacity='1'; }

async function fetchLive(q){
    try{
        const r = await fetch(`buscar.php?ajax=1&q=${encodeURIComponent(q)}`);
        renderLive(await r.json(), q);
    }catch(e){ console.error(e); }
}

function renderLive(users, q){
    staticBox.style.opacity = '.35';
    if(!users.length){
        liveBox.innerHTML = `<div class="live-empty">Nenhum aluno encontrado para "<strong>${esc(q)}</strong>"</div>`;
    } else {
        liveBox.innerHTML = users.map(u => {
            const foto = u.foto
                ? `<img src="/conecta-escola/uploads/fotos/${esc(u.foto)}" alt="">`
                : u.nome.charAt(0).toUpperCase();
            return `<a class="live-item" href="perfil.php?id=${parseInt(u.id)}">
                <div class="s-avatar">${foto}</div>
                <div><b>${esc(u.nome)}</b><span>${esc(u.email)}</span></div>
                <div class="live-badge">${esc(u.turma)}</div>
            </a>`;
        }).join('');
    }
    liveBox.classList.add('open');
}

function esc(s){
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}
function doSearch(){
    const q = input.value.trim();
    if(q) window.location.href = `buscar.php?q=${encodeURIComponent(q)}`;
}
</script>
</body>
</html>
