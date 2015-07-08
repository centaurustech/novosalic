<?php
class Movimentacao extends GenericModel
{
    protected $_banco = "SAC";
    protected $_schema = 'dbo';
    protected $_name = "tbMovimentacao";
    
    /**
     * Grava registro. Se seja passado um ID ele altera um registro existente
     * @param array $dados - array com dados referentes as colunas da tabela no formato "nome_coluna_1"=>"valor_1","nome_coluna_2"=>"valor_2"
     * @return ID do registro inserido/alterado ou FALSE em caso de erro
     */
    public function salvar($dados)
    {
        //DECIDINDO SE INCLUI OU ALTERA UM REGISTRO
        if(isset($dados['idMovimentacao']) && !empty ($dados['idMovimentacao'])){
            //UPDATE
            $rsAbrangencia = $this->find($dados['idMovimentacao'])->current();
        }else{
            //INSERT
            $dados['idMovimentacao'] = null;
            return $this->insert($dados);
            //$rsMovimentacao = $this->createRow();
        }
        
        //ATRIBUINDO VALORES AOS CAMPOS QUE FORAM PASSADOS
        $rsMovimentacao->idProjeto = $dados['idProjeto']; 
        $rsMovimentacao->Movimentacao = $dados['Movimentacao'];
        $rsMovimentacao->DtMovimentacao = $dados['DtMovimentacao'];
        $rsMovimentacao->stEstado = $dados['stEstado'];
        $rsMovimentacao->Usuario = $dados['Usuario']; 

        //SALVANDO O OBJETO
        $id = $rsMovimentacao->save();

        if($id){
            return $id;
        }else{
            return false;
        }
    }

    public function buscarStatusAtualProposta($idPreProjeto) {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from($this->_name);
        $slct->where('idProjeto = ? ', $idPreProjeto);
        $slct->where('stEstado = ? ', 0);
        $slct->order(array("DtMovimentacao DESC"));
        return $this->fetchRow($slct);
    }

    public function buscarTecCoordAdmissibilidade($idPronac, $idusuario=null) {

        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->distinct();
        $slct->from(
                array('mov' => $this->_schema . '.' . $this->_name), array()
        );
        
        $slct->joinInner(
                array('pre'=>'PreProjeto'),
                'pre.idPreProjeto = mov.idProjeto',
                array(),
                'SAC.dbo'
                );
        
        $slct->joinLeft(
                array('proj'=>'Projetos'),
                'proj.idProjeto = mov.idProjeto',
                array(),
                'SAC.dbo'
                );
        $slct->joinInner(
                array('usu'=>'Usuarios'),
                'usu.usu_codigo = mov.Usuario',
                array(),
                'TABELAS.dbo'
                );
        $slct->joinInner(
                array('age'=>'Agentes'),
                'age.CNPJCPF = usu.usu_identificacao',
                array('age.idAgente'),
                'AGENTES.dbo'
                );
        $slct->joinInner(
                array('nm'=>'Nomes'),
                'age.idAgente = nm.idAgente',
                array('Nome'=>'nm.Descricao'),
                'AGENTES.dbo'
                );
        
        $slct->joinInner(
                array('uog'=>'UsuariosXOrgaosXGrupos'),
                'uog.uog_usuario = usu.usu_codigo',
                array(),
                'TABELAS.dbo'
                );
        $slct->joinInner(
                array('gru'=>'Grupos'),
                'gru.gru_codigo = uog.uog_grupo',
                array('Perfil'=>'gru.gru_nome', 'cdPerfil'=>'gru.gru_codigo'),
                'TABELAS.dbo'
                );
        
        $slct->joinInner(
                array('org'=>'Orgaos'),
                'org.Codigo = uog.uog_orgao and org.Codigo = usu.usu_orgao',
                array('Orgao'=>'org.Sigla'),
                'SAC.dbo'
        );
        

        $slct->where('gru.gru_codigo in (92,131) ');
        $slct->where('proj.IdPRONAC = ? ', $idPronac);
        //$slct->where('usu.usu_codigo <> ? ', $idusuario);
//xd($slct->assemble());
        return $this->fetchAll($slct);
    }
}
?>
