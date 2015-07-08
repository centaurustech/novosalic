<?php
/**
 * DAO Convenio
 * @author jefferson.silva - XTI
 * @since 15/01/2014
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class Convenio extends GenericModel
{
	protected $_banco  = "SAC";
	protected $_schema = "dbo";
	protected $_name   = "Convenio";
    
    
    public function buscarDadosConvenios($where=array(), $order=array()) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('a' => $this->_name),
            array(
                new Zend_Db_Expr("AnoProjeto+Sequencial as PRONAC"),
                new Zend_Db_Expr("CASE
                                    WHEN Opcao = 0
                                         THEN 'Convênio'
                                            ELSE 'Termo Aditivo'
                                     END as descOpcao
                "),
                new Zend_Db_Expr("Opcao,NumeroConvenio,DtConvenio,DtPublicacao,DtInicioVigencia,DtFinalVigencia,ValorConvenio")
            )
        );

        
       //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }
        $select->order('Contador');

        //xd($select->assemble());
        return $this->fetchAll($select);
    }

} // fecha class