function analisarProjeto(id){
		
	$('#analisarProjeto'+id).dialog(
			{
				height: 360,
				width: 630,
				resizable: false,
				EscClose: false,
				modal: true
			});
	
}


function modal2(){
$('#modal2').modal(
		{
			appendTo:'body',
			focus:true,
			overlayId:'modal-overlay',
			zIndex:1000,
			closeClass:'Close',
			escClose:true
		}	
);

$('#modal2').draggable({handle: '#title'});

}

