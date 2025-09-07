<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
$conn = new mysqli("localhost","root","","sistema_seguranca");
if ($conn->connect_error) { die("Erro de conexão: " . $conn->connect_error); }

function safe($s){ return htmlspecialchars((string)$s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Sistema de Segurança - Multisat</title>
  <style>
    :root{ --brand:#b30000; --bg:#f4f6f9; --card:#ffffff; --border:#e5e7eb; --text:#111827; --muted:#6b7280; }
    *{box-sizing:border-box}
    body{font-family:Arial,Helvetica,sans-serif;background:var(--bg);margin:0;color:var(--text)}
    header{ background:var(--brand); color:#fff; padding:12px 20px; display:flex; align-items:center; justify-content:space-between; gap:16px }
    header img{ height:46px }
    nav a{ color:#fff; text-decoration:none; font-weight:bold; padding:8px 12px; border-radius:6px; background:rgba(255,255,255,.12); margin-left:8px }
    nav a:hover{ background:rgba(255,255,255,.22) }
    .container{max-width:1100px;margin:18px auto;padding:0 14px}
    .card{ background:var(--card); border:1px solid var(--border); border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,.05); overflow:hidden; margin-bottom:16px }
    .section-title{ font-size:18px; font-weight:700; padding:14px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; background:#fff }
    .hint{ color:var(--muted); font-size:12px }
    .local-row{ display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-top:1px solid var(--border); background:#fff; cursor:pointer; }
    .local-row:hover{ background:#fafafa }
    .local-name{ font-weight:700; color:#065f46 }
    .table-wrap{ display:none; background:#fff }
    table{ width:100%; border-collapse:collapse }
    th,td{ padding:10px 12px; border-top:1px solid var(--border); text-align:left }
    thead th{ background:var(--brand); color:#fff; border-top:none }
    td.actions a{ text-decoration:none; font-weight:600; margin-right:8px; padding:6px 10px; border-radius:6px; border:1px solid var(--border); background:#fff; color:#1f2937; }
    td.actions a:hover{ background:#f3f4f6 }
    .empty{ padding:18px; color:var(--muted) }
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
<?php
$condos = $conn->query("
  SELECT DISTINCT condominio
  FROM dispositivos
  WHERE condominio IS NOT NULL AND condominio <> ''
  ORDER BY condominio ASC
");

if (!$condos || $condos->num_rows === 0){
  echo '<div class="card"><div class="empty">Nenhum dispositivo cadastrado ainda.</div></div>';
} else {
  while($c = $condos->fetch_assoc()){
    $condominio = $c['condominio'];
    ?>
    <div class="card">
      <div class="section-title">
        <div>Condomínio: <?php echo safe($condominio); ?></div>
        <div class="hint">Clique no local para abrir/fechar a lista</div>
      </div>
      <?php
      $locais = $conn->query("
        SELECT DISTINCT COALESCE(NULLIF(local, ''), 'Sem local definido') AS local_norm
        FROM dispositivos
        WHERE condominio = '".$conn->real_escape_string($condominio)."'
        ORDER BY local_norm ASC
      ");
      if ($locais && $locais->num_rows > 0){
        while($l = $locais->fetch_assoc()){
          $local = $l['local_norm'];
          $tblId = 'tbl_'.md5($condominio.'|'.$local);
          ?>
          <div class="local-row" onclick="toggleTable('<?php echo $tblId; ?>')">
            <div class="local-name">Local: <?php echo safe($local); ?></div>
            <div class="hint">mostrar/ocultar</div>
          </div>

          <div id="<?php echo $tblId; ?>" class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>IP</th>
                  <th>Usuário</th>
                  <th>Senha</th>
                  <th>Dispositivo</th>
                  <th>MAC</th>
                  <th>Modelo</th>
                  <th>Observação</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
              <?php
              $sql = "
                SELECT id, ip, usuario, nome_dispositivo, mac, modelo, observacao
                     , '******' AS senha_mask
                FROM dispositivos
                WHERE condominio = '".$conn->real_escape_string($condominio)."'
                  AND COALESCE(NULLIF(local,''), 'Sem local definido') = '".$conn->real_escape_string($local)."'
                ORDER BY nome_dispositivo ASC, ip ASC
              ";
              $disps = $conn->query($sql);
              if ($disps && $disps->num_rows > 0){
                while($d = $disps->fetch_assoc()){
                  ?>
                  <tr>
                    <td><?php echo safe($d['ip']); ?></td>
                    <td><?php echo safe($d['usuario']); ?></td>
                    <td><?php echo safe($d['senha_mask']); ?></td>
                    <td><?php echo safe($d['nome_dispositivo']); ?></td>
                    <td><?php echo safe($d['mac']); ?></td>
                    <td><?php echo safe($d['modelo']); ?></td>
                    <td><?php echo safe($d['observacao']); ?></td>
                    <td class="actions">
                      <a href="editar_dispositivo.php?id=<?php echo (int)$d['id']; ?>">Editar</a>
                      <a href="excluir_dispositivo.php?id=<?php echo (int)$d['id']; ?>" onclick="return confirm('Deseja excluir este dispositivo?')">Excluir</a>
                    </td>
                  </tr>
                  <?php
                }
              } else {
                echo '<tr><td colspan="8" class="empty">Nenhum dispositivo neste local.</td></tr>';
              }
              ?>
              </tbody>
            </table>
          </div>
          <?php
        }
      } else {
        echo '<div class="empty">Sem locais cadastrados neste condomínio.</div>';
      }
      ?>
    </div>
    <?php
  }
}
$conn->close();
?>
</div>

<script>
  function toggleTable(id){
    var el = document.getElementById(id);
    if (!el) return;
    el.style.display = (el.style.display === "none" || el.style.display === "") ? "block" : "none";
  }
</script>
</body>
</html>
