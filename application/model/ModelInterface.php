<?php

/**
 *
 * @author Mikhail Cavalcanti<mikhail.leite@xti.com.br>
 */
interface ModelInterface
{
    public function salvar();
    public function atualizar();
    public function buscar($id = null);
    public function deletar($id);
}
