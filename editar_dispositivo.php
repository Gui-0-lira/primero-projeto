<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit; }

$conn = new mysqli("localhost","root","","sistema_seguranca");
if ($conn->connect_error) { die("Erro de conexão: ".$conn->connect_error); }

function safe($s){ return htmlspecialchars((string)$s ?? '', ENT_QUOTES, 'UTF-8'); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id<=0) { die("ID inválido."); }

/* Carrega registro atual */
$stmt = $conn->prepare("SELECT * FROM dispositivos WHERE id=? LIMIT 1");
$stmt->bind_param("i",$id);
$stmt->execute();
$disp = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$disp) { die("Dispositivo não encontrado."); }

/* Mensagens */
$msg=""; $ok=false;

/* Salvar alterações */
if (isset($_POST['salvar'])) {
  $usuario          = trim($_POST['usuario'] ?? "");
  $senha_plana      = trim($_POST['senha'] ?? "");   // se vazio, mantém a atual
  $ip               = trim($_POST['ip'] ?? "");
  $nome_dispositivo = trim($_POST['nome_dispositivo'] ?? "");
  $modelo           = trim($_POST['modelo'] ?? "");
  $mac              = trim($_POST['mac'] ?? "");
  $tipo_dispositivo = trim($_POST['tipo_dispositivo'] ?? "");
  $condominio       = trim($_POST['condominio'] ?? "");
  $local            = isset($_POST['local']) ? implode(", ", (array)$_POST['local']) : "";
  $observacao       = trim($_POST['observacao'] ?? "");

  if ($usuario==="" || $ip==="" || $nome_dispositivo==="" || $condominio==="") {
    $msg = "Preencha Usuário, IP, Nome do dispositivo e Condomínio.";
  } else {
    if ($senha_plana!=="") {
      $senha_hash = password_hash($senha_plana, PASSWORD_DEFAULT);
      $sql = "UPDATE dispositivos SET usuario=?, senha=?, ip=?, nome_dispositivo=?, modelo=?, mac=?, tipo_dispositivo=?, condominio=?, local=?, observacao=? WHERE id=?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssssssssssi",$usuario,$senha_hash,$ip,$nome_dispositivo,$modelo,$mac,$tipo_dispositivo,$condominio,$local,$observacao,$id);
    } else {
      $sql = "UPDATE dispositivos SET usuario=?, ip=?, nome_dispositivo=?, modelo=?, mac=?, tipo_dispositivo=?, condominio=?, local=?, observacao=? WHERE id=?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("sssssssssi",$usuario,$ip,$nome_dispositivo,$modelo,$mac,$tipo_dispositivo,$condominio,$local,$observacao,$id);
    }

    if ($stmt->execute()) {
      $ok=true; $msg="Dispositivo atualizado com sucesso!";
      $stmt->close();

      // recarrega os dados atualizados
      $stmt = $conn->prepare("SELECT * FROM dispositivos WHERE id=? LIMIT 1");
      $stmt->bind_param("i",$id);
      $stmt->execute();
      $disp = $stmt->get_result()->fetch_assoc();
      $stmt->close();
    } else {
      $msg="Erro ao atualizar: ".$stmt->error;
      $stmt->close();
    }
  }
}

/* Pré-seleção dos checkboxes de local */
$locaisMarcados = array_map('trim', $disp['local'] ? explode(',', $disp['local']) : []);
$locaisMarcados = array_filter($locaisMarcados, fn($v)=>$v!=="");
$optsLocais = ['Bloco','Apartamento','Portaria','Garagem'];
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Editar Dispositivo - Multisat</title>
<style>
:root{--brand:#b30000;--bg:#f4f6f9;--card:#fff;--border:#e5e7eb;--text:#111827;--muted:#6b7280}
*{box-sizing:border-box}
body{margin:0;font-family:Arial,Helvetica,sans-serif;background:var(--bg);color:var(--text)}
header{background:var(--brand);padding:14px 20px;display:flex;justify-content:center;align-items:center}
header .logo img{height:125px;display:block}
.top-actions{display:flex;gap:10px;justify-content:center;margin:12px 0}
.top-actions a, .top-actions button.linklike{
  padding:8px 12px;border-radius:10px;text-decoration:none;font-weight:700;
  border:1px solid var(--border);background:#fff;color:#111827;cursor:pointer
}
.top-actions a.primary{background:#16a34a;color:#fff;border-color:#16a34a}
.container{max-width:900px;margin:10px auto 22px;padding:0 14px}
.card{background:rgba(255,255,255,.88);border:1px solid var(--border);border-radius:14px;box-shadow:0 8px 24px rgba(0,0,0,.08)}
.card-header{padding:16px 18px;border-bottom:1px solid var(--border);font-weight:700}
.card-body{padding:18px}
.row{display:flex;gap:14px;flex-wrap:wrap}
.field{flex:1 1 280px;display:flex;flex-direction:column;margin-bottom:12px}
label{font-weight:600;margin-bottom:6px}
input[type=text],input[type=password],textarea{border:1px solid var(--border);border-radius:10px;padding:10px;background:#fff}
textarea{resize:vertical;min-height:70px}
.local-group{display:flex;gap:12px;flex-wrap:wrap}
.btn{padding:12px 16px;border:none;border-radius:10px;background:var(--brand);color:#fff;font-weight:700;cursor:pointer}
.btn.secondary{background:#1f2937}
.btn.danger{background:#dc2626}
.msg{margin:12px 0;padding:10px 12px;border-radius:10px;font-weight:600}
.msg.ok{background:#ecfdf5;color:#16a34a;border:1px solid #a7f3d0}
.msg.err{background:#fef2f2;color:#b91c1c;border:1px solid #fecaca}
.small-hint{color:var(--muted);font-size:12px;margin-top:-6px;margin-bottom:8px}
</style>
</head>
<body>

<header><div class="logo"><img src="imagens/logo.png" alt="Logo Multisat"></div></header>

<div class="top-actions">
  <a href="listar_dispositivos.php">← Voltar para Lista</a>
  <a class="primary" href="cadastrar_dispositivo.php">+ Novo</a>
  <a href="logout.php">Sair</a>
</div>

<div class="container">
  <div class="card">
    <div class="card-header">Editar Dispositivo #<?php echo (int)$disp['id']; ?></div>
    <div class="card-body">

      <?php if($msg): ?>
        <div class="msg <?php echo $ok?'ok':'err'; ?>"><?php echo safe($msg); ?></div>
      <?php endif; ?>

      <form method="POST" autocomplete="off">
        <div class="row">
          <div class="field">
            <label>Usuário (do dispositivo)</label>
            <input type="text" name="usuario" value="<?php echo safe($disp['usuario']); ?>" required>
          </div>

          <div class="field">
            <label>Senha (do dispositivo)</label>
            <input type="password" name="senha" placeholder="Deixe em branco para manter a atual">
            <div class="small-hint">Se você não digitar nada, a senha permanece a mesma.</div>
          </div>

          <div class="field">
            <label>IP</label>
            <input type="text" name="ip" value="<?php echo safe($disp['ip']); ?>" required>
          </div>

          <div class="field">
            <label>Nome do dispositivo</label>
            <input type="text" name="nome_dispositivo" value="<?php echo safe($disp['nome_dispositivo']); ?>" required>
          </div>

          <div class="field">
            <label>Modelo</label>
            <input type="text" name="modelo" value="<?php echo safe($disp['modelo']); ?>">
          </div>

          <div class="field">
            <label>MAC address</label>
            <input type="text" name="mac" value="<?php echo safe($disp['mac']); ?>">
          </div>

          <div class="field">
            <label>Tipo de dispositivo</label>
            <input type="text" name="tipo_dispositivo" value="<?php echo safe($disp['tipo_dispositivo']); ?>">
          </div>

          <div class="field">
            <label>Condomínio</label>
            <input type="text" name="condominio" value="<?php echo safe($disp['condominio']); ?>" required>
          </div>
        </div>

        <div class="field">
          <label>Local</label>
          <div class="local-group">
            <?php foreach($optsLocais as $opt):
              $checked = in_array($opt, $locaisMarcados, true) ? 'checked' : '';
            ?>
              <label><input type="checkbox" name="local[]" value="<?php echo safe($opt); ?>" <?php echo $checked; ?>> <?php echo safe($opt); ?></label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="field">
          <label>Observação</label>
          <textarea name="observacao"><?php echo safe($disp['observacao']); ?></textarea>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <button class="btn" type="submit" name="salvar" value="1">Salvar alterações</button>
          <a class="btn secondary" href="listar_dispositivos.php">Cancelar</a>
          <a class="btn danger" href="excluir_dispositivo.php?id=<?php echo (int)$disp['id']; ?>"
             onclick="return confirm('Tem certeza que deseja excluir este dispositivo?');">Excluir</a>
        </div>
      </form>

    </div>
  </div>
</div>

</body>
</html>