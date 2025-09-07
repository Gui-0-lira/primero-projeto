<?php
session_start();

// Se já estiver logado, manda pra lista
if (isset($_SESSION['usuario'])) {
  header("Location: listar_dispositivos.php");
  exit;
}

$conn = new mysqli("localhost","root","","sistema_seguranca");
if ($conn->connect_error) { die("Erro de conexão: " . $conn->connect_error); }

$msg = "";
if (isset($_POST['entrar'])) {
  $usuario = trim($_POST['usuario'] ?? "");
  $senha   = trim($_POST['senha'] ?? "");

  if ($usuario === "" || $senha === "") {
    $msg = "Informe usuário e senha.";
  } else {
    $stmt = $conn->prepare("SELECT usuario, senha FROM usuarios WHERE usuario = ? LIMIT 1");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows === 1) {
      $row = $res->fetch_assoc();
      if (password_verify($senha, $row['senha'])) {
        $_SESSION['usuario'] = $row['usuario'];
        header("Location: listar_dispositivos.php");
        exit;
      } else {
        $msg = "Senha incorreta.";
      }
    } else {
      $msg = "Usuário não encontrado.";
    }
    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Login - Multisat</title>
  <style>
    :root{
      --brand:#b30000; --bg:#f4f6f9; --card:#ffffff; --border:#e5e7eb;
      --text:#111827; --muted:#6b7280; --ok:#16a34a; --err:#dc2626;
    }
    *{box-sizing:border-box}
    body{font-family:Arial,Helvetica,sans-serif;background:var(--bg);margin:0;color:var(--text)}
    header{
      background:var(--brand); color:#fff; padding:12px 20px;
      display:flex; align-items:center; justify-content:space-between; gap:16px
    }
    header img{ height:46px }
    nav a{
      color:#fff; text-decoration:none; font-weight:bold; padding:8px 12px;
      border-radius:6px; background:rgba(255,255,255,.12); margin-left:8px
    }
    nav a:hover{ background:rgba(255,255,255,.22) }

    .container{max-width:420px;margin:52px auto;padding:0 14px}
    .card{
      background:var(--card); border:1px solid var(--border); border-radius:12px;
      box-shadow:0 4px 16px rgba(0,0,0,.05); overflow:hidden
    }
    .card-header{ font-size:18px; font-weight:700; padding:16px; border-bottom:1px solid var(--border) }
    .card-body{ padding:18px }

    .field{ display:flex; flex-direction:column; margin-bottom:14px }
    label{ font-weight:600; margin-bottom:6px }
    input[type=text], input[type=password]{
      border:1px solid var(--border); border-radius:8px; padding:10px; background:#fff;
    }
    .btn{
      width:100%; padding:12px 16px; border:none; border-radius:8px; cursor:pointer;
      font-weight:700; background:var(--brand); color:#fff; margin-top:8px
    }
    .msg{ margin:12px 0; padding:10px 12px; border-radius:8px; font-weight:600 }
    .msg.err{ background:#fef2f2; color:var(--err); border:1px solid #fecaca }
    .hint{ color:var(--muted); font-size:12px; margin-top:8px }
    .links{ display:flex; justify-content:space-between; margin-top:8px; font-size:14px }
    .links a{ color:#1f2937; text-decoration:none; }
    .links a:hover{ text-decoration:underline; }
  </style>
</head>
<body>
<header>
  <img src="/dispositivos/imagens/logo.png" alt="Logo Multisat" />
  <nav>
    <a href="login.php">Login</a>
  </nav>
</header>

<div class="container">
  <div class="card">
    <div class="card-header">Login do Administrador</div>
    <div class="card-body">

      <?php if ($msg): ?>
        <div class="msg err"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <form method="POST" autocomplete="off">
        <div class="field">
          <label>Usuário</label>
          <input type="text" name="usuario" value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario'], ENT_QUOTES, 'UTF-8') : '' ?>" required>
        </div>
        <div class="field">
          <label>Senha</label>
          <input type="password" name="senha" required>
        </div>
        <button class="btn" type="submit" name="entrar" value="1">Entrar</button>
      </form>

      <div class="hint">
        Use seu usuário cadastrado pelo administrador. Se você é o primeiro acesso, crie o admin.
      </div>
      <div class="links">
        <a href="listar_dispositivos.php">Ir para a Lista</a>
        <a href="cadastrar_dispositivo.php">Cadastrar Dispositivo</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>
