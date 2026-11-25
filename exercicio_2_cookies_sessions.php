<?php
session_start();
$conn = new mysqli("localhost", "root", "", "monitoramento");
if ($conn->connect_error) die("Falha na conexao: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

// Cookie identifica este navegador nesta demonstracao.
if (empty($_COOKIE["id_visitante"])) {
    $identificador = bin2hex(random_bytes(16));
    setcookie("id_visitante", $identificador, time()+2592000, "/");
} else $identificador = $_COOKIE["id_visitante"];

$stmt = $conn->prepare("SELECT id_visitante FROM visitantes WHERE identificador=?");
$stmt->bind_param("s", $identificador);
$stmt->execute();
$visit = $stmt->get_result()->fetch_assoc();
if ($visit) $id_visitante = $visit["id_visitante"];
else {
    $stmt2 = $conn->prepare("INSERT INTO visitantes (identificador) VALUES (?)");
    $stmt2->bind_param("s", $identificador);
    $stmt2->execute();
    $id_visitante = $stmt2->insert_id;
    $stmt2->close();
}
$stmt->close();

$pagina = $_GET["pagina"] ?? "inicio";
$termo = trim($_GET["busca"] ?? "");
$stmt = $conn->prepare("INSERT INTO acessos (id_visitante,pagina_acessada,termo_pesquisado) VALUES (?,?,?)");
$stmt->bind_param("iss", $id_visitante, $pagina, $termo);
$stmt->execute(); $stmt->close();

$_SESSION["paginas"] = ($_SESSION["paginas"] ?? 0) + 1;
$total = $conn->query("SELECT COUNT(*) total FROM acessos")->fetch_assoc()["total"];
$visitantes = $conn->query("SELECT COUNT(*) total FROM visitantes")->fetch_assoc()["total"];
$paginas = $conn->query("SELECT pagina_acessada,COUNT(*) quantidade FROM acessos GROUP BY pagina_acessada ORDER BY quantidade DESC");
$historico = $conn->query("SELECT a.pagina_acessada,a.termo_pesquisado,a.data_acesso,v.id_visitante FROM acessos a INNER JOIN visitantes v ON a.id_visitante=v.id_visitante ORDER BY a.data_acesso DESC LIMIT 25");
?>
<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cookies e Sessions</title><style>
*{box-sizing:border-box}body{font-family:Arial;background:#f1f4f8;margin:0;padding:25px;color:#263238}.wrap{max-width:1000px;margin:auto}
header,.card{background:white;padding:22px;border-radius:10px;margin-bottom:20px;box-shadow:0 2px 8px #ddd}header{background:#243b55;color:white}
a{display:inline-block;margin:5px;padding:10px;background:#e8eef5;border-radius:5px;color:#243b55;text-decoration:none}
input,button{padding:10px;border:1px solid #ccc;border-radius:5px}button{background:#243b55;color:white;cursor:pointer}
table{width:100%;border-collapse:collapse;margin-top:12px}th,td{padding:9px;border-bottom:1px solid #ddd;text-align:left}th{background:#243b55;color:white}
.grid{display:flex;gap:12px;flex-wrap:wrap}.metric{background:#e8f1fb;padding:15px;border-radius:7px;flex:1;min-width:150px}.scroll{overflow-x:auto}.small{font-size:12px;color:#666}
</style></head><body><div class="wrap">
<header><h1>Como os sites registram acessos?</h1><p>Demonstração de cookies, sessions e histórico dentro deste site.</p></header>
<div class="card"><h2>Navegação simulada</h2>
<a href="?pagina=inicio">Início</a><a href="?pagina=produtos">Produtos</a><a href="?pagina=promocoes">Promoções</a><a href="?pagina=contato">Contato</a>
<form method="GET" style="margin-top:15px"><input type="hidden" name="pagina" value="busca"><input name="busca" placeholder="Pesquisar neste site"><button>Pesquisar</button></form>
<p class="small">Cookie identificador: <?= htmlspecialchars(substr($identificador,0,12)) ?>... | Páginas nesta sessão: <?= $_SESSION["paginas"] ?></p>
</div>
<div class="card"><h2>Painel de monitoramento</h2><div class="grid">
<div class="metric">Visitantes identificados<br><strong><?= $visitantes ?></strong></div><div class="metric">Acessos registrados<br><strong><?= $total ?></strong></div><div class="metric">Acessos nesta sessão<br><strong><?= $_SESSION["paginas"] ?></strong></div></div>
<h3>Páginas mais acessadas</h3><div class="scroll"><table><tr><th>Página</th><th>Quantidade</th></tr>
<?php while($p=$paginas->fetch_assoc()){ ?><tr><td><?= htmlspecialchars($p["pagina_acessada"]) ?></td><td><?= $p["quantidade"] ?></td></tr><?php } ?></table></div>
<h3>Histórico recente</h3><div class="scroll"><table><tr><th>ID visitante</th><th>Página</th><th>Termo pesquisado</th><th>Data/hora</th></tr>
<?php while($h=$historico->fetch_assoc()){ ?><tr><td><?= $h["id_visitante"] ?></td><td><?= htmlspecialchars($h["pagina_acessada"]) ?></td><td><?= htmlspecialchars($h["termo_pesquisado"]??"") ?></td><td><?= htmlspecialchars($h["data_acesso"]) ?></td></tr><?php } ?></table></div>
<p class="small">Limite didático: registra apenas ações nesta aplicação. Não lê o histórico do navegador nem acompanha automaticamente outros sites.</p>
</div></div></body></html><?php $conn->close(); ?>