<?php 
/**
 * 
 * @author Caio Lucena
 */
class ComprovarPagamentoController extends GenericControllerNew
{
    /**
     * 
     */
    public function init()
    {
        parent::init();
        # context
        $this->_helper->getHelper('contextSwitch')
            ->addActionContext('pesquisar', 'json')
            ->addActionContext('deletar', 'json')
            ->initContext()
        ;
    }

    /**
     * 
     */
    public function deletarAction()
    {
        $comprovanteParamentoModel = new ComprovantePagamento($this->getRequest()->getParam('comprovante'));
        $comprovanteParamentoModel->deletar();
    }

    /**
     * 
     */
    public function pesquisarAction()
    {
        $comprovanteParamentoModel = new ComprovantePagamento();
        $this->view->comprovantes = $comprovanteParamentoModel->pesquisarComprovantePorItem($this->getRequest()->getParam('item'));
        array_walk($this->view->comprovantes, function(&$value){
            $value = array_map('utf8_encode', $value);
        });
    }
}
