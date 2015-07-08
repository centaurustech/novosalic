<?php 
 
class MarcasController extends GenericControllerNew {
     
    /**
     * Reescreve o método init()
     * @access public
     * @param void
     * @return void
     */
    
    public function init()
    {
        $auth = Zend_Auth::getInstance(); // pega a autenticaç?o
        $this->view->title = "Salic - Sistema de Apoio ?s Leis de Incentivo ? Cultura"; // título da página
        // 3 => autenticaç?o scriptcase e autenticaç?o/permiss?o zend (AMBIENTE PROPONENTE E MINC)
        // utilizar quando a Controller ou a Action for acessada via scriptcase e zend
        // define as permiss?es

        $this->idusuario = $auth->getIdentity()->usu_codigo;
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo

        $codGrupo = $GrupoAtivo->codGrupo; //  Grupo ativo na sessão
        $codOrgao = $GrupoAtivo->codOrgao; //  Órgão ativo na sessão
        $this->codGrupo = $codGrupo; //  Grupo ativo na sessão
        $this->codOrgao = $codOrgao;
        $this->view->codOrgao = $codOrgao;
        $this->view->codGrupo = $codGrupo;
        $this->view->grupoativo = $codGrupo;

        //$this->view->idUsuarioLogado = $idusuario;
        $PermissoesGrupo = array();
        $PermissoesGrupo[] = 121; //Tecnico de Acompanhamento

        parent::perfil(1, $PermissoesGrupo);
        if (isset($auth->getIdentity()->usu_codigo)) {
            $this->getIdUsuario = UsuarioDAO::getIdUsuario($auth->getIdentity()->usu_codigo);
            if ($this->getIdUsuario) {
                $this->getIdUsuario = $this->getIdUsuario["idAgente"];
            } else {
                $this->getIdUsuario = 0;
            }
        } else {
            $this->getIdUsuario = $auth->getIdentity()->IdUsuario;
        }
        parent::init();
    }

    public function indexAction() 
    {
        $tbArquivoImagem = new tbArquivoImagem();
        $tblUsuario = new Usuario();
        $rsUsuario = $tblUsuario->buscarOrgaoSuperior($this->codOrgao);
        $idOrgaoSuperior = $rsUsuario->idSecretaria;

        if($idOrgaoSuperior == 251){
            $lista = $tbArquivoImagem->listarMarcasAcompanhamentoArea('p.Area <> 2');
        } else {
            $lista = $tbArquivoImagem->listarMarcasAcompanhamentoArea('p.Area = 2');
        }
        $this->view->lista = $lista;
    }

    public function processarMarcasAction()
    {
        if($_POST['justificativa'] != ''){

            $dados = array('stAtivoDocumentoProjeto' => $_POST['stAtivo']);
            $where = array('idDocumento = ?' => $_POST['documento']);

            $tbDocumentoProjeto = new tbDocumentoProjeto();
            $resultado = $tbDocumentoProjeto->update($dados, $where);
            if($resultado){
                if($_POST['stAtivo'] == 'D'){
                    $msg = 'A Marca foi DEFERIDA com sucesso!';
                    $assunto = 'SALIC - Marca Deferida';
                } else {
                    $msg = 'A Marca foi INDEFERIDA com sucesso!';
                    $assunto = 'SALIC - Marca Indeferida';

                    $dados = array('dsDocumento' => utf8_decode($_POST['justificativa']));
                    $where = array('idDocumento = ?' => $_POST['documento']);
                    $tbDocumento = new tbDocumento();
                    $resultado2 = $tbDocumento->update($dados, $where);
                }

                $projetos = new Projetos();
                $ListaEmails = $projetos->buscarProjetoEmails($_POST['pronacId']);

                if(count($ListaEmails)>0){
                    foreach ($ListaEmails as $lista) {
                        $EnviarEmails = new EmailDAO();
                        $EnviarEmails->enviarEmail($lista->Email, $assunto, utf8_decode($_POST['justificativa']));
                    }
                }
                echo json_encode(array('resposta'=>true, 'mensagem'=>$msg));

            } else {
                echo json_encode(array('resposta'=>false));
            }

        } else {
            echo json_encode(array('resposta'=>false));
        }
        die();
    }
    
}


