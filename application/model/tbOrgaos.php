<?php
/**
 * DAO Orgaos
 * @since 16/03/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbOrgaos extends GenericModel
{
	protected $_banco  = "TABELAS";
	protected $_schema = "dbo";
	protected $_name   = "Orgaos";


	public function orgaosXprojetos($idOrgao){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                array('oxp'=>'dbo.Orgaos'),
                array('org_superior')
        );
        $select->where('oxp.org_codigo = ?', $idOrgao);
//        xd($select->assemble());
        return $this->fetchAll($select);
    }

} // fecha class