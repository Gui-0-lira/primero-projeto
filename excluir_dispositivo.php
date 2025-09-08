<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit; }

$conn = new mysqli("localhost","root","","sistema_seguranca");
if ($conn->connect_error) { die("Erro de conexão: ".$conn->connect_error); }
function safe($s){ return htmlspecialchars((string)$s ?? '', ENT_QUOTES, 'UTF-8'); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
if ($id <= 0) { die("ID inválido."); }

$msg = ""; $ok = false;

/* Se confirmou via POST, exclui */
if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
  $stmt = $conn->prepare("DELETE FROM dispositivos WHERE id=? LIMIT 1");
  $stmt->bind_param("i", $id);
  if ($stmt->execute() && $stmt->affected_rows > 0) {
    $ok = true;
    $msg = "Dispositivo #{$id} excluído com sucesso.";
  } else {
    $msg = "Erro ao excluir (pode já ter sido removido).";
  }
  $stmt->close();
}

/* Se não excluiu ainda, busca dados para mostrar no card de confirmação */
$disp = null;
if (!$ok) {
  $stmt = $conn->prepare("SELECT * FROM dispositivos WHERE id=? LIMIT 1");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $disp = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$disp) { $msg = "Dispositivo não encontrado ou já excluído."; }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Excluir Dispositivo - Multisat</title>
<style>
:root{--brand:#b30000;--bg:#f4f6f9;--card:#fff;--border:#e5e7eb;--text:#111827;--muted:#6b7280}
*{box-sizing:border-box}
body{margin:0;font-family:Arial,Helvetica,sans-serif;background:var(--bg);color:var(--text)}
header{background:var(--brand);padding:14px 20px;display:flex;justify-content:center;align-items:center}
header .logo img{height:125px;display:block}
.top-actions{display:flex;gap:10px;justify-content:center;margin:12px 0}
.top-actions a{padding:8px 12px;border-radius:10px;text-decoration:none;font-weight:700;border:1px solid var(--border);background:#fff;color:#111827}
.container{max-width:800px;margin:10px auto 22px;padding:0 14px}
.card{background:rgba(255,255,255,.88);border:1px solid var(--border);border-radius:14px;box-shadow:0 8px 24px rgba(0,0,0,.08);overflow:hidden}
.card-header{padding:16px 18px;border-bottom:1px solid var(--border);font-weight:700}
.card-body{padding:18px}
.row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.item{background:#fff;border:1px solid var(--border);border-radius:10px;padding:10px}
.label{font-size:12px;color:var(--muted);margin-bottom:4px}
.value{font-weight:700}
.actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}
.btn{padding:10px 14px;border:none;border-radius:10px;font-weight:700;cursor:pointer}
.btn.danger{background:#dc2626;color:#fff}
.btn.secondary{background:#1f2937;color:#fff}
.msg{margin:12px 0;padding:10px 12px;border-radius:10px;font-weight:600}
.msg.ok{background:#ecfdf5;color:#16a34a;border:1px solid #a7f3d0}
.msg.err{background:#fef2f2;color:#b91c1c;border:1px solid #fecaca}
</style>
</head>
<body>

<header><div class="logo"><img src="imagens/logo.png" alt="Logo Multisat"></div></header>

<div class="top-actions">
  <a href="listar_dispositivos.php">← Voltar para Lista</a>
  <a href="cadastrar_dispositivo.php">+ Novo</a>
  <a href="logout.php">Sair</a>
</div>

<div class="container">
  <div class="card">
    <div class="card-header">Excluir Dispositivo #<?php echo (int)$id; ?></div>
    <div class="card-body">
      <?php if($msg): ?>
        <div class="msg <?php echo $ok ? 'ok' : 'err'; ?>"><?php echo safe($msg); ?></div>
      <?php endif; ?>

      <?php if(!$ok && $disp): ?>
        <p><strong>Tem certeza que deseja excluir este dispositivo?</strong></p>
        <div class="row">
          <div class="item"><div class="label">Condomínio</div><div class="value"><?php echo safe($disp['condominio']); ?></div></div>
          <div class="item"><div class="label">Local</div><div class="value"><?php echo safe($disp['local']); ?></div></div>
          <div class="item"><div class="label">Nome do dispositivo</div><div class="value"><?php echo safe($disp['nome_dispositivo']); ?></div></div>
          <div class="item"><div class="label">IP</div><div class="value"><?php echo safe($disp['ip']); ?></div></div>
          <div class="item"><div class="label">Usuário</div><div class="value"><?php echo safe($disp['usuario']); ?></div></div>
          <div class="item"><div class="label">MAC</div><div class="value"><?php echo safe($disp['mac']); ?></div></div>
          <div class="item"><div class="label">Modelo</div><div class="value"><?php echo safe($disp['modelo']); ?></div></div>
          <div class="item"><div class="label">Observação</div><div class="value"><?php echo safe($disp['observacao']); ?></div></div>
        </div>

        <form method="POST" class="actions" onsubmit="return confirm('Confirmar exclusão do dispositivo #<?php echo (int)$id; ?>?');">
          <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
          <button class="btn danger" type="submit" name="confirm" value="yes">Sim, excluir</button>
          <a class="btn secondary" href="listar_dispositivos.php">Cancelar</a>
        </form>
      <?php elseif($ok): ?>
        <div class="actions">
          <a class="btn secondary" href="listar_dispositivos.php">Voltar para Lista</a>
          <a class="btn" style="background:#16a34a;color:#fff" href="cadastrar_dispositivo.php">Cadastrar novo</a>
        </div>
      <?php else: ?>
        <div class="actions">
          <a class="btn secondary" href="listar_dispositivos.php">Voltar para Lista</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

</body>
</html>
