<!-- ========== INÍCIO MENU ========== --> 
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl(); ?>/public/scripts/quickmenu.js"></script>
<script type="text/javascript">
    function layout_fluido()
    {
        var janela = $(window).width();

        var fluidNavGlobal = janela - 245;
        var fluidConteudo = janela - 253;
        var fluidTitulo = janela - 252;
        var fluidRodape = janela - 19;

        $("#navglobal").css("width",fluidNavGlobal);
        $("#conteudo").css("width",fluidConteudo);
        $("#titulo").css("width",fluidTitulo);
        $("#rodapeConteudo").css("width",fluidConteudo);
        $("#rodape").css("width",fluidRodape);

        $("div#rodapeConteudo").attr("id", "rodapeConteudo_com_menu");
    }

    $(document).ready(function(){

    
    });
    
  
</script>
<div id="menu">
    <!-- início: conteúdo principal #container -->
    <div id="container">
        <div id="menuContexto">
            <div class="top"></div>
            <div id="qm0" class="qmmc sanfona">
                
                <?php if(1 == 1):?>
                	
                	<a class="no_seta" href="<?php echo $this->url(array('controller' => 'manterpropostaincentivofiscal', 'action' => 'consultarresponsaveis')); ?>" title="Novo Responsável">Gerenciar Responsáveis</a>
                	<a class="no_seta" href="<?php echo $this->url(array('controller' => 'manterpropostaincentivofiscal', 'action' => 'novoresponsavel')); ?>" title="Novo Responsável">Novo Responsável</a>
                	<a class="no_seta" href="<?php echo $this->url(array('controller' => 'manterpropostaincentivofiscal', 'action' => 'vincularpropostas')); ?>" title="Vincular Propostas">Vincular Propostas</a>
                	<!--
                	<a class="no_seta" href="<?php echo $this->url(array('controller' => 'manterpropostaincentivofiscal', 'action' => 'vincularprojetos')); ?>" title="Desvincular Projetos">Desvincular Projetos</a>
                	<a class="no_seta" href="<?php echo $this->url(array('controller' => 'procuracao', 'action' => 'index')); ?>" title="Procuração">Procuração</a>
	                 -->
                <?php endif;?>
                
                
            </div>
            <div class="bottom">
            </div>
            <div id="space_menu">
            </div>
        </div>
        <div id="alertar"></div>
        <!-- final: navegação local #qm0 -->
    </div>
</div>


<!-- ========== FIM MENU ========== -->




