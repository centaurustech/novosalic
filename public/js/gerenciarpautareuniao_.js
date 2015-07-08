function cronometrouso(funcaoFim){
    var contador = setInterval(function(){
        var minu = $('#cronometro #minu').html()*1;
        var seg = $('#cronometro #seg').html()*1;

        if(seg == 0){
            minu--;
            seg = 59;
        }
        else
            seg--;

        if(seg < 10)
            seg = '0'+seg;
        if(minu < 10)
            minu = '0'+minu;

        $('#cronometro #minu').html(minu);
        $('#cronometro #seg').html(seg);

        if(minu==0 && seg==0){
            clearInterval(contador);
            funcaoFim();
        }
        else{
            $('#menuGerenciar').css('display','none');
        }
    },1000);
}

function abrirdados(count, idpronac, tipoagente, envioplenaria){
    if($(".tradd[id='"+count+"']").attr('aberto') == 'false'){
        if($.trim($("#dadosplenaria"+count).html()) == '' ){
            var tr = "<tr id='dadosplenaria"+count+"' class='divdadosprojeto'>";
            tr += "<td colspan=\"8\">";
            tr += "<fieldset>";
            tr += "<input type='button' view='parecerconsolidado' idprojeto='parecerconsolidado"+count+"' class='btn_adicionar' onClick='dadosprojetos("+idpronac+", this)'/>Parecer Consolidado";
            tr += "</fieldset>";
            tr += "<div id='parecerconsolidado"+count+"' aberto='false'></div>";
            tr += "<fieldset>";
            tr += "<input type='button' view='analisedeconta' idprojeto='analisedeconta"+count+"' class='btn_adicionar' onClick='dadosprojetos("+idpronac+", this)'/>An&aacute;lise de Corte Sugerido";
            tr += "</fieldset>";
            tr += "<div id='analisedeconta"+count+"' aberto='false'></div>";
            tr += "<fieldset>";
            tr += "<input type='button' view='analisedeconteudo' idprojeto='analisedeconteudo"+count+"' class='btn_adicionar' onClick='dadosprojetos("+idpronac+", this)'/>An&aacute;lise de Conte&uacute;do";
            tr += "</fieldset>";
            tr += "<div id='analisedeconteudo"+count+"' aberto='false'></div>";
            tr += "<fieldset>";
            tr += "<input type='button' view='analisedecustos' idprojeto='analisedecustos"+count+"' class='btn_adicionar' onClick='dadosprojetos("+idpronac+", this)'/>An&aacute;lise de Custos";
            tr += "</fieldset>";
            tr += "<div id='analisedecustos"+count+"' aberto='false'></div>";
            if(tipoagente == 'cc' && envioplenaria =='s'){
                tr += "<fieldset>";
                tr += "<input type='button' view='aprovarparecer' idprojeto='aprovarparecer"+count+"' class='btn_adicionar' onClick='dadosprojetos("+idpronac+", this)'/>Retirar a Plen&aacute;ria";
                tr += "</fieldset>";
                tr += "<div id='aprovarparecer"+count+"' aberto='false'></div>";
            }
            if(tipoagente == 'coc' && envioplenaria =='n'){
                tr += "<fieldset>";
                tr += "<input type='button' view='aprovarparecer' idprojeto='aprovarparecer"+count+"' class='btn_adicionar' onClick='dadosprojetos("+idpronac+", this)'/>Submeter a Plen&aacute;ria";
                tr += "</fieldset>";
                tr += "<div id='aprovarparecer"+count+"' aberto='false'></div>";
            }
            tr += "</td>";
            tr += "</td>";
            $(".tradd[id='"+count+"']").after(tr) ;
            $(".tradd[id='"+count+"']").attr('aberto','true');
        }
        else{
            $("#dadosplenaria"+count).removeClass('sumir')
            $(".tradd[id='"+count+"']").attr('aberto','true');
        }
    }
    else{
        $("#dadosplenaria"+count).addClass('sumir')
        $(".tradd[id='"+count+"']").attr('aberto','false');
    }
}

