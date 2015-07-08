<?php 
 
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of VincularresponsavelController
 *
 * @author tisomar
 */
class VincularresponsavelController extends GenericControllerNew {

    private $emailResponsavel  	= null;
    private $idResponsavel  	= 0;
    private $idAgente 	    	= 0;
    private $idUsuario  		= 0;

    public function init() {

    	// verifica as permissões
        $PermissoesGrupo = array();
        $PermissoesGrupo[] = 97;  // Gestor Salic

        $auth = Zend_Auth::getInstance(); // instancia da autenticação

        if (isset($auth->getIdentity()->usu_codigo)) 
        {
            parent::perfil(1, $PermissoesGrupo);
        } 
        else 
        {
            parent::perfil(4, $PermissoesGrupo);
        }

        /*********************************************************************************************************/

        $cpf = isset($auth->getIdentity()->usu_codigo) ? $auth->getIdentity()->usu_identificacao : $auth->getIdentity()->Cpf;        
        
        /*********************************************************************************************************/

        // Busca na SGCAcesso
        $sgcAcesso = new Sgcacesso();
        $buscaAcesso = $sgcAcesso->buscar(array('Cpf = ?' => $cpf));
        
        // Busca na Usuarios
        $usuarioDAO = new Usuario();
        $buscaUsuario = $usuarioDAO->buscar(array('usu_identificacao = ?' => $cpf));

        // Busca na Agentes
        $agentesDAO = new Agentes();
        $buscaAgente = $agentesDAO->BuscaAgente($cpf);
        
    
        if( count($buscaAcesso) > 0)
        { 
        	$this->idResponsavel 	= $buscaAcesso[0]->IdUsuario; 
        	$this->emailResponsavel = $buscaAcesso[0]->Email; 
        }
        if( count($buscaAgente) > 0 ){ $this->idAgente 	   = $buscaAgente[0]->idAgente; }
        if( count($buscaUsuario) > 0 ){ $this->idUsuario   = $buscaUsuario[0]->usu_codigo; }
        
        //xd($this->idResponsavel);
        /*********************************************************************************************************/

        //xd($this->idResponsavel);
        
        $this->view->idAgenteLogado = $this->idAgente;
        parent::init();
        // chama o init() do pai GenericControllerNew
    }

    public function indexAction() {
    }

    public function mostraragentesAction() 
    {
    	$this->_helper->layout->disableLayout();
        $ag = new Agentes;
        
        if (isset($_POST['cnpjcpf'])){
            $cnpjcpf = $_POST['cnpjcpf'];
            $nome = $_POST['nome'];
            $cnpjcpfSemMask = Mascara::delMaskCPFCNPJ($cnpjcpf);
            
            if ($cnpjcpf != ''){
                $wh['CNPJCPF = ?'] = Mascara::delMaskCPFCNPJ($cnpjcpf);
                $where['ag.CNPJCPF = ?'] = Mascara::delMaskCPFCNPJ($cnpjcpf);
                $buscaAgente = $ag->buscar($wh);

                if($buscaAgente->count() == 0) {
                    echo "  <input type='hidden' id='novoproponente' value='".$cnpjcpfSemMask."' />
                            <table class='tabela' style='margin:1% auto; width:40%;'>
                                <tr>
                                    <td class='red centro'>Proponente n&atilde;o cadastrado! Deseja cadastrar agora?</td>
                                    <td class='centro'><input type='button' class='btn_cadastrar_proponente' id='novoprop' onclick='novoproponente();' /></td>
                                </tr>
                            </table>";
                    exit();
                }
            }
            
            if ($nome != ''){
                $where["nm.Descricao like (?)"] = "%" . $nome . "%";
            }
           
            $buscarvinculo = $ag->buscarNovoProponente($where, $this->idResponsavel);
            if ($buscarvinculo->count() > 0){
                $this->montaTela('vincularresponsavel/mostraragentes.phtml', array('vinculo' => $buscarvinculo,'idResponsavel' => $this->idResponsavel));
            } else {
                echo "<div id='msgAgenteVinculado'>Nenhum registro encontrado!</div>
                    <script>
                        alertModal(null, 'msgAgenteVinculado', null, 150);
                      </script>";
                exit();
            }

        } else {
            $where = array();
            $where["vp.idUsuarioResponsavel = ?"] = $this->idUsuario;
            //$where["v.siVinculo = ?"] = 2;
            $buscarvinculo = $ag->buscarAgenteVinculoProponente($where);
//            xd($buscarvinculo);
            $this->montaTela('vincularresponsavel/mostraragentes.phtml', array('vinculo' => $buscarvinculo));
        }
    }

    public function vinculoAction() 
    {
        $this->_helper->layout->disableLayout();

        $v 				= new TbVinculo();
        $pp 			= new PreProjeto();
        $vprp 			= new tbVinculoPropostaResponsavelProjeto();
        $emailDAO 		= new EmailDAO();
        $internetDAO 	= new Internet();
        
        /*Temos que ver aonde vamos buscar o email do cara?*/
        $buscarEmail = $internetDAO->buscarEmailAgente(null, $_POST['idAgente'], 1, null, true);
        
        $emailProponente = $buscarEmail[0]->Email;
        $assunto = 'Solicitação de vinculo ao responsável';
        $texto = 'Favor verificar o vinculo solicitado no Sistema SALIC WEB';
        
        if (isset($_POST['solicitarvinculo'])) 
        {
            $idAgenteProponente 	= $_POST['idAgente'];
            $idUsuarioResponsavel 	= $this->idResponsavel;

            $dados = array( 'idUsuarioResponsavel' 	=> $idUsuarioResponsavel,
                			'idAgenteProponente' 	=> $idAgenteProponente,
			                'dtVinculo' 			=> new Zend_Db_Expr('GETDATE()'),
			                'siVinculo' 			=> 0
            );
            
            try 
            {

	            $where['idAgenteProponente   = ?'] = $idAgenteProponente;
		        $where['idUsuarioResponsavel = ?'] = $idUsuarioResponsavel;
		        $vinculocadastrado = $v->buscar($where);
		    	
		        if(count($vinculocadastrado) > 0)
				{
					$v->alterar($dados, $where);            		
		        }
		        else 
		        {
		        	$v->inserir($dados);	
		        }
            	
                
                $enviarEmail = $emailDAO->enviarEmail($emailProponente, $assunto, $texto);
                
                echo json_encode(array('error' => false));
            } 
            catch (Zend_Exception $e) 
            {
                echo json_encode(array('error' => true));
            }
        }
        
        if (isset($_POST['solicitarvinculoproposta'])) 
        {
            $idpreprojeto 			= $_POST['idpreprojeto'];
            $buscarpreprojeto 		= $pp->buscar(array('idPreProjeto = ?' => $idpreprojeto))->current();
            $idAgenteProponente 	= $buscarpreprojeto->idAgente;
            $idUsuarioResponsavel 	= $this->idUsuario;
            
            $buscarvinculo = $v->buscar(array('idAgenteProponente = ? ' => $idAgenteProponente, 'idUsuarioResponsavel = ?' => $idUsuarioResponsavel))->current();
            $idVinculo = $buscarvinculo->idVinculo;

            $dados = array('idVinculo' => $idVinculo, 'idPreProjeto' => $idpreprojeto, 'siVinculoProposta' => 0);
            
            try 
            {
                $vprp->inserir($dados);
                echo json_encode(array('error' => false));
            } 
            catch (Zend_Exception $e) 
            {
                echo json_encode(array('error' => true));
            }
        }
        
        if (isset($_POST['aceitevinculo'])) 
        {
            $dados = array('siVinculoProposta' => $_POST['stVinculoProposta']);
            $where = "idVinculoProposta = {$_POST['idVinculoProposta']}";
            
            try 
            {
                $vprp->alterar($dados, $where);
                echo json_encode(array('error' => false));
            } 
            catch (Zend_Exception $e) 
            {
                echo json_encode(array('error' => true));
            }
        }
        
        if (isset($_POST['desvincular'])) 
        {
            $dados = array('siVinculoProposta' => 1);
            $where = "idVinculoProposta = {$_POST['idVinculoProposta']}";

            try 
            {
                $vprp->alterar($dados, $where);
                echo json_encode(array('error' => false));
            } 
            catch (Zend_Exception $e) 
            {
                echo json_encode(array('error' => true));
            }
        }
        
        exit();
    }

    public function vincularresponsavelAction() {
        $ag = new Agentes;
        $buscarvinculo = $ag->buscarAgenteVinculoResponsavel(array('vr.idAgenteProponente = ?' => $this->idUsuario, 'siVinculoProposta = ?' => 0));
        $buscarvinculado = $ag->buscarAgenteVinculoResponsavel(array('vr.idAgenteProponente = ?' => $this->idUsuario, 'siVinculoProposta = ?' => 2));
        $this->view->vinculo = $buscarvinculo;
        $this->view->vinculado = $buscarvinculado;
    }
    
    public function vincularproponenteAction() {
        $ag = new Agentes;
        $buscarvinculo = $ag->buscarAgenteVinculoProponente(array('vp.idAgenteProponente = ?' => $this->idUsuario, 'siVinculoProposta = ?' => 0));
        $buscarvinculado = $ag->buscarAgenteVinculoProponente(array('vp.idAgenteProponente = ?' => $this->idUsuario, 'siVinculoProposta = ?' => 2));
        $this->view->vinculo = $buscarvinculo;
        $this->view->vinculado = $buscarvinculado;
    }

    public function consultarresponsavelAction() {
        
    }

    public function mostraresponsavelAction() {
        $this->_helper->layout->disableLayout();
        $ag = new Agentes();
        if ($_POST) {
            $cnpjcpf = $_POST['cnpjcpf'];
            $nome = $_POST['nome'];
            $stVinculo = $_POST['stVinculo'];
            if ($cnpjcpf != '') {
                $where['ag.CNPJCPF = ?'] = Mascara::delMaskCPFCNPJ($cnpjcpf);
            }
            if ($nome != '') {
                $where["nm.Descricao like (?)"] = "%" . $nome . "%";
            }
            if ($stVinculo != '') {
                $where['vprp.siVinculoProposta = ?'] = $stVinculo;
            }
        } else {
            $where['vr.idAgenteProponente = ?'] = $this->idAgente;
            $where['vprp.idPreProjeto is not null'] = '';
        }
        $buscarVinculo = $ag->buscarAgenteVinculoResponsavel($where);
//        xd($buscarVinculo);
        $this->view->vinculo = $buscarVinculo;
    }
    
    

    /********************************************************************************************************/
    
    public function vinculoproponenteAction()
    {
    	$tbVinculo = new TbVinculo();
        $tbVinculoProposta = new tbVinculoPropostaResponsavelProjeto();
        $PreProjetoDAO = new PreProjeto();

        $idVinculo = $this->_request->getParam("idVinculo");
        $siVinculo = $this->_request->getParam("siVinculo");
        $idUsuarioR = $this->_request->getParam("idUsuario");

        $dados = array('siVinculo' => $siVinculo,'dtVinculo' => new Zend_Db_Expr("GETDATE()") );
        $where['idVinculo = ?'] = $idVinculo;
        $msg = '';

        if($siVinculo == 1) {
            $msg = 'O responsável foi rejeitado.';
        } else if($siVinculo == 2) {
            $msg = 'Responsável vinculado com sucesso!';
        } else if($siVinculo == 3) {
            $msg = 'O responsável foi desvinculado.';
        }

        try {
            $alterar = $tbVinculo->alterar($dados, $where);

            if($siVinculo == 3) {
                $alterarVinculoProposta = $PreProjetoDAO->retirarProjetosVinculos($siVinculo, $idVinculo);
                $retirarPropostas = $PreProjetoDAO->retirarProjetos($this->idResponsavel, $idUsuarioR, $this->idAgente);
            }
            parent::message($msg, "manterpropostaincentivofiscal/consultarresponsaveis", "CONFIRM");
        } catch (Exception $e) {
            parent::message("Falha na recuperação dos dados!", "manterpropostaincentivofiscal/consultarresponsaveis", "ERROR");
        }
    }
    
    public function vinculoresponsavelAction()
    {
    	$tbVinculo	= new TbVinculo();
    	$agentes 	= new Agentes();
    	
    	$idResponsavel 		= $this->_request->getParam("idResponsavel");
    	$idProponente 		= $this->idAgente;

    	
    	$where['idUsuarioResponsavel = ?'] = $idResponsavel;
    	$where['idAgenteProponente   = ?'] = $idProponente;
		$vinculo = $tbVinculo->buscar($where);
    	

    	$dados = array('idAgenteProponente'		=> $idProponente,
    				   'dtVinculo' 				=> new Zend_Db_Expr("GETDATE()"),
    				   'siVinculo' 				=> 2,
    				   'idUsuarioResponsavel' 	=> $idResponsavel
    	);
    	
    	try {

    		if(count($vinculo) > 0)
    		{
    			$dadosUP['siVinculo'] = 2;
    			$whereUP['idVinculo = ?'] = $vinculo[0]->idVinculo;
    			
    			$update = $tbVinculo->alterar($dadosUP, $whereUP);
    		}
    		else 
    		{
	    		$insere = $tbVinculo->inserir($dados);
    		}
    			
    		
    		parent::message("vinculado com sucesso!", "manterpropostaincentivofiscal/novoresponsavel", "CONFIRM");
    		
    	} catch (Exception $e) 
    	{
    		parent::message("Erro ao vincular! ".$e->getMessage(), "manterpropostaincentivofiscal/novoresponsavel", "ERROR");
    	}
    	
    	
    }
    
    /**
	 * Método trocarproponente()
	 * UC 89 - Fluxo FA1 - Trocar Proponente
	 * @access public
	 * @param void
	 * @return void
	 */
    public function trocarproponenteAction()
    {
    	$tbVinculoPropostaDAO 	= new tbVinculoPropostaResponsavelProjeto();
    	$PreProjetoDAO 			= new PreProjeto();
    	
    	$dadosPropronente 		= $this->_request->getParam("propronente");

    	$parte = explode(":", $dadosPropronente);
		$idNovoVinculo 		= $parte[0];
		$idNovoPropronente 	= $parte[1];	
			
    	$idVinculoProposta		= $this->_request->getParam("idVinculoProposta"); // Vinculo a alterar
    	$idPreProjeto 			= $this->_request->getParam("idPreProjeto"); // idPreProjeto
    	
    	$mecanismo 			    = $this->_request->getParam("mecanismo");
    	
    	try {

    		$dados['siVinculoProposta'] = 3;
    		$where['idVinculoProposta = ?'] = $idVinculoProposta;
    		$alteraVP = $tbVinculoPropostaDAO->alterar($dados, $where, false);
    			
    		$novosDados = array('idVinculo' 		=> $idNovoVinculo, 
    							'idPreProjeto' 		=> $idPreProjeto, 
    							'siVinculoProposta' => 2);
    		
    		$insere = $tbVinculoPropostaDAO->inserir($novosDados, false);

    		$alteraPP = $PreProjetoDAO->alteraproponente($idPreProjeto, $idNovoPropronente);
    		
    		if($mecanismo == 2)
    		{
	    		parent::message("Proponente trocado com sucesso!", "manterpropostaedital/dadospropostaedital?idPreProjeto=".$idPreProjeto, "CONFIRM");
    		}
			else
			{
	    		parent::message("Proponente trocado com sucesso!", "manterpropostaincentivofiscal/editar?idPreProjeto=".$idPreProjeto, "CONFIRM");
			} 
    		
    		
    	} 
    	catch (Exception $e) 
    	{
    		parent::message("Erro. ".$e->getMessage(), "manterpropostaincentivofiscal/editar?idPreProjeto=".$idPreProjeto, "ERROR");
    	}
    	
    	
    	
    	
    }

    
        /**
         * Método trocarproponente()
         * UC 89 - Fluxo FA1 - Trocar Proponente
         * @access public
         * @param void
         * @return void
         */
        public function vincularpropostasAction() {
            $tbVinculoPropostaDAO = new tbVinculoPropostaResponsavelProjeto();
            $PreProjetoDAO = new PreProjeto();

            $opcaovinculacao = $this->_request->getParam("opcaovinculacao");
            $idPreProjeto = $this->_request->getParam("propostas");

            $dadosResponsavel = $this->_request->getParam("responsavel");
            $parte = explode(":", $dadosResponsavel);
            $idVinculo = $parte[0];
            $idResponsavel = $parte[1];

            $idResponsavelRetirar = $parte[1];

            $msg = "Responsável vinculado com sucesso!";
            if($opcaovinculacao == 1) {
                $idResponsavel = $this->idResponsavel;
                $msg = "O responsável foi desvinculado.";
            }

            try {
                $dados['siVinculoProposta'] = 3;
                $where['idPreProjeto = ?'] = $idPreProjeto;
                $alteraVP = $tbVinculoPropostaDAO->alterar($dados, $where, false);

                $novosDados = array(
                        'idVinculo' => $idVinculo,
                        'idPreProjeto' => $idPreProjeto,
                        'siVinculoProposta' => 2
                );
                $insere = $tbVinculoPropostaDAO->inserir($novosDados, false);
                $alteraPP = $PreProjetoDAO->alteraresponsavel($idPreProjeto, $idResponsavel);
                parent::message($msg, "manterpropostaincentivofiscal/vincularpropostas", "CONFIRM");

            } catch (Exception $e) {
                parent::message("Erro. ".$e->getMessage(), "manterpropostaincentivofiscal/vincularpropostas", "ERROR");
            }
        }
    
    /**
	 * Método vincularprojetos()
	 * UC 89 - Fluxo FA8 - Desvincular Projetos
	 * @access public
	 * @param void
	 * @return void
	 */
    public function vincularprojetosAction()
    {
    	$tbVinculoPropostaDAO 	= new tbVinculoPropostaResponsavelProjeto();
    	$PreProjetoDAO 			= new PreProjeto();
    	
    	$idPreProjeto			= $this->_request->getParam("propostas");
		$idResponsavel 			= $this->idResponsavel;

		//x($idPreProjeto);
		//xd($idResponsavel);
		
		
    	try {

    		$dados['siVinculoProposta'] = 3;
    		$where['idPreProjeto = ?'] = $idPreProjeto;
    		$alteraVP = $tbVinculoPropostaDAO->alterar($dados, $where, false);
    		
    		// Cadê a procuração?
    		
    		/* Não vai cadastrar pois ele é dono da sua proposta
    		$novosDados = array('idVinculo' 		=> $idVinculo, 
    							'idPreProjeto' 		=> $idPreProjeto, 
    							'siVinculoProposta' => 2
    		);
    		
    		$insere = $tbVinculoPropostaDAO->inserir($novosDados, false);
			*/
    		$alteraPP = $PreProjetoDAO->alteraresponsavel($idPreProjeto, $idResponsavel);
    		
    		parent::message("O responsável foi desvinculado.", "manterpropostaincentivofiscal/vincularprojetos", "CONFIRM");
    		
    	} 
    	catch (Exception $e) 
    	{
    		parent::message("Erro. ".$e->getMessage(), "manterpropostaincentivofiscal/vincularprojetos", "ERROR");
    	}
    	
    	
    	
    	
    }
    

}

?>
