<?php
/**
 * PATCH DE CONTRASTE -- carimbo "VENDIDO" ilegivel no tema claro.
 *
 * No tema claro o site troca --grad-ouro por um tom escuro (bom para texto em
 * degrade, ruim como FUNDO). O carimbo .rlg.vendido usa esse degrade de fundo
 * com texto escuro por cima -> escuro no escuro. Este patch acrescenta ao
 * assets/tema-claro.css uma regra que devolve o dourado claro ao carimbo,
 * so no tema claro. Nao mexe no tema escuro.
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

$arquivo = $base . '/assets/tema-claro.css';
$marcador = 'patch-contraste-vendido';
$regra = "\n\n/* === {$marcador}: carimbo VENDIDO legivel no tema claro (12/11/2026) ===\n"
       . "   No tema claro --grad-ouro fica escuro; como o carimbo usa esse degrade\n"
       . "   de FUNDO com texto escuro, virava escuro-no-escuro. Volta ao dourado\n"
       . "   claro, mantendo o texto escuro legivel. So afeta o tema claro. */\n"
       . "html[data-theme=\"light\"] .rlg.vendido{\n"
       . "  background:linear-gradient(to right,#F7931A,#FFD600) !important;\n"
       . "  color:#0A0C10 !important;\n"
       . "}\n"
       . "html[data-theme=\"light\"] .rlg.vendido .rlg-valor{ border-color:#0A0C10 !important; }\n";

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
        $msg = '✅ Patch aplicado em assets/tema-claro.css. Backup salvo (tema-claro.css.bak-...). '
             . 'Recarregue a pagina do lote (Ctrl+Shift+R) para ver o carimbo VENDIDO legivel.';
    }
}

renderHead('Patch contraste VENDIDO');
renderHeader($usuario);
?>
<div class="neo-container narrow">
  <h1 style="display:inline-block">🎨 PATCH: CARIMBO "VENDIDO"</h1>
  <p style="font-weight:700;margin-top:12px">
    Deixa o carimbo <strong>VENDIDO</strong> legivel no <strong>tema claro</strong>
    (fundo dourado claro, texto escuro). O tema escuro nao muda.
  </p>

  <?php if ($erro): ?><div class="neo-alert err mt-24"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
  <?php if ($msg): ?><div class="neo-alert ok mt-24"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

  <div class="neo-alert warn mt-24">⚠ Depois de aplicar, <strong>delete este arquivo</strong> (patch_css_vendido.php) do servidor.</div>
  <div class="text-center mt-24"><a href="admin.php" class="neo-btn dark">← VOLTAR AO ADMIN</a></div>
</div>
<?php renderFooter(); ?>
