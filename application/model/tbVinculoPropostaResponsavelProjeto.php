<?php 
class tbVinculoPropostaResponsavelProjeto extends GenericModel{
    
    protected $_banco = 'Agentes';
    protected $_name = 'tbVinculoProposta';
    



	/* Método 
     * 
     * */
    public function buscarResponsaveisProponentes($where=array()) 
    {
    	$slct = $this->select();
        $slct->distinct();
        $slct->setIntegrityCheck(false);
        
        $slct->from(
                array('VP' => $this->_name), 
                array('*')
        );

        $slct->joinInner(
                array('VI' => 'tbVinculo'),'VI.idVinculo = VP.idVinculo', 
                array('*')
        );
        
        
        foreach ($where as $coluna => $valor) 
        {
            $slct->where($coluna, $valor);
        }
       // xd($slct->assemble());
        return $this->fetchAll($slct);
    }
    


}

?>
