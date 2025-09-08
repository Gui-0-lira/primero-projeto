<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit; }

$conn = new mysqli("localhost","root","","sistema_seguranca");
if ($conn->connect_error) { die("Erro de conexão: ".$conn->connect_error); }

function safe($s){ return htmlspecialchars((string)$s ?? '', ENT_QUOTES, 'UTF-8'); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { die("ID inválido."); }

/* Carrega o dispositivo (para mostrar na confirmação) */
$stmt = $conn->prepare("SELECT id, usuario, ip, nome_dispositivo, modelo, mac, tipo_dispositivo, condominio, local, observacao FROM dispositivos WHERE id=? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$disp = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$disp) { die("Dispositivo não encontrado."); }

/* Se confirmou exclusão (POST) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === 'sim') {
    $stmt = $conn->prepare("DELETE FROM dispositivos WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $stmt->close();
        // opcional: mensagem via querystring (sua lista não usa, mas não atrapalha)
        header("Location: listar_dispositivos.php");
        exit;
    } else {
        $erroDelete = "Falha ao excluir: " . safe($stmt->error);
        $stmt->close();
    }
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
.container{max-width:700px;margin:10px auto 22px;padding:0 14px}
.card{background:rgba(255,255,255,.88);border:1px solid var(--border);border-radius:14px;box-shadow:0 8px 24px rgba(0,0,0,.08);overflow:hidden}
.card-header{padding:16px 18px;border-bottom:1px solid var(--border);font-weight:700}
.card-body{padding:18px}
.row{display:grid;grid-template-columns: 1fr 1fr;gap:10px}
.field{background:#fff;border:1px solid var(--border);border-radius:10px;padding:10px}
.field label{display:block;font-weight:700;color:#374151;margin-bottom:4px}
.actions{display:flex;gap:10px;margin-top:16px;flex-wrap:wrap}
.btn{padding:10px 14px;border:none;border-radius:10px;font-weight:700;cursor:pointer}
.btn.danger{background:#dc2626;color:#fff}
.btn.secondary{background:#1f2937;color:#fff;text-decoration:none;display:inline-block}
.msg.err{margin:12px 0;padding:10px 12px;border-radius:10px;background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-weight:600}
.small{color:var(--muted);font-size:13px;margin-top:8px}
@media (max-width:720px){ .row{grid-template-columns:1fr} }
</style>
</head>
<body>

<header><div class="logo"><img src="imagens/logo.png" alt="Logo Multisat"></div></header>

<div class="top-actions">
  <a href="listar_dispositivos.php">← Voltar para Lista</a>
  <a href="cadastrar_dispositivo.php">+ Adicionar Dispositivo</a>
  <a href="logout.php">Sair</a>
</div>

<div class="container">
  <div class="card">
    <div class="card-header">Confirmar exclusão do dispositivo #<?php echo (int)$disp['id']; ?></div>
    <div class="card-body">
      <?php if (!empty($erroDelete)): ?>
        <div class="msg err"><?php echo $erroDelete; ?></div>
      <?php endif; ?>

      <p class="small">Esta ação é permanente e não pode ser desfeita.</p>

      <div class="row">
        <div class="field">
          <label>Condomínio</label>
          <?php echo safe($disp['condominio']); ?>
        </div>
        <div class="field">
          <label>Local</label>
          <?php echo safe($disp['local']); ?>
        </div>
        <div class="field">
          <label>Nome do dispositivo</label>
          <?php echo safe($disp['nome_dispositivo']); ?>
        </div>
        <div class="field">
          <label>Usuário (do dispositivo)</label>
          <?php echo safe($disp['usuario']); ?>
        </div>
        <div class="field">
          <label>IP</label>
          <?php echo safe($disp['ip']); ?>
        </div>
        <div class="field">
          <label>Modelo</label>
          <?php echo safe($disp['modelo']); ?>
        </div>
        <div class="field">
          <label>MAC</label>
          <?php echo safe($disp['mac']); ?>
        </div>
        <div class="field">
          <label>Tipo</label>
          <?php echo safe($disp['tipo_dispositivo']); ?>
        </div>
        <div class="field" style="grid-column:1/-1">
          <label>Observação</label>
          <?php echo nl2br(safe($disp['observacao'])); ?>
        </div>
      </div>

      <form method="post" class="actions">
        <input type="hidden" name="confirm" value="sim">
        <button class="btn danger" type="submit">Excluir definitivamente</button>
        <a class="btn secondary" href="listar_dispositivos.php">Cancelar</a>
      </form>
    </div>
  </div>
</div>

</body>
</html>
