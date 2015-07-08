<?php
/**
 * Alterar Projeto
 * @author Equipe RUP - Politec
 * @since 15/01/2011
 * @version 1.0
 * @package application
 * @subpackage application.view.scripts.alterarprojeto
 * @copyright ? 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */
?>



<!-- ========== INÍCIO BREADCRUMB (LINKS TOPO) ========== -->
<div id="breadcrumb">
	<ul>
		<li class="first">
                    <a href="<?php echo $this->baseUrl(); ?>" title="Início">Início</a>
                </li>
                <li>
                    <!--a href="<?php echo $this->baseUrl(); ?>" title="Início"-->Manuten&ccedil;&atilde;o<!--/a-->
                </li>
		<li>
                    <a href="<?php echo $this->url(array('controller' => 'Alterarprojeto', 'action' => 'consultarprojeto')); ?>">Alterar Projeto</a>
                </li>
	</ul>
</div>
<!-- ========== FIM BREADCRUMB (LINKS TOPO) ========== -->






<!-- ========== INÍCIO CONTEÚDO ========== -->
<script>

$(document).ready(function(){
    $("#pronac").numeric();
});

</script>



<div id="titulo"><div>Alterar Projeto<span class="voltar"><a href="#" onclick="voltar()">Voltar</a></span></div></div></form>

<div id="conteudo">

<form class="form" name="formBuscar" id="formBuscar" method="GET" action="<?php echo $this->url(array('controller' => 'realizarprestacaodecontas', 'action' => 'coordenadorgeralprestacaocontas')); ?>">


		<table class="tabela_busca">

			<tr>
				<th class="esquerda titulo_campo">
				<label>PRONAC</label>

				</th>
				<td>

					<div class="left"></div>

					<label for="pronac"> </label>
					<input class="input w200" type="text" name="pronac" id="pronac"

						onclick="limpar_campo(this, '');" onkeypress="limpar_campo(this, '');"

						onblur="restaurar_campo(this, '');" onmouseout="restaurar_campo(this, '');"

						maxlength="7" onkeyup="mascara(this, format_pronac);" value=""  />

					<div class="right"></div>
				</td>

			</tr>

		</table>

		<p class="centro">
		<input class="btn_localizar" type="submit" value="" />
		</p>
	</form>
	<!-- ========== FIM FORMUL??Â RIO DE BUSCA PRONAC ========== -->

</div><!-- final: conte??do principal #conteudo -->

<!-- in?­cio: detalhe final da div conteudo #rodapeConteudo -->
<div id="rodapeConteudo"></div>
<!-- final: detalhe final da div conteudo #rodapeConteudo -->


<br clear="all" />




</body>
</html>