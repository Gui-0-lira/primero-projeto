<?php
session_start();
if (isset($_SESSION['usuario'])) {
  header("Location: listar_dispositivos.php");
  exit;
}
$conn = new mysqli("localhost","root","","sistema_seguranca");
if ($conn->connect_error) { die("Erro de conexão: ".$conn->connect_error); }

$msg = "";
if (isset($_POST['entrar'])) {
  $usuario = trim($_POST['usuario'] ?? "");
  $senha   = trim($_POST['senha'] ?? "");
  if ($usuario === "" || $senha === "") {
    $msg = "Informe usuário e senha.";
  } else {
    $stmt = $conn->prepare("SELECT usuario, senha FROM usuarios WHERE usuario=? LIMIT 1");
    $stmt->bind_param("s",$usuario);
    $stmt->execute();
    $res=$stmt->get_result();
    if ($res && $res->num_rows===1) {
      $row=$res->fetch_assoc();
      if (password_verify($senha,$row['senha'])) {
        $_SESSION['usuario']=$row['usuario'];
        header("Location: listar_dispositivos.php"); exit;
      } else $msg="Senha incorreta.";
    } else $msg="Usuário não encontrado.";
    $stmt->close();
  }
}
function safe($s){ return htmlspecialchars((string)$s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Login - Multisat</title>
<style>
:root{
  --brand:#b30000; --bg:#f4f6f9; --card:#fff;
  --border:#9ca3af;           /* borda mais escura */
  --border-focus:#6b7280;     /* foco mais escuro */
  --text:#111827; --muted:#6b7280;
}
*{box-sizing:border-box}
body{margin:0;font-family:Arial,Helvetica,sans-serif;background:var(--bg);color:var(--text)}
header{background:var(--brand);padding:14px 20px;display:flex;justify-content:center;align-items:center}
header .logo img{height:125px;display:block}
.container{max-width:420px;margin:52px auto;padding:0 14px}
.card{background:rgba(255,255,255,.88);border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 8px 24px rgba(0,0,0,.08)}
.card-header{padding:16px 18px;border-bottom:1px solid #e5e7eb;font-weight:700}
.card-body{padding:18px}
.field{display:flex;flex-direction:column;margin-bottom:12px}
label{font-weight:600;margin-bottom:6px}
input[type=text],input[type=password]{
  width:100%;
  border:1px solid var(--border);
  border-radius:10px;padding:10px;background:#fff;color:var(--text);
  outline:none; transition:border-color .15s ease;
}
input[type=text]:focus, input[type=password]:focus{
  border-color:var(--border-focus);
}
.input-wrap{ position:relative; }
.toggle-pass{
  position:absolute; right:10px; top:50%; transform:translateY(-50%);
  background:transparent; border:none; cursor:pointer; padding:4px 6px;
  font-size:18px;
}
.btn{
  width:100%;padding:12px 16px;border:none;border-radius:10px;background:var(--brand);
  color:#fff;font-weight:700;cursor:pointer
}
.msg{margin:10px 0;padding:10px 12px;border-radius:10px;background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;font-weight:600}
.links{display:flex;justify-content:space-between;margin-top:10px}
.links a{color:#111827;text-decoration:none}
.links a:hover{text-decoration:underline}
</style>
</head>
<body>

<header><div class="logo"><img src="imagens/logo.png" alt="Logo Multisat"></div></header>

<div class="container">
  <div class="card">
    <div class="card-header">Login do Administrador</div>
    <div class="card-body">
      <?php if($msg): ?><div class="msg"><?php echo safe($msg); ?></div><?php endif; ?>

      <form method="POST" autocomplete="off">
        <div class="field">
          <label for="user">Usuário</label>
          <input id="user" type="text" name="usuario" value="<?php echo safe($_POST['usuario'] ?? ''); ?>" required>
        </div>

        <div class="field">
          <label for="pass">Senha</label>
          <div class="input-wrap">
            <input id="pass" type="password" name="senha" required>
            <button type="button" class="toggle-pass" onclick="togglePassword()">👁️</button>
          </div>
        </div>

        <button class="btn" type="submit" name="entrar" value="1">Entrar</button>
      </form>

      <div class="links">
        <a href="listar_dispositivos.php">Ir para a Lista</a>
        <a href="cadastrar_dispositivo.php">Cadastrar Dispositivo</a>
      </div>
    </div>
  </div>
</div>

<script>
function togglePassword(){
  const input = document.getElementById('pass');
  input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
