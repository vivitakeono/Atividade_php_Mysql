<?php
session_start();
$conn = new mysqli("localhost", "root", "", "quiz");
if ($conn->connect_error) die("Falha na conexao: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

$_SESSION["pontos"] = $_SESSION["pontos"] ?? 0;
$_SESSION["respondidas"] = $_SESSION["respondidas"] ?? 0;
$msg = "";

if (isset($_POST["responder"])) {
    $id = (int)$_POST["id_pergunta"];
    $resposta = $_POST["resposta"] ?? "";
    $stmt = $conn->prepare("SELECT resposta_correta FROM perguntas WHERE id_pergunta=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if ($r) {
        $_SESSION["respondidas"]++;
        if ($resposta === $r["resposta_correta"]) {
            $_SESSION["pontos"]++;
            $msg = "Resposta correta!";
        } else $msg = "Resposta incorreta. Tente novamente!";
    }
    $stmt->close();
}
if (isset($_POST["reiniciar"])) {
    $_SESSION["pontos"] = 0;
    $_SESSION["respondidas"] = 0;
    $msg = "Jogo reiniciado!";
}

$categorias = $conn->query("SELECT id_categoria, descricao FROM categorias ORDER BY descricao");
$cat = (int)($_GET["categoria"] ?? 0);
if ($cat > 0) {
    $stmt = $conn->prepare("SELECT p.*, c.descricao AS categoria FROM perguntas p INNER JOIN categorias c ON p.id_categoria=c.id_categoria WHERE p.id_categoria=? ORDER BY RAND() LIMIT 1");
    $stmt->bind_param("i", $cat);
    $stmt->execute();
    $pergunta = $stmt->get_result();
} else {
    $pergunta = $conn->query("SELECT p.*, c.descricao AS categoria FROM perguntas p INNER JOIN categorias c ON p.id_categoria=c.id_categoria ORDER BY RAND() LIMIT 1");
}
?>
<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Quiz</title><style>
*{box-sizing:border-box}body{font-family:Arial;background:#f1f4f8;margin:0;padding:25px;color:#263238}.wrap{max-width:800px;margin:auto}
header,.card{background:white;padding:24px;border-radius:12px;margin-bottom:20px;box-shadow:0 2px 8px #ddd}header{background:#243b55;color:white}
button,select{padding:11px;border:0;border-radius:6px}button{background:#243b55;color:white;cursor:pointer}label.op{display:block;background:#f2f5f8;padding:12px;margin:8px 0;border-radius:6px}
.msg{background:#dcefe1;padding:12px;border-radius:6px}.score{font-size:20px;font-weight:bold}
</style></head><body><div class="wrap">
<header><h1>Quiz de Conhecimentos</h1><p>Escolha uma categoria e teste seus conhecimentos!</p></header>
<div class="card"><h2>Categoria</h2><form method="GET"><select name="categoria"><option value="0">Todas</option>
<?php while($c=$categorias->fetch_assoc()){ ?><option value="<?= $c["id_categoria"] ?>" <?= $cat==$c["id_categoria"]?"selected":"" ?>><?= htmlspecialchars($c["descricao"]) ?></option><?php } ?>
</select> <button>Filtrar</button></form></div>
<div class="card"><p class="score">Pontos: <?= $_SESSION["pontos"] ?> | Respondidas: <?= $_SESSION["respondidas"] ?></p>
<?php if($msg!=""){ ?><p class="msg"><?= htmlspecialchars($msg) ?></p><?php } ?>
<?php if($pergunta && $pergunta->num_rows){ $p=$pergunta->fetch_assoc(); ?>
<h2><?= htmlspecialchars($p["pergunta"]) ?></h2><p>Categoria: <?= htmlspecialchars($p["categoria"]) ?></p>
<form method="POST"><input type="hidden" name="id_pergunta" value="<?= $p["id_pergunta"] ?>">
<?php foreach(["a","b","c"] as $l){ ?><label class="op"><input type="radio" name="resposta" value="<?= strtoupper($l) ?>" required> <?= strtoupper($l) ?>) <?= htmlspecialchars($p["alternativa_".$l]) ?></label><?php } ?>
<button name="responder">Responder</button></form>
<?php } else { ?><p>Nenhuma pergunta encontrada para esta categoria.</p><?php } ?>
<form method="POST" onsubmit="return confirm('Reiniciar pontuacao?')"><button name="reiniciar">Reiniciar jogo</button></form>
</div></div></body></html><?php $conn->close(); ?>