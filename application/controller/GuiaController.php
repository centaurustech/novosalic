<?php
/**
 * @author Caio Lucena <caioflucena@gmail.com>
 */
class GuiaController extends GenericControllerNew
{
    /**
     * (non-PHPdoc)
     * @see GenericControllerNew::init()
     */
    public function init() {
        /** /
        $auth = Zend_Auth::getInstance(); // instancia da autenticacao;
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessao com o grupo ativo
        $codGrupo = $GrupoAtivo->codGrupo; //  Grupo ativo na sessao
        $codOrgao = $GrupoAtivo->codOrgao; //  Orgao ativo na sessao
        $this->view->codOrgao = $codOrgao;
        //Da permissao de acesso a todos os grupos do usuario logado afim de atender o UC72
        if (isset($auth->getIdentity()->usu_codigo)) {
            //Recupera todos os grupos do Usuario
            $Usuario = new Usuario(); // objeto usuário
            $grupos = $Usuario->buscarUnidades($auth->getIdentity()->usu_codigo, 21);
            foreach ($grupos as $grupo) {
                $PermissoesGrupo[] = $grupo->gru_codigo;
            }
            $this->idusuario = $auth->getIdentity()->usu_codigo;
            $this->view->idUsuarioLogado = $this->idusuario;
            isset($auth->getIdentity()->usu_codigo) ? parent::perfil(1, $PermissoesGrupo) : parent::perfil(4, $PermissoesGrupo);
        } else {
            $this->idusuario = $auth->getIdentity()->IdUsuario;
        }
        parent::init();
        /**/
        $this->_helper->getHelper('contextSwitch')
            ->addActionContext('cadastrar', 'json')
            ->addActionContext('atualizar', 'json')
            ->addActionContext('deletar', 'json')
            ->addActionContext('pesquisar', 'json')
            ->initContext()
        ;
    }

    /**
     * (non-PHPdoc)
     * @see GenericControllerNew::postDispatch()
     */
    public function postDispatch(){;}

    /**
     * 
     */
    public function cadastrarAction()
    {
        try {
            $guia = new GuiaModel(
                null,
                $this->getRequest()->getParam('categoria'),
                $this->getRequest()->getParam('nomeGuia'),
                $this->getRequest()->getParam('txtGuia')
            );
            $guia->cadastrar();
            $this->view->guia = $guia->toStdClass();
        } catch(Exception $e) {
        	xd($e);
            $this->view->error = $e->getMessage();
        }
    }

    /**
     * 
     */
    public function atualizarAction()
    {
        $guia = new GuiaModel(
            $this->getRequest()->getParam('guia'),
            $this->getRequest()->getParam('categoria'),
            $this->getRequest()->getParam('nomeGuia'),
            $this->getRequest()->getParam('txtGuia')
        );
        $guia->atualizar();
        $this->view->guia = $guia->toStdClass();
    }

    /**
     * @return void
     */
    public function deletarAction()
    {
        $guia = new GuiaModel($this->getRequest()->getParam('guia'));
        $this->view->guia = $guia->deletar();
    }

    /**
     * 
     */
    public function pesquisarAction()
    {
        $guiaModel = new GuiaModel();
        $this->view->guia = $guiaModel->pesquisar($this->getRequest()->getParam('guia'));
    }
}
