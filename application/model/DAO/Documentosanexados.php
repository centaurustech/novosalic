<?php
Class Documentosanexados extends Zend_Db_Table{

       	protected $_name    = 'SAC.dbo.Projetos';

       	public static function buscar($pronac)
       	{
       		$sql = "select 
       				CONVERT(CHAR(10),Do.dtDocumento,103) as Data, 
       				Td.dsTipoDocumento, Do.NoArquivo, Pr.IdPRONAC, Pr.NomeProjeto
				from 
					SAC.dbo.Projetos Pr
					LEFT JOIN SAC.dbo.tbDocumento Do ON Do.IdPRONAC = Pr.IdPRONAC
					LEFT JOIN BDCORPORATIVO.scCorp.tbArquivo Ar on Ar.idArquivo = Do.idDocumento
					LEFT JOIN SAC.dbo.tbTipoDocumento Td on Do.idTipoDocumento = Do.idTipoDocumento
					where Pr.IdPRONAC= " . $pronac . "";
       		
       		
       		$db  = Zend_Registry::get('db');
			$db->setFetchMode(Zend_DB::FETCH_OBJ);
			$resultado = $db->fetchAll($sql);

			return $resultado;
       	}
       	
       	public function inserirArquivo($name,$fileType)
		{
			$name     = $_FILES['arquivo']['name']; // nome
			$arquivoTemp     = $_FILES['arquivo']['tmp_name']; // nome temporário
			$fileType     = $_FILES['arquivo']['type']; // tipo
			$arquivoTamanho  = $_FILES['arquivo']['size']; // tamanho
			if (!empty($name))
			{
				$arquivoExtensao = Upload::getExtensao($name); // extensão
			}
			if (!empty($arquivoTemp))
			{
				$arquivoBinario  = Upload::setBinario($arquivoTemp); // binário
				$arquivoHash     = Upload::setHash($arquivoTemp); // hash
			}
			
				if (empty($arquivoTemp)) // nome do arquivo
				{
					throw new Exception("Por favor, informe o arquivo!");
				}
				else if ($arquivoExtensao == 'exe' || $arquivoExtensao == 'bat' || 
				$fileType == 'application/exe' || $fileType == 'application/x-exe' || 
				$fileType == 'application/dos-exe') // extensão do arquivo
				{
					throw new Exception("A extensão do arquivo é inválida!");
				}
				else if ($arquivoTamanho > 10485760) // tamanho do arquivo: 10MB
				{
					throw new Exception("O arquivo não pode ser maior do que 10MB!");
				}
				// faz o cadastro no banco de dados
				else
				{
					
				}
			$tbArquivo = "INSERT INTO BDCORPORATIVO.scCorp.tbArquivo " .
								"(nmArquivo, sgExtensao, dsTipo, dtEnvio ,stAtivo)  " .
							"VALUES ('$name', '$fileType', 'application/pdf', GETDATE(),'A')";
				$db = Zend_Registry :: get('db');
					$db->setFetchMode(Zend_DB :: FETCH_OBJ);
			$resultado = $db->query();
			return $resultado;
		}
		
		public function inserirArquivoImagem($idGeradoArquivo,$data)
		{
			$db = Zend_Registry :: get('db');
			$db->setFetchMode(Zend_DB :: FETCH_OBJ);
			$tbArquivoImagem = "INSERT INTO BDCORPORATIVO.scCorp.tbArquivoImagem " .
								"(idArquivo,biArquivo) " .
							"VALUES ($idGeradoArquivo,$data)";
			$resultado = $db->query();
			return $resultado;
		}
		
		public function ultimoIdArquivo() 
		{
			$db = Zend_Registry :: get('db');
			$db->setFetchMode(Zend_DB :: FETCH_OBJ);				
			$idGerado = $db->fetchOne("SELECT MAX(idArquivo) as id from BDCORPORATIVO.scCorp.tbArquivo");	
			return $idGerado;
		}
	
       	
}