<?php
class Situacao extends GenericModel {

    protected $_banco = 'SAC';
    protected $_name = 'dbo.Situacao';

    public function listasituacao($codigosituacao=array())
	{
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                        array('sit'=>$this->_name)
                      );
            foreach($codigosituacao as $situacao)
            {
            	$select->orwhere('sit.Codigo = ?', $situacao);
            }
            return $this->fetchAll($select);
	} // fecha método listasituacao()
        
}
?>
