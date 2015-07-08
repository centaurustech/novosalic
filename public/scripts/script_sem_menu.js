// Função para ajustar o tamanho do layout
function layout_fluido() {
	
	var janela = $(window).width();	
	
	var fluidNavGlobal = janela - 245;
	var fluidConteudo = janela - 40;
	var fluidTitulo = janela - 39;
	var fluidRodape = janela - 19;	
	
	$("#navglobal").css("width",fluidNavGlobal);	
	$("#conteudo").css("width",fluidConteudo);
	$("#titulo").css("width",fluidTitulo);
	$("#rodapeConteudo").css("width",fluidConteudo);
	$("#rodape").css("width",fluidRodape);
	
}

$(document).ready(function(){ 
	// Ativa os textfields
	$("input").focus( function() { 
		if(this.type=="text" && this.className != "campoBusca") {
			//$(this).parent().addClass("on");
		}
	});	

	$("textarea").focus( function() { 
		//$(this).parent().addClass("on-textarea");
	});

	// Desativa os textfields
	$("input").blur( function() { 
		if(this.type=="text" && this.className != "campoBusca") { 
			//$(this).parent().removeClass("on"); 
		}
	});

	$("textarea").blur( function() { 
		//$(this).parent().removeClass("on-textarea");
	});	

	layout_fluido();

	// Efeito mouseover do menu global
	$("#navglobal ul li").mouseover(function() {
  		if ( this.className != "noOverHere" ) {			
			$(this).addClass("ativo");			
		}

		}).mouseout(function() {
			$(this).removeClass("ativo");		
		
	});
	
});

// Ao redimensionar o navegador reajustar o layout
$(window).resize(function() {
	layout_fluido();
});

// Invoca o menu estilo ipod
$(function(){ 	
	$(".hierarchybreadcrumb").menu({
		content: $('.hierarchybreadcrumb').next().html(),
		crumbDefaultText: ' '
	});
	//	$("#menuContainer").height();
	// alert($("ul.fg-menu-current").height());
});