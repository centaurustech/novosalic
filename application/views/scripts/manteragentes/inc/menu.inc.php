<!-- ========== INÍCIO MENU ========== -->
<style type="text/css">
		.sanfonaDiv {
			clear: both;
			display: none;
		}
</style>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl(); ?>/public/scripts/quickmenu.js"></script>
<div id="menu">

	<script type="text/javascript">
	$(function(){
		$('.menuHorizontal').each(function(){
			var menu = this;
			$(menu).menu({
				content: $(menu).next().html(),
				flyOut: true
			});
		});
	});
	</script>

	<!-- início: conteúdo principal #container -->
	<div id="container">

		<!-- início: navegação local #qm0 -->
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
		</script>

		<div id="menuContexto">
			<div class="top"></div>
			<div id="qm0" class="qmmc">
				<a class ="no_seta" href="<?php echo $this->url(array('controller' => 'manteragentes', 'action' => 'buscaragente')); ?>" title="Ir para Localizar Agente">Pesquisar Agentes</a>
				<a class ="no_seta" href="<?php echo $this->url(array('controller' => 'manteragentes', 'action' => 'agentes')); ?>?acao=cc" title="Ir para Incluir Agente" >Incluir Agente</a>
                                <a class ="no_seta" href="<?php echo $this->url(array('controller' => 'vincularresponsavel', 'action' => 'index')); 	?>" title="Ir para Vincular Responsável">	Vincular Responsável </a>
                                <a class ="no_seta" href="<?php echo $this->url(array('controller' => 'vincularresponsavel', 'action' => 'desvincularresponsavel')); 		?>" title="Ir para Desvincular Responsável" >		Desvincular Responsável</a>
                        </div>
			<div class="bottom"></div>
			<div id="space_menu"></div>
		</div>

		<script type="text/javascript">
		$(document).ready(function(){
			$('.sanfona > a').click(function(){
				$('.sanfona .sanfonaDiv').each(function(indice, valor) {
					$(valor).hide('fast');
				});
				$(this).next().toggle('fast');
			});
		});
		</script>
		<!-- final: navegação local #qm0 -->
	</div>
</div>
<!-- ========== FIM MENU ========== -->