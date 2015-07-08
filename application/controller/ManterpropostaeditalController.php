<?php
/**
 * Controller Disvincular Agentes
 * @author Equipe RUP - Politec
 * @since 07/06/2010
 * @version 1.0
 * @package application
 * @subpackage application.controller
 * @link http://www.cultura.gov.br
 * @copyright ï¿½ 2010 - Ministï¿½rio da Cultura - Todos os direitos reservados.
 */
require_once "GenericControllerNew.php";

class ManterpropostaeditalController extends GenericControllerNew {

    private $getIdUsuario   = 0;
    private $idResponsavel  = 0;
    private $idAgente       = 0;
    private $idUsuario      = 0;
    private $cpfLogado      = null;
	
    /**
     * Reescreve o método init()
     * @access public
     * @param void
     * @return void
     */
    public function init() {
        ini_set('memory_limit', '128M');

        $auth = Zend_Auth::getInstance(); // pega a autenticação
        $GrupoAtivo   = new Zend_Session_Namespace('GrupoAtivo');

	    // verifica as permissoes
        $PermissoesGrupo = array();
        $PermissoesGrupo[] = 97;  // Gestor do SALIC
        $PermissoesGrupo[] = 93;  // Coordenador de Parecerista

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
        
        // Busca na SGCAcesso
        $sgcAcesso 	 = new Sgcacesso();
        $buscaAcesso = $sgcAcesso->buscar(array('Cpf = ?' => $cpf));
        
        // Busca na Usuarios
        $usuarioDAO   = new Usuario();
        $buscaUsuario = $usuarioDAO->buscar(array('usu_identificacao = ?' => $cpf));

        // Busca na Agentes
        $agentesDAO  = new Agentes();
        $buscaAgente = $agentesDAO->BuscaAgente($cpf);
        
    
        if( count($buscaAcesso) > 0){ $this->idResponsavel = $buscaAcesso[0]->IdUsuario; }
        if( count($buscaAgente) > 0 ){ $this->idAgente 	   = $buscaAgente[0]->idAgente; }
        if( count($buscaUsuario) > 0 ){ $this->idUsuario   = $buscaUsuario[0]->usu_codigo; }
        
        if($this->idAgente != 0)
        {
        	$this->usuarioProponente = "S";
        }
        
        /*********************************************************************************************************/
        $this->cpfLogado = $cpf;
        
        parent::init();
        
        
        //VALIDA ITENS DO MENU (Documento pendentes)
        if (isset($_GET['idPreProjeto']) && !empty($_GET['idPreProjeto'])) {
            $get = Zend_Registry::get("get");
            $this->view->documentosPendentes = AnalisarPropostaDAO::buscarDocumentoPendente($get->idPreProjeto);

            if (!empty($this->view->documentosPendentes)) {
                $verificarmenu = 1;
                $this->view->verificarmenu = $verificarmenu;
            } else {
                $verificarmenu = 0;
                $this->view->verificarmenu = $verificarmenu;
            }

            //(Enviar Proposta ao MinC , Excluir Proposta)
            $mov = new Movimentacao();
            $movBuscar = $mov->buscar(array('idProjeto = ?' => $get->idPreProjeto), array('idMovimentacao desc'), 1, 0)->current();

            if (isset($movBuscar->Movimentacao) && $movBuscar->Movimentacao != 95) {
                $enviado = 'true';
                $this->view->enviado = $enviado;
            } else {
                $enviado = 'false';
                $this->view->enviado = $enviado;
            }

            //VERIFICA SE A PROPOSTA ESTA COM O MINC
            $Movimentacao = new Movimentacao();
            $rsStatusAtual = $Movimentacao->buscarStatusAtualProposta($get->idPreProjeto);
            if (count($rsStatusAtual) > 0) {
                $this->view->movimentacaoAtual = isset($rsStatusAtual->Movimentacao) ? $rsStatusAtual->Movimentacao : '';
            } else {
                $this->view->movimentacaoAtual = null;
            }
            
            //VERIFICA SE A PROPOSTA FOI ENVIADA AO MINC ALGUMA VEZ
            $arrbusca = array();
            $arrbusca['idProjeto = ?'] 		= $get->idPreProjeto;
            $arrbusca['Movimentacao = ?'] 	= '96';
            $rsHistMov 						= $Movimentacao->buscar($arrbusca);
            $this->view->blnJaEnviadaAoMinc = $rsHistMov->count();
        }
        //*****************
        //FIM DA VALIDAÇ?O
        //*****************
        
    }

// fecha método init()

    public function indexAction() {
        
    }
    public function editalAction() {

        $get = Zend_Registry::get('get');
        $idpreprojeto = $get->idpreprojeto;
        $array = array();
        //$array['idUsuario'] = 31041;
        $array['idUsuario'] = $this->idUsuario;
        $tbedital = ManterpropostaeditalDAO::buscaredital($array);
        $array = array();
        $i = 0;
        /**
         * @todo: escolher melhor forma para carregamento de dados.
         */
        foreach ($tbedital as $valores) {
            $array[$valores->Proponente . ' : ' . $valores->CNPJCPF]['idPreProjeto'] = $valores->idPreProjeto;
            $array[$valores->Proponente . ' : ' . $valores->CNPJCPF]['CNPJCPF'] = $valores->CNPJCPF;
            $array[$valores->Proponente . ' : ' . $valores->CNPJCPF]['idAgente'] = $valores->idAgente;
            $array[$valores->Proponente . ' : ' . $valores->CNPJCPF][$i]['idPreProjeto'] = $valores->idPreProjeto;
            $array[$valores->Proponente . ' : ' . $valores->CNPJCPF][$i]['NomeProjeto'] = $valores->NomeProjeto;
            $array[$valores->Proponente . ' : ' . $valores->CNPJCPF][$i]['Mecanismo'] = $valores->Mecanismo;
            $array[$valores->Proponente . ' : ' . $valores->CNPJCPF][$i]['MecanismoDesc'] = $valores->MecanismoDesc;
            $array[$valores->Proponente . ' : ' . $valores->CNPJCPF][$i]['stTipoDemanda'] = $valores->stTipoDemanda;
            $array[$valores->Proponente . ' : ' . $valores->CNPJCPF][$i]['idEdital'] = $valores->idEdital;
            $array[$valores->Proponente . ' : ' . $valores->CNPJCPF][$i]['Edital'] = $valores->Edital;
            $array[$valores->Proponente . ' : ' . $valores->CNPJCPF][$i]['CNPJCPF'] = $valores->CNPJCPF;
            $i++;
        }
        $this->view->edital = $array;
    }

    public function dadospropostaeditalAction() {

        if (isset($_REQUEST['idPreProjeto']) && !empty($_REQUEST['idPreProjeto'])) {
            
            /* =============================================================================== */ 
            /* ==== VERIFICA PERMISSAO DE ACESSO DO PROPONENTE A PROPOSTA OU AO PROJETO ====== */
            /* =============================================================================== */
            $this->verificarPermissaoAcesso(true, false, false);

            $_SESSION['idPreProjeto'] = $_REQUEST['idPreProjeto'];

            $where = array();
            $where['p.stTipoDemanda NOT LIKE ?'] = 'NA';
            $where['NOT EXISTS (?)'] = new Zend_Db_Expr('select * from SAC.dbo.projetos pr where p.idPreProjeto = pr.idProjeto');
            $where['p.stestado = ?'] = 1;
            $where['fd.idClassificaDocumento not in (?)'] = array(23, 24, 25);
            $where['p.idPreProjeto = ?'] = $_REQUEST['idPreProjeto'];

            $tblPreProjeto = new PreProjeto();
            $dados = $tblPreProjeto->buscarPropostaEditalCompleto($where);

            //$dados  = ManterpropostaeditalDAO::exibirDadosPropostaEditalCompleto($_REQUEST['idPreProjeto']);

            $get = Zend_Registry::get("get");
            $this->view->documentosPendentes = AnalisarPropostaDAO::buscarDocumentoPendente($get->idPreProjeto);

            if (!empty($this->view->documentosPendentes)) {
                $verificarmenu = 1;
                $this->view->verificarmenu = $verificarmenu;
            } else {
                $verificarmenu = 0;
                $this->view->verificarmenu = $verificarmenu;
            }

            $mov = new Movimentacao();
            $movBuscar = $mov->buscar(array('idProjeto = ?' => $get->idPreProjeto), array('idMovimentacao desc'), 1, 0)->current();

            if (isset($movBuscar->Movimentacao) && $movBuscar->Movimentacao != 95) {
                $enviado = 'true';
                $this->view->enviado = $enviado;
            } else {
                $enviado = 'false';
                $this->view->enviado = $enviado;
            }


            if ($dados) {
                $this->view->idPreProjeto 		= $dados[0]->idPreProjeto;
                $this->view->nomeAgente 		= $dados[0]->nomeAgente;
                $this->view->nomeProjeto 		= $dados[0]->NomeProjeto;
                $this->view->resumoProjeto 		= $dados[0]->ResumoDoProjeto;
                $this->view->idAgente 			= $dados[0]->idAgente;
                $this->view->AgenciaBancaria 	= $dados[0]->AgenciaBancaria;
                $this->view->DtInicioDeExecucao = $dados[0]->DtInicioDeExecucao;
                $this->view->DtFinalDeExecucao 	= $dados[0]->DtFinalDeExecucao;
                $this->view->idEdital 			= $dados[0]->idEdital;
            } else {
                $this->view->mensagem = 'Pré Projeto não encontrado.';
                $this->view->tpmensagem = "msgERROR";
            }
            
            
        	$ag = new Agentes();
            $verificarvinculo = $ag->buscarAgenteVinculoProponente(array('vprp.idPreProjeto = ?' => $dados[0]->idPreProjeto, 
            															 'vprp.siVinculoProposta = ?' => 2));
            if(count($verificarvinculo) > 0){
                if($verificarvinculo[0]->siVinculo != 2) {
                    $this->view->siVinculoProponente = true;
                } else {
                    $this->view->siVinculoProponente = false;
                }
            }
            
            
            
            
           $tblVinculo = new TbVinculo();

	        $arrBuscaP['VP.idPreProjeto = ?'] 			= $dados[0]->idPreProjeto;
	        $arrBuscaP['VI.idUsuarioResponsavel = ?'] 	= $this->idResponsavel;
	        $rsVinculoP = $tblVinculo->buscarVinculoProponenteResponsavel($arrBuscaP);

	        $arrBuscaN['VI.siVinculo IN (0,2)'] 		= '';
	        $arrBuscaN['VI.idUsuarioResponsavel = ?'] 	= $this->idResponsavel;
	        $rsVinculoN = $tblVinculo->buscarVinculoProponenteResponsavel($arrBuscaN);
            
            $this->view->listaProponentes = $rsVinculoN;
            $this->view->dadosVinculo = $rsVinculoP;
            /************************************/
            
            
        } else {

            $dados = array();
            if (isset($_REQUEST['idAgente']) && !empty($_REQUEST['idAgente'])) {
                $dados = ManterpropostaeditalDAO::buscarNomeAgente($_REQUEST);
            }
            if ($dados) {
                $this->view->nomeAgente = $dados[0]->Descricao;
                $this->view->idAgente = $_REQUEST['idAgente'];
                $this->view->idEdital = $_REQUEST['idEdital'];
            } else {
                $this->view->mensagem = "Agente n&atilde;o encontrado";
                $this->view->tpmensagem = "msgERROR";
                //$this->view->tpmensagem = msgCONFIRM;
                //$this->view->tpmensagem = msgALERT;
            }
        }
        if (isset($_REQUEST['mensagem'])) {
            $this->view->mensagem = $_REQUEST['mensagem'];
            $this->view->tpmensagem = $_REQUEST['tpmensagem'];
        }
        $tbeditalpreprojeto = ManterpropostaeditalDAO::buscarpreprojeto();
        $this->view->editalpreprojeto = $tbeditalpreprojeto;
    }

    public function inserirdadospropostaeditalAction() {
        $array = array('mensagem' => '');

        if ($_REQUEST['idAgente'] && $_REQUEST['idEdital'] && $_REQUEST['nomeProjeto'] && $_REQUEST['resumoProjeto']) {
            try {
                if (strlen(trim($_REQUEST['resumoProjeto'])) > 1000) {

                    $array['mensagem'] = 'Quantidade de caracteres maior que o permitido. Limite: 1000 caracteres.';
                    $array['tpmensagem'] = 'msgERROR';
                    $this->_redirect('/manterpropostaedital/dadospropostaedital?idPreProjeto=' . $_REQUEST['idPreProjeto'] . '&idAgente=' . $_REQUEST['idAgente'] . '&idEdital=' . $_REQUEST['idEdital'] . '&mensagem=' . $array['mensagem'] . '&tpmensagem=' . $array['tpmensagem']);
                }

                $array = array();
                $array['Mecanismo'] 			= 2; // Adicionado, Edital deve ser Mecanismo 2
                $array['idAgente'] 				= $_REQUEST['idAgente'];
                $array['idEdital'] 				= $_REQUEST['idEdital'];
                $array['AgenciaBancaria'] 		= $_REQUEST['agencia'];
                $datainicio 					= explode('/',$_REQUEST['dtIniExec']);
                $array['DtInicioDeExecucao'] 	= $datainicio['2'].'-'.$datainicio['1'].'-'.$datainicio['0'];
                $datafim 						= explode('/',$_REQUEST['dtFimExec']);
                $array['DtFinalDeExecucao'] 	= $datafim['2'].'-'.$datafim['1'].'-'.$datafim['0'];
                $array['NomeProjeto'] 			= TratarString::escapeString($_REQUEST['nomeProjeto']);
                $array['stTipoDemanda'] 		= 'ED';
                $array['ResumoDoProjeto'] 		= trim(TratarString::escapeString($_REQUEST['resumoProjeto']));
                
                // Salvar o responsável
                $array['idUsuario'] 			= $this->idResponsavel;

                if (isset($_REQUEST['idPreProjeto']) ) {
                    $array['idPreProjeto'] 	= $_REQUEST['idPreProjeto'];
                    $dados = ManterpropostaeditalDAO::alterarDadosProposta($array);
                    $array['mensagem'] 		= 'Alteração realizada com sucesso!';
                    $array['tpmensagem'] 	= 'msgCONFIRM';
                    $array['mensagem'] 		= htmlspecialchars($array['mensagem']);
                    parent::message("Altera&ccedil;&atilde;o realizada com sucesso!", "/manterpropostaedital/dadospropostaedital?idPreProjeto=" . $array['idPreProjeto'], "CONFIRM");
                } else {
                    $dados = ManterpropostaeditalDAO::inserirProposta($array);
                    $array['idPreProjeto'] = $dados;
                    $tblMovimentacao = new Movimentacao();
                    $dados = array(	"idProjeto" 		=> $array['idPreProjeto'],
			                        "Movimentacao" 		=> "95", //Status = Proposta com Proponente
			                        "DtMovimentacao" 	=> date("Y/m/d H:i:s"),
			                        "stEstado" 			=> "0",
			                        "Usuario" 			=> $this->idUsuario); //$this->view->usuario->usu_codigo;

                    $tblMovimentacao->salvar($dados);
                    
                    
                    /*******************************************************************************************/
	                // Salvando os dados na TbVinculoProposta
	                $tbVinculoDAO 		  = new TbVinculo();
	                $tbVinculoPropostaDAO = new tbVinculoPropostaResponsavelProjeto();
	                
	                $whereVinculo['idUsuarioResponsavel = ?'] = $this->idResponsavel;
	                $whereVinculo['idAgenteProponente   = ?'] = $_REQUEST['idAgente'];
	                $vinculo = $tbVinculoDAO->buscar($whereVinculo);
	                
	                if(count($vinculo) == 0)
	                {
						$dadosV = array( 'idAgenteProponente'		=> $_REQUEST['idAgente'],
	    				   				'dtVinculo' 				=> new Zend_Db_Expr("GETDATE()"),
	    				   				'siVinculo' 				=> 2,
	    				   				'idUsuarioResponsavel' 		=> $this->idResponsavel
	    				);
	
	    				$insere = $tbVinculoDAO->inserir($dadosV);
	                }
	
	                
	                $vinculo2 = $tbVinculoDAO->buscar($whereVinculo);
	                if(count($vinculo2) > 0)
	                {
		                $novosDadosV = array('idVinculo' 			=> $idVinculo = $vinculo2[0]->idVinculo, 
		    								 'idPreProjeto' 		=> $array['idPreProjeto'], 
		    								 'siVinculoProposta' 	=> 2
		                );
		    		
		    			$insere = $tbVinculoPropostaDAO->inserir($novosDadosV, false);
	                }
	                /*******************************************************************************************/
                    
                    
                    
                    
                    $array['mensagem'] = 'Cadastro realizado com sucesso!';
                    $array['tpmensagem'] = 'msgCONFIRM';
                }
            } catch (Zend_Exception $ex) {
                parent::message("Não foi possível realizar a operação!", "/manterpropostaincentivofiscal/listar-propostas", "ERROR");
            }
        } else {
            $array['mensagem'] 	 = 'Dados incorretos.';
            $array['tpmensagem'] = 'Erro!';
        }
        $this->_redirect('/manterpropostaedital/dadospropostaedital?idPreProjeto=' . $array['idPreProjeto'] . '&mensagem=' . $array['mensagem'] . '&tpmensagem=' . $array['tpmensagem']);
    }

    public function localderealizacaoeditalAction() {
        
    }

    public function responderquestionarioeditalAction() {
        if (isset($_REQUEST['idPreProjeto'])) {
            
            /* =============================================================================== */ 
            /* ==== VERIFICA PERMISSAO DE ACESSO DO PROPONENTE A PROPOSTA OU AO PROJETO ====== */
            /* =============================================================================== */
            $this->verificarPermissaoAcesso(true,false,false);
            
//            $_SESSION['idPreProjeto'] = $_REQUEST['idPreProjeto'];
//            $dados                    = ManterpropostaeditalDAO::exibirDadosPropostaEditalCompleto($_SESSION);

            $where = array();
            $where['p.stTipoDemanda NOT LIKE ?'] = 'NA';
            $where['NOT EXISTS (?)'] = new Zend_Db_Expr('select * from SAC.dbo.projetos pr where p.idPreProjeto = pr.idProjeto');
            $where['p.stestado = ?'] = 1;
            $where['fd.idClassificaDocumento not in (?)'] = array(23, 24, 25);
            $where['p.idPreProjeto = ?'] = $_REQUEST['idPreProjeto'];

            $tblPreProjeto = new PreProjeto();
            $dados = $tblPreProjeto->buscarPropostaEditalCompleto($where);
        } else {
            parent::message("Projeto não informado!", "/manterpropostaincentivofiscal/listar-propostas", "ERROR");
        }
        if ($dados) {
            $this->view->idPreProjeto = $dados[0]->idPreProjeto;
            $this->view->nomeAgente = $dados[0]->nomeAgente;
            $this->view->nomeProjeto = $dados[0]->NomeProjeto;
            $this->view->resumoProjeto = $dados[0]->ResumoDoProjeto;
            $this->view->idAgente = $dados[0]->idAgente;
            $this->view->idEdital = $dados[0]->idEdital;
            $this->view->dados = $dados[0];
        } else {
            parent::message("Pré Projeto não encontrado!", "/manterpropostaincentivofiscal/listar-propostas", "ERROR");
        }
        $this->view->idUsuario = $this->idUsuario;
        //xd($dados);
    }

    public function enviararquivoeditalAction() {
        
        ini_set('memory_limit', '-1');
        
        /* =============================================================================== */ 
        /* ==== VERIFICA PERMISSAO DE ACESSO DO PROPONENTE A PROPOSTA OU AO PROJETO ====== */
        /* =============================================================================== */
        $this->verificarPermissaoAcesso(true, false, false);
        
        $get = Zend_Registry::get('get');
        
        $tbl = new tbDocumentosPreProjeto();
        $rs = $tbl->buscarDocumentos(array("idProjeto = ?" => $get->idPreProjeto));
        $this->view->arquivosProposta = $rs;

        $tbPreProjeto = new PreProjeto();
        $dadosProjeto = $tbPreProjeto->buscarAgentePreProjeto(array('idPreProjeto = ?'=>$get->idPreProjeto))->current();

        $tbA = new tbDocumentosAgentes();
        $rsA = $tbA->buscarDadosDocumentos(array("idAgente = ?" => $dadosProjeto->idAgente));
        $this->view->arquivosProponente = $rsA;
        
    }

    public function listararquivosAction() {

        $opcao = !empty($_GET['classificao']) ? $_GET['classificao'] : -1;
        $where = array(
            'Opcao = ?' => $opcao
        );

        $tblDocumentos = new DocumentosExigidos();
        $Documento = $tblDocumentos->buscar($where, 'Descricao desc');
        foreach ($Documento as $doc) {
            echo utf8_encode('<option value="' . $doc->Codigo . '" >' . $doc->Descricao . '</option>');
        }
        exit(0);
    }

    public function incluirAnexoAction() {
// pega as informações do arquivo
        $idUltimoArquivo = null;
        $post = Zend_Registry::get('post');
        if (is_file($_FILES['arquivo']['tmp_name'])) {
            $arquivoNome = $_FILES['arquivo']['name']; // nome
            $arquivoTemp = $_FILES['arquivo']['tmp_name']; // nome temporário
            $arquivoTipo = $_FILES['arquivo']['type']; // tipo
            $arquivoTamanho = $_FILES['arquivo']['size']; // tamanho
            if (!empty($arquivoNome) && !empty($arquivoTemp)) {
                $arquivoExtensao = Upload::getExtensao($arquivoNome); // extensão
                $arquivoBinario = Upload::setBinario($arquivoTemp); // binário
                $arquivoHash = Upload::setHash($arquivoTemp); // hash
            }
            if ($arquivoTamanho > 10485760) // tamanho do arquivo: 10MB
            {
                parent::message("O arquivo não pode ser maior do que 10MB!", "manterpropostaedital/enviararquivoedital?idPreProjeto=" . $post->idPreProjeto . "&edital=" . $post->edital, "ALERT");
            }
            // cadastra dados do arquivo
            // cadastra dados do arquivo

            $tblTbDocumentoPreProjeto = new tbDocumentosPreProjeto();
            $tblTbDocumentoAgentes = new tbDocumentosAgentes();
            try {
                //Verifica se tipo de documento ja esta cadastrado
                $where = array();
                if($post->tipoDocumento == 1){
                    $tbPreProjeto = new PreProjeto();
                    $dadosProjeto = $tbPreProjeto->buscarAgentePreProjeto(array('idPreProjeto = ?'=>$post->idPreProjeto))->current();
                    
                    $where['idAgente = ?'] = $dadosProjeto->idAgente;
                    $where['CodigoDocumento = ?'] = $post->documento;
                } else {
                    $where['idProjeto = ?'] = $post->idPreProjeto;
                    $where['CodigoDocumento = ?'] = $post->documento;
                }

                if($post->tipoDocumento == 1){
                    
                    if($tblTbDocumentoAgentes->buscar($where)->count() > 0){
                        parent::message("Tipo de documento já cadastrado!", "manterpropostaedital/enviararquivoedital?idPreProjeto=" . $post->idPreProjeto, "ALERT");
                    }
                    
                    $dadosArquivo = array(
                        'CodigoDocumento' => $post->documento,
                        'idAgente' => $dadosProjeto->idAgente,
                        'Data' => new Zend_Db_Expr('GETDATE()'),
                        'imDocumento' => new Zend_Db_Expr("CONVERT(varbinary(MAX), {$arquivoBinario})"),
                        'NoArquivo' => $arquivoNome,
                        'TaArquivo' => $arquivoTamanho
                    );
                    $idUltimoArquivo = $tblTbDocumentoAgentes->inserir($dadosArquivo);
                } else {
                    
                    if($tblTbDocumentoPreProjeto->buscar($where)->count() > 0){
                        parent::message("Tipo de documento já cadastrado!", "manterpropostaedital/enviararquivoedital?idPreProjeto=" . $post->idPreProjeto, "ALERT");
                    }
                    
                    $dadosArquivo = array(
                        'CodigoDocumento' => $post->documento,
                        'idProjeto' => $post->idPreProjeto,
                        'Data' => new Zend_Db_Expr('GETDATE()'),
                        'imDocumento' => new Zend_Db_Expr("CONVERT(varbinary(MAX), {$arquivoBinario})"),
                        'NoArquivo' => $arquivoNome,
                        'TaArquivo' => $arquivoTamanho,
                        'dsDocumento' => $post->observacao
                    );
                    $idUltimoArquivo = $tblTbDocumentoPreProjeto->inserir($dadosArquivo);
                }
                //REMOVER AS PENDENCIAS DE DOCUMENTO
                //$tblDocAnexados = new tbDocumentosPreProjeto();
                //$rsDocsAnexados = $tblDocAnexados->buscar(array("idProjeto = ?"=>$post->idPreProjeto));
                $tblDocumentosPendentesProjeto = new DocumentosProjeto();
                $tblDocumentosPendentesProponente = new DocumentosProponente();

                $tblDocumentosPendentesProjeto->delete("idProjeto = {$post->idPreProjeto} AND CodigoDocumento = {$post->documento}");
                $tblDocumentosPendentesProponente->delete("idProjeto = {$post->idPreProjeto} AND CodigoDocumento = {$post->documento}");

                //xd($rsDocsAnexados);
//                foreach($rsDocsAnexados as $anexo){
//                    $tblDocumentosPendentesProjeto->delete("idProjeto = {$post->idPreProjeto} AND CodigoDocumento = {$anexo->CodigoDocumento}");
//                    $tblDocumentosPendentesProponente->delete("idProjeto = {$post->idPreProjeto} AND CodigoDocumento = {$anexo->CodigoDocumento}");
//                }
            } catch (Zend_Exception $e) {
                parent::message("Falha ao anexar arquivo!<br>{$e->getMessage()}", "manterpropostaedital/enviararquivoedital?idPreProjeto=" . $post->idPreProjeto . "&edital=" . $post->edital, "ERROR");
            } catch (Exception $e){
                parent::message("Tipo de documento já cadastrado!", "manterpropostaedital/enviararquivoedital?idPreProjeto=" . $post->idPreProjeto . "&edital=" . $post->edital, "ALERT");
            }

            if ($idUltimoArquivo) {
                parent::message("Arquivo anexado com sucesso!", "manterpropostaedital/enviararquivoedital?idPreProjeto=" . $post->idPreProjeto . "&edital=" . $post->edital, "CONFIRM");
            } else {
                parent::message("Falha ao anexar arquivo!", "manterpropostaedital/enviararquivoedital?idPreProjeto=" . $post->idPreProjeto . "&edital=" . $post->edital, "ERROR");
            }
        }else{
            parent::message("Falha ao anexar arquivo! O tamanho máximo permitido é de 10MB.", "manterpropostaincentivofiscal/listarproposta?idPreProjeto=" . $post->idPreProjeto . "&edital=" . $post->edital, "ERROR");
        }
    }

    public function incluirarquivoAction() {
        $post = Zend_Registry::get('post');

        if ($_FILES['arquivo']['tmp_name']) {

            $idArquivo = $this->anexararquivo();
            $dados = array(
                'idArquivo' => $idArquivo,
                'idTipoDocumento' => 0, //$post->tipodocumento,
                'dsDocumento' => $post->observacao
            );
            //xd($dados);
            $tabela = new tbDocumento();
            $idDocumento = $tabela->inserir($dados);

            if ($idDocumento) {
                $idDocumento = $tabela->ultimodocumento(array('idArquivo = ? ' => $idArquivo));
                $idDocumento = $idDocumento->idDocumento;

                $dados = array(
                    'idTipoDocumento' => 0, //$post->tipodocumento,
                    'idDocumento' => $idDocumento,
                    'idProposta' => $post->idPreProjeto,
                    'stAtivoDocumentoProposta' => 0
                );
                //xd($dados);
                $DocumentoProposta = new tbDocumentoProposta();
                $DocumentoProposta->inserir($dados);
            } else {
                parent::message("Falha ao anexar", "manterpropostaedital/enviararquivoedital?idPreProjeto=" . $post->idPreProjeto . "&edital=" . $post->edital, "ERROR");
            }
            parent::message("Documento anexado com sucesso!", "manterpropostaedital/enviararquivoedital?idPreProjeto=" . $post->idPreProjeto . "&edital=" . $post->edital, "CONFIRM");
        } else {
            parent::message("Documento n&atilde;o informado", "manterpropostaedital/enviararquivoedital?idPreProjeto=" . $post->idPreProjeto . "&edital=" . $post->edital, "ERROR");
        }
    }

    private function anexararquivo() {
        // pega as informações do arquivo
        $idUltimoArquivo = 'null';
        $post = Zend_Registry::get('post');
        
        if (is_file($_FILES['arquivo']['tmp_name'])) {
            $arquivoNome = $_FILES['arquivo']['name']; // nome
            $arquivoTemp = $_FILES['arquivo']['tmp_name']; // nome temporário
            $arquivoTipo = $_FILES['arquivo']['type']; // tipo
            $arquivoTamanho = $_FILES['arquivo']['size']; // tamanho
            if (!empty($arquivoNome) && !empty($arquivoTemp)) {
                $arquivoExtensao = Upload::getExtensao($arquivoNome); // extensão
                $arquivoBinario = Upload::setBinario($arquivoTemp); // binário
                $arquivoHash = Upload::setHash($arquivoTemp); // hash
            }
            if ($arquivoTamanho > 10485760) // tamanho do arquivo: 10MB
            {
                parent::message("O arquivo não pode ser maior do que 10MB!", "manterpropostaedital/enviararquivoedital?idPreProjeto=" . $post->idPreProjeto . "&edital=" . $post->edital, "ALERT");
            }
            // cadastra dados do arquivo
            $dadosArquivo = array(
                'nmArquivo' => $arquivoNome,
                'sgExtensao' => $arquivoExtensao,
                'dsTipoPadronizado' => $arquivoTipo,
                'nrTamanho' => $arquivoTamanho,
                'dtEnvio' => new Zend_Db_Expr('GETDATE()'),
                'dsHash' => $arquivoHash,
                'stAtivo' => 'I');
            $cadastrarArquivo = ArquivoDAO::cadastrar($dadosArquivo);

            // pega o id do último arquivo cadastrado
            $idUltimoArquivo = ArquivoDAO::buscarIdArquivo();
            $idUltimoArquivo = (int) $idUltimoArquivo[0]->id;

            // cadastra o binário do arquivo
            $dadosBinario = array(
                'idArquivo' => $idUltimoArquivo,
                'biArquivo' => $arquivoBinario);
            $cadastrarBinario = ArquivoImagemDAO::cadastrar($dadosBinario);
        }
        return $idUltimoArquivo;
    }

    public function enviarpropostaaominceditalAction() {
        
    }

    public function manteragentesAction() {
        
    }

    public function dadosproponenteenderecoeditalAction() {

        $get = Zend_Registry::get('get');
        $idpreprojeto = $get->idpreprojeto;
        $cpf = $get->cpf;
        $tbedital = ManterpropostaeditalDAO::buscaredital();
        $this->view->edital = $tbedital;
        $tbeditalpreprojeto = ManterpropostaeditalDAO::buscarpreprojeto();
        $this->view->editalpreprojeto = $tbeditalpreprojeto;
        $tbeendereco = ManterpropostaeditalDAO::buscaendereco($cpf);
        $this->view->endereco = $tbeendereco;
    }

    public function documentospendenteseditalAction() {
        
        /* =============================================================================== */ 
        /* ==== VERIFICA PERMISSAO DE ACESSO DO PROPONENTE A PROPOSTA OU AO PROJETO ====== */
        /* =============================================================================== */
        $this->verificarPermissaoAcesso(true, false, false);
        
        $get = Zend_Registry::get("get");
        $this->view->documentosPendentes = AnalisarPropostaDAO::buscarDocumentoPendente($get->idPreProjeto);
    }

    public function msnenviadasaominceditalAction() {
        
    }

    public function acompanhesuapropostaeditalAction() {
        
    }

    public function alterardtnascimentoeditalAction() {
        
    }

    public function dadosenderecoeditalAction() {
        
    }

    public function novodadosenderecoeditalAction() {
        
    }

    public function exluirpropostaAction() {

        /* =============================================================================== */ 
        /* ==== VERIFICA PERMISSAO DE ACESSO DO PROPONENTE A PROPOSTA OU AO PROJETO ====== */
        /* =============================================================================== */
        $this->verificarPermissaoAcesso(true, false, false);
        
        $get = Zend_Registry::get("get");
        $idPreProjeto = $get->idPreProjeto;

        //BUSCANDO REGISTRO A SER ALTERADO
        $tblPreProjeto = new PreProjeto();
        $rsPreProjeto = $tblPreProjeto->find($idPreProjeto)->current();
        //altera Estado da proposta
        $rsPreProjeto->stEstado = 0;

        if ($rsPreProjeto->save()) {
            parent::message("Exclus&atilde;o realizada com sucesso!", "/manterpropostaincentivofiscal/listar-propostas", "CONFIRM");
        } else {
            parent::message("N&atilde;o foi possível realizar a opera&ccedil;&atilde;o!", "/manterpropostaincentivofiscal/listar-propostas", "ERROR");
        }
    }

    public function editallocalizarAction() {
        $this->view->idAgente = $_REQUEST['idAgente'];
    }

    public function editalconfirmarAction() {

        if ($_REQUEST['idAgente']) {
            if (isset($_POST['dtEditalInicial']) || isset($_POST['nrEdital']) || isset($_POST['dtEditalFinal']) || isset($_POST['dtInicoInscricaoInicial']) || isset($_POST['dtInicoInscricaoFinal']) || isset($_POST['dtFinalInscricaoInicial']) || isset($_POST['dtFinalInscricaoFinal']) || isset($_POST['Classificacao']) || isset($_POST['nmEdital'])) {
                $dados = ManterpropostaeditalDAO::buscaEditalConfirmarAvancada($_POST);
            } else {
                $dados = ManterpropostaeditalDAO::buscaEditalConfirmar();
            }
            $array = array();
            $i = 0;
            foreach ($dados as $dado) {
                $array[$dado->TipoFundo][$i]['idEdital'] = $dado->idEditalTb;
                $array[$dado->TipoFundo][$i]['NrEditalTb'] = $dado->NrEditalTb;
                $array[$dado->TipoFundo][$i]['Ano'] = $dado->Ano;
                $array[$dado->TipoFundo][$i]['Unidade'] = $dado->Unidade;
                $array[$dado->TipoFundo][$i]['nmDocumento'] = $dado->nmDocumento;
                $array[$dado->TipoFundo][$i]['DtEditalTb'] = $dado->DtEditalTb;
                $array[$dado->TipoFundo][$i]['dtIniFase'] = $dado->dtIniFase;
                $array[$dado->TipoFundo][$i]['Classificacao'] = $dado->Classificacao;
                $array[$dado->TipoFundo][$i]['dtFimFase'] = $dado->dtFimFase;
                $array[$dado->TipoFundo][$i]['Objeto'] = $dado->Objeto;
                $array[$dado->TipoFundo][$i]['idAgente'] = $_REQUEST['idAgente'];
                $i++;
            }
            $this->view->dados = $array;
            $this->view->idAgente = $_REQUEST['idAgente'];
        } else {
            $this->view->mensagem = "CPF não encontrado.";
        }
    }

    public function editalconfirmarlocalizarAction() {
        if ($_REQUEST['idEdital']) {
            $this->view->dado = ManterpropostaeditalDAO::buscaEditalConfirmarLocalizar(array('idEdital' => $_REQUEST['idEdital']));
        }
        if (!$this->view->dado || !$_REQUEST['idEdital']) {
            $this->view->message = 'Edital não encontrado.';
        } else {
            $this->view->dado = $this->view->dado[0];
        }
    }

    public function editalnovoAction() {

        $post = Zend_Registry::get('post');
        $idPreProjeto = $post->idPreProjeto;
        $this->view->idAgente = $post->idAgente;
        $arrDados = array();
        //$arrDados['idUsuario'] = 31041;
        $arrDados['idUsuario'] = $this->idUsuario;
        $tbedital = ManterpropostaeditalDAO::buscaredital($arrDados);
        $this->view->edital = $tbedital;
        $tbeditalpreprojeto = ManterpropostaeditalDAO::buscarpreprojeto();
        $this->view->editalpreprojeto = $tbeditalpreprojeto;
    }

    public function editalresumoAction() {
        $array = array();
        $this->view->dados = ManterpropostaeditalDAO::listarEditalResumo($array);
    }

    public function gerarpdfAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();


        $output = '
			<style>
				th{
                                background:#ABDA5D;
                                color:#3A7300;
                                text-transform:uppercase;
                                font-size:14px;
        			font-weight: bold;
        			font-family: sans-serif;
        			height: 16px;
        			line-height: 16px;
        		}
        		td{
        			color:#000;
        			font-size:14px;
        			font-family: sans-serif;
        			height: 14px;
        			line-height: 14px;
        		}
        		.blue{
        			color: blue;
        		}
        		.red{
        			color: red;
        		}
        		.orange{
        			color: orange;
        		}
        		.green{
        			color: green;
        		}

        		.direita{
        			text-align: right;
        		}

        		.centro{
        			text-align: center;
        		}

			</style>';



        $output .= $_POST['html'];

        $patterns = array();
        $patterns[] = '/<table.*?>/is';
        $patterns[] = '/<thead>/is';
        $patterns[] = '/<\/thead>/is';
        $patterns[] = '/<tbody>/is';
        $patterns[] = '/<\/tbody>/is';
        $patterns[] = '/<col.*?>/is';
        $patterns[] = '/<a.*?>/is';

        $replaces = array();
        $replaces[] = '<table cellpadding="0" cellspacing="1" border="1" width="97%" align="center">';
        $replaces[] = '';
        $replaces[] = '';
        $replaces[] = '';
        $replaces[] = '';
        $replaces[] = '';
        $replaces[] = '';

        $output = preg_replace($patterns, $replaces, $output);

        $pdf = new PDF($output, 'pdf');
        $pdf->gerarRelatorio('h');
        //$this->view->dados = ManterpropostaeditalDAO::listarEditalResumo(array());
    }

    public function enviarPropostaAoMincAction() {
        
        /* =============================================================================== */ 
        /* ==== VERIFICA PERMISSAO DE ACESSO DO PROPONENTE A PROPOSTA OU AO PROJETO ====== */
        /* =============================================================================== */
        $this->verificarPermissaoAcesso(true, false, false);
        
        //recupera parametros
        $get = Zend_Registry::get('get');
        $idPreProjeto = $get->idPreProjeto;

        $erro = "";
        $msg = "";

        if (!empty($idPreProjeto)) {
            $arrResultado = $this->validarEnvioPropostaAoMinc($idPreProjeto);
            if ($arrResultado['erro'] !== false) {
                $arrResultado['erro'] = 1;
            } elseif ($arrResultado['erro'] !== true) {
                $arrResultado['erro'] = 0;
            }
            //xd($arrResultado);
            //METODO QUE MONTA TELA DO USUARIO ENVIANDO TODOS OS PARAMENTROS NECESSARIO DENTRO DO ARRAY
            $this->montaTela("manterpropostaedital/enviarproposta.phtml", array("acao" => $this->_urlPadrao . "/manterpropostaedital/salvar",
                "erro" => $arrResultado['erro'],
                "resultado" => $arrResultado));
        } else {

            parent::message("Necessário informar o número da proposta.", "/manterpropostaedital/index", "ERROR");
        }
    }

    public function validarEnvioPropostaAoMinc($idPreProjeto) {

        //BUSCA DADOS DO PROJETO
        $arrBusca = array();
        $arrBusca['idPreProjeto = ?'] = $idPreProjeto;
        $tblPreProjeto = new PreProjeto();
        $rsPreProjeto = $tblPreProjeto->buscar($arrBusca)->current();

        /* ======== VERIFICA TODAS AS INFORMACOES NECESSARIAS AO ENVIO DA PROPOSTA ======= */

        $arrResultado = array();

        $arrResultado['erro'] = false;

        //valida mes de envio da proposta
        /* if(date("m") == "01" || date("m") == "12"){
          $arrResultado['prazoenvioproposta']['erro'] = true;
          $arrResultado['prazoenvioproposta']['msg'] = "Conforme Art 5º da Instrução Normativa nº 1, de 5 de outubro de 2010, nenhuma proposta poderá ser enviada ao MinC nos meses de DEZEMBRO e JANEIRO! - <font color='red'>PENDENTE</font>";
          return $arrResultado;
          } */

        /*         * ******* MOVIMENTACAO ******** */
        $tblMovimentacao = new Movimentacao();
        $rsMovimentacao = $tblMovimentacao->buscar(array("idProjeto = ?" => $idPreProjeto), array("idMovimentacao DESC"))->current();

        if (count($rsMovimentacao) > 0) {

            if ($rsMovimentacao->Movimentacao != 95) {
                $arrResultado['erro'] = true;
                $arrResultado['movimentacao']['erro'] = false;
                $arrResultado['movimentacao']['msg'] = "A Proposta Cultural encontra-se no Minist&eacute;rio da Cultura";
            }
        }

        /*         * ******* DADOS DO PROPONENTE ******** */
        $tblProponente = new Proponente();
        //$rsProponente = $tblProponente->buscar(array("a.idAgente = ?"=>$rsPreProjeto->idAgente))->current();

        $tblAgente = new Agentes();
        $rsProponente = $tblAgente->buscarAgenteNome(array("a.idAgente = ?" => $rsPreProjeto->idAgente))->current();

        $regularidade = Regularidade::buscarSalic($rsProponente->CNPJCPF);

        $dadosEndereco = EnderecoNacionalDAO::buscarEnderecoNacional($rsPreProjeto->idAgente);

        $dadosEmail = Email::buscar($rsPreProjeto->idAgente);

        $dadosDirigente = ManterAgentesDAO::buscarVinculados(null, null, null, null, $rsPreProjeto->idAgente);
        //$dadosDirigente = ManterAgentes::buscaDirigentes($rsProponente->CNPJCPF);

        $tblLocaisRealizacao = new Abrangencia();
        $dadosLocais = $tblLocaisRealizacao->buscar(array("a.idProjeto" => $idPreProjeto, "a.stAbrangencia" => 1));

        if (count($rsProponente) > 0) {
        	
        //VERIFICA SE O PROPONENTE ESTÁ VINCULADO
	        $vinculoProponente = new tbVinculoPropostaResponsavelProjeto();
	        $whereProp['VP.idPreProjeto = ?'] 		= $idPreProjeto;
	        $whereProp['VP.siVinculoProposta = ?'] 	= 2;
	        $rsVinculo = $vinculoProponente->buscarResponsaveisProponentes($whereProp);
	        
			if($rsVinculo[0]->siVinculo == 2){
				$arrResultado['erro'] = false;
		        $arrResultado['vinculoproponente']['erro'] = false;
		        $arrResultado['vinculoproponente']['msg'] = "Vinculo do Proponente REGULAR";
			} else {
				$arrResultado['erro'] = true;
		        $arrResultado['vinculoproponente']['erro'] = true;
	    	    $arrResultado['vinculoproponente']['msg'] = "Vinculo do Proponente IRREGULAR";
			}
			
            //REGULARIDADE DO PROPONENTE
            if (count($regularidade) > 0) {
                if ($regularidade[0]->Habilitado == "S") {
                    $arrResultado['regularidadeproponente']['erro'] = false;
                    $arrResultado['regularidadeproponente']['msg'] = "Proponente em situa&ccedil;&atilde;o REGULAR no Minist&eacute;rio da Cultura - <font color='green'>OK</font>";
                } else {
                    $arrResultado['erro'] = true;
                    $arrResultado['regularidadeproponente']['erro'] = true;
                    $arrResultado['regularidadeproponente']['msg'] = "Proponente em situa&ccedil;&atilde;o IRREGULAR no Minist&eacute;rio da Cultura - <font color='red'>PENDENTE</font>";
                }
            } else {
                $arrResultado['regularidadeproponente']['erro'] = false;
                $arrResultado['regularidadeproponente']['msg'] = "Proponente em situa&ccedil;&atilde;o REGULAR no Minist&eacute;rio da Cultura - <font color='green'>OK</font>";
            }

            //E-MAIL
            $blnEmail = false;
            if (count($dadosEmail) > 0) {
                foreach ($dadosEmail as $email) {
                    if ($email->Status == 1) {
                        $blnEmail = true;
                    }
                }
                if ($blnEmail === false) {
                    $arrResultado['erro'] = true;
                    $arrResultado['email']['erro'] = true;
                    $arrResultado['email']['msg'] = "E-mail do proponente inexistente - <font color='red'>PENDENTE</font>";
                } else {
                    $arrResultado['email']['erro'] = false;
                    $arrResultado['email']['msg'] = "E-mail do proponente - <font color='green'>OK</font>";
                }
            } else {
                $arrResultado['erro'] = true;
                $arrResultado['email']['erro'] = true;
                $arrResultado['email']['msg'] = "E-mail do proponente inexistente - <font color='red'>PENDENTE</font>";
            }

            //ENDERECO
            $blnEndCorrespondencia = false;
            if (count($dadosEndereco) > 0) {
                foreach ($dadosEndereco as $endereco) {
                    if ($endereco->Status == 1) {
                        $blnEndCorrespondencia = true;
                    }
                }
                if ($blnEndCorrespondencia === false) {
                    $arrResultado['erro'] = true;
                    $arrResultado['endereco']['erro'] = true;
                    $arrResultado['endereco']['msg'] = "Dados cadastrais do proponente inexistente ou n&atilde;o h&aacute; endere&ccedil;o para correspond&ecirc;ncia selecionado";
                } else {
                    $arrResultado['endereco']['erro'] = false;
                    $arrResultado['endereco']['msg'] = "Dados cadastrais do proponente - <font color='green'>OK</font>";
                }
            } else {
                $arrResultado['erro'] = true;
                $arrResultado['endereco']['erro'] = true;
                $arrResultado['endereco']['msg'] = "Dados cadastrais do proponente inexistente ou n&atilde;o h&aacute; endere&ccedil;o para correspond&ecirc;ncia selecionado";
            }

            //NATUREZA
            if ($rsProponente->TipoPessoa == 1) {
                $tblNatureza = new Natureza();
                $dadosNatureza = $tblNatureza->buscar(array("idAgente = ?" => $rsPreProjeto->idAgente));

                if (count($dadosNatureza) > 0) {
                    $arrResultado['dirigente']['erro'] = false;
                    $arrResultado['dirigente']['msg'] = "Natureza do proponente - <font color='green'>OK</font>";
                } else {
                    $arrResultado['erro'] = true;
                    $arrResultado['dirigente']['erro'] = true;
                    $arrResultado['dirigente']['msg'] = "Natureza do proponente - <font color='red'>PENDENTE</font>";
                }
            }

            //DIRIGENTE
            if ($rsProponente->TipoPessoa == 1) {

                if (count($dadosDirigente) > 0) {
                    $arrResultado['dirigente']['erro'] = false;
                    $arrResultado['dirigente']['msg'] = "Cadastro de Dirigente - <font color='green'>OK</font>";
                } else {
                    $arrResultado['erro'] = true;
                    $arrResultado['dirigente']['erro'] = true;
                    $arrResultado['dirigente']['msg'] = "Cadastro de Dirigente - <font color='red'>PENDENTE</font>";
                }
            }

            //LOCAIS DE RALIZACAO
            if (count($dadosLocais) > 0) {
                $arrResultado['locaisrealizacao']['erro'] = false;
                $arrResultado['locaisrealizacao']['msg'] = "Local de realiza&ccedil;&atilde;o da proposta - <font color='green'>OK</font>";
            } else {
                $arrResultado['erro'] = true;
                $arrResultado['locaisrealizacao']['erro'] = true;
                $arrResultado['locaisrealizacao']['msg'] = "O Local de realiza&ccedil;&atilde;o da proposta n&atilde;o foi preenchido - <font color='red'>PENDENTE</font>";
            }
        } else {
            $arrResultado['erro'] = true;
            $arrResultado['proponente']['erro'] = true;
            $arrResultado['proponente']['msg'] = "Dados cadastrais do proponente inexistente ou n&atilde;o h&aacute; endere&ccedil;o para correspond&ecirc;ncia selecionado";
        }

        return $arrResultado;
    }

    public function confirmarEnvioPropostaAoMincAction() {
        
        /* =============================================================================== */ 
        /* ==== VERIFICA PERMISSAO DE ACESSO DO PROPONENTE A PROPOSTA OU AO PROJETO ====== */
        /* =============================================================================== */
        $this->verificarPermissaoAcesso(true, false, false);
        
        //recupera parametros
        $get = Zend_Registry::get('get');
        $idPreProjeto = $get->idPreProjeto;
        $valida = $get->valida;
        $idTecnico = null;
        $rsTecnicos = array();
        $idOrgaoSuperior = null;

        if (isset($_REQUEST['edital'])) {
            $edital = "&edital=s";
        } else {
            $edital = "";
        }

        if (!empty($idPreProjeto) && $valida == "s") {
            $tblPreProjeto = new PreProjeto();
            $tblAvaliacao = new AnalisarPropostaDAO();

            //recupera dados do projeto
            $rsPreProjeto = $tblPreProjeto->find($idPreProjeto)->current();

            // Recuperando edital
            $tblEdital = new Edital();
            $rsEdital = $tblEdital->buscar(array("idEdital = ?" => $rsPreProjeto->idEdital))->current();
            $idOrgaoSuperior = $rsEdital->idOrgao;
            //verifica se a proposta ja foi recebida por um tecnico
            $avaliacao = $tblAvaliacao->verificarAvaliacao($idPreProjeto);

            //SE A PROPOSTA JA FOI AVALIADA POR UM TECNICO E O MESMO ESTIVER ATIVO, ATRIBUI A AVALIACAO A ELE
            if (count($avaliacao) > 0) {
                if ($avaliacao[0]->ConformidadeOK == 0 || $avaliacao[0]->ConformidadeOK == 1) {
                    //verifica se o tecnico esta habilitado
                    $arrBusca = array();
                    $arrBusca['sis_codigo = '] = 21;
                    $arrBusca['gru_codigo = '] = 92;
                    $arrBusca['usu_codigo = '] = $avaliacao[0]->idTecnico;
                    $analista = AdmissibilidadeDAO::buscarAnalistas($arrBusca);

                    if (count($analista) > 0) {
                        if ($analista[0]->uog_status == 1) {
                            $idTecnico = $avaliacao[0]->idTecnico;
                        } else {
                            $idTecnico = null;
                            //recupera todos os tecnicos do orgao para fazer o balanceamento
                            $rsTecnicos = $tblPreProjeto->recuperarTecnicosOrgao($idOrgaoSuperior);
                        }
                    } else {
                        $idTecnico = null;
                        //recupera todos os tecnicos do orgao para fazer o balanceamento
                        $rsTecnicos = $tblPreProjeto->recuperarTecnicosOrgao($idOrgaoSuperior);
                    }
                } else {
                    $idTecnico = $avaliacao[0]->idTecnico;
                }
            } else {
                //recupera todos os tecnicos do orgao para fazer o balanceamento
                $rsTecnicos = $tblPreProjeto->recuperarTecnicosOrgao($idOrgaoSuperior);
            }


            //SE A PROPOSTA NUNCA FOI AVALIADA OU SE O TECNICO Q A AVALIOU ESTA DESABILITADO FAZ O BALANCEAMENTO
            if (count($rsTecnicos) > 0 && $idTecnico == null) {
                $arrTecnicosPropostas = array();

                foreach ($rsTecnicos as $tecnico) {
                    $rsAvaliacaoPorTecnico = $tblAvaliacao->recuperarQtdePropostaTecnicoOrgao($tecnico->uog_orgao, $tecnico->usu_codigo);
                    $arrTecnicosPropostas[$tecnico->usu_codigo] = $rsAvaliacaoPorTecnico[0]->qtdePropostas;
                }
                asort($arrTecnicosPropostas);

                //PEGA O ID DO TECNICO Q TEM MENOS PROPOSTAS
                $ct = 1;
                foreach ($arrTecnicosPropostas as $chave => $valor) {
                    if ($ct == 1) {
                        $idTecnico = $chave;
                        $ct++;
                    } else {
                        break;
                    }
                }
            }

            //INICIA PERSISTENCIA DOS DADOS
            if ($idTecnico) {

                $db = Zend_Db_Table::getDefaultAdapter();
                //$db->beginTransaction();

                try {

                    //======== PERSXISTE DADOS DA MOVIMENTACAO ==========/
                    //atualiza status da ultima movimentacao
                    //$tblAvaliacao->updateEstadoMovimentacao($idPreProjeto);
                    $tblMovimentacao = new Movimentacao();
                    //Mudando as movimentacoes anteriores para o stEstado = 1
                    $rsRetorno = $tblMovimentacao->update(array("stEstado" => 1), "idProjeto = {$idPreProjeto}");
                    //Pegando ultima movimentacao
                    $rsMov = $tblMovimentacao->buscar(array("idProjeto = ?" => $idPreProjeto), array("idMovimentacao DESC"), 1, 0)->current();

                    if (count($rsMov) > 0) {
                        $ultimaMovimentacao = $rsMov->Movimentacao;
                        //xd($ultimaMovimentacao);
                        //Pegando penultima movimentacao
                        $rsMov = $tblMovimentacao->buscar(array("idProjeto = ?" => $idPreProjeto, "Movimentacao <> ?" => $ultimaMovimentacao), array("idMovimentacao DESC"), 1, 0)->current();
                        //xd($rsMov->toArray());

                        $movimentacaoDestino = 96;
                        if (count($rsMov) > 0) {
                            $movimentacaoDestino = $rsMov->Movimentacao;
                        }
                        //xd($movimentacaoDestino);
                    } else {
                        $movimentacaoDestino = 96;
                    }

                    //PERSISTE DADOS DA MOVIMENTACAO
                    
                    $tblMovimentacao = new Movimentacao();
                    $dados = array("idProjeto" => $idPreProjeto,
                        "Movimentacao" => $movimentacaoDestino, //satus
                        //"DtMovimentacao" => date("Y/m/d H:i:s"),
                        "DtMovimentacao" => new Zend_Db_Expr('GETDATE()'),
                        "stEstado" => "0", //esta informacao estava fixa trigger
                        "Usuario" => $this->idUsuario);

                    $tblMovimentacao->inserir($dados);

                    //======== PERSXISTE DADOS DA AVALIACAO ==========/
                    //atualiza status da ultima avaliacao
                    $tblAvaliacao->updateEstadoAvaliacao($idPreProjeto);

                    $dados = array();
                    $dados['idPreProjeto'] = $idPreProjeto;
                    $dados['idTecnico'] = $idTecnico; //$this->idUsuario;
                    $dados['dtEnvio'] = "'" . date("Y/m/d H:i:s") . "'";
                    $dados['dtAvaliacao'] = "'" . date("Y/m/d H:i:s") . "'";
                    $dados['avaliacao'] = "";
                    $dados['conformidade'] = 9;
                    $dados['estado'] = 0;

                    //PERSISTE DADOS DA AVALIACAO PROPOSTA
                    $tblAvaliacao->inserirAvaliacao($dados);

//                    //REMOVER AS PENDENCIAS DE DOCUMENTO
//                    $tblDocAnexados = new tbDocumentosPreProjeto();
//                    $rsDocsAnexados = $tblDocAnexados->buscar(array("idProjeto = ?"=>$idPreProjeto));
//                    $tblDocumentosPendentesProjeto = new DocumentosProjeto();
//                    $tblDocumentosPendentesProponente = new DocumentosProponente();
//                    //xd($rsDocsAnexados);
//                    foreach($rsDocsAnexados as $anexo){
//                        $tblDocumentosPendentesProjeto->delete("idProjeto = {$idPreProjeto} AND CodigoDocumento = {$anexo->CodigoDocumento}");
//                        $tblDocumentosPendentesProponente->delete("idProjeto = {$idPreProjeto} AND CodigoDocumento = {$anexo->CodigoDocumento}");
//                    }
                    //$db->commit();

                    parent::message("A Proposta foi enviado com sucesso ao Minist&eacute;rio da Cultura!", "/manterpropostaedital/enviar-proposta-ao-minc?idPreProjeto=" . $idPreProjeto . $edital, "CONFIRM");
                    die();
                } catch (Exception $e) {
                    //$db->rollback();
                    //xd($e->getMessage());
                    parent::message("O Projeto n&atilde;o foi enviado ao Minist&eacute;rio da Cultura!", "/manterpropostaedital/enviar-proposta-ao-minc?idPreProjeto=" . $idPreProjeto . $edital, "ERROR");
                    die();
                }
            } else { //fecha IF se encontrou tecnicos para enviar a proposta
                parent::message("A Proposta n&atilde;o foi enviado ao Minist&eacute;rio da Cultura. T&eacute;cnico n&atilde;o localizado", "/manterpropostaedital/enviar-proposta-ao-minc?idPreProjeto=" . $idPreProjeto . $edital, "ERROR");
                die();
            }
        } else {
            //xd($e->getMessage());
            parent::message("A Proposta n&atilde;o foi enviado ao Minist&eacute;rio da Cultura.", "/manterpropostaedital/enviar-proposta-ao-minc?idPreProjeto=" . $idPreProjeto . $edital, "ERROR");
        }
    }

    /*
      public function confirmarEnvioPropostaAoMincAction()
      {
      //recupera parametros
      $get = Zend_Registry::get('get');
      $idPreProjeto = $get->idPreProjeto;
      $valida = $get->valida;

      if(isset($_REQUEST['edital'])){
      $edital = "&edital=s";
      }else{
      $edital = "";
      }

      if(!empty ($idPreProjeto) && $valida == "s")
      {
      try{

      $tblPreProjeto = new PreProjeto();
      $tblAvaliacao = new AnalisarPropostaDAO();

      //atualiza status da ultima movimentacao
      $tblAvaliacao->updateEstadoMovimentacao($idPreProjeto);

      //persiste os dados de Movimentacao
      $tblMovimentacao = new Movimentacao();
      $dados = array("idProjeto"=>$idPreProjeto,
      "Movimentacao"=>"96",//satus
      "DtMovimentacao"=>date("Y/m/d H:i:s"),
      "stEstado"=>"0",//esta informacao estava fixa trigger
      "Usuario"=>$this->idUsuario);

      $tblMovimentacao->salvar($dados);


      //recupera dados do projeto
      $rsPreProjeto = $tblPreProjeto->find($idPreProjeto)->current();

      // Recuperando edital
      $tblEdital = new Edital();
      $rsEdital = $tblEdital->buscar(array("idEdital = ?"=>$rsPreProjeto->idEdital))->current();
      $idOrgaoSuperior = $rsEdital->idOrgao;

      //                if($rsPreProjeto->AreaAbrangencia == 0){
      //                    $idOrgaoSuperior = 251;
      //                }else{
      //                    $idOrgaoSuperior = 160;
      //                }
      //recupera todos os tecnicos do orgao
      $rsTecnicos = $tblPreProjeto->recuperarTecnicosOrgao($idOrgaoSuperior);

      $arrTecnicosPropostas = array();
      $tecnico = 0;
      foreach($rsTecnicos as $tecnico){
      $rsAvaliacaoPorTecnico = $tblAvaliacao->recuperarQtdePropostaTecnicoOrgao($tecnico->uog_orgao,$tecnico->usu_codigo);
      $arrTecnicosPropostas[$tecnico->usu_codigo]= $rsAvaliacaoPorTecnico[0]->qtdePropostas;
      }
      asort($arrTecnicosPropostas);
      //PEGA O ID DO TECNICO Q TEM MENOS PROPOSTAS
      $ct=1;
      foreach ($arrTecnicosPropostas as $chave => $valor) {
      if($ct == 1){
      $idTecnico = $chave;
      $ct++;
      }else{
      break;
      }
      }

      $dados = array();
      $dados['idPreProjeto']  = $idPreProjeto;
      $dados['idTecnico']     = $idTecnico;
      $dados['dtEnvio']       = date("Y/m/d");
      $dados['dtAvaliacao']   = date("Y/m/d");
      $dados['avaliacao']     = "";
      $dados['conformidade']  = 9;
      $dados['estado']        = 0;
      $dados['movimentacao']  = 96;

      //insere primeira avaliaç?o da propsota
      $tblAvaliacao->inserirAvaliacao($dados);

      parent::message("O Projeto foi enviado com sucesso ao Minist&eacute;rio da Cultura!", "/manterpropostaedital/enviar-proposta-ao-minc?idPreProjeto=".$idPreProjeto.$edital, "CONFIRM");

      }catch (Exception $e){

      parent::message("O Projeto n&atilde;o foi enviado ao Minist&eacute;rio da Cultura.", "/manterpropostaedital/enviar-proposta-ao-minc?idPreProjeto=".$idPreProjeto.$edital, "ERROR");
      }
      }else{

      parent::message("O Projeto n&atilde;o foi enviado ao Minist&eacute;rio da Cultura.", "/manterpropostaedital/enviar-proposta-ao-minc?idPreProjeto=".$idPreProjeto.$edital, "ERROR");

      }

      }
     */

    public function validarAgenciaBancariaAction() {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $ba = new BancoAgencia;
        $validaragencia = $ba->buscar(array('Agencia = ?'=>$_POST['agencia']))->count();
        if($validaragencia > 0){
            echo json_encode(array('error'=>false));
        }
        else{
            echo json_encode(array('error'=>true));
        }
        exit();
    }


    /**
     * Método para efetuar a exclusão do arquivo
     */
    public function excluiranexoAction() {
        if (isset($_GET['idArquivo']) && !empty($_GET['idArquivo']) && isset($_GET['idPreProjeto']) && !empty($_GET['idPreProjeto']) && isset($_GET['tipoDocumento']) && !empty($_GET['tipoDocumento'])) :

            if($_GET['tipoDocumento'] == 'proposta'){
                $tbDocumentosPreProjeto = new tbDocumentosPreProjeto();
                $tbDocumentosPreProjeto->apagar(array('idDocumentosPreprojetos = ?' => $_GET['idArquivo']));
            } else {
                $tbDocumentosAgentes = new tbDocumentosAgentes();
                $tbDocumentosAgentes->apagar(array('idDocumentosAgentes = ?' => $_GET['idArquivo']));
            }

            parent::message('Exclusão efetuada com sucesso!', 'manterpropostaedital/enviararquivoedital?idPreProjeto=' . $_GET['idPreProjeto'], 'CONFIRM');
        endif;
    }
}