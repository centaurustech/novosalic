<?php
/**
 * DAO tbAssinantesPrestacao
 * @since 10/02/2015
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbAssinantesPrestacao extends GenericModel
{
	protected $_banco  = "SAC";
	protected $_schema = "dbo";
	protected $_name   = "tbAssinantesPrestacao";

    /* 
     * Criada em 10/02/2015
     * @author: Jefferson Alessandro
     * Essa consulta retorna os dados dos assinantes de prestação de contas - Perfil: Coordenador de Prestação de Contas
     */
    public function buscarAssinantesPrestacaoDeContas($where=array(), $order=array(), $tamanho=-1, $inicio=-1, $qtdeTotal=false)
    {
        $cargoTable = new CargoAssinantePrestacaoDeContasTable();
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from( 
            array('a' => $this->_name),
            array(
                new Zend_Db_Expr("
                    a.idAssinantesPrestacao,
                    a.nmAssinante,
                    a.tpCargo,
                    a.dtCadastro,
                    a.stAtivo
                ")
            )
        );

        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        if ($qtdeTotal) {
//            xd($select->assemble());
            return $this->fetchAll($select)->count();
        }

        //adicionando linha order ao select
        $select->order($order);

        // paginacao
        if ($tamanho > -1) {
            $tmpInicio = 0;
            if ($inicio > -1) {
                $tmpInicio = $inicio;
            }
            $select->limit($tamanho, $tmpInicio);
        }

        //xd($select->assemble());
        return $this->fetchAll($select);
    }


} // fecha class