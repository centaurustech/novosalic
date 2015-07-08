<?php

/**
 * Description of ComprovarPagamentoServiceController
 *
 * @author mikhail
 */
class ComprovarPagamentoServiceController extends ServicoController
{

    /**
     * Action que define a classe de servico que será publicada como serviço
     * pelo protocolo soap
     */
    public function comprovarPagamentoAction()
    {
        $this->setServiceClass('ComprovantePagamentoService');
    }

}
