<?php
/**
 * Description of Segmento
 *
 * @author 01610881125
 */
class Segmento extends GenericModel {
    protected $_banco   = 'SAC';
    protected $_name    = 'Segmento';
    protected $_schema  = 'dbo';

    public function buscaCompleta($where=array(), $order=array(), $tamanho=-1, $inicio=-1, $count=false){
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(array('s'=>$this->_name),
                    array('id'=>'Codigo', 'descricao'=>'Descricao')
        );
        
        $slct->joinInner(array('a'=>'Area'),'LEFT(s.Codigo, 1) = a.Codigo',
                            array(),'SAC.dbo'
        );

        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $slct->where($coluna, $valor);
        }

        //adicionando linha order ao select
        $slct->order($order);

        // paginacao
        if ($tamanho > -1) {
            $tmpInicio = 0;
            if ($inicio > -1) {
                $tmpInicio = $inicio;
            }
            $slct->limit($tamanho, $tmpInicio);
        }
        //xd($slct->getPart(Zend_Db_Select::COLUMNS));
//        xd($slct->assemble());
        return $this->fetchAll($slct);
    }



	/**
	 * Busca os segmentos para as combos
	 * @access public
	 * @param array $where (filtros)
	 * @param array $order (ordenação)
	 * @return object
	 */
	public function combo($where = array(), $order = array())
	{
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->from(array('s' => $this->_schema . '.vSegmento')
			,array('s.Codigo AS id'
                            ,'s.Segmento AS descricao'
			)
		);
		$select->joinInner(
			array('a' => 'Area')
                            ,'s.Area = a.Codigo'
			,array()
		);

		// adiciona quantos filtros foram enviados
		foreach ($where as $coluna => $valor) :
			$select->where($coluna, $valor);
		endforeach;

		// adicionando linha order ao select
		$select->order($order);

		return $this->fetchAll($select);
	} // fecha método combo()
        
        public function buscaSegmentos()
        {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            return $this->fetchAll($select);
        }
}