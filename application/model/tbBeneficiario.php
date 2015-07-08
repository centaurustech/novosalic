<?php
/**
 * DAO tbBeneficiario
 * @since 16/03/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbBeneficiario extends GenericModel
{
	protected $_banco  = "SAC";
	protected $_schema = "dbo";
	protected $_name   = "tbBeneficiario";


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
	 * Método para alterar
	 * @access public
	 * @param array $dados
	 * @param integer $where
	 * @return integer (quantidade de registros alterados)
	 */
	public function alterarDados($dados, $where)
	{
		$where = "idBeneficiario = " . $where;
		return $this->update($dados, $where);
	} // fecha método alterarDados()

        public function buscarUsandoCAST($idRelatorio)
        {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                    array('a' => $this->_schema . '.' . $this->_name),
                    array('a.idRelatorio', 'CAST(a.dsBeneficiario AS TEXT) AS dsBeneficiario', 'a.tpBeneficiario', 'tpBeneficiario', 'a.nrCNPJ', 'a.nrCPF', 'CAST(a.dsPublicoAlvo AS TEXT) AS dsPublicoAlvo', 'CAST(a.dsEntrega AS TEXT) AS dsEntrega')
            );
            $select->where('a.idRelatorio = ?', $idRelatorio);
    //            xd($select->assemble());
            return $this->fetchAll($select);
        }

        public function buscarJustificativaCAST($idRelatorio)
        {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                    array('a' => $this->_schema . '.' . $this->_name),
                    array(
                            'a.idRelatorio',
                            'a.nrCNPJ',
                            'a.nrCPF',
                            'a.dsBeneficiario',
                            'CAST(a.dsPublicoAlvo AS TEXT) AS dsPublicoAlvo', 
                            'CAST(a.dsEntrega AS TEXT) AS dsEntrega',
                            'a.tpBeneficiario',
                            'a.stCNPJ',
                            'a.stCPF',
                            'a.stPublicoAlvo',
                            'CAST(a.dsJustificativaAcompanhamento AS TEXT) AS dsJustificativaAcompanhamento',
                        )
            );
            $select->where('a.idRelatorio = ?', $idRelatorio);
    //            xd($select->assemble());
            return $this->fetchAll($select);
        }


} // fecha class