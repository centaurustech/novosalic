<?php

/**
 * AnexarDocumentosController
 * @author Equipe RUP - Politec
 * @since 28/04/2010
 * @version 1.0
 * @package application
 * @subpackage application.controllers
 * @link http://www.cultura.gov.br
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 */
require_once "GenericControllerNew.php";

class AnexardocumentosController extends GenericControllerNew
{

    /**
     * Reescreve o método init()
     * @access public
     * @param void
     * @return void
     */
    public function init()
    {
        $this->view->title = "Salic - Sistema de Apoio às Leis de Incentivo à Cultura"; // título da página
        $auth = Zend_Auth::getInstance(); // pega a autenticação
        $Usuario = new UsuarioDAO(); // objeto usuário
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo

        if ($auth->hasIdentity()) // caso o usuário esteja autenticado
        {
            // verifica as permissões
            $PermissoesGrupo = array();
            //$PermissoesGrupo[] = 93;  // Coordenador de Parecerista
            //$PermissoesGrupo[] = 94;  // Parecerista
            $PermissoesGrupo[] = 103; // Coordenador de Análise
            $PermissoesGrupo[] = 118; // Componente da Comissão
            //$PermissoesGrupo[] = 119; // Presidente da Mesa
            //$PermissoesGrupo[] = 120; // Coordenador Administrativo CNIC
            if (!in_array($GrupoAtivo->codGrupo, $PermissoesGrupo)) // verifica se o grupo ativo está no array de permissões
            {
                parent::message("Você não tem permissão para acessar essa área do sistema!", "principal/index", "ALERT");
            }

            // pega as unidades autorizadas, orgãos e grupos do usuário (pega todos os grupos)
            $grupos = $Usuario->buscarUnidades($auth->getIdentity()->usu_codigo, 21);

            // manda os dados para a visão
            $this->view->usuario = $auth->getIdentity(); // manda os dados do usuário para a visão
            $this->view->arrayGrupos = $grupos; // manda todos os grupos do usuário para a visão
            $this->view->grupoAtivo = $GrupoAtivo->codGrupo; // manda o grupo ativo do usuário para a visão
            $this->view->orgaoAtivo = $GrupoAtivo->codOrgao; // manda o órgão ativo do usuário para a visão
        } // fecha if
        else // caso o usuário não esteja autenticado
        {
            return $this->_helper->redirector->goToRoute(array('controller' => 'index', 'action' => 'logout'), null, true);
        }
        parent::init(); // chama o init() do pai GenericControllerNew
    }

    /**
     * Redireciona para o fluxo inicial do sistema
     * @access public
     * @param void
     * @return void
     */
    public function indexAction()
    {
        // despacha para buscarpronac.phtml
        $pronac = $this->_request->getParam("idpronac");
        $this->_redirect("anexardocumentos/documentos-anexados/idpronac/$pronac");
        $auth = Zend_Auth::getInstance(); // pega a autenticação
        $idagente = GerenciarPautaReuniaoDAO::consultaAgenteUsuario($auth->getIdentity()->usu_codigo);
        $idagente = $idagente['idAgente'];
        //-------------------------------------------------------------------------------------------------------------
           $reuniao = new Reuniao();
            $ConsultaReuniaoAberta = $reuniao->buscar(array("stEstado = ?" => 0));
            if($ConsultaReuniaoAberta->count() > 0)
            {
                $ConsultaReuniaoAberta = $ConsultaReuniaoAberta->current()->toArray();
                $this->view->dadosReuniaoPlenariaAtual = $ConsultaReuniaoAberta;
                //---------------------------------------------------------------------------------------------------------------
                $votantes = new Votante();
                $exibirVotantes = $votantes->selecionarvotantes($ConsultaReuniaoAberta['idNrReuniao']);
                if (count($exibirVotantes) > 0) {
                    foreach ($exibirVotantes as $votantes) {
                        $dadosVotante[] = $votantes->idAgente;
                    }
                    if (count($dadosVotante) > 0) {
                        if (in_array($idagente, $dadosVotante)) {
                            $this->view->votante = true;
                        } else {
                            $this->view->votante = false;
                        }
                    }
                }
            }
            else{
                parent::message("Não existe CNIC aberta no momento. Favor aguardar!", "principal/index", "ERROR");
            }
    }

    /**
     * Método com o formulário para buscar o PRONAC
     * @access public
     * @param void
     * @return void
     */
    public function buscardocumentosAction()
    {
        // recebe o pronac via get
        $idpronac = $this->_request->getParam("idpronac");
        $this->view->idpronac = $idpronac;

        try
        {
            // verifica se o pronac veio vazio
            if (empty($idpronac))
            {
                throw new Exception("Por favor, informe o PRONAC!");
            }
            // valida o número do pronac
            else if (!is_numeric($idpronac) || strlen($idpronac) > 20)
            {
                throw new Exception("O Nº do PRONAC é inválido!");
            }
            else
            {
//                        die('fasdfasd');
                // integração MODELO e VISÃO

                $Projetos = new Projetos();
                $resultPronac = $Projetos->buscar(array('IdPRONAC = ?' => $idpronac));

                // caso o PRONAC não esteja cadastrado
                if (!$resultPronac) {
                    throw new Exception("Registro não encontrado!");
                }
                // caso o PRONAC esteja cadastrado, vai para a página de busca
                // dos seus documentos (comprovantes)
                else {
                    // pega o id do pronac
                    $idpronac = $resultPronac[0]->IdPRONAC;
                    $pronac = $resultPronac[0]->AnoProjeto.$resultPronac[0]->Sequencial;
                    $buscarpronac = $resultPronac;
                    // busca os documentos (comprovantes) do pronac

                    $Documentos = new DocumentosProjeto();
                    $resultComprovantes = $Documentos->documentosAnexados($idpronac);

//                    $resultComprovantes = AnexardocumentosDAO::buscarArquivos($idpronac);
//                    xd($resultComprovantes);

                    // caso não existam comprovantes cadastrados
                    if (count($resultComprovantes) == 0)
                    {
                        $this->view->message = "Nenhum arquivo anexado ao PRONAC Nº " . $pronac . "!";
                        $this->view->message_type = "ALERT";
                        $this->view->buscarpronac = $buscarpronac;
                    }
                    else
                    {
                        // manda os comprovantes para a visão
                        $this->view->buscarcomprovantes = $resultComprovantes;
                        $this->view->buscarpronac = $buscarpronac;
                        $auth = Zend_Auth::getInstance(); // pega a autenticação
                        $Usuario = new Usuario(); // objeto usuário
                        $idagente = $Usuario->getIdUsuario($auth->getIdentity()->usu_codigo);
                        $idagente = $idagente['idAgente'];
                        $reuniao = new Reuniao();
                        $ConsultaReuniaoAberta = $reuniao->buscar(array("stEstado = ?" => 0));
                        if($ConsultaReuniaoAberta->count() > 0)
                        {
                            $ConsultaReuniaoAberta = $ConsultaReuniaoAberta->current()->toArray();
                            $this->view->dadosReuniaoPlenariaAtual = $ConsultaReuniaoAberta;
                            //---------------------------------------------------------------------------------------------------------------
                            $votantes = new Votante();
                            $exibirVotantes = $votantes->selecionarvotantes($ConsultaReuniaoAberta['idNrReuniao']);
                            if (count($exibirVotantes) > 0) {
                                foreach ($exibirVotantes as $votantes) {
                                    $dadosVotante[] = $votantes->idAgente;
                                }
                                if (count($dadosVotante) > 0) {
                                    if (in_array($idagente, $dadosVotante)) {
                                        $this->view->votante = true;
                                    } else {
                                        $this->view->votante = false;
                                    }
                                }
                            }
                        }
                        else{
                            parent::message("Não existe CNIC aberta no momento. Favor aguardar!", "principal/index", "ERROR");
                        }
                    }
                }
            } // fecha else
        } // fecha try
        catch (Zend_Exception $e)
        {
           die("ERRO:".$e->getMessage());
        }
    }
    
    public function documentosAnexadosAction(){
        $idPronac = $this->_request->getParam("idpronac");
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }

        $Projetos = new Projetos();
        $projeto = $Projetos->buscar(array('IdPRONAC = ?' => $idPronac))->current();
        $this->view->projeto = $projeto;

        $tbDoc = new paDocumentos();
        $rs = $tbDoc->marcasAnexadas($idPronac);
        $this->view->registros = $rs;
        
        $this->view->idPronac = $idPronac;
    }

    // fecha método buscardocumentosAction()

    public function downdocAction()
    {
        $id = $this->_request->getParam("id");
        $tipo = $this->_request->getParam("tipo");
        @ini_set("mssql.textsize", 10485760);
        @ini_set("mssql.textlimit", 10485760);
        @ini_set("upload_max_filesize", "10M");

        $response = new Zend_Controller_Response_Http;

        $resultado = AnexardocumentosDAO::uploadDocumento($id, $tipo);

        if (!$resultado)
        {
            $this->view->message = 'Não foi possível abrir o arquivo!';
            $this->view->message_type = 'ERROR';
        }
        else
        {
            // lê os cabeçalhos formatado
            foreach ($resultado as $r)
            {
                $this->_helper->layout->disableLayout();        // Desabilita o Zend Layout
                $this->_helper->viewRenderer->setNoRender();    // Desabilita o Zend Render
                Zend_Layout::getMvcInstance()->disableLayout(); // Desabilita o Zend MVC
                $this->_response->clearBody();                  // Limpa o corpo html
                $this->_response->clearHeaders();               // Limpa os headers do Zend
                $ext = explode('.', $r->noarquivo);

                $nome = $ext['0'];
                $extencao = $ext[1];
                if($tipo != 'docProp'){
                    $binario = base64_decode($r->imdocumento);
                }
                else{
                    $binario = $r->imdocumento;
                }
                $this->getResponse()
                        ->setHeader('Content-Type', $extencao)
                        ->setHeader('Content-Disposition', 'attachment; filename="' . $r->noarquivo . '"')
                        ->setHeader("Connection", "close")
                        ->setHeader("Content-transfer-encoding", "binary")
                        ->setHeader("Cache-control", "private")
                        ->setBody($binario);
            } // fecha foreach
        }
    }

    /**
     * Método para buscar os documentos (comprovantes) do PRONAC
     * @access public
     * @param void
     * @return void
     */
    public function cadastrardocumentosAction()
    {
        $this->view->combotipodocumento = Tipodocumento::buscar();

        // caso o formulário seja enviado via post
        if ($this->getRequest()->isPost())
        {
            // recebe os dados via post
            $post = Zend_Registry::get('post');
            $pronac = $post->pronac;
            $tipoDocumento = $post->tipoDocumento;
            $titulo = $post->titulo;
            $descricao = $post->descricao;

            // pega as informações do arquivo
            $arquivoNome = $_FILES['arquivo']['name']; // nome
            $arquivoTemp = $_FILES['arquivo']['tmp_name']; // nome temporário
            $arquivoTipo = $_FILES['arquivo']['type']; // tipo
            $arquivoTamanho = $_FILES['arquivo']['size']; // tamanho
            if (!empty($arquivoNome))
            {
                $arquivoExtensao = Upload::getExtensao($arquivoNome); // extensão
            }
            if (!empty($arquivoTemp))
            {
                $arquivoBinario = Upload::setBinario($arquivoTemp); // binário
                $arquivoHash = Upload::setHash($arquivoTemp); // hash
            }

            try
            {
                // integração MODELO e VISÃO
                // busca o PRONAC de acordo com o id no banco
                $resultado = Pronac::buscar($pronac);

                // caso o PRONAC não esteja cadastrado
                if (!$resultado)
                {
                    parent::message("Registro não encontrado!", "buscarpronac");
                }
                // caso o PRONAC esteja cadastrado, vai para a página de busca
                else
                {
                    $this->view->buscarpronac = $resultado;
                }

                // valida os campos vazios
                if (empty($tipoDocumento))
                {
                    throw new Exception("Por favor, informe o tipo de documento!");
                }
                else if (empty($descricao))
                {
                    throw new Exception("Por favor, informe a descrição do documento!");
                }
                else if (empty($arquivoTemp)) // nome do arquivo
                {
                    throw new Exception("Por favor, informe o arquivo!");
                }
                else if ($arquivoExtensao == 'exe' || $arquivoExtensao == 'bat' ||
                        $arquivoTipo == 'application/exe' || $arquivoTipo == 'application/x-exe' ||
                        $arquivoTipo == 'application/dos-exe') // extensão do arquivo
                {
                    throw new Exception("A extensão do arquivo é inválida!");
                }
                else if ($arquivoTamanho > 10485760) // tamanho do arquivo: 10MB
                {
                    throw new Exception("O arquivo não pode ser maior do que 10MB!");
                }
                // faz o cadastro no banco de dados
                else
                {
                    // cadastra dados do arquivo
                    $sql = "INSERT INTO BDCORPORATIVO.scCorp.tbArquivo (nmArquivo, sgExtensao, dsTipo, nrTamanho, dtEnvio, dsHash, stAtivo) " .
                            "VALUES ('" . $arquivoNome . "', '" . $arquivoExtensao . "', '" . $arquivoTipo . "', '" . $arquivoTamanho . "', GETDATE(), '" . $arquivoHash . "', 'A')";
                    $db = Zend_Registry :: get('db');
                    $db->setFetchMode(Zend_DB :: FETCH_OBJ);
                    $resultado = $db->query($sql);

                    // pega o id do arquivo
                    $db = Zend_Registry :: get('db');
                    $db->setFetchMode(Zend_DB :: FETCH_OBJ);
                    $idGerado = $db->fetchOne("SELECT MAX(idArquivo) AS id FROM BDCORPORATIVO.scCorp.tbArquivo");

                    // insere o binário do arquivo
                    $sql = "INSERT INTO BDCORPORATIVO.scCorp.tbArquivoImagem " .
                            "VALUES ($idGerado, $arquivoBinario)";
                    $db = Zend_Registry :: get('db');
                    $db->setFetchMode(Zend_DB :: FETCH_OBJ);
                    $resultado = $db->query($sql);

                    // insere informações do documento
                    $sql = "INSERT INTO BDCORPORATIVO.scSac.tbComprovanteExecucao (idPRONAC, idTipoDocumento, nmComprovante, dsComprovante, idArquivo, idSolicitante, dtEnvioComprovante, stParecerComprovante, stComprovante) " .
                            "VALUES ($pronac, $tipoDocumento, '$titulo', '$descricao', $idGerado, 9997, GETDATE(), 'AG', 'A')";
                    $db = Zend_Registry :: get('db');
                    $db->setFetchMode(Zend_DB :: FETCH_OBJ);
                    $resultado = $db->query($sql);

                    if ($resultado)
                    {
                        parent::message("Cadastro realizado com sucesso!", "anexardocumentos/buscardocumentos?pronac=" . $pronac);
                    }
                    else
                    {
                        throw new Exception("Erro ao realizar cadastro");
                    }
                }
            } // fecha try
            catch (Exception $e)
            {
                $this->view->message = $e->getMessage();
                $this->view->message_type = "ERROR";
                $this->view->tipoDocumento = $tipoDocumento;
                $this->view->titulo = $titulo;
                $this->view->descricao = $descricao;
            }
        }
        // quando a página é aberta
        else
        {
            // recebe o pronac via get
            $get = Zend_Registry::get('get');
            $pronac = $get->pronac;

            try
            {
                // verifica se o pronac veio vazio
                if (empty($pronac))
                {
                    parent::message("Por favor, informe o PRONAC!", "buscarpronac");
                }
                else
                {
                    // integração MODELO e VISÃO
                    // busca o PRONAC de acordo com o id no banco
                    $resultado = Pronac::buscar($pronac);

                    // caso o PRONAC não esteja cadastrado
                    if (!$resultado)
                    {
                        parent::message("Registro não encontrado!", "buscarpronac");
                    }
                    // caso o PRONAC esteja cadastrado, vai para a página de busca
                    else
                    {
                        $this->view->buscarpronac = $resultado;
                    }
                } // fecha else
            } // fecha try
            catch (Exception $e)
            {
                $this->view->message = $e->getMessage();
            }
        }
    }

}

