<?php
/**
 * DAO tbArquivo
 * @since 07/01/2013
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbArquivamento extends GenericModel {
    protected $_banco  = "SAC";
    protected $_schema = "dbo";
    protected $_name   = "tbArquivamento";


    public function confirirArquivamentoProjeto($pronac) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                array("a" => $this->_name),
                array('a.Data', 'a.CaixaInicio', 'a.CaixaFinal'), 'SAC.dbo'
        );
        $select->joinInner(
                array('b' => 'Projetos'), 'a.idPronac = b.idPronac',
                array(''), 'SAC.dbo'
        );

        $select->where('a.stEstado = ?', 1);
        $select->where('a.stAcao = ?', 0);
        $select->where('b.AnoProjeto+b.Sequencial = ?', $pronac);

        return $this->fetchRow($select);
    } // fecha método buscarDados()

} // fecha class