<!-- ========== IN?CIO MENU ========== --> 
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl(); ?>/public/scripts/quickmenu.js"></script> 
<div id="menu"> 
 
    <!-- inￜcio: conteￜdo principal #container --> 
    <div id="container"> 
 
        <!-- inￜcio: navegaￜￜo local  --> 
        <script type="text/javascript"> 
            function layout_fluido() {
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
 
        <style type="text/css"> 
            .sanfonaDiv { 
                clear: both; 
                display: none; 
            } 
        </style> 
        <div id="corfirma" title="Confirmacao" style='display:none;'></div> 
        <div id="ok" title="Confirmacao" style='display:none;'></div>
        <?php

            $get = Zend_Registry::get("get");
            $pronac = null;
            $projeto = null;
            //define id do PreProjeto que sera passado as outras implementacoes
            $codPronac = "?idPronac=";
            if(isset($this->idPronac)){
                $codPronac .= $this->idPronac;
                $pronac = $this->idPronac;
            }elseif(isset($get->idPronac)){
                $codPronac .= $get->idPronac;
                $pronac = $get->idPronac;
            }
            
            //define id do PreProjeto que sera passado as outras implementacoes
            $codProjeto = "?idPreProjeto=";
            if(isset($this->idPreProjeto)){
                $codProjeto .= $this->idPreProjeto;
                $projeto = $get->idPreProjeto;
            }elseif(isset($get->idPreProjeto)){
                $codProjeto .= $get->idPreProjeto;
                $projeto = $get->idPreProjeto;
            }
        ?>
        <div id="menuContexto"> 
            <div class="top"></div> 
            <div id="qm0" class="qmmc">
                <?php if(!empty($pronac)):?>
                <a class="no_seta" href="<?php echo $this->url(array('controller' => 'consultardadosprojeto', 'action' => 'index')); ?><?php echo $codPronac;?>">Dados do Projeto</a>
                <?php endif;?>
                <?php if(!empty($projeto)):?>
                    <?php if(isset($_GET['edital'])):?>
                    <a class="no_seta" href="<?php echo $this->url(array('controller' => 'manterpropostaedital', 'action' => 'dadospropostaedital')); ?><?php echo $codProjeto;?>">Dados da Proposta</a>
                    <?php else:?>
                    <a class="no_seta" href="<?php echo $this->url(array('controller' => 'manterpropostaincentivofiscal', 'action' => 'editar')); ?><?php echo $codProjeto;?>">Dados da Proposta</a>
                    <?php endif;?>
                <?php endif;?>
                <?php
                    if(isset($this->menumsg)){
                ?>
                <div class="sanfonaDiv"></div>
                <a class="no_seta" href="<?php echo $this->url(array('controller' => 'mantermensagens', 'action' => 'consultarmensagem')); ?>/idpronac/<?php echo $this->idPronac; ?>">Mensagens</a>
                <?php } ?>
            </div> 
            <div class="bottom"></div>
            <div id="space_menu"></div>
        <!-- final: navegaￜￜo local --> 
        </div> 
    </div> 
</div> 
<!-- ========== FIM MENU ========== -->