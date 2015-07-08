<?php
/**
 * Description of tbTipoInabilitado
 *
 * @author Emerson Silva
 */
class tbTipoInabilitado extends GenericModel {

    protected $_name   = 'tbTipoInabilitado';
    protected $_schema = 'dbo';
    protected $_banco  = 'SAC';


    public function cadastrarinabilitacao($data){
        $insert = $this->insert($data);
        return $insert;
    }

}
?>