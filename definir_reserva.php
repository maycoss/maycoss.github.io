<?php
/**
 * DEFINIR PRECO DE RESERVA PADRAO -- lotes sem preco de reserva recebem R$ 50,00
 *
 * Uso: subir este arquivo na pasta /colecao e abrir /definir_reserva.php logado como admin.
 * 1o acesso: mostra os lotes sem preco de reserva (preview, nao altera nada).
 * Clicar em "APLICAR": faz backup de data/hw.db e grava preco_reserva = 50.00 nesses lotes.
 *
 * O lance inicial de cada lote NAO e alterado -- so o campo "Preco reserva".
 * Depois de usar, DELETE este arquivo do servidor.
 */
// Acha o config.php/_base.php esteja este script na raiz /colecao ou numa subpasta (ex.: /colecao/reserva).
$base = __DIR__;
if (!is_file($base . '/config.php') && is_file(dirname($base) . '/config.php')) {
    $base = dirname($base);
}
require_once $base . '/config.php';
require_once $base . '/_base.php';
requireAdmin();
$pdo = db();
$usuario = usuarioLogado();
$msg = '';
$erro = '';

$VALOR = 50.00;
$WHERE = 'preco_reserva IS NULL OR preco_reserva <= 0';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirmar'] ?? '') === '1') {
    // Backup do banco antes de qualquer alteracao.
    try {
        $info = $pdo->query('PRAGMA database_list')->fetch(PDO::FETCH_ASSOC);
        $dbfile = $info['file'] ?? '';
        if ($dbfile && is_file($dbfile)) {
            $bak = $dbfile . '.bak-reserva-' . date('YmdHis');
            if (!@copy($dbfile, $bak)) {
                $erro = 'Nao consegui criar o backup do banco. Nada foi alterado.';
            }
        }
    } catch (Exception $e) {
        $erro = 'Erro ao preparar o backup: ' . $e->getMessage();
    }

    if (!$erro) {
        // So grava onde ainda esta vazio -- idempotente e seguro contra edicoes simultaneas.
        $afetados = $pdo->exec("UPDATE leiloes SET preco_reserva = {$VALOR} WHERE {$WHERE}");
        $msg = '✅ ' . (int)$afetados . ' lote(s) atualizado(s) com preco de reserva de R$ 50,00. '
             . 'Backup do banco salvo na pasta data/ (hw.db.bak-reserva-...).';
    }
}

$lotes = $pdo->query("SELECT id, nome, status, lance_inicial, preco_reserva FROM leiloes WHERE {$WHERE} ORDER BY id DESC")
             ->fetchAll(PDO::FETCH_ASSOC);
$total = count($lotes);

renderHead('Definir preco de reserva');
renderHeader($usuario);
?>
<div class="neo-container narrow">
  <h1 style="display:inline-block">🏷️ PRECO DE RESERVA PADRAO</h1>
  <p style="font-weight:700;margin-top:12px">
    Lotes <strong>sem preco de reserva</strong> (vazio ou zero) recebem <strong>R$ 50,00</strong>.
    O lance inicial de cada lote nao e alterado.
  </p>

  <?php if ($erro): ?><div class="neo-alert danger mt-24"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
  <?php if ($msg): ?><div class="neo-alert ok mt-24"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

  <?php if (!$lotes): ?>
    <div class="neo-alert ok mt-24">✅ Nenhum lote sem preco de reserva. Pode deletar este arquivo do servidor.</div>
  <?php else: ?>
  <form method="POST" class="neo-card mt-24">
    <p style="font-weight:700"><?= $total ?> lote(s) sem preco de reserva:</p>
    <table class="neo-table" style="width:100%">
      <tr><th>ID</th><th>Lote</th><th>Status</th><th>Lance inicial</th><th>Reserva atual</th><th>Vira</th></tr>
      <?php foreach ($lotes as $l): ?>
        <tr>
          <td><?= (int)$l['id'] ?></td>
          <td><strong><?= htmlspecialchars($l['nome']) ?></strong></td>
          <td><?= htmlspecialchars((string)$l['status']) ?></td>
          <td>R$ <?= number_format((float)$l['lance_inicial'], 2, ',', '.') ?></td>
          <td>
            <?= ($l['preco_reserva'] === null || (float)$l['preco_reserva'] <= 0)
                ? '—'
                : 'R$ ' . number_format((float)$l['preco_reserva'], 2, ',', '.') ?>
          </td>
          <td style="font-weight:700;color:var(--neo-accent)">R$ 50,00</td>
        </tr>
      <?php endforeach; ?>
    </table>
    <input type="hidden" name="confirmar" value="1">
    <button class="neo-btn wide dark mt-16" type="submit"
            onclick="return confirm('Gravar R$ 50,00 de preco de reserva em <?= $total ?> lote(s)? Um backup do banco sera feito antes.')">
      ✅ APLICAR R$ 50,00 EM <?= $total ?> LOTE(S)
    </button>
  </form>
  <?php endif; ?>

  <div class="neo-alert warn mt-24">⚠ Depois de aplicar, <strong>delete este arquivo</strong> (definir_reserva.php) do servidor.</div>
  <div class="text-center mt-24"><a href="admin.php" class="neo-btn dark">← VOLTAR AO ADMIN</a></div>
</div>
<?php renderFooter(); ?>
