<!-- ========== INÍCIO MENU ========== -->
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl(); ?>/public/scripts/quickmenu.js"></script>
<div id="menu">

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

            $(document).ready(function(){

                $(".Desativado").click(function(){

                    $("#produtoDesativado").dialog({
                        title :'Erro',
                        resizable: false,
                        width:400,
                        height:150,
                        modal: true,
                        autoOpen:false,
                        buttons: {
                            'OK': function() {
                                $(this).dialog('close');
                            }
                        }
                    });
                    $("#produtoDesativado").dialog('open');
                });
            });

        </script>

        <style type="text/css">
            .sanfonaDiv {
                clear: both;
            }
        </style>
        
        <div id="menuContexto">
            <div class="top"></div>
            <div id="qm0" class="qmmc sanfona">
                <a href="<?php echo $this->baseUrl(); ?>/analisarexecucaofisicatecnico/parecer-tecnico?idpronac=<?php echo $this->idPronac; ?>&relatorio=<?php echo $this->idRelatorio; ?>" title="Parecer T&eacute;cnico" class="no_seta">Parecer T&eacute;cnico</a>
                <a href="<?php echo $this->baseUrl(); ?>/analisarexecucaofisicatecnico/etapas-de-trabalho?idpronac=<?php echo $this->idPronac; ?>&relatorio=<?php echo $this->idRelatorio; ?>" title="Etapas de Trabalho" class="no_seta">Etapas de Trabalho</a>
                <a href="<?php echo $this->baseUrl(); ?>/analisarexecucaofisicatecnico/local-de-realizacao?idpronac=<?php echo $this->idPronac; ?>&relatorio=<?php echo $this->idRelatorio; ?>" title="Local de Realiza&ccedil;&atilde;o" class="no_seta">Local de Realiza&ccedil;&atilde;o</a>
                <a href="<?php echo $this->baseUrl(); ?>/analisarexecucaofisicatecnico/plano-de-divulgacao?idpronac=<?php echo $this->idPronac; ?>&relatorio=<?php echo $this->idRelatorio; ?>" title="Plano de Divulga&ccedil;&atilde;o" class="no_seta">Plano de Divulga&ccedil;&atilde;o</a>
                <a href="<?php echo $this->baseUrl(); ?>/analisarexecucaofisicatecnico/plano-de-distribuicao?idpronac=<?php echo $this->idPronac; ?>&relatorio=<?php echo $this->idRelatorio; ?>" title="Plano de Distribui&ccedil;&atilde;o" class="no_seta">Plano de Distribui&ccedil;&atilde;o</a>
                <a href="<?php echo $this->baseUrl(); ?>/analisarexecucaofisicatecnico/metas-comprovadas?idpronac=<?php echo $this->idPronac; ?>&relatorio=<?php echo $this->idRelatorio; ?>" title="Metas Comprovadas" class="no_seta">Metas Comprovadas</a>
                <a href="<?php echo $this->baseUrl(); ?>/analisarexecucaofisicatecnico/itens-comprovados?idpronac=<?php echo $this->idPronac; ?>&relatorio=<?php echo $this->idRelatorio; ?>" title="Itens Comprovados" class="no_seta">Itens Comprovados</a>
                <a href="<?php echo $this->baseUrl(); ?>/analisarexecucaofisicatecnico/comprovantes-de-execucao?idpronac=<?php echo $this->idPronac; ?>&relatorio=<?php echo $this->idRelatorio; ?>" title="Comprovantes de Execu&ccedil;&atilde;o" class="no_seta">Comprovantes de Execu&ccedil;&atilde;o</a>
            </div>
            <div class="bottom"></div>
        </div>
    </div>
</div>
<!-- ========== FIM MENU ========== -->
