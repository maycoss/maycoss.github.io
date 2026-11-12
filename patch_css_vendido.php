<?php
/**
 * PATCH DE CONTRASTE -- carimbo "VENDIDO" ilegivel no SITE PRINCIPAL (tema papel).
 *
 * No tema "papel" o papel.css faz --grad-ouro = linear-gradient(#000,#000),
 * ou seja, PRETO. O carimbo .rlg.vendido usa esse "dourado" de fundo com texto
 * escuro (#0A0C10) por cima -> preto no preto, invisivel. Este patch acrescenta
 * ao assets/papel.css uma regra que poe o TEXTO BRANCO no carimbo (combina com
 * o tema, que ja usa paineis pretos com texto branco). Nao mexe no tema escuro.
 *
 * Uso: abrir esta pagina logado como admin. Faz backup do CSS e aplica.
 * E idempotente: rodar de novo nao duplica. Depois, pode deletar este arquivo.
 */
$base = __DIR__;
if (!is_file($base . '/config.php') && is_file(dirname($base) . '/config.php')) {
    $base = dirname($base);
}
require_once $base . '/config.php';
require_once $base . '/_base.php';
requireAdmin();
$usuario = usuarioLogado();

$arquivo = $base . '/assets/papel.css';
$marcador = 'patch-vendido-papel';
$regra = "\n\n/* === {$marcador}: carimbo VENDIDO legivel no tema papel (12/11/2026) ===\n"
       . "   No tema papel --grad-ouro vira preto; como o carimbo .rlg.vendido usa\n"
       . "   esse fundo preto com texto escuro, ficava preto-no-preto. Texto e borda\n"
       . "   do carimbo passam a branco. So afeta o site principal (html.papel). */\n"
       . "html.papel .rlg.vendido{ color:#fff !important; }\n"
       . "html.papel .rlg.vendido .rlg-valor{ border-color:#fff !important; }\n";

$msg = '';
$erro = '';

if (!is_file($arquivo)) {
    $erro = "Nao encontrei o arquivo: {$arquivo}";
} elseif (strpos((string)file_get_contents($arquivo), $marcador) !== false) {
    $msg = 'ℹ️ O patch ja estava aplicado (marcador encontrado). Nada a fazer. Pode deletar este arquivo.';
} else {
    $bak = $arquivo . '.bak-' . date('YmdHis');
    if (!@copy($arquivo, $bak)) {
        $erro = 'Nao consegui criar o backup do CSS. Nada foi alterado.';
    } elseif (@file_put_contents($arquivo, $regra, FILE_APPEND) === false) {
        $erro = 'Nao consegui gravar no CSS (permissao?). Backup criado, mas nada alterado.';
    } else {
        $msg = '✅ Patch aplicado em assets/papel.css. Backup salvo (papel.css.bak-...). '
             . 'Recarregue a pagina do lote (Ctrl+Shift+R) para ver o carimbo VENDIDO legivel.';
    }
}

renderHead('Patch contraste VENDIDO');
renderHeader($usuario);
?>
<div class="neo-container narrow">
  <h1 style="display:inline-block">🎨 PATCH: CARIMBO "VENDIDO"</h1>
  <p style="font-weight:700;margin-top:12px">
    Deixa o carimbo <strong>VENDIDO</strong> legivel no site principal
    (texto branco sobre o painel preto). O tema escuro nao muda.
  </p>

  <?php if ($erro): ?><div class="neo-alert err mt-24"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
  <?php if ($msg): ?><div class="neo-alert ok mt-24"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

  <div class="neo-alert warn mt-24">⚠ Depois de aplicar, <strong>delete este arquivo</strong> (patch_css_vendido.php) do servidor.</div>
  <div class="text-center mt-24"><a href="admin.php" class="neo-btn dark">← VOLTAR AO ADMIN</a></div>
</div>
<?php renderFooter(); ?>
