<?php
/**
 * DAO tbComprovanteBeneficiario
 * @since 16/03/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbComprovanteBeneficiario extends GenericModel
{
	protected $_banco  = "SAC";
	protected $_schema = "dbo";
	protected $_name   = "tbComprovanteBeneficiario";

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
        public function buscarDocumentosPronac($idpronac, $tpRelatorio) {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                    array('a' => $this->_schema . '.' . $this->_name)
            );
            $select->joinInner(
                    array('d' => 'tbRelatorio'),
                    'd.idRelatorio = a.idRelatorio',
                    array('*'),
                    'SAC.dbo'
                    );
            $select->joinInner(
                    array('e' => 'tbDocumento'),
                    'e.idDocumento = a.idDocumento',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('f' => 'tbArquivo'),
                    'f.idArquivo = e.idArquivo',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('g' => 'tbTipoDocumento'),
                    'g.idTipoDocumento = e.idTipoDocumento',
                    array('dsTipoDocumento'),
                    'SAC.dbo'
                    );
            $select->joinInner(
                    array('h' => 'tbRelatorioTrimestral'),
                    'h.idRelatorio = d.idRelatorio',
                    array(''),
                    'SAC.dbo'
                    );

            $select->where('d.idPRONAC = ?', $idpronac);
            $select->where('d.tpRelatorio = ?', $tpRelatorio);
            $select->where('h.stRelatorioTrimestral = 1');
//            xd($select->assemble());
            return $this->fetchAll($select);

        }

        /**
	 * Método para consultar os arquivos anexados
	 * @access public
	 * @param array $dados
	 * @param integer $where
	 * @return integer (quantidade de registros alterados)
	 */
        public function buscarDocumentosPronac2($idpronac, $tpRelatorio) {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                    array('a' => $this->_schema . '.' . $this->_name)
            );
            $select->joinInner(
                    array('d' => 'tbRelatorio'),
                    'd.idRelatorio = a.idRelatorio',
                    array('*'),
                    'SAC.dbo'
                    );
            $select->joinInner(
                    array('e' => 'tbDocumento'),
                    'e.idDocumento = a.idDocumento',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('f' => 'tbArquivo'),
                    'f.idArquivo = e.idArquivo',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('g' => 'tbTipoDocumento'),
                    'g.idTipoDocumento = e.idTipoDocumento',
                    array('dsTipoDocumento'),
                    'SAC.dbo'
                    );
            $select->joinInner(
                    array('h' => 'tbRelatorioTrimestral'),
                    'h.idRelatorio = d.idRelatorio',
                    array(''),
                    'SAC.dbo'
                    );

            $select->where('d.idPRONAC = ?', $idpronac);
            $select->where('d.tpRelatorio = ?', $tpRelatorio);
            $select->where('h.stRelatorioTrimestral = 2');
//            xd($select->assemble());
            return $this->fetchAll($select);

        }


        /**
	 * Método para consultar os arquivos anexados
	 * @access public
	 * @param array $dados
	 * @param integer $where
	 * @return integer (quantidade de registros alterados)
	 */
        public function buscarDocumentosPronac3($idpronac, $tpRelatorio) {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                    array('a' => $this->_schema . '.' . $this->_name)
            );
            $select->joinInner(
                    array('d' => 'tbRelatorio'),
                    'd.idRelatorio = a.idRelatorio',
                    array('*'),
                    'SAC.dbo'
                    );
            $select->joinInner(
                    array('e' => 'tbDocumento'),
                    'e.idDocumento = a.idDocumento',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('f' => 'tbArquivo'),
                    'f.idArquivo = e.idArquivo',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('g' => 'tbTipoDocumento'),
                    'g.idTipoDocumento = e.idTipoDocumento',
                    array('dsTipoDocumento'),
                    'SAC.dbo'
                    );
            $select->joinInner(
                    array('h' => 'tbRelatorioTrimestral'),
                    'h.idRelatorio = d.idRelatorio',
                    array(''),
                    'SAC.dbo'
                    );

            $select->where('d.idPRONAC = ?', $idpronac);
            $select->where('d.tpRelatorio = ?', $tpRelatorio);
            $select->where('h.stRelatorioTrimestral in (5,7)');
//            xd($select->assemble());
            return $this->fetchAll($select);

        }

        /**
	 * Método para consultar os arquivos anexados
	 * @access public
	 * @param array $dados
	 * @param integer $where
	 * @return integer (quantidade de registros alterados)
	 */
        public function buscarDocumentosPronac4($idpronac, $tpRelatorio) {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                    array('a' => $this->_schema . '.' . $this->_name)
            );
            $select->joinInner(
                    array('d' => 'tbRelatorio'),
                    'd.idRelatorio = a.idRelatorio',
                    array('*'),
                    'SAC.dbo'
                    );
            $select->joinInner(
                    array('e' => 'tbDocumento'),
                    'e.idDocumento = a.idDocumento',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('f' => 'tbArquivo'),
                    'f.idArquivo = e.idArquivo',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('g' => 'tbTipoDocumento'),
                    'g.idTipoDocumento = e.idTipoDocumento',
                    array('dsTipoDocumento'),
                    'SAC.dbo'
                    );
            $select->joinInner(
                    array('h' => 'tbRelatorioTrimestral'),
                    'h.idRelatorio = d.idRelatorio',
                    array(''),
                    'SAC.dbo'
                    );

            $select->where('d.idPRONAC = ?', $idpronac);
            $select->where('d.tpRelatorio = ?', $tpRelatorio);
//            xd($select->assemble());
            return $this->fetchAll($select);

        }


} // fecha class