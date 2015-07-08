<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Vinculo
 *
 */
class tbDocumentoProjeto extends GenericModel {

    protected $_banco  = "BDCORPORATIVO";
    protected $_schema = "scCorp";
    protected $_name   = "tbDocumentoProjeto";

    public function excluir($where)
    {
        return $this->delete($where);
    }

}
?>
