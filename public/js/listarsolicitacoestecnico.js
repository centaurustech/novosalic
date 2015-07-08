$(document).ready(function(){
    $("#btParecer").click(function(){
        $("#dsParecerTecnico").val($('#editor').html());
        //alert($("#dsParecerTecnico").val());exit();
        if($("#dsParecerTecnico").val()){
            $("form[name='formularioComentario']").submit();
        }else{
            alert("Favor Prencher o Comentário!");
            $("#editor").focus();
        }
    });

    $("#btDiligenciar").click(function(){
        if($("#dsParecerTecnico").val()){
            $("#diligenciar").val(true);
            $("form[name='formularioComentario']").submit();
        }else{
            alert("Favor Prencher o Comentário!");
            $("#dsParecerTecnico").focus();
        }
    });

    $("#btCancelar").click(function(){
        history.go(-1);
    });

    $("#btAprovar").click(function(){
        $("#dsJustificativaAvaliacao").val($('#editor').html());
        if($("#dsJustificativaAvaliacao").val()){
            $("#stAprovacao").val('D');
            $("form[name='formularioAprovacao']").submit();
        }else{
            alert("Favor Prencher o Comentário!");
            $("#editor").focus();
        }
    });

    $("#btNaoAprovar").click(function(){
        $("#dsJustificativaAvaliacao").val($('#editor').html());
        if($("#dsJustificativaAvaliacao").val()){
            $("#stAprovacao").val('I');
            $("form[name='formularioAprovacao']").submit();
        }else{
            alert("Favor Prencher o Comentário!");
            $("#editor").focus();
        }
    });

    $("#btRetornoTecnico").click(function(){
        $("#dsJustificativaAvaliacao").val($('#editor').html());
        if($("#dsJustificativaAvaliacao").val()){
            $("#stAprovacao").val('RT');
            $("form[name='formularioAprovacao']").submit();
        }else{
            alert("Favor Prencher o Comentário!");
            $("#editor").focus();
        }
    });
    $(".mouse").mouseover(function(){
        $(this).attr('style','background-color:#ACDA5D;color:#ffffff;');
    });

    $(".mouse").mouseout(function(){
        $(this).removeAttr('style');
    });

});

function abrir_fechar(id_div){

    $('#'+id_div).slideToggle(400);

    $('#maiss'+id_div).toggle().focus();

    $('#menoss'+id_div).toggle().focus();

}
