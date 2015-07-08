<?php
/**
 * ManterAgentesDAO
 * @author Equipe RUP - Politec
 * @since 09/08/2010
 * @version 1.0
 * @package application
 * @subpackage application.model.DAO
 * @link http://www.politec.com.br
 * @copyright © 2010 - Politec - Todos os direitos reservados.
 */

class ManterAgentesDAO extends Zend_Db_Table
{
	/**
	 * Método para buscar agentes
	 * @access public
	 * @static
	 * @param string $cnpjcpf
	 * @param string $nome
	 * @param integer $idAgente
	 * @return object
	 */
	public static function buscarAgentes($cnpjcpf = null, $nome = null, $idAgente = null)
	{
		$sql = "SELECT DISTINCT A.idAgente
					,A.CNPJCPF
					,A.CNPJCPFSuperior
					,A.TipoPessoa
					,N.Descricao Nome
					,E.Cep CEP
					,E.UF
					,E.Status
					,U.Sigla dsUF
					,E.Cidade
					,M.Descricao dsCidade
					,E.TipoEndereco
					,E.idEndereco
					,VE.Descricao dsTipoEndereco
					,E.TipoLogradouro
					,VL.Descricao dsTipoLogradouro
					,E.Logradouro
					,E.Numero
					,E.Complemento
					,E.Bairro
					,T.stTitular
					,E.Divulgar DivulgarEndereco
					,E.Status EnderecoCorrespondencia
					,T.cdArea
					,SA.Descricao dsArea
					,T.cdSegmento
					,SS.Descricao dsSegmento

				FROM AGENTES.dbo.Agentes A 
					LEFT JOIN AGENTES.dbo.Nomes N on N.idAgente = A.idAgente  
					LEFT JOIN AGENTES.dbo.EnderecoNacional E on E.idAgente = A.idAgente 
					LEFT JOIN AGENTES.dbo.Municipios M  on M.idMunicipioIBGE = E.Cidade 
					LEFT JOIN AGENTES.dbo.UF U on U.idUF = E.UF 
					LEFT JOIN AGENTES.dbo.Verificacao VE on VE.idVerificacao = E.TipoEndereco  
					LEFT JOIN AGENTES.dbo.Verificacao VL on VL.idVerificacao = E.TipoLogradouro 
					LEFT JOIN AGENTES.dbo.tbTitulacaoConselheiro T on T.idAgente = A.idAgente   
					LEFT JOIN AGENTES.dbo.Visao V on V.idAgente = A.idAgente 
					LEFT JOIN SAC.dbo.Area SA on SA.Codigo = T.cdArea 
					LEFT JOIN SAC.dbo.Segmento SS on SS.Codigo = T.cdSegmento

				WHERE (A.TipoPessoa = 0 OR A.TipoPessoa = 1) ";

		if (!empty($cnpjcpf)) // busca pelo cpf/cnpj
		{
			$sql.= " AND A.CNPJCPF = '".$cnpjcpf."'";
		}
		if (!empty($nome)) // filtra pelo nome
		{
			$sql.= " AND N.Descricao LIKE '".$nome."%'";
		}
		if (!empty($idAgente)) // busca de acordo com o id do agente
		{
			$sql.= " AND A.idAgente =". $idAgente;
		}

		$sql.= " ORDER BY E.Status Desc, N.Descricao Asc ";

		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		return $db->fetchAll($sql);
	} // fecha método buscarAgentes()



	/**
	 * Método para buscar agentes vinculados
	 * @access public
	 * @static
	 * @param string $cnpjcpfSuperior
	 * @param string $nome
	 * @param integer $idAgente
	 * @param integer $idVinculado
	 * @param integer $idVinculoPrincipal
	 * @return object
	 */
	public static function buscarVinculados($cnpjcpfSuperior = null, $nome = null, $idAgente = null, $idVinculado = null, $idVinculoPrincipal = null)
	{
		$sql = "SELECT a.idAgente
				,a.CNPJCPF
				,a.CNPJCPFSuperior 
				,n.Descricao AS Nome

			FROM Agentes.dbo.Agentes a
				,Agentes.dbo.Nomes n
				,Agentes.dbo.Visao vis
				,Agentes.dbo.Verificacao ver
				,Agentes.dbo.Vinculacao vin
				,Agentes.dbo.Tipo tp

			WHERE a.idAgente = n.idAgente
				AND a.idAgente = vis.idAgente
				AND a.idAgente = vin.idAgente
				AND tp.idTipo = ver.IdTipo
				AND ver.idVerificacao = vis.Visao
				AND (a.TipoPessoa = 0 OR a.TipoPessoa = 1) 
				AND (n.TipoNome = 18 OR n.TipoNome = 19)  
				AND vis.Visao = 198 ";

		if (!empty($cnpjcpfSuperior)) // busca pelo cnpj/cpf com o vinculo principal
		{
			$sql.= " AND a.CNPJCPFSuperior = '$cnpjcpfSuperior'";
		}
		if (!empty($nome)) // filtra pelo nome
		{
			$sql.= " AND n.Descricao LIKE '$nome%'";
		}
		if (!empty($idAgente)) // busca pelo idAgente
		{
			$sql.= " AND vin.idAgente = $idAgente";
		}
		if (!empty($idVinculado)) // busca pelo idVinculado
		{
			$sql.= " AND vin.idVinculado = $idVinculado";
		}
		if (!empty($idVinculoPrincipal)) // busca pelo idVinculoPrincipal
		{
			$sql.= " AND vin.idVinculoPrincipal = $idVinculoPrincipal";
		}

		$sql.= " ORDER BY n.Descricao";

		
		
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		return $db->fetchAll($sql);		
	} // fecha método buscarVinculados()



	/**
	 * Método para buscar os endereços do agente
	 * @access public
	 * @static
	 * @param integer $idAgente
	 * @return object
	 */
	public static function buscarEnderecos($idAgente = null)
	{
		$sql = "SELECT  idEndereco,
				idAgente,
				Logradouro,
                TipoLogradouro,
                VL.Descricao as dsTipoLogradouro,
				Numero,
				Bairro,
				Complemento,
				Cep,
				Status,
				Divulgar,
				Usuario,
				VE.Descricao TipoEndereco,
                VE.idVerificacao as CodTipoEndereco,
				M.Descricao Municipio,
                M.idMunicipioIBGE CodMun,
				U.Sigla UF,
                U.idUF CodUF
                        FROM AGENTES.dbo.EnderecoNacional E 
							LEFT JOIN AGENTES.dbo.Verificacao VE on VE.idVerificacao = E.TipoEndereco 
							LEFT JOIN AGENTES.dbo.Municipios M  on M.idMunicipioIBGE = E.Cidade 
							LEFT JOIN AGENTES.dbo.UF U on U.idUF = E.UF
							LEFT JOIN AGENTES.dbo.Verificacao VL on VL.idVerificacao = E.TipoLogradouro
					
			WHERE E.idAgente = '".$idAgente."' ";
		
		$sql.= "ORDER BY Status DESC";

		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		return $db->fetchAll($sql);
	} // fecha método buscarEnderecos()








        /**
	 * Método para buscar os e-mails do agente
	 * @access public
	 * @static
	 * @param integer $idAgente
	 * @return object
	 */
	public static function buscarEmails($idAgente = null)
	{
		$sql = "SELECT I.idInternet
					,I.idAgente
					,I.TipoInternet
					,V.Descricao tipo
					,I.Descricao
					,I.Status
					,I.Divulgar

				FROM AGENTES.dbo.Internet I
					,AGENTES.dbo.Tipo T
					,AGENTES.dbo.Verificacao V

				WHERE I.TipoInternet = V.idVerificacao
					AND T.idTipo = V.IdTipo ";

		if (!empty($idAgente)) // busca de acordo com o id do agente
		{
			$sql.= " AND I.idAgente = $idAgente";
		}

		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		return $db->fetchAll($sql);
	} // fecha método buscarEmails()



	/**
	 * Método para buscar os telefones do agente
	 * @access public
	 * @static
	 * @param integer $idAgente
	 * @return object
	 */
	public static function buscarFones($idAgente = null)
	{
		$sql = "SELECT
                    Tl.idTelefone,
                    ddd.Codigo as DDD,
                    ddd.Codigo as Codigo,
                    Ag.IdAgente,
                    Tl.TipoTelefone,
                    CASE
                    WHEN Tl.TipoTelefone = 22 or Tl.TipoTelefone = 24
                    THEN 'Residencial'
                    WHEN Tl.TipoTelefone = 23 or Tl.TipoTelefone = 25
                    THEN 'Comercial'
                    WHEN Tl.TipoTelefone = 26
                    THEN 'Celular'
                    WHEN Tl.TipoTelefone = 27
                    THEN 'Fax'
                    END as dsTelefone,
                    Uf.Sigla as ufSigla,
                    Tl.Numero,
                    Tl.Divulgar
                FROM AGENTES.dbo.Telefones Tl
                    INNER JOIN AGENTES.dbo.Uf as Uf on Uf.idUF = Tl.UF
                    INNER JOIN AGENTES.dbo.Agentes Ag on Ag.IdAgente = Tl.IdAgente
                    LEFT JOIN AGENTES.dbo.DDD ddd On Tl.DDD = ddd.Codigo ";

		if (!empty($idAgente)){ // busca de acordo com o id do agente
			$sql.= " WHERE Tl.idAgente = $idAgente";
		}
        
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		return $db->fetchAll($sql);
	} // fecha método buscaFones()



	/**
	 * Método para buscar as áreas culturais
	 * @access public
	 * @static
	 * @param void
	 * @return object
	 */
	public static function buscarAreasCulturais()
	{
		$sql = "SELECT Codigo AS id
					,Descricao AS descricao

				FROM SAC.dbo.Area 

				ORDER BY Descricao;";

		try
		{
			$db = Zend_Registry::get('db');
			$db->setFetchMode(Zend_DB::FETCH_OBJ);
		}
		catch (Zend_Exception_Db $e)
		{
			$this->view->message = "Erro ao buscar Área Cultural: " . $e->getMessage();
		}
		return $db->fetchAll($sql);
	} // fecha método buscarAreasCulturais()



	/**
	 * Método para cadastrar dados do agente
	 * @access public
	 * @static
	 * @param array $dados
	 * @return boolean
	 */
	public static function cadastrarAgente($dados)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$insert = $db->insert('AGENTES.dbo.Agentes', $dados); // cadastra

		if ($insert)
		{
			return true;
		}
		else
		{
			return false;
		}
	} // fecha método cadastrarAgente()

	
	/**
	 * Método para cadastrar dados do agente
	 * @access public
	 * @static
	 * @param array $dados
	 * @return int (idAgente)
	 */
	public static function cadastraAgente($dados)
	{
		
		//INSTANCIANDO UM OBJETO DE ACESSO AOS DADOS DA TABELA
        $Agentes = new Agentes();

        $rsAgente = $Agentes->createRow();

        //ATRIBUINDO VALORES AOS CAMPOS QUE FORAM PASSADOS

if(isset($dados['stTipoRespPergunta'])){ $rsAgente->stTipoRespPergunta = $dados['stTipoRespPergunta']; }
        if(isset($dados['dsPergunta'])){ $rsAgente->dsPergunta = $dados['dsPergunta']; }

if(isset($dados['dtCadastramento'])){ $rsAgente->dtCadastramento = $dados['dtCadastramento']; }

if(isset($dados['idPessoaCadastro'])){ $rsAgente->idPessoaCadastro = $dados['idPessoaCadastro']; }
       
        //SALVANDO O OBJETO CRIADO
        $id = $rsAgente->save();

        if($id){
            return $id;
        }else{
            return false;
        }
		
		
		
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		
		$db->insert('AGENTES.dbo.Agentes', $dados); 
		return $db->lastInsertId();
		
	} 



	/**
	 * Método para alterar dados do agente
	 * @access public
	 * @static
	 * @param integer $idAgente
	 * @param array $dados
	 * @return boolean
	 */
	public static function alterarAgente($idAgente, $dados)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$where = "idAgente = " . $idAgente; // condição para alteração

		$update = $db->update('AGENTES.dbo.Agentes', $dados, $where); // altera

		if ($update)
		{
			return true;
		}
		else
		{
			return false;
		}
	} // fecha método alterarAgente()



	/**
	 * Método para cadastrar o vínculo entre os agentes
	 * @access public
	 * @static
	 * @param array $dados
	 * @return boolean
	 */
	public static function cadastrarVinculados($dados)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$insert = $db->insert('AGENTES.dbo.Vinculacao', $dados); // cadastra

		if ($insert)
		{
			return true;
		}
		else
		{
			return false;
		}
	} // fecha método cadastrarVinculados()

} // fecha class