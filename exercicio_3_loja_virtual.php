<?php
$conn = new mysqli("localhost", "root", "", "loja_virtual");
if ($conn->connect_error) die("Falha na conexao: " . $conn->connect_error);
$conn->set_charset("utf8mb4");
$msg=""; $tipo="ok";

if(isset($_POST["cadastrar_cliente"])){
    $nome=trim($_POST["nome"]); $email=trim($_POST["email"]);
    $stmt=$conn->prepare("INSERT INTO clientes(nome,email) VALUES(?,?)");
    $stmt->bind_param("ss",$nome,$email);
    if($stmt->execute()) $msg="Cliente cadastrado!";
    else {$msg="Erro: verifique se o e-mail já está cadastrado."; $tipo="erro";}
    $stmt->close();
}
if(isset($_POST["cadastrar_pedido"])){
    $id=(int)$_POST["id_cliente"]; $valor=(float)$_POST["valor_total"];
    $stmt=$conn->prepare("INSERT INTO pedidos(id_cliente,valor_total,status) VALUES(?,?,'Pendente')");
    $stmt->bind_param("id",$id,$valor);
    if($stmt->execute()) $msg="Pedido registrado!"; else {$msg="Erro ao registrar pedido."; $tipo="erro";}
    $stmt->close();
}
if(isset($_POST["atualizar_status"])){
    $id=(int)$_POST["id_pedido"]; $status=$_POST["status"];
    $stmt=$conn->prepare("UPDATE pedidos SET status=? WHERE id_pedido=?");
    $stmt->bind_param("si",$status,$id);
    if($stmt->execute()) $msg="Status atualizado!"; else {$msg="Erro ao atualizar."; $tipo="erro";}
    $stmt->close();
}
if(isset($_POST["excluir_pedido"])){
    $id=(int)$_POST["id_pedido"];
    $stmt=$conn->prepare("DELETE FROM pedidos WHERE id_pedido=?");
    $stmt->bind_param("i",$id);
    if($stmt->execute()) $msg="Pedido excluído!"; else {$msg="Erro ao excluir."; $tipo="erro";}
    $stmt->close();
}

$clientes=$conn->query("SELECT id_cliente,nome FROM clientes ORDER BY nome");
$pedidos=$conn->query("SELECT p.*,c.nome cliente FROM pedidos p INNER JOIN clientes c ON p.id_cliente=c.id_cliente ORDER BY p.data_pedido DESC");
$resumo=$conn->query("SELECT COUNT(*) quantidade,COALESCE(SUM(valor_total),0) total FROM pedidos")->fetch_assoc();
$por_cliente=$conn->query("SELECT c.nome,COUNT(p.id_pedido) quantidade,COALESCE(SUM(p.valor_total),0) total FROM clientes c LEFT JOIN pedidos p ON c.id_cliente=p.id_cliente GROUP BY c.id_cliente,c.nome ORDER BY total DESC");
?>
<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Loja virtual</title><style>
*{box-sizing:border-box}body{font-family:Arial;background:#f1f4f8;margin:0;padding:25px;color:#263238}.wrap{max-width:1050px;margin:auto}
header,.card{background:white;padding:22px;border-radius:10px;margin-bottom:20px;box-shadow:0 2px 8px #ddd}header{background:#243b55;color:white}
h2{color:#243b55;margin-top:0}label{display:block;margin:12px 0 5px;font-weight:bold}input,select{width:100%;padding:10px;border:1px solid #ccc;border-radius:5px}
button{padding:10px 13px;border:0;border-radius:5px;background:#243b55;color:white;cursor:pointer}.danger{background:#bd3328}
.msg{padding:12px;border-radius:5px}.ok{background:#d4edda;color:#155724}.erro{background:#f8d7da;color:#721c24}
table{width:100%;border-collapse:collapse;margin-top:12px}th,td{padding:9px;border-bottom:1px solid #ddd;text-align:left}th{background:#243b55;color:white}
.scroll{overflow-x:auto}.grid{display:flex;gap:12px;flex-wrap:wrap}.metric{background:#e8f1fb;padding:15px;border-radius:7px;flex:1;min-width:180px}
</style></head><body><div class="wrap">
<header><h1>Loja Virtual — Gestão de Pedidos</h1><p>Cadastro de clientes e acompanhamento de pedidos</p></header>
<?php if($msg!=""){ ?><p class="msg <?= $tipo ?>"><?= htmlspecialchars($msg) ?></p><?php } ?>
<div class="card"><h2>Cadastrar cliente</h2><form method="POST">
<label>Nome:</label><input name="nome" maxlength="100" required>
<label>E-mail:</label><input type="email" name="email" maxlength="120" required>
<button name="cadastrar_cliente" style="margin-top:12px">Cadastrar cliente</button></form></div>
<div class="card"><h2>Registrar pedido</h2><form method="POST">
<label>Cliente:</label><select name="id_cliente" required><option value="">Selecione</option>
<?php while($c=$clientes->fetch_assoc()){ ?><option value="<?= $c["id_cliente"] ?>"><?= htmlspecialchars($c["nome"]) ?></option><?php } ?></select>
<label>Valor total (R$):</label><input type="number" name="valor_total" min="0.01" step="0.01" required>
<button name="cadastrar_pedido" style="margin-top:12px">Registrar pedido</button></form></div>
<div class="card"><h2>Resumo</h2><div class="grid"><div class="metric">Pedidos: <strong><?= $resumo["quantidade"] ?></strong></div>
<div class="metric">Total: <strong>R$ <?= number_format((float)$resumo["total"],2,",",".") ?></strong></div></div></div>
<div class="card"><h2>Pedidos cadastrados</h2><div class="scroll"><table>
<tr><th>Número</th><th>Cliente</th><th>Data</th><th>Valor</th><th>Status</th><th>Ações</th></tr>
<?php if($pedidos->num_rows){while($p=$pedidos->fetch_assoc()){ ?><tr>
<td><?= $p["id_pedido"] ?></td><td><?= htmlspecialchars($p["cliente"]) ?></td>
<td><?= date("d/m/Y H:i",strtotime($p["data_pedido"])) ?></td>
<td>R$ <?= number_format((float)$p["valor_total"],2,",",".") ?></td><td><?= htmlspecialchars($p["status"]) ?></td>
<td><form method="POST"><input type="hidden" name="id_pedido" value="<?= $p["id_pedido"] ?>">
<select name="status"><option <?= $p["status"]=="Pendente"?"selected":"" ?>>Pendente</option><option <?= $p["status"]=="Pago"?"selected":"" ?>>Pago</option><option <?= $p["status"]=="Enviado"?"selected":"" ?>>Enviado</option><option <?= $p["status"]=="Concluido"?"selected":"" ?>>Concluido</option></select>
<button name="atualizar_status">Salvar</button></form>
<form method="POST" onsubmit="return confirm('Excluir pedido?')"><input type="hidden" name="id_pedido" value="<?= $p["id_pedido"] ?>"><button class="danger" name="excluir_pedido">Excluir</button></form></td></tr>
<?php }}else{ ?><tr><td colspan="6">Nenhum pedido cadastrado.</td></tr><?php } ?></table></div></div>
<div class="card"><h2>Total de compras por cliente</h2><div class="scroll"><table><tr><th>Cliente</th><th>Pedidos</th><th>Total comprado</th></tr>
<?php while($c=$por_cliente->fetch_assoc()){ ?><tr><td><?= htmlspecialchars($c["nome"]) ?></td><td><?= $c["quantidade"] ?></td><td>R$ <?= number_format((float)$c["total"],2,",",".") ?></td></tr><?php } ?>
</table></div></div></div>
<script>
document.querySelectorAll('form').forEach(form=>form.addEventListener('submit',e=>{
 const b=e.submitter;
 if(b && b.name==='cadastrar_pedido'){
  const v=parseFloat(form.querySelector('[name="valor_total"]').value);
  if(v<=0){e.preventDefault();alert('Informe um valor maior que zero.');}
 }
}));
</script></body></html><?php $conn->close(); ?>