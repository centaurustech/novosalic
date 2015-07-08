<!-- ========== INICIO MENU ========== -->
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl(); ?>/public/scripts/quickmenu.js"></script>
<div id="menu">

    <!-- inicio: conteudo principal #container -->
    <div id="container">

        <!-- ========== INÍCIO AJUSTE DO LAYOUT PARA ACOPLAR MENU LATERAL ========== -->
        <script type="text/javascript">
            function layout_fluido(){
                    var janela         = $(window).width();
                    var fluidNavGlobal = janela - 245;
                    var fluidConteudo  = janela - 253;
                    var fluidTitulo    = janela - 252;
                    var fluidRodape    = janela - 19;
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

            $(document).ready(function(){
                $('a.sanfona').click(function(){
                    $(this).next().toggle('fast');
                });
            });
        </script>
        <!-- ========== FIM AJUSTE DO LAYOUT PARA ACOPLAR MENU LATERAL ========== -->

     	<?php
        $get = Zend_Registry::get("get");
        //define id do PreProjeto que sera passado as outras implementacoes
        $codPronac = "?idPronac=";
        if(isset($this->idPronac)){
            $codPronac .= $this->idPronac;
        }elseif(isset($get->idPronac)){
            $codPronac .= $get->idPronac;
        }

        $codPreProjeto = "&idPreProjeto=";
        if(isset($this->idPreProjeto)){
            $codPreProjeto .= $this->idPreProjeto;
        }elseif(isset($get->idPreProjeto)){
            $codPreProjeto .= $get->idPreProjeto;
        }
        ?>
        <div id="menuContexto">
            <div class="top"></div>
            <div id="qm0" class="qmmc sanfona">
                <a href="#" title="Abrir menu Consultar">Consultar</a>
                <div style="display: none;">
                    <!--<div id="qm0" class="qmmc sanfona" style="float:left; padding: 0px; margin: 0px;">
                        <a href="#" class="" style="background-image: url(http://localhost/Prototipos/public/img/seta-menu-contexto-off.png);">Gerencial</a>
                        <div id="qm0" style="display: none;float:left; padding: 0px; margin: 0px;" class="qmmc sanfona">
                            <a href="#" class="" style="background-image: url(http://localhost/Prototipos/public/img/seta-menu-contexto-off.png);">Análise</a>
                            <div style="display: none;">
                                <a href='<?php echo $this->url(array('controller' => 'admissibilidade', 'action' => 'listar-propostas-analise-visual-tecnico')); ?>'>Visual por Técnico</a>
                                <a href='<?php echo $this->url(array('controller' => 'admissibilidade', 'action' => 'historico-analise-visual')); ?>'>Histórico da Análise Visual</a>
                                <a href='<?php echo $this->url(array('controller' => 'admissibilidade', 'action' => 'listar-propostas-analise-final')); ?>'>Proposta em Análise Final</a>
                            </div>
                        </div>
                    </div>-->
                    <div id="qm0" class="qmmc" style="float:left; padding: 0px; margin: 0px;">
                        <a href='<?php echo $this->url(array('controller' => 'admissibilidade', 'action' => 'listar-propostas-analise-visual-tecnico')); ?>' title="Ir para Visual por T&eacute;cnico">Visual por T&eacute;cnico</a>
                        <?php //if ($this->grupoAtivo == 97) : ?>
                        <a href='<?php echo $this->url(array('controller' => 'admissibilidade', 'action' => 'listar-propostas-analise-documental-tecnico')); ?>' title="Ir para Documental por T&eacute;cnico">Documental por T&eacute;cnico</a>
                        <?php //endif; ?>
                        <a href='<?php echo $this->url(array('controller' => 'admissibilidade', 'action' => 'historico-analise-visual')); ?>' title="Ir para Hist&oacute;rico da An&aacute;lise Visual">Hist&oacute;rico da An&aacute;lise Visual</a>
                        <a href='<?php echo $this->url(array('controller' => 'admissibilidade', 'action' => 'listar-propostas-analise-final')); ?>' title="Ir para Proposta em An&aacute;lise Final">Proposta em An&aacute;lise Final</a>
                        <a href='<?php echo $this->url(array('controller' => 'admissibilidade', 'action' => 'painel-projetos-distribuidos')); ?>' title="Ir para Projetos Distribu&iacute;dos por &Oacute;rg&atilde;os">Projetos Distribu&iacute;dos por &Oacute;rg&atilde;os</a>
                    </div>
                </div>
                <a href="#" title="Abrir menu An&aacute;lise">An&aacute;lise</a>
                <div style="display: none;">
                    <!--<div id="qm0" class="qmmc sanfona" style="float:left; padding: 0px; margin: 0px;">
                        <a href="<?php echo $this->url(array('controller' => 'admissibilidade', 'action' => 'listar-propostas')); ?>" style="background-image: url(http://localhost/Prototipos/public/img/seta-menu-contexto-off.png);">Avaliação</a>
                        <a href="#" style="background-image: url(http://localhost/Prototipos/public/img/seta-menu-contexto-off.png);">Coordenação</a>
                        <div style="display: none;">
                            <a href='<?php echo $this->url(array('controller' => 'admissibilidade', 'action' => 'gerenciaranalistas')); ?>'>Gerenciar Analistas</a>
                            <a href='<?php echo $this->url(array('controller' => 'admissibilidade', 'action' => 'redistribuiranalise')); ?>'>Redistribuir Análise</a>
                            <a href='<?php echo $this->url(array('controller' => 'admissibilidade', 'action' => 'gerenciamentodepropostas')); ?>'>Gerenciar Proposta</a>
                        </div>
                    </div>-->
                    <div style="float:left; padding: 0px; margin: 0px;" >
                        <a class="no_seta" href='<?php echo $this->url(array('controller' => 'Gerenciarparecertecnico', 'action' => 'imprimiretiqueta')); ?>' title="Imprimir Etiqueta e Projeto">Imprimir Etiqueta e Projeto</a>
                    </div>
                    <!--
                    <div style="float:left; padding: 0px; margin: 0px;">
                        <a class="no_seta" href='<?php echo $this->url(array('controller' => 'Gerenciarparecertecnico', 'action' => 'imprimirparecertecnico')); ?>'>Imprimir Parecer Técnico</a>
                    </div>
                    -->
                    <div style="float:left; padding: 0px; margin: 0px;">
                        <a class="no_seta" href='<?php echo $this->url(array('controller' => 'admissibilidade', 'action' => 'alterarunianalisepropostaconsulta')); ?>' title="Ir para Alterar Unidade da an&aacute;lise da Proposta">Alterar Uni. da an&aacute;lise da Proposta</a>
                    </div>
                </div>
                <?php if(!empty ($_REQUEST['idPronac']) || !empty ($_REQUEST['idPreProjeto'])): ?>
                <!--<a class="no_seta" href="<?php echo $this->url(array('controller' => 'diligenciar', 'action' => 'listardiligenciaanalista')); ?><?php echo $codPronac.$codPreProjeto;?>">Diligências</a>-->
                <?php endif; ?>
				<!--<a class="no_seta" href="<?php echo $this->url(array('controller' => 'diligenciar', 'action' => 'listardiligenciaadmissibilidade')); ?>">Dilig&ecirc;ncias</a>--> 
            </div>
            <div class="bottom"></div>
        </div>

    </div>

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
<!-- final: navegacao local #qm0 -->

<!-- ========== FIM MENU ========== -->