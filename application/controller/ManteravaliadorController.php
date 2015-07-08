<?php

class ManteravaliadorController extends GenericControllerNew {
	
	public function init() {
        $this->view->title = "Salic - Sistema de Apoio рs Leis de Incentivo р Cultura"; // tэtulo da pсgina
        $auth = Zend_Auth::getInstance(); // pega a autenticaчуo
        $Usuario = new UsuarioDAO(); // objeto usuсrio
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessуo com o grupo ativo

        if ($auth->hasIdentity()) { // caso o usuсrio estja autenticado
            // verifica as permissѕes
            $PermissoesGrupo = array();
            $PermissoesGrupo[] = 114; //Coordenador de Editais
	        
            if (!in_array($GrupoAtivo->codGrupo, $PermissoesGrupo)) { // verifica se o grupo ativo estс no array de permissѕes
                parent::message("Vocъ nуo tem permissуo para acessar essa сrea do sistema!", "principal/index", "ALERT");
            }

            // pega as unidades autorizadas, orgуos e grupos do usuсrio (pega todos os grupos)
            $grupos = $Usuario->buscarUnidades($auth->getIdentity()->usu_codigo, 21);

            // manda os dados para a visуo
            $this->view->usuario = $auth->getIdentity(); // manda os dados do usuсrio para a visуo
            $this->view->arrayGrupos = $grupos; // manda todos os grupos do usuсrio para a visуo
            $this->view->grupoAtivo = $GrupoAtivo->codGrupo; // manda o grupo ativo do usuсrio para a visуo
            $this->view->orgaoAtivo = $GrupoAtivo->codOrgao; // manda o ѓrgуo ativo do usuсrio para a visуo

        } // fecha if
        else {
            // caso o usuсrio nуo esteja autenticado
            return $this->_helper->redirector->goToRoute(array('controller' => 'index', 'action' => 'logout'), null, true);
        }

        parent::init(); // chama o init() do pai GenericControllerNew
    }
    
    public function indexAction() {
        /** Usuario Logado *********************************************** */
        $auth = Zend_Auth::getInstance(); // instancia da autenticaчуo
        $idusuario = $auth->getIdentity()->usu_codigo;
        $idorgao = $auth->getIdentity()->usu_orgao;
        //$this->_redirect("tramitarprojetos/despacharprojetos");

        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessуo com o grupo ativo
        $codGrupo = $GrupoAtivo->codGrupo; //  Grupo ativo na sessуo
        $codOrgao = $GrupoAtivo->codOrgao; //  гrgуo ativo na sessуo
        $this->view->codGrupo = $codGrupo;

        /*         * *************************************************************** */
    }
	
    public function cadastraravaliadorAction()
    {
    	/** Usuario Logado *********************************************** */
        $auth = Zend_Auth::getInstance(); // instancia da autenticaчуo
        $idusuario = $auth->getIdentity()->usu_codigo;
        $idorgao = $auth->getIdentity()->usu_orgao;

        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessуo com o grupo ativo
        $codGrupo = $GrupoAtivo->codGrupo; //  Grupo ativo na sessуo
        $codOrgao = $GrupoAtivo->codOrgao; //  гrgуo ativo na sessуo
        
        $this->view->codGrupo = $codGrupo;
        $this->view->codOrgao = $codOrgao;
        
        $Orgao = new Orgaos();
        
        $NomeOrgao = $Orgao->pesquisarNomeOrgao($codOrgao);
        $this->view->nomeOrgao = $NomeOrgao;

        /*         * *************************************************************** */
        
    }
    
    public function manteravaliadorAction()
    {

    	/** Usuario Logado *********************************************** */
        $auth = Zend_Auth::getInstance(); // instancia da autenticaчуo
        $idusuario = $auth->getIdentity()->usu_codigo;
       	//xd($auth->getIdentity());
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessуo com o grupo ativo
        $codGrupo = $GrupoAtivo->codGrupo; //  Grupo ativo na sessуo
        $codOrgao = $GrupoAtivo->codOrgao; //  гrgуo ativo na sessуo
        
        $this->view->codGrupo = $codGrupo;
        $this->view->codOrgao = $codOrgao;
        
        $Orgao = new Orgaos();

        $NomeOrgao = $Orgao->pesquisarNomeOrgao($codOrgao);
        $this->view->nomeOrgao = $NomeOrgao;

        /*         * *************************************************************** */
        
        if (isset($_POST['cpf']) || isset($_GET['cpf'])) { 
     		if(isset($_POST['cpf'])){
	        	$cpf = $_POST['cpf'];
	        	$this->view->cpf = $cpf;
     		}
     		else if(isset($_GET['cpf'])){
     			$cpf = $_GET['cpf'];
	        	$this->view->cpf = $cpf;
     		}
//			xd($cpf);
		        $buscaIdAgente = ManterAvaliadorDAO::buscaIdAgente($cpf);
		        $this->view->buscaIdAgente = $buscaIdAgente;
		    if(!empty( $buscaIdAgente[0]) ){   
		        foreach($buscaIdAgente as $idAgente){
		        	$idAgente = $idAgente->idAgente;
		        }
		        $avaliador = ManterAvaliadorDAO::buscaAvaliador($cpf, $idAgente);
		        $this->view->dadosAvaliador = $avaliador
		        ;
		        $avaliador = ManterAvaliadorDAO::buscaAvaliador($cpf, $idAgente);
	        	$this->view->nomeAvaliador = $avaliador[0]->nome;
	        	
		        $this->view->idAgente = $idAgente; 
		        if($idAgente){
		        	$editais = ManterAvaliadorDAO::buscaEditaisAtivos($idAgente);
		        	$this->view->editais = $editais;
		     	}
		        $idEdital = ManterAvaliadorDAO::listarEditaisAvaliador(); //BUSCA DA MODAL EDITAIS
		       	$this->view->dadosEditalAvaliador = $idEdital;
        	}
        	else{
        		parent::message("CPF nуo cadastrado!", "/manteravaliador/cadastraravaliador", "ERROR");
        	}
//        if (empty($_GET['cpf'])) {
//        	if (empty($_POST['cpf'])){
//        		parent::message("Digite o CPF!", "manteravaliador/cadastraravaliador", "CONFIRM");
//        	}
//        }
        }
    	if (isset($_POST['idEdit'])) { //DESVINCULAR
    		$idAgente = $_POST['idAgen'];
        	$idEdital = $_POST['idEdit'];
        	$cpf = $_POST['cpf2'];
        	$this->view->cpf = $cpf;
        	//xd($cpf);
        	$alterar = new tbAvaliadorEdital();
        	$dados = array('stAtivo' => 'I');
            $where = "idAvaliador = $idAgente and idEdital = $idEdital";
            $atualizarProjeto = $alterar->alterarAvaliador($dados, $where);
  
	        	$buscaIdAgente = ManterAvaliadorDAO::buscaIdAgente($cpf);
		        $this->view->buscaIdAgente = $buscaIdAgente;
		        //xd($buscaIdAgente);
		    if($buscaIdAgente){   
		        foreach($buscaIdAgente as $idAgente){
		        	$idAgente = $idAgente->idAgente;
		        }
		        $avaliador = ManterAvaliadorDAO::buscaAvaliador($cpf, $idAgente);
		        $this->view->dadosAvaliador = $avaliador;
		        $this->view->idAgente = $idAgente; 
		        if($idAgente){
		        	$editais = ManterAvaliadorDAO::buscaEditaisAtivos($idAgente);
		        	$this->view->editais = $editais;
		     	}
		        $idEdital = ManterAvaliadorDAO::listarEditaisAvaliador(); //BUSCA DA MODAL EDITAIS
		       	$this->view->dadosEditalAvaliador = $idEdital;
		    }
    		else{
        		$this->view->cpf = $cpf;
        	}
        	parent::message("Edital Desvinculado com Sucesso!", "/manteravaliador/manteravaliador?cpf={$cpf}", "CONFIRM");

        }
        
    	if (isset($_POST['idAgente'])) { //VINCULAR
        	$idAgente= $_POST['idAgente'];
        	$idEdit= $_POST['idEdital2'];
			
        	foreach($idEdit as $idEdital){
//	        	$buscaEdital = ManterAvaliadorDAO::buscaEditais($idAgente, $idEdital);
	        	$alterar = new tbAvaliadorEdital();
	        	$vinculado = $alterar->buscar(array('idAvaliador = ?'=>$idAgente, 'idEdital = ?'=>$idEdital))->toArray();
	        	
	        	if($vinculado){
	        		if($vinculado[0]['stAtivo'] == 'I'){
			        	$dados = array('stAtivo' => 'A');
			            $where = "idAvaliador = $idAgente and idEdital = $idEdital";
			            $atualizarProjeto = $alterar->alterarAvaliador($dados, $where);
			            parent::message("Edital Vinculado com Sucesso!", "/manteravaliador/manteravaliador?cpf={$cpf}", "CONFIRM");
	        		}elseif($vinculado[0]['stAtivo'] == 'A'){
	        			parent::message("Edital jс vinculado!", "/manteravaliador/manteravaliador?cpf={$cpf}", "ALERT");
	        		}
	        	}else{
	        		$dadosInserir = array(
	        			'idEdital' => $idEdital,
	                    'idAvaliador' => $idAgente,
	                    'stAtivo' => 'A'
	                );
	                $inserir = $alterar->inserirAvaliador($dadosInserir);
	        	}
        	}

        	parent::message("Edital Vinculado com Sucesso!", "/manteravaliador/manteravaliador?cpf={$cpf}", "CONFIRM");
        }

    }
    
    public function incluireditalAction()
    {
    	/** Usuario Logado *********************************************** */
        $auth = Zend_Auth::getInstance(); // instancia da autenticaчуo
        $idusuario = $auth->getIdentity()->usu_codigo;
        $idorgao = $auth->getIdentity()->usu_orgao;
        
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessуo com o grupo ativo
        $codGrupo = $GrupoAtivo->codGrupo; //  Grupo ativo na sessуo
        $codOrgao = $GrupoAtivo->codOrgao; //  гrgуo ativo na sessуo
        
        $this->view->codGrupo = $codGrupo;
        $this->view->codOrgao = $codOrgao;
        $this->view->usuario = $idusuario;
        
        $Orgao = new Orgaos();

        $NomeOrgao = $Orgao->pesquisarNomeOrgao($codOrgao);
        $this->view->nomeOrgao = $NomeOrgao;

        /* *************************************************************** */
        
    }
}
?>