<?php
/**
 * Description of Projetos
 *
 * @author augusto
 */
class Enquadramento extends GenericModel
{
	protected $_name   = "Enquadramento";
	protected $_schema = "dbo";
	protected $_banco  = "SAC";



    public function alterarEnquadramento($dados)
    {
        $id = null;
        //INSTANCIANDO UM OBJETO DE ACESSO AOS DADOS DA TABELA
        $tmpTbl = new Enquadramento();
        $tmpTbl = $tmpTbl->find($dados['IdEnquadramento'])->current();

        if($tmpTbl){
            //ATRIBUINDO VALORES AOS CAMPOS QUE FORAM PASSADOS
            $tmpTbl->Enquadramento   = $dados['Enquadramento'];
            $tmpTbl->DtEnquadramento = $dados['DtEnquadramento'];
            $tmpTbl->Logon = $dados['Logon'];
            $id = $tmpTbl->save();
        }
        if($id){
            return $id;
        }else{
            return false;
        }
    }



	/**
	 * Método para buscar o enquadramento do projeto
	 * @access public
	 * @param integer $idPronac
	 * @param string $pronac
	 * @param boolean $buscarTodos (informa se busca todos ou somente um)
	 * @return array || object
	 */
	public function buscarDados($idPronac = null, $pronac = null, $buscarTodos = true)
	{
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->from($this->_name);

		// busca pelo idPronac
		if (!empty($idPronac))
		{
			$select->where("IdPRONAC = ?", $idPronac);
		}

		// busca pelo pronac
		if (!empty($pronac))
		{
			$select->where("AnoProjeto+Sequencial = ?", $pronac);
		}

		return $buscarTodos ? $this->fetchAll($select) : $this->fetchRow($select);
	} // fecha método buscarDados()



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
	 * @param integer $idEnquadramento
	 * @param integer $idPronac
	 * @param string $pronac
	 * @return integer (quantidade de registros alterados)
	 */
	public function alterarDados($dados, $idEnquadramento = null, $idPronac = null, $pronac = null)
	{
		// altera pelo id do enquadramento
		if (!empty($idEnquadramento))
		{
			$where = "IdEnquadramento = " . $idEnquadramento;
		}

		// altera pelo id do pronac
		else if (!empty($idPronac))
		{
			$where = "IdPRONAC = " . $idPronac;
		}

		// altera pelo pronac
		else if (!empty($pronac))
		{
			$where = "AnoProjeto+Sequencial = " . $pronac;
		}

		return $this->update($dados, $where);
	} // fecha método alterarDados()



	/**
	 * Método para excluir
	 * @access public
	 * @param integer $idEnquadramento
	 * @param integer $idPronac
	 * @param string $pronac
	 * @return integer (quantidade de registros excluídos)
	 */
	public function excluirDados($idEnquadramento = null, $idPronac = null, $pronac = null)
	{
		// exclui pelo id do enquadramento
		if (!empty($idEnquadramento))
		{
			$where = "IdEnquadramento = " . $idEnquadramento;
		}

		// exclui pelo id do pronac
		else if (!empty($idPronac))
		{
			$where = "IdPRONAC = " . $idPronac;
		}

		// exclui pelo pronac
		else if (!empty($pronac))
		{
			$where = "AnoProjeto+Sequencial = " . $pronac;
		}

		return $this->delete($where);
	} // fecha método excluirDados()

} // fecha class