<!-- ========== INICIO MENU ========== -->
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl(); ?>/public/scripts/quickmenu.js"></script>

<div id="menu">
    <!-- inicio: conteudo principal #container -->
    <div id="container">
        <div id="menuContexto" style="margin-bottom: 50px;">
            <div class="top"></div>
            <div id="qm0" class="qmmc sanfona">
                <a class="no_seta" href="<?php echo $this->url(array('controller' => 'edital', 'action' => 'novo-modulo', 'idEdital' => $this->idEdital)); ?>" title="Adicionar módulo">Adicionar módulo</a>
            </div>
            <div class="bottom"></div>
        </div>
    </div>
</div>

<style type="text/css">
    .sanfonaDiv {
        clear: both;
        display: none;
    }
</style>

<script type="text/javascript">
    
    function layout_fluido() {
        var janela = $(window).width();
        var fluidNavGlobal = janela - 245;
        var fluidConteudo = janela - 253;
        var fluidTitulo = janela - 252;
        var fluidRodape = janela - 19;
        $("#navglobal").css("width", fluidNavGlobal);
        $("#conteudo").css("width", fluidConteudo);
        $("#titulo").css("width", fluidTitulo);
        $("#rodapeConteudo").css("width", fluidConteudo);
        $("#rodape").css("width", fluidRodape);
        $("div#rodapeConteudo").attr("id", "rodapeConteudo_com_menu");
    }
    
</script>