<?php

/**
 * Description of VisaoModel
 *
 * @author colaborador
 */
class VisaoModel implements ModelInterface
{

    /**
     * 
     */
    const SITUACAO_ATIVO = 'A';

    /**
     *
     * @var Situacao
     */
    private $table = null;

    /**
     * 
     */
    public function __construct()
    {
        $this->table = new Visao();
    }

    /**
     * 
     * @throws Exception
     */
    public function atualizar()
    {
        throw new Exception('Método não implementado');
    }

    /**
     * 
     * @param type $id
     * @throws Exception
     */
    public function buscar($id = null)
    {
        throw new Exception('Método não implementado');
    }

    /**
     * 
     * @param type $id
     * @throws Exception
     */
    public function deletar($id)
    {
        throw new Exception('Método não implementado');
    }

    /**
     * 
     * @throws Exception
     */
    public function salvar()
    {
        throw new Exception('Método não implementado');
    }

    /**
     * 
     * @param type $cpfCnpjAgente
     * @param type $tipoVisao
     */
    public function adicionaVisao($cpfCnpjAgente, $tipoVisao)
    {
        $agentesTable = new Agentes();

        $select = $this->table->select()
          ->setIntegrityCheck(false)
          ->from(array('Visao' => $this->table->info(Zend_Db_Table::NAME)))
          ->joinInner(array('Agentes', $agentesTable->info(Zend_Db_Table::NAME)), 'Agentes.idAgente = Visao.idAgente')
          ->where('Agentes.CNPJCPF = ?', $cpfCnpjAgente)
          ->where('Visao.Visao = ?', $tipoVisao);

        $visaoRow = $this->table->fetchRow($select);

        if (empty($visaoRow)) {
            $agenteRow = $agentesTable->fetchRow($agentesTable->select()->where('CNPJCPF = ?', $cpfCnpjAgente));
            $this->table->inserir(
                array(
                    'idAgente' => $agenteRow->idAgente,
                    'Visao' => $tipoVisao,
                    'Usuario' => Zend_Auth::getInstance()->getIdentity()->usu_codigo,
                    'stAtivo' => self::SITUACAO_ATIVO
                )
            );
        }
    }

}
