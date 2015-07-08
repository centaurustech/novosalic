//CKEDITOR.replaceAll();
$(document).ready(function(){
    // css
    mascaras();

    // events
    $("#produto").change(function(){
        if (!$(this).val()) {
            $('#etapa option').each(function(){
                if ($(this).val()) { $(this).remove(); }
            });
            return;
        }
        $.ajax({
            url: baseUrl + '/comprovarexecucaofinanceira/carregaselectajax',
            type: 'POST',
            data : {'tpSelect': 'etapa', 'ckItens': selectedItem, 'idpronac': pronac, 'idProduto': $(this).val()},
            dataType: 'json',
            success: function(result){
                $(result).each(function(){
                    $('#etapa').append($('<option value="'+ $(this).get(0).id+'">'+ $(this).get(0).nome+'</option>'));
                });
            }
        });
    });
    $("#etapa").change(function(){
        if (!$(this).val()) {
            $('#item option').each(function(){
                if ($(this).val()) { $(this).remove(); }
            });
            return;
        }
        $.ajax({
            url: baseUrl + '/comprovarexecucaofinanceira/carregaselectajax',
            type: 'POST',
            data : {'tpSelect': 'itens', 'ckItens': selectedItem, 'idpronac': pronac, 'idProduto': $('#produto').val(), 'idEtapa': $(this).val()},
            dataType: 'json',
            success: function(result){
                $(result).each(function(){
                    $('#item').append($('<option value="'+ $(this).get(0).id+'">'+ $(this).get(0).nome+'</option>'));
                });
            }
        });
    });
    $("#item").change(function(){
        if (!$(this).val()) {
            $('#item option').each(function(){
                if ($(this).val()) { $(this).remove(); }
            });
            return;
        }
        $.ajax({
            url: baseUrl + '/comprovarexecucaofinanceira/comprovacaopagamento/format/json',
            type: 'POST',
            data : {'idpronac': pronac, 'ckItens': $(this).val()},
            dataType: 'json',
            success: function(result){
                $('#tabelaItemValor tbody tr td:nth-child(4)').html('R$ 0,00');
                $('#tabelaItemValor tbody tr td:nth-child(5)').html('R$ 0,00');
                if (result.item && result.item.valorAprovado) {
                    $('#tabelaItemValor tbody tr td:nth-child(4)').html('R$ '+parseInt(result.item.valorAprovado));
                }
                if (result.item && result.item.valorComprovado) {
                    $('#tabelaItemValor tbody tr td:nth-child(5)').html('R$ '+parseInt(result.item.valorComprovado));
                }
                $('#idAgente').html(result.idAgente);
                $('#CNPJCPF').html(result.CNPJCPF);
                $('#Descricao').html(result.Descricao);
                console.log(result.comprovantePagamento);
            }
        });
    });
    $('#CNPJCPF').blur(function(){
    	
    });
});


/** /
//CKEDITOR.replaceAll();
var corpo  = '#<?php echo $corpo;?>';
$(document).ready(function(){
    
    mascaras(corpo);

    var idProduto = carregarPgHtml('<?php echo $carregarSelectHref;?>','#produto',{tpSelect:'produto',idpronac:'<?php echo $this->idpronac?>',ckItens:<?php echo json_encode($this->ckItens);?>});
    if(idProduto != undefined){
        var idEtapa = carregarPgHtml('<?php echo $carregarSelectHref;?>','#etapa',{tpSelect:'etapa',idpronac:'<?php echo $this->idpronac?>',idProduto:idProduto,ckItens:<?php echo json_encode($this->ckItens);?>});
        if(idEtapa != undefined)  
            carregarPgHtml('<?php echo $carregarSelectHref;?>','#itens',{tpSelect:'itens',idpronac:'<?php echo $this->idpronac?>',idProduto:idProduto,idEtapa:idEtapa,ckItens:<?php echo json_encode($this->ckItens);?>});
    }
    
    $('#produto').change(function (){
        var idEtapa = carregarPgHtml('<?php echo $carregarSelectHref;?>','#etapa',{tpSelect:'etapa',idpronac:'<?php echo $this->idpronac?>',idProduto:$('#produto').val(),ckItens:<?php echo json_encode($this->ckItens);?>});
        if(idEtapa != undefined)
            carregarPgHtml('<?php echo $carregarSelectHref;?>','#itens',{tpSelect:'itens',idpronac:'<?php echo $this->idpronac?>',idProduto:$('#produto').val(),idEtapa:idEtapa,ckItens:<?php echo json_encode($this->ckItens);?>});
        else
            carregarPgHtml('<?php echo $carregarSelectHref;?>','#itens',{tpSelect:'itens',idpronac:'<?php echo $this->idpronac?>',idProduto:$('#produto').val(),idEtapa:$('#etapa').val(),ckItens:<?php echo json_encode($this->ckItens);?>});
    });
    $('#etapa').change(function (){
        carregarPgHtml('<?php echo $carregarSelectHref;?>','#itens',{tpSelect:'itens',idpronac:'<?php echo $this->idpronac?>',idProduto:$('#produto').val(),idEtapa:$('#etapa').val(),ckItens:<?php echo json_encode($this->ckItens);?>});
    });
    
    $(corpo+' a').click(function(){
        if ($(this).attr('id') == 'btnCancelar') { return; }
        if($(this).attr('retorna') == undefined){
            var info = {
                este        :   this,
                corpo       :   '<?php echo $corpo;?>',
                parametros  :   {idpronac:'<?php echo $this->idpronac;?>',ckItens:<?php echo json_encode($this->ckItens);?>}
            };
            acoesLink(info);
            return false;
        }
    });
    
    $(corpo+' #additemcusto').click(function(){
		$('#tableItensCusto').show();
        var resposta = buscarJson('<?php echo $verificarValoresHref;?>',{idpronac:'<?php echo $this->idpronac?>',idPlanilhaAprovacao:$(corpo+' #itens').val(),valor:$(corpo+' #vlComprovado').val()});

        if(resposta.retorno){
            var info = {
                corpo                   :   corpo,
                tabela                  :   corpo+' #tableItensCusto',
                produto                 :   buscarHtmlSelect(corpo+' #produto'),
                idProduto               :   $(corpo+' #produto').val(),
                etapa                   :   buscarHtmlSelect(corpo+' #etapa'),
                idEtapa                 :   $(corpo+' #etapa').val(),
                itens                   :   buscarHtmlSelect(corpo+' #itens'),
                idItem                  :   $(corpo+' #itens').val(),
                valor                   :   $(corpo+' #vlComprovado').val(),
                idpronac                :   '<?php echo $this->idpronac;?>',
                ckItens                 :   <?php echo json_encode($this->ckItens);?>,
                comprovar               :   true
            };
            listaPEI(info);
        }
        else{
            janelaAlerta(resposta.mensagem);
        }
    });
    
    $(corpo+' #salvar').click(function(){
    
        $("#frComprovarPagamento").validate({
            rules:{
                arquivo : {
                    required : true
                }
            },
            messages:{
                arquivo : {
                    required : 'Obrigatório anexar um arquivo'
                }
            }
        });
    
        if(validarFormulario('#frComprovarPagamento',ag1)){
            if($('#tpDocumento').val() != 0){
                $('#frComprovarPagamento').submit();
            } else {
                janelaAlerta('Selecione um tipo de documento.');
            }
        }
        return false;
    });
    
    $(".btn_descrever_item").click(function(){
        $("#desc_item").html('<img src="<?php echo $this->baseUrl(); ?>/public/img/ajax.gif" alt="carregando"><br/><br/>Carregando...<br>Por Favor, aguarde!!');
        $("#desc_item").dialog({
                        width:780,
                        height:680,
                        modal:true,
                        title: 'Comprova&ccedil;&atilde;o de Pagamento',
                        resizable: true
                    });
        $.post(
                "<?php echo $descreverItemHref; ?>", 
                {
                    ckItens : <?php echo json_encode($this->ckItens);?>,
                    idpronac : <?php echo $this->idpronac;?>
                },
                function(data){
                    $("#desc_item").html(data);
                }
            );
    });


    $('#tpDocumento').change(function(){
        if($(this).val() == 3){
            $('#nrSerie').attr("null", "false");
            $('#nmSerieTxt').html("S&eacute;rie<span style='color:red'>*</span>");
        } else {
            $('#nrSerie').attr("null", "true");
            $('#nmSerieTxt').html("S&eacute;rie");
        }
    });

    $('.excluirComprovante').click(function(){
        var idComprovantePagamento = $(this).attr('idComprovante');
        var idPlanilhaAprovacao = $(this).attr('idPlanilhaAprovacao');
            linha = $(this).parent().parent();
        $("#divExcluirComprovantes").html("");
        $('#divExcluirComprovantes').html('<br><center>Aguarde, atualizando dados...<br><img src="<?php echo $this->baseUrl(); ?>/public/img/ajax.gif" /></center><br>');
        $.ajax({
            async: true,
            type: "post",
            url: "<?php echo $this->url(array('controller' => 'comprovarexecucaofinanceira', 'action' => 'excluircomprovacaopagamento')); ?>",
            data:{
                idComprovantePagamento : idComprovantePagamento,
                idPlanilhaAprovacao : idPlanilhaAprovacao
            },
            success: function(data){
                $("#msgAlerta").dialog("destroy");
                $("#msgAlerta").html('<center>Exclusão feita com sucesso!<br /><br /><img src="<?php echo $this->baseUrl(); ?>/public/img/ajax.gif" alt="carregando"><br />Por favor, aguarde!!</center>');
                $("#msgAlerta").dialog({
                    resizable: false,
                    title: 'Alerta!',
                    width:300,
                    height:150,
                    modal: true
                });
                $('.ui-dialog-titlebar-close').remove();

                window.setInterval(function() {
                    linha.remove();
                    $("#msgAlerta").dialog("destroy");
                }, 2500);
            },
            error: function(data) {
                message('Ocorreu um erro ao tentar excluir o comprovante de pagamento.', 'ERROR');
            },
            complete: function(){
                $("#resultadoFinalizar").html("");
            },
            dataType : 'json'

        });

    });
    
});
/**/