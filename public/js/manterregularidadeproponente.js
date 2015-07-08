function habilitaCampos(tipo)
{

    if (tipo == 1)
    {

        var verificaFederal = $("input:checkbox[name='checkFederal']:checked").length;
        if ( verificaFederal == 1 )
        {
            $('#txtDtFut').css('background','#FFFFCC');
//            $('#quitacaoFederalHora').css('background','#FFFFCC');
            $('#quitacaoFederalDias').css('background','#FFFFCC');
            $('#quitacaoFederalProjeto').css('background','#FFFFCC');
//            $('#quitacaoFederalProtocolo').css('background','#FFFFCC');
            $('#federal').removeAttr('disabled');
            $('#txtDtFut').removeAttr('disabled');
//            $('#quitacaoFederalHora').removeAttr('disabled');
            $('#quitacaoFederalDias').removeAttr('disabled');
            $('#quitacaoFederalProjeto').removeAttr('disabled');
//            $('#quitacaoFederalProtocolo').removeAttr('disabled');
            $("input[name='verificaqf']").val('1');
        }
        else
        {
            $('#federal').attr('disabled', true);
            $('#txtDtFut').attr('disabled', true);
//            $('#quitacaoFederalHora').attr('disabled', true);
            $('#quitacaoFederalDias').attr('disabled', true);
            $('#quitacaoFederalProjeto').attr('disabled', true);
//            $('#quitacaoFederalProtocolo').attr('disabled', true);
            $('#txtDtFut').removeAttr('style');
//            $('#quitacaoFederalHora').removeAttr('style');
            $('#quitacaoFederalDias').removeAttr('style');
            $('#quitacaoFederalProjeto').removeAttr('style');
//            $('#quitacaoFederalProtocolo').removeAttr('style');
            $("input[name='verificaqf']").val('0');
        }
    }
    else if (tipo == 3)
    {
        var verificaFGTS = $("input:checkbox[name='checkFGTS']:checked").length;
        if ( verificaFGTS == 1 )
        {
            $('#txtDtFutFGTS').css('background','#FFFFCC');
//            $('#quitacaoFGTSHora').css('background','#FFFFCC');
            $('#quitacaoFGTSDias').css('background','#FFFFCC');
            $('#quitacaoFGTSProjeto').css('background','#FFFFCC');
//            $('#quitacaoFGTSProtocolo').css('background','#FFFFCC');
            $('#fgts').removeAttr('disabled');
            $('#txtDtFutFGTS').removeAttr('disabled');
//            $('#quitacaoFGTSHora').removeAttr('disabled');
            $('#quitacaoFGTSDias').removeAttr('disabled');
            $('#quitacaoFGTSProjeto').removeAttr('disabled');
//            $('#quitacaoFGTSProtocolo').removeAttr('disabled');
            $("input[name='verificafgts']").val('1');
        }
        else
        {
            $('#fgts').attr('disabled', true);
            $('#txtDtFutFGTS').attr('disabled', true);
//            $('#quitacaoFGTSHora').attr('disabled', true);
            $('#quitacaoFGTSDias').attr('disabled', true);
            $('#quitacaoFGTSProjeto').attr('disabled', true);
//            $('#quitacaoFGTSProtocolo').attr('disabled', true);
            $('#txtDtFutFGTS').removeAttr('style');
//            $('#quitacaoFGTSHora').removeAttr('style');
            $('#quitacaoFGTSDias').removeAttr('style');
            $('#quitacaoFGTSProjeto').removeAttr('style');
//            $('#quitacaoFGTSProtocolo').removeAttr('style');
            $("input[name='verificafgts']").val('0');
        }

    }
    else if (tipo == 4)
    {
        var verificaCADIN = $("input:checkbox[name='checkCADIN']:checked").length;
        if ( verificaCADIN == 1 )
        {
            $('#cadin').css('background','#FFFFCC');
//            $('#quitacaoCADINHora').css('background','#FFFFCC');
            $('#quitacaoCADINDias').css('background','#FFFFCC');
            $('#quitacaoCADINProjeto').css('background','#FFFFCC');
            $('#quitacaoCADINSituacao').css('background','#FFFFCC');
//            $('#quitacaoCADINProtocolo').css('background','#FFFFCC');
            $('#cadin').removeAttr('disabled');
            $('#txtDtFutCADIN').removeAttr('disabled');
//            $('#quitacaoCADINHora').removeAttr('disabled');
            $('#quitacaoCADINProjeto').removeAttr('disabled');
            $('#quitacaoCADINSituacao').removeAttr('disabled');
//            $('#quitacaoCADINProtocolo').removeAttr('disabled');
            $("input[name='verificacadin']").val('1');
        }
        else
        {
            $('#cadin').attr('disabled', true);
//            $('#quitacaoCADINHora').attr('disabled', true);
            $('#quitacaoCADINProjeto').attr('disabled', true);
            $('#quitacaoCADINProjeto').attr('disabled', true);
            $('#quitacaoCADINSituacao').attr('disabled', true);
//            $('#quitacaoCADINProtocolo').attr('disabled', true);
            $('#txtDtFutCADIN').attr('disabled', true);
            $('#txtDtFutCADIN').removeAttr('style');
//            $('#quitacaoCADINHora').removeAttr('style');
            $('#quitacaoCADINDias').removeAttr('style');
            $('#quitacaoCADINProjeto').removeAttr('style');
            $('#quitacaoCADINSituacao').removeAttr('style');
//            $('#quitacaoCADINProtocolo').removeAttr('style');
            $("input[name='verificacadin']").val('0');
        }

    }
    else if (tipo == 5)
    {
        var verificaCEPIM = $("input:checkbox[name='checkCEPIM']:checked").length;
        if ( verificaCEPIM == 1 )
        {
            $('#cepim').css('background','#FFFFCC');
//            $('#quitacaoCEPIMHora').css('background','#FFFFCC');
            $('#quitacaoCEPIMDias').css('background','#FFFFCC');
            $('#quitacaoCEPIMProjeto').css('background','#FFFFCC');
            $('#quitacaoCEPIMSituacao').css('background','#FFFFCC');
//            $('#quitacaoCEPIMProtocolo').css('background','#FFFFCC');
            $('#cepim').removeAttr('disabled');
            $('#txtDtFutCEPIM').removeAttr('disabled');
//            $('#quitacaoCEPIMHora').removeAttr('disabled');
            $('#quitacaoCEPIMProjeto').removeAttr('disabled');
            $('#quitacaoCEPIMSituacao').removeAttr('disabled');
//            $('#quitacaoCEPIMProtocolo').removeAttr('disabled');
            $("input[name='verificacepim']").val('1');
        }
        else
        {
            $('#cepim').attr('disabled', true);
//            $('#quitacaoCEPIMHora').attr('disabled', true);
            $('#quitacaoCEPIMProjeto').attr('disabled', true);
            $('#quitacaoCEPIMProjeto').attr('disabled', true);
            $('#quitacaoCEPIMSituacao').attr('disabled', true);
//            $('#quitacaoCEPIMProtocolo').attr('disabled', true);
            $('#txtDtFutCEPIM').attr('disabled', true);
            $('#txtDtFutCEPIM').removeAttr('style');
//            $('#quitacaoCEPIMHora').removeAttr('style');
            $('#quitacaoCEPIMDias').removeAttr('style');
            $('#quitacaoCEPIMProjeto').removeAttr('style');
            $('#quitacaoCEPIMSituacao').removeAttr('style');
//            $('#quitacaoCEPIMProtocolo').removeAttr('style');
            $("input[name='verificacepim']").val('0');
        }

    }
    else
    {
        var verificaINSS = $("input:checkbox[name='checkINSS']:checked").length;
        if ( verificaINSS == 1 )
        {
            $('#inss').removeAttr('disabled');
            $('#txtDtFutINSS').removeAttr('disabled');
            $('#txtDtFutINSS').css('background','#FFFFCC');
            $('#quitacaoINSSHora').removeAttr('disabled');
            $('#quitacaoINSSDias').css('background','#FFFFCC');
            $('#quitacaoINSSHora').css('background','#FFFFCC');
            $('#quitacaoINSSProjeto').removeAttr('disabled');
            $('#quitacaoINSSDias').removeAttr('disabled');
            $('#quitacaoINSSProjeto').css('background','#FFFFCC');
            $('#quitacaoINSSProtocolo').removeAttr('disabled');
            $('#quitacaoINSSProtocolo').css('background','#FFFFCC');
            $("input[name='verificainss']").val('1');
        }
        else
        {
            $('#inss').attr('disabled', true);
            $('#txtDtFutINSS').attr('disabled', true);
            $('#txtDtFutINSS').removeAttr('style');
            $('#quitacaoINSSHora').attr('disabled', true);
            $('#quitacaoINSSDias').attr('disabled', true);
            $('#quitacaoINSSDias').removeAttr('style');
            $('#quitacaoINSSHora').removeAttr('style');
            $('#quitacaoINSSDias').removeAttr('style');
            $('#quitacaoINSSProjeto').attr('disabled', true);
            $('#quitacaoINSSProjeto').removeAttr('style');
            $('#quitacaoINSSProtocolo').attr('disabled', true);
            $('#quitacaoINSSProtocolo').removeAttr('style');
            $("input[name='verificainss']").val('0');
        }
    }
}

function mascara_hora(hora,tipo)
{
    if ( tipo == 1)
    {
        var form = document.forms[0].quitacaoFederalHora;
    }
    else if ( tipo == 2 )
    {
        var form = document.forms[0].quitacaoEstadualHora;
    }
    else if ( tipo == 3 )
    {
        var form = document.forms[0].quitacaoFGTSHora;
    }
    else if ( tipo == 4 )
    {
        var form = document.forms[0].quitacaoCADINHora;
    }
    else
    {
        var form = document.forms[0].quitacaoINSSHora;
    }

    var myhora = '';
    myhora = myhora + hora;
    if (myhora.length == 2)
    {
        myhora = myhora + ':';
        form.value = myhora;
    }
    if (myhora.length == 5)
    {
        myhora = myhora + ':';
        form.value = myhora;
    }
    if (myhora.length == 8)
    {
        verifica_hora(tipo);
    }
}

function verifica_hora(tipo)
{
    if ( tipo == 1)
    {
        form = document.forms[0].quitacaoFederalHora;
        hrs = (document.forms[0].quitacaoFederalHora.value.substring(0,2));
        min = (document.forms[0].quitacaoFederalHora.value.substring(3,5));
        seg = (document.forms[0].quitacaoFederalHora.value.substring(6,8));
    }
    else if ( tipo == 2 )
    {
        form = document.forms[0].quitacaoEstadualHora;
        hrs = (document.forms[0].quitacaoEstadualHora.value.substring(0,2));
        min = (document.forms[0].quitacaoEstadualHora.value.substring(3,5));
        seg = (document.forms[0].quitacaoEstadualHora.value.substring(6,8));
    }
    else if ( tipo == 3 )
    {
        form = document.forms[0].quitacaoFGTSHora;
        hrs = (document.forms[0].quitacaoFGTSHora.value.substring(0,2));
        min = (document.forms[0].quitacaoFGTSHora.value.substring(3,5));
        seg = (document.forms[0].quitacaoFGTSHora.value.substring(6,8));
    }
    else if ( tipo == 4 )
    {
        form = document.forms[0].quitacaoCADINHora;
        hrs = (document.forms[0].quitacaoCADINHora.value.substring(0,2));
        min = (document.forms[0].quitacaoCADINHora.value.substring(3,5));
        seg = (document.forms[0].quitacaoCADINHora.value.substring(6,8));
    }
    else
    {
        form = document.forms[0].quitacaoINSSHora;
        hrs = (document.forms[0].quitacaoINSSHora.value.substring(0,2));
        min = (document.forms[0].quitacaoINSSHora.value.substring(3,5));
        seg = (document.forms[0].quitacaoINSSHora.value.substring(6,8));
    }


    situacao = "";
    // verifica data e hora
    if ((hrs < 00 ) || (hrs > 23) || ( min < 00) ||( min > 59) || ( seg < 00) ||( seg > 59)){
        situacao = "falsa";
    }

    if (form.value == "") {
        situacao = "falsa";
    }

    if (situacao == "falsa")
    {
        $("#dialog-alert2").dialog({
            resizable: false,
            width:300,
            height:160,
            modal: true,
            autoOpen:false,
            buttons: {
                'OK': function() {
                    $(this).dialog('close');
                    
                }
            }
        });

        $("#dialog-alert2").dialog('open');
        $("#dialog-alert2").html("Hora Inválida!");
        form.value = '';
    }
}

