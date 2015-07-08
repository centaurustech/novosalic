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
    /*$(function(){
        $('.menuHorizontal').each(function(){
            var menu = this;
            $(menu).menu({
                content: $(menu).next().html(),
                flyOut: true
            });
        });
    });*/

    $(document).ready(function(){

       /*$('.sanfona > a').click(function(){
            $('.sanfona .sanfonaDiv').each(function(indice, valor) {
                $(valor).hide('fast');
            });
            $(this).next().toggle('fast');
        });*/

        $('.planilha').click(function(){
            $(this).next().toggle('fast');
        });

      
    });
    
    function JSExcluirProposta(idPreProjeto) {
        
        $("#modalExcluirProposta").html("Deseja realmente excluir sua proposta?");
        $("#modalExcluirProposta").dialog("destroy");
        $("#modalExcluirProposta").dialog
        ({
            width:450,
            height:200,
            EscClose:false,
            modal:true
            ,buttons:
            {
                'Cancelar':function()
                {
                    $(this).dialog('close'); // fecha a modal
                },
                'OK':function()
                {
                    window.location = "<?php echo $this->baseUrl(); ?>/manterpropostaedital/exluirproposta"+idPreProjeto;
                    $(this).dialog('close'); // fecha a modal
                }
            }
        });
        return false;
    }

	function trocarproponente()
	{

		$("#trocarproponente").dialog("destroy");
        $("#trocarproponente").dialog
        ({
            width:600,
            height:250,
            EscClose:false,
            modal:true,
            title:'Trocar Proponente'
            ,buttons:
            {
                'Cancelar':function()
                {
                    $(this).dialog('close'); // fecha a modal
                },
                'Novo Proponente':function()
                {
                	window.location = "<?php echo $this->url(array('controller' => 'vincularresponsavel', 'action' => 'index')); ?>";
                },
                'Trocar Proponente':function()
                {
                	$("#formtrocaproponente").submit();
                	
                }
            }
        });

	    

        return false;
	}
    
</script>
<div id="menu">
    <!-- início: conteúdo principal #container -->
    <div id="container">
        <!-- início: navegação local #qm0 -->
        <?php

            $get = Zend_Registry::get("get");
            //define id do PreProjeto que sera passado as outras implementacoes
            $codProjeto = "?idPreProjeto=";
            if(isset($this->proposta->idPreProjeto)){
                $codProjeto .= $this->proposta->idPreProjeto;
                $idPreProjeto = $this->proposta->idPreProjeto;
            }elseif(isset($get->idPreProjeto)){
                $codProjeto .= $get->idPreProjeto;
                $idPreProjeto = $get->idPreProjeto;
            }
        ?>
        <div id="menuContexto">
            <div class="top"></div>
            <div id="qm0" class="qmmc sanfona">
                <a class="no_seta" href="<?php echo $this->url(array('controller' => 'manterpropostaincentivofiscal', 'action' => 'editar')).$codProjeto; ?>" title="Ir para Proposta Atual">Proposta Atual </a>
                <a class="no_seta" href="<?php echo $this->url(array('controller' => 'localderealizacao', 'action' => 'index')).$codProjeto; ?>" title="Ir para Local de realização / deslocamento">Local de realização / Deslocamento</a>
                <!--a class="no_seta" href="<?php echo $this->url(array('controller' => 'deslocamento', 'action' => 'index')).$codProjeto; ?>" title="Ir para Deslocamento">Deslocamento</a-->
                <a class="no_seta" href="<?php echo $this->url(array('controller' => 'divulgacao', 'action' => 'index')).$codProjeto; ?>" title="Ir para Plano de divulgação">Plano de divulgação</a>
                <a class="no_seta" href="<?php echo $this->url(array('controller' => 'plano-distribuicao', 'action' => 'index')).$codProjeto; ?>" title="Ir para Plano de distribuição">Plano de distribuição</a>
                <!--<a class="no_seta" href="<?php echo $this->url(array('controller' => 'manterorcamento', 'action' => 'index')).$codProjeto; ?>" title="Ir para Orçamento">Orçamento</a>-->
                <a href="#" title="Orcamento" class="planilha">Planilha Or&ccedil;ament&aacute;ria</a>
                <div id="qm0" class="sanfona sanfonaDiv" style="display: none;">
                    <a href="<?php echo $this->url(array('controller' => 'manterorcamento', 'action' => 'produtoscadastrados')).$codProjeto; ?>" title="Custos por Produtos">Custos por Produtos</a>
                    <a href='<?php echo $this->url(array('controller' => 'manterorcamento', 'action' => 'custosadministrativos')).$codProjeto; ?>'>Custos Administrativos</a>
                    <a href='<?php echo $this->url(array('controller' => 'manterorcamento', 'action' => 'planilhaorcamentariageral')).$codProjeto; ?>'>Planilha Or&ccedil;ament&aacute;ria Geral</a>
                </div>
                <?php //if(isset($this->blnJaEnviadaAoMinc) && $this->blnJaEnviadaAoMinc >= 1): ?>
                    <!--<a class="no_seta" href="#" onclick="return false;" style="color:#9d9d9d;">Anexar Documentos</a>-->
                <?php //else: ?>
                    <a class="no_seta" href="<?php echo $this->url(array('controller' => 'manterpropostaedital', 'action' => 'enviararquivoedital')); ?><?php echo $codProjeto; ?>">Anexar Documentos</a>
                <?php //endif; ?>
                <a class="no_seta" href="<?php echo $this->url(array('controller' => 'manterpropostaedital', 'action' => 'documentospendentesedital')); ?><?php echo $codProjeto; ?>">Documentos Pendentes</a>
                <a class="no_seta" href="<?php echo $this->url(array('controller' => 'mantertabelaitens', 'action' => 'index')).$codProjeto; ?>">Solicitar inclus&atilde;o de itens de custo</a>
                
                <a href="#" title="Orcamento" class="planilha">Itens de custo or&ccedil;ament&aacute;rio</a>
                <div id="qm0" class="sanfona sanfonaDiv" style="display: none;">
                    <a href="<?php echo $this->url(array('controller' => 'mantertabelaitens', 'action' => 'index')).$codProjeto; ?>" title="Solicitar">Solicitar</a>
                    <a href='<?php echo $this->url(array('controller' => 'mantertabelaitens', 'action' => 'minhas-solicitacoes')).$codProjeto; ?>'>Minhas solicita&ccedil;&otilde;es</a>
                </div>
                
                <a class="no_seta" href="<?php echo $this->url(array('controller' => 'Gerarimprimirpdf', 'action' => 'index')); ?><?php echo $codProjeto; ?>">Imprimir/Gerar PDF</a>
                <?php //if(isset($this->blnPossuiDiligencias) && $this->blnPossuiDiligencias > 0): ?>
                    <a class="no_seta" href="<?php echo $this->url(array('controller' => 'diligenciar', 'action' => 'listardiligenciaproponente')).$codProjeto; ?>" title="Visualizar dilig&ecirc;ncias">Msg enviadas pelo MinC</a>
                <?php //endif; ?>
                <?php if(isset($this->movimentacaoAtual) && $this->movimentacaoAtual == '95'): ?>
                    <a class="no_seta" href="#" onclick="javascript:JSExcluirProposta('<?php echo $codProjeto; ?>');">Excluir Proposta</a>
                    <a class="no_seta" href="<?php echo $this->url(array('controller' => 'manterpropostaincentivofiscal', 'action' => 'enviar-proposta-ao-minc')).$codProjeto; ?>" title="Enviar Proposta ao MinC">Enviar Proposta ao MinC</a>
                <?php endif; ?>
                
                <?php if($this->siVinculoProponente): ?>
                    <a class="no_seta" href="#" onclick="trocarproponente('<?php echo $codProjeto; ?>');">Trocar Proponente</a>
                <?php endif; ?>
                
                
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
<div id="modalExcluirProposta" style="display:none"></div>

<div id="trocarproponente" style="display:none">

<form id="formtrocaproponente" action="<?php echo $this->url(array('controller' => 'vincularresponsavel', 'action' => 'trocarproponente')); ?>" method="post">	
<input type="hidden" value="<?php echo $this->dadosVinculo[0]->idVinculoProposta; ?>" name="idVinculoProposta" />
<input type="hidden" value="<?php echo $this->idPreProjeto; ?>" name="idPreProjeto" />
<input type="hidden" value="1" name="mecanismo" />
	<table class="tabela">
		<tr>
			<td class="titulo_tabela">Selecione um Proponente ou cadastre um novo.</td>
		</tr>
		<tr>
			<td>
				CPF/CNPJ Proponente:&nbsp;
				<select name="propronente" id="propronente" class="input_simples w240">
				<?php $idAgente = 0; ?>
				<?php foreach ($this->listaProponentes as $lp):?>
					
					<?php if($lp->idAgenteProponente != $idAgente):?>
						<option value="<?php echo $lp->idVinculo;?>:<?php echo $lp->idAgenteProponente;?>"><?php echo $lp->NomeProponente;?></option>
					<?php endif;?>
					
				<?php $idAgente = $lp->idAgenteProponente; ?>
				<?php endforeach;?>
				</select>&nbsp;<span id="msgValidaProponente"></span>&nbsp;&nbsp;&nbsp;
			</td>
		</tr>
	</table>
</form>	
</div>
<!-- ========== FIM MENU ========== -->




