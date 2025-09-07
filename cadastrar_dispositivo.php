<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost","root","","sistema_seguranca");
if ($conn->connect_error) { die("Erro de conexão: " . $conn->connect_error); }

$msg = "";
$ok  = false;

function safe($s){ return htmlspecialchars((string)$s ?? '', ENT_QUOTES, 'UTF-8'); }

if (isset($_POST['cadastrar'])) {
    $usuario          = trim($_POST['usuario'] ?? "");
    $senha_plana      = trim($_POST['senha'] ?? "");
    $ip               = trim($_POST['ip'] ?? "");
    $nome_dispositivo = trim($_POST['nome_dispositivo'] ?? "");
    $modelo           = trim($_POST['modelo'] ?? "");
    $mac              = trim($_POST['mac'] ?? "");
    $tipo_dispositivo = trim($_POST['tipo_dispositivo'] ?? "");
    $condominio       = trim($_POST['condominio'] ?? "");
    $local            = isset($_POST['local']) ? implode(", ", $_POST['local']) : "";
    $observacao       = trim($_POST['observacao'] ?? "");

    if ($usuario === "" || $senha_plana === "" || $ip === "" || $nome_dispositivo === "" || $condominio === "") {
        $msg = "Preencha Usuário, Senha, IP, Nome do dispositivo e Condomínio.";
    } else {
        $senha_hash = password_hash($senha_plana, PASSWORD_DEFAULT);

        $sql = "INSERT INTO dispositivos 
                (usuario, senha, ip, nome_dispositivo, modelo, mac, tipo_dispositivo, condominio, local, observacao)
                VALUES (?,?,?,?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param(
                "ssssssssss",
                $usuario, $senha_hash, $ip, $nome_dispositivo, $modelo, $mac, $tipo_dispositivo, $condominio, $local, $observacao
            );
            if ($stmt->execute()) {
                $ok = true;
                $msg = "Dispositivo registrado com sucesso!";
                $_POST = [];
            } else {
                $msg = "Erro ao inserir: " . safe($stmt->error);
            }
            $stmt->close();
        } else {
            $msg = "Erro na preparação da consulta.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Cadastrar Dispositivo - Multisat</title>
  <style>
    :root{ --brand:#b30000; --bg:#f4f6f9; --card:#ffffff; --border:#e5e7eb; --text:#111827; --muted:#6b7280; --ok:#16a34a; --err:#dc2626; }
    *{box-sizing:border-box}
    body{font-family:Arial,Helvetica,sans-serif;background:var(--bg);margin:0;color:var(--text)}
    header{ background:var(--brand); color:#fff; padding:12px 20px; display:flex; align-items:center; justify-content:space-between; gap:16px }
    header img{ height:46px }
    nav a{ color:#fff; text-decoration:none; font-weight:bold; padding:8px 12px; border-radius:6px; background:rgba(255,255,255,.12); margin-left:8px }
    nav a:hover{ background:rgba(255,255,255,.22) }
    .container{max-width:900px;margin:18px auto;padding:0 14px}
    .card{ background:var(--card); border:1px solid var(--border); border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,.05); overflow:hidden }
    .card-header{ font-size:18px; font-weight:700; padding:16px; border-bottom:1px solid var(--border) }
    .card-body{ padding:18px }
    form .row{ display:flex; gap:14px; flex-wrap:wrap }
    .field{ flex:1 1 280px; display:flex; flex-direction:column; margin-bottom:12px }
    label{ font-weight:600; margin-bottom:6px }
    input[type=text],input[type=password],textarea{ border:1px solid var(--border); border-radius:8px; padding:10px; background:#fff; }
    textarea{ resize:vertical; min-height:70px }
    .local-group{ display:flex; gap:12px; flex-wrap:wrap; }
    .local-group label{ font-weight:600; margin:0 10px 0 4px }
    .actions{ margin-top:10px; display:flex; gap:10px }
    .btn{ padding:10px 16px; border:none; border-radius:8px; cursor:pointer; font-weight:700; }
    .btn-primary{ background:var(--brand); color:#fff }
    .btn-secondary{ background:#111827; color:#fff }
    .msg{ margin:12px 0; padding:10px 12px; border-radius:8px; font-weight:600 }
    .msg.ok{ background:#ecfdf5; color:var(--ok); border:1px solid #a7f3d0 }
    .msg.err{ background:#fef2f2; color:var(--err); border:1px solid #fecaca }
  </style>
</head>
<body>
<header>
  <img src="/dispositivos/imagens/logo.png" alt="Logo Multisat" />
  <nav>
    <a href="cadastrar_dispositivo.php">+ Adicionar Dispositivo</a>
    <a href="listar_dispositivos.php">Lista</a>
    <a href="logout.php">Sair</a>
  </nav>
</header>

<div class="container">
  <div class="card">
    <div class="card-header">Cadastrar Novo Dispositivo</div>
    <div class="card-body">

      <?php if ($msg): ?>
        <div class="msg <?php echo $ok ? 'ok' : 'err'; ?>"><?php echo safe($msg); ?></div>
      <?php endif; ?>

      <form method="POST" autocomplete="off">
        <div class="row">
          <div class="field">
            <label>Usuário (do dispositivo)</label>
            <input type="text" name="usuario" value="<?php echo safe($_POST['usuario'] ?? '') ?>" required>
          </div>
          <div class="field">
            <label>Senha (do dispositivo)</label>
            <input type="password" name="senha" value="" required>
          </div>
          <div class="field">
            <label>IP</label>
            <input type="text" name="ip" placeholder="Ex.: 192.168.1.10" value="<?php echo safe($_POST['ip'] ?? '') ?>" required>
          </div>
          <div class="field">
            <label>Nome do dispositivo</label>
            <input type="text" name="nome_dispositivo" placeholder="Câmera Portaria, DVR, etc." value="<?php echo safe($_POST['nome_dispositivo'] ?? '') ?>" required>
          </div>
          <div class="field">
            <label>Modelo</label>
            <input type="text" name="modelo" value="<?php echo safe($_POST['modelo'] ?? '') ?>">
          </div>
          <div class="field">
            <label>MAC address</label>
            <input type="text" name="mac" placeholder="AA:BB:CC:DD:EE:FF" value="<?php echo safe($_POST['mac'] ?? '') ?>">
          </div>
          <div class="field">
            <label>Tipo de dispositivo</label>
            <input type="text" name="tipo_dispositivo" placeholder="Câmera, DVR, NVR, Face ID..." value="<?php echo safe($_POST['tipo_dispositivo'] ?? '') ?>">
          </div>
          <div class="field">
            <label>Condomínio</label>
            <input type="text" name="condominio" value="<?php echo safe($_POST['condominio'] ?? '') ?>" required>
          </div>
        </div>

        <div class="field">
          <label>Local</label>
          <div class="local-group">
            <?php
              $selecionados = isset($_POST['local']) ? (array)$_POST['local'] : [];
              $opts = ['Bloco','Apartamento','Portaria','Garagem'];
              foreach($opts as $opt){
                $ck = in_array($opt, $selecionados) ? 'checked' : '';
                echo '<label><input type="checkbox" name="local[]" value="'.$opt.'" '.$ck.'> '.$opt.'</label>';
              }
            ?>
          </div>
        </div>

        <div class="field">
          <label>Observação</label>
          <textarea name="observacao" placeholder="Ex.: Prédio A, 1º andar; Portaria (entrada principal)"><?php echo safe($_POST['observacao'] ?? '') ?></textarea>
        </div>

        <div class="actions">
          <button class="btn btn-primary" type="submit" name="cadastrar" value="1">Registrar</button>
          <a class="btn btn-secondary" href="listar_dispositivos.php">Voltar para a lista</a>
        </div>
      </form>

    </div>
  </div>
</div>
</body>
</html>
