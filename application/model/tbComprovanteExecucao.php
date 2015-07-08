<?php

/**
 * DAO tbComprovanteBeneficiario
 * @since 16/03/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright ï¿½ 2011 - Ministï¿½rio da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbComprovanteExecucao extends GenericModel
{
	protected $_banco  = "SAC";
	protected $_schema = "dbo";
	protected $_name   = "tbComprovanteExecucao";


	/**
	 * Método para cadastrar
	 * @access public
	 * @param array $dados
	 * @return integer (retorna o ï¿½ltimo id cadastrado)
	 */
	public function cadastrarDados($dados)
	{
		return $this->insert($dados);
	} // fecha método cadastrarDados()



	/**
	 * Método para alterar
	 * @access public
	 * @param array $dados
	 * @param integer $where
	 * @return integer (quantidade de registros alterados)
	 */
	public function alterarDados($dados, $where)
	{
		$where = "idComprovanteExecucao = " . $where;
		return $this->update($dados, $where);
	} // fecha método alterarDados()


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
                    array('b' => 'tbDocumento'),
                    'b.idDocumento = a.idDocumento',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('c' => 'tbArquivo'),
                    'c.idArquivo = b.idArquivo',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('d' => 'tbRelatorio'),
                    'd.idRelatorio = a.idRelatorio',
                    array('*'),
                    'SAC.dbo'
                    );
            $select->joinInner(
                    array('e' => 'tbTipoDocumento'),
                    'e.idTipoDocumento = b.idTipoDocumento',
                    array('dsTipoDocumento'),
                    'SAC.dbo'
                    );
            $select->joinLeft(
                    array('f' => 'tbRelatorioTrimestral'),
                    'f.idRelatorio = d.idRelatorio',
                    array(''),
                    'SAC.dbo'
                    );

            $select->where('d.idPRONAC = ?', $idpronac);
            $select->where('d.tpRelatorio = ?', $tpRelatorio);
            $select->where('f.stRelatorioTrimestral = 1');
//            xd($select->assemble());
            return $this->fetchAll($select);

        }

        /**
	 * Método para consultar os arquivos anexados (ESPECIFICO DO COMPROVANTE DE EXCUCAï¿½?O DO UC 25)
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
                    array('b' => 'tbDocumento'),
                    'b.idDocumento = a.idDocumento',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('c' => 'tbArquivo'),
                    'c.idArquivo = b.idArquivo',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('d' => 'tbRelatorio'),
                    'd.idRelatorio = a.idRelatorio',
                    array('*'),
                    'SAC.dbo'
                    );
            $select->joinInner(
                    array('e' => 'tbTipoDocumento'),
                    'e.idTipoDocumento = b.idTipoDocumento',
                    array('dsTipoDocumento'),
                    'SAC.dbo'
                    );
            $select->joinInner(
                    array('f' => 'tbRelatorioTrimestral'),
                    'f.idRelatorio = d.idRelatorio',
                    array(''),
                    'SAC.dbo'
                    );

            $select->where('d.idPRONAC = ?', $idpronac);
            $select->where('d.tpRelatorio = ?', $tpRelatorio);
            $select->where('f.stRelatorioTrimestral = 2');
            return $this->fetchAll($select);

        }

        /**
	 * Método para consultar os arquivos anexados (ESPECIFICO DO COMPROVANTE DE EXCUCAï¿½?O DO UC 25)
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
                    array('b' => 'tbDocumento'),
                    'b.idDocumento = a.idDocumento',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('c' => 'tbArquivo'),
                    'c.idArquivo = b.idArquivo',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('d' => 'tbRelatorio'),
                    'd.idRelatorio = a.idRelatorio',
                    array('*'),
                    'SAC.dbo'
                    );
            $select->joinInner(
                    array('e' => 'tbTipoDocumento'),
                    'e.idTipoDocumento = b.idTipoDocumento',
                    array('dsTipoDocumento'),
                    'SAC.dbo'
                    );
            $select->joinInner(
                    array('f' => 'tbRelatorioTrimestral'),
                    'f.idRelatorio = d.idRelatorio',
                    array(''),
                    'SAC.dbo'
                    );

            $select->where('d.idPRONAC = ?', $idpronac);
            $select->where('d.tpRelatorio = ?', $tpRelatorio);
            $select->where('f.stRelatorioTrimestral in (5,7)');
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
                    array('b' => 'tbDocumento'),
                    'b.idDocumento = a.idDocumento',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('c' => 'tbArquivo'),
                    'c.idArquivo = b.idArquivo',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('d' => 'tbRelatorio'),
                    'd.idRelatorio = a.idRelatorio',
                    array('*'),
                    'SAC.dbo'
                    );
            $select->joinInner(
                    array('e' => 'tbTipoDocumento'),
                    'e.idTipoDocumento = b.idTipoDocumento',
                    array('dsTipoDocumento'),
                    'SAC.dbo'
                    );
            $select->joinLeft(
                    array('f' => 'tbRelatorioTrimestral'),
                    'f.idRelatorio = d.idRelatorio',
                    array(''),
                    'SAC.dbo'
                    );

            $select->where('d.idPRONAC = ?', $idpronac);
            $select->where('d.tpRelatorio = ?', $tpRelatorio);
//            xd($select->assemble());
            return $this->fetchAll($select);

        }

        /**
	 * Método para consultar os arquivos anexados (ESPECIFICO DO COMPROVANTE DE EXCUCAï¿½?O DO UC 25)
	 * @access public
	 * @param array $dados
	 * @param integer $where
	 * @return integer (quantidade de registros alterados)
	 */
        public function buscarDocumentosPronac5($idpronac, $tpRelatorio) {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                    array('a' => $this->_schema . '.' . $this->_name)
            );
            $select->joinInner(
                    array('b' => 'tbDocumento'),
                    'b.idDocumento = a.idDocumento',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('c' => 'tbArquivo'),
                    'c.idArquivo = b.idArquivo',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('d' => 'tbRelatorio'),
                    'd.idRelatorio = a.idRelatorio',
                    array('*'),
                    'SAC.dbo'
                    );
            $select->joinInner(
                    array('e' => 'tbTipoDocumento'),
                    'e.idTipoDocumento = b.idTipoDocumento',
                    array('dsTipoDocumento'),
                    'SAC.dbo'
                    );
            $select->joinInner(
                    array('f' => 'tbRelatorioTrimestral'),
                    'f.idRelatorio = d.idRelatorio',
                    array(''),
                    'SAC.dbo'
                    );

            $select->where('d.idPRONAC = ?', $idpronac);
            $select->where('d.tpRelatorio = ?', $tpRelatorio);
            return $this->fetchAll($select);

        }

        /**
	 * Método para consultar os arquivos anexados (ESPECIFICO DO COMPROVANTE DE EXCUCAï¿½?O DO UC 25)
	 * @access public
	 * @param array $dados
	 * @param integer $where
	 * @return integer (quantidade de registros alterados)
	 */
        public function buscarDocumentosPronac6($idpronac, $tpRelatorio) {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                    array('a' => $this->_schema . '.' . $this->_name)
            );
            $select->joinInner(
                    array('b' => 'tbDocumento'),
                    'b.idDocumento = a.idDocumento',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('c' => 'tbArquivo'),
                    'c.idArquivo = b.idArquivo',
                    array('*'),
                    'BDCORPORATIVO.scCorp'
                    );
            $select->joinInner(
                    array('d' => 'tbRelatorio'),
                    'd.idRelatorio = a.idRelatorio',
                    array('*'),
                    'SAC.dbo'
                    );
            $select->joinInner(
                    array('e' => 'tbTipoDocumento'),
                    'e.idTipoDocumento = b.idTipoDocumento',
                    array('dsTipoDocumento'),
                    'SAC.dbo'
                    );

            $select->where('d.idPRONAC = ?', $idpronac);
            $select->where('d.tpRelatorio = ?', $tpRelatorio);
            return $this->fetchAll($select);

        }

} // fecha class