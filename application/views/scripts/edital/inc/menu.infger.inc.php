
<!-- ========== INÍCIO MENU ========== -->

<!-- ========== INÍCIO AJUSTE DO LAYOUT PARA ACOPLAR MENU LATERAL ========== -->
<script type="text/javascript">
<!--
    function layout_fluido()
    {
        var janela = $(window).width();
        var fluidNavGlobal = janela - 245;
        var fluidConteudo = janela - 253;
        var fluidTitulo = janela - 252;
        var fluidRodape = janela - 19;
        $("#navglobal").css("width", fluidNavGlobal);
        $("#conteudo").css("width", fluidConteudo);
        $("#titulo").css("width", fluidTitulo);
        $("#rodapeConteudo").css("width", fluidConteudo);
        $("#imagemRodape").css("width", fluidConteudo);
        $("#rodape").css("width", fluidRodape);
        $("#conteudo").css("min-height", $('#menuContexto').height()); // altura minima do conteudo
        $("#rodapeConteudo").css("margin-left", "225px");
        $(".sanfonaDiv").css("clear", "both");
        $(".sanfonaDiv").css("width", "91%");
    } // fecha função layout_fluido()

    $(document).ready(function()
    {
        $('a.sanfona').click(function()
        {
            $(this).next().toggle('fast');
        });
    });
//-->
</script>
<!-- ========== FIM AJUSTE DO LAYOUT PARA ACOPLAR MENU LATERAL ========== -->
<div id="menuContexto">
    <div class="top"></div>
    <div id="qm0" class="qmmc sanfona">
        <a class="no_seta" href="<?php echo $this->url(array('controller' => 'edital', 'action' => 'informacao-geral', 'idEdital' => $this->idEdital), '', true); ?>" title="Informações gerais">Informações gerais</a>
        <a class="no_seta" href="<?php echo $this->url(array('controller' => 'edital', 'action' => 'criterios-avaliacao', 'idEdital' => $this->idEdital), '', true); ?>" title="Critérios de avaliação">Critérios de avaliação</a>
    </div>
    <br clear="left" />
    <div class="bottom"></div>
</div>

<!-- ========== FIM MENU ========== -->