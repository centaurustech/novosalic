<?php
$operacoescustomizavelHref =   $this->url(array('controller' => 'cadastraredital', 'action' => 'operacoescustomizavel'));
?>

<script> 
var formulario = {
    idFormulario:'',
    iniciar:function(form,inform,funcRetorno){
        formulario.idFormulario =   form;
        formulario.idInform     =   inform;
        formulario.apagarValidacao();
        var resposta = formulario.validarForm();
        if(resposta){
            formulario.montarEnvio.pagina = $(formulario.idFormulario).attr('action');
            formulario.montarEnvio.data   = $(formulario.idFormulario).serializeArray();
            if(funcRetorno!=undefined){
                formulario.montarEnvio.funcaoRetorno = funcRetorno;
            }else{
                formulario.montarEnvio.funcaoRetorno = function (resposta){};
            }
                formulario.montarEnvio.envioForm();
        }
    },
    resetForm:function(){
        $(formulario.idFormulario).find('input').each(function(){
            if($(this).attr('apagar')!='false' && $(this).attr('type')!='button' && $(this).attr('type')!='submit'){
                $(this).val('');
            }
        });
        $(formulario.idFormulario).find('textarea').each(function(){
            if($(this).attr('apagar')!='false'){
                $(this).val('');
            }
        });
        $(formulario.idFormulario).find('select').each(function(){
            if($(this).attr('apagar')!='false'){
                $(this).val('');
            }
        });
        formulario.apagarValidacao();
    },
    apagarValidacao: function(){
        setTimeout(function(){
            formulario.montarMensagem.apagaMens();
            $('.campoTextoErro').removeClass('campoTextoErro');
        },10000);
    },
    idInform:'',
    validarForm:function(){
        formulario.montarMensagem.inimensagem();
        //validar quando um campo estiver vazio somente se neste estiver um atributo null="false"
        $(formulario.idFormulario).find('[null=false]').each(function(){
            if($.trim($(this).val()).length == 0){
                formulario.montarMensagem.addmensagem( $(this).attr('title')+' n&atilde;o pode ser vazio.');
                $(this).addClass('campoTextoErro');
            }
        });
        $(formulario.idFormulario).find('[valida=maiorQzero]').each(function(){
            if($.trim($(this).val()).length > 0 && !(formulario.limparFormatacao($(this).val())>0)){
                formulario.montarMensagem.addmensagem( $(this).attr('title')+' n&atilde;o pode ser igual a 0(Zero).');
                $(this).addClass('campoTextoErro');
            }
        });

        if(formulario.montarMensagem.mensagem != ''){
            if(formulario.idInform!='')
                formulario.montarMensagem.mensagemErro();//mostrar erros
            return false;
        }
        else{

            return true;
        }
    },
    montarMensagem:{
        mensagem:'',
        inimensagem:function(){
            formulario.montarMensagem.mensagem  =   '';
        },
        addmensagem:function(msg){
            formulario.montarMensagem.mensagem  +=  '<li>'+msg+'</li>';
        },
        mensagemErro: function(){
            $('#'+formulario.idInform).html('<div id="divMensagensSistema" style="border: 1px solid rgb(255, 0, 0); color: rgb(139, 0, 0); font-family: Arial; font-size: 12px;"><ul>'+formulario.montarMensagem.mensagem+'</ul></div>');
        },
        mensagemInfo: function(){
            $('#'+formulario.idInform).html('<div id="divMensagensSistema" style="border: 1px solid rgb(30, 144, 255); color: rgb(0, 154, 205); font-family: Arial; font-size: 12px;"><ul>'+formulario.montarMensagem.mensagem+'</ul></div>');
        },
        apagaMens:function(){
            if(formulario.idInform!='')
                $('#'+formulario.idInform).html('');
        }
    },
    montarEnvio:{
        pagina:'',
        data:{},
        type:'POST',
        dataType:'json',
        funcaoRetorno:function (resposta){

        },
        funcResp:function(data){
            switch(data.retorno){
                case 'INSERIR':
                    formulario.montarMensagem.inimensagem();
                    if(data.mensagem=='')
                        formulario.montarMensagem.addmensagem('Cadastro Realizado Com Sucesso!');
                    else
                        formulario.montarMensagem.addmensagem(data.mensagem);
                    formulario.resetForm();
                    formulario.montarMensagem.mensagemInfo();
                    break;
                case 'ALTERAR':
                    formulario.montarMensagem.inimensagem();
                    if(data.mensagem=='')
                        formulario.montarMensagem.addmensagem('Alteração Realizada Com Sucesso!');
                    else
                        formulario.montarMensagem.addmensagem(data.mensagem);
                    formulario.resetForm();
                    formulario.montarMensagem.mensagemInfo();
                    break;
                case 'EXCLUIR':
                    formulario.montarMensagem.inimensagem();
                    if(data.mensagem=='')
                        formulario.montarMensagem.addmensagem('Exclusão Realizada Com Sucesso!');
                    else
                        formulario.montarMensagem.addmensagem(data.mensagem);
                    formulario.resetForm();
                    formulario.montarMensagem.mensagemInfo();
                    break;
                case 'ERRO':
                    formulario.montarMensagem.inimensagem();
                    if(data.mensagem=='')
                        formulario.montarMensagem.addmensagem('ERRO!');
                    else
                        formulario.montarMensagem.addmensagem(data.mensagem);
                    formulario.montarMensagem.mensagemErro();
                    break;
            }
            formulario.montarEnvio.funcaoRetorno(data);
        },
        envioForm:function(){
            $.ajax({
              type: formulario.montarEnvio.type,
              url:  formulario.montarEnvio.pagina,
              data: formulario.montarEnvio.data,
              success: function(data){
                  formulario.montarEnvio.funcResp(data);
              }
              ,dataType: formulario.montarEnvio.dataType
            });
        }
    },
    limparFormatacao:function(vCampo){
        var tCampo = vCampo.length;

        for (var i=0; i<tCampo; i++) {
            vCampo = vCampo.replace ("-","");
                    vCampo = vCampo.replace (".","");
            vCampo = vCampo.replace ("/","");
            vCampo = vCampo.replace (":","");
            vCampo = vCampo.replace (" ","");
            vCampo = vCampo.replace (",","");
       }

       return vCampo;
    },
    verificarValor:function(){
        //alert('aqui');
        var soma = 0;
        var v1 = parseFloat($('#valorApoio').val().replace( /[^0-9]/g, '' ));
        var vlpi = parseFloat($('#valorpi').html().replace( /[^0-9]/g, '' ));
        $('.valor_pagamento').each(function(i,e){
            soma += parseFloat($(this).html().replace( /[^0-9]/g, '' ));
        });

        soma += v1;

        if(soma <= vlpi){
            return true;
        } else {
            formulario.idInform = 'recebeinformacao';
            formulario.montarMensagem.mensagem = '* Os valores inseridos excedem o valor do PI';
            formulario.montarMensagem.mensagemErro();
        }
    }
}
salvarMultiplosFormularios = {
    idexecucao : '',
    exec : function(){
        $(this.idexecucao+' form').each(function(){

            if(validarForm.exec(this)){
                $.ajax({
                  type: 'post',
                  url:  '<?php echo $operacoescustomizavelHref;?>',
                  data: $(this).serialize(),
                  success: function(data){
                    if(data != undefined){
                        //$('#avisos').html(data.mensagem);

                        //alert(data)
                        if(data.result){
                            $('#guiaDigital').find('.avisos').each(function(){
                                $(this).html('<div id="divMensagensSistema" style="display:none;border: 1px solid rgb(30, 144, 255); color: rgb(0, 154, 205); font-family: Arial; font-size: 12px;">'+data.mensagem+'</div>');
                            });
                        }
                        else{
                           $('#guiaDigital').find('.avisos').each(function(){
                                $(this).html('<div id="divMensagensSistema" style="display:none;border: 1px solid rgb(30, 144, 255); color: rgb(0, 154, 205); font-family: Arial; font-size: 12px;">'+data.mensagem+'</div>');
                            });
                        }
                        $('#guiaDigital').find('#divMensagensSistema').each(function(){
                            var idDiv = $(this);
                            idDiv.show();
                            setTimeout(function(){
                                idDiv.hide();
                            },1000);
                        });
                    }
                  }
                  ,dataType: 'json'
                });
            }
        });

    }
}
validarForm = {
    formulario : '',
    exec : function (form){
        this.formulario = form;
        montarMensagem.inimensagem();

        montarMensagem.idInform =   form+' div';
        var texto   = '';

        $(form).find('[type=text]').each(function(){
            texto   =   $.trim($(this).val());
        });
        $(form).find('[type=checkbox]').each(function(){
            //if($(this).attr('checked'))
                texto   =   'simCK';
        });
        $(form).find('[type=radio]').each(function(){
            if($(this).attr('checked'))
                texto   =   'simRG';
        });
        $(form).find('select').each(function(){
            texto   =   $.trim($(this).val());
        });
        $(form).find('textarea').each(function(){
            try{
                texto   =   CKEDITOR.instances[$(this).attr('id')].getData();
                texto   =   texto.replace(/(<.*?>|\s)/g,'');
                $(this).val(CKEDITOR.instances[$(this).attr('id')].getData());
            }
            catch(e){

            }
        });
        if(texto != '')
            return true;
    }
}

montarMensagem = {
    mensagem:'',
    idInform:'avisos',
    inimensagem:function(){
        this.mensagem  =   '';
    },
    addmensagem:function(msg){
        this.mensagem  +=  '<li>'+msg+'</li>';
    },
    mensagemErro: function(){
        $('#'+this.idInform).html('<div id="divMensagensSistema" style="border: 1px solid rgb(255, 0, 0); color: rgb(139, 0, 0); font-family: Arial; font-size: 12px;"><ul>'+this.mensagem+'</ul></div>');
    },
    mensagemInfo: function(){
        $('#'+this.idInform).html('<div id="divMensagensSistema" style="border: 1px solid rgb(30, 144, 255); color: rgb(0, 154, 205); font-family: Arial; font-size: 12px;"><ul>'+this.mensagem+'</ul></div>');
    },
    apagaMens:function(){
        if(this.idInform!='')
            $('#'+this.idInform).html('');
    }
}

function formataNumeroEmMoeda(num) {
    num = num.toString().replace(/\$|\,/g,'');
    if(isNaN(num))
        num = "0";
    sign = (num == (num = Math.abs(num)));
    num = Math.floor(num*100+0.50000000001);
    cents = num%100;
    num = Math.floor(num/100).toString();
    if(cents<10)
        cents = "0" + cents;

    for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++){

        num = num.substring(0,num.length-(4*i+3))+ "."+
            num.substring(num.length-(4*i+3));

    }

    return (((sign)?'':'-') + num + ',' + cents);
}
function limparCampo(valor, validos) {
    // retira caracteres invalidos da string
    var result = "";
    var aux;
    for (var i=0; i < valor.length; i++) {
        aux = validos.indexOf(valor.substring(i, i+1));
        if (aux>=0) {
            result += aux;
        }
    }
    return result;
}

function formataValorMonetario(campo,maxlenght,event,qtdCasasDecimais) {
    var tecla = event.keyCode;
    vr = limparCampo(campo.value,"0123456789");
    tam = vr.length;
    dec=qtdCasasDecimais

    if (tam < maxlenght && tecla != 8){
        tam = vr.length + 1 ;
    }

    if (tecla == 8 )
    {
        tam = tam - 1 ;
    }

    if ( tecla == 8 || tecla >= 48 && tecla <= 57 || tecla >= 96 && tecla <= 105 )
    {

        if ( tam <= dec )
        {
            campo.value = vr ;
        }

        if ( (tam > dec) && (tam <= 5) ){
            campo.value = vr.substr( 0, tam - 2 ) + "," + vr.substr( tam - dec, tam ) ;
        }
        if ( (tam >= 6) && (tam <= 8) ){
            campo.value = vr.substr( 0, tam - 5 ) + "." + vr.substr( tam - 5, 3 ) + "," + vr.substr( tam - dec, tam ) ;
        }
        if ( (tam >= 9) && (tam <= 11) ){
            campo.value = vr.substr( 0, tam - 8 ) + "." + vr.substr( tam - 8, 3 ) + "." + vr.substr( tam - 5, 3 ) + "," + vr.substr( tam - dec, tam ) ;
        }
        if ( (tam >= 12) && (tam <= 14) ){
            campo.value = vr.substr( 0, tam - 11 ) + "." + vr.substr( tam - 11, 3 ) + "." + vr.substr( tam - 8, 3 ) + "." + vr.substr( tam - 5, 3 ) + "," + vr.substr( tam - dec, tam ) ;
        }
        if ( (tam >= 15) && (tam <= 17) ){
            campo.value = vr.substr( 0, tam - 14 ) + "." + vr.substr( tam - 14, 3 ) + "." + vr.substr( tam - 11, 3 ) + "." + vr.substr( tam - 8, 3 ) + "." + vr.substr( tam - 5, 3 ) + "," + vr.substr( tam - 2, tam ) ;
        }
    }

}

        
        function mudancaDeOperacaoInc(){
            formulario.idFormulario = '#formInclusaoPagamento';
            formulario.resetForm();
            $('#operacao').val('inserirOpcao');
            $("#btSubmit").val('Incluir');
            $("#btCancelar").css('display','none');
            $('#tdParcelas').html('');
        }
        function mudancaDeOperacaoIncFP(){
            formulario.idFormulario = '#formInclusaoPagamento';
            formulario.resetForm();
            $("#nrPergunta").val('');
            $("#dsPergunta").val('');
            $('#operacao').val('inserirPergunta');
            $("#btSubmit").val('Inserir Forma de Pagamento');
            $("#btCancelar").css('display','none');
        }
        function recalcularValorTotal(valorApoio){
            var total = 0;
            $('#tdParcelas').find('input[type=text]').each(function(){
                if($(this).val()!='')
                    total   +=  parseFloat(limparFormatacao($(this).val()));
            });
            if((valorApoio-total)>0){
                formulario.idInform = 'recebeinformacao';
                formulario.montarMensagem.inimensagem();
                formulario.montarMensagem.addmensagem('Dividir o resto entre as parcelas!');

                $('#resto')
                .html('Resto: R$ '+formataNumeroEmMoeda((valorApoio-total)/100))
                .css('color','#00D700')
                .css('fontWeight','bold');
            }
            else{
                if((valorApoio-total)!=0){
                    formulario.idInform = 'recebeinformacao';
                    formulario.montarMensagem.inimensagem();
                    formulario.montarMensagem.addmensagem('Remover valores das parcelas!');

                    $('#resto')
                    .html('Ultrapassou: R$ '+formataNumeroEmMoeda(((valorApoio-total)/100)*(-1)))
                    .css('color','#D70000')
                    .css('fontWeight','bold');
                }
                else{
                    formulario.montarMensagem.inimensagem();
                    $('#resto').html('');
                }
            }
        }
        function limparFormatacao(vCampo){
            var tCampo = vCampo.length;

            for (var i=0; i<tCampo; i++) {
                vCampo = vCampo.replace ("-","");
                        vCampo = vCampo.replace (".","");
                vCampo = vCampo.replace ("/","");
                vCampo = vCampo.replace (":","");
                vCampo = vCampo.replace (" ","");
                vCampo = vCampo.replace (",","");
           }

           return vCampo;
        }

</script>
