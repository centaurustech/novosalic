<?php
/**
 * DAO tbDocumentoAceitacao
 * @since 16/03/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbDocumentoAceitacao extends GenericModel
{
	protected $_banco  = "SAC";
	protected $_schema = "dbo";
	protected $_name   = "tbDocumentoAceitacao";

        /**
	 * Método para cadastrar
	 * @access public
	 * @param array $dados
	 * @return integer (retorna o último id cadastrado)
	 */
	public function cadastrarDados($dados)
	{
		return $this->insert($dados);
	} // fecha método cadastrarDados()


        /**
	 * Método para consultar os arquivos anexados
	 * @access public
	 * @param array $dados
	 * @param integer $where
	 * @return integer (quantidade de registros alterados)
	 */
        public function buscarDocumentosPronac($idpronac) {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                    array('a' => $this->_schema . '.' . $this->_name)
            );
            $select->joinInner(
                    array('b' => 'tbRelatorioConsolidado'),
                    'b.idRelatorioConsolidado = a.idRelatorioConsolidado',
                    array('*'),
                    'SAC.dbo'
                    );
            $select->joinInner(
                    array('c' => 'tbRelatorio'),
                    'c.idRelatorio = b.idRelatorio',
                    array('*'),
                    'SAC.dbo'
                    );
            $select->joinInner(
                    array('d' => 'tbDocumento'),
                    'd.idDocumento = a.idDocumento',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('e' => 'tbArquivo'),
                    'e.idArquivo = d.idArquivo',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('f' => 'tbTipoDocumento'),
                    'f.idTipoDocumento = d.idTipoDocumento',
                    array('dsTipoDocumento'),
                    'BDCORPORATIVO.scCorp'
                    );

            $select->where('c.idPRONAC = ?', $idpronac);
//            xd($select->query());
            return $this->fetchAll($select);

        }

} // fecha class