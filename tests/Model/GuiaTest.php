<?php
/**
 * 
 * @author Caio Lucena <caioflucena@gmail.com>
 * @todo Criar uma guia apos finalizar os testes, deixando assim a mesma para o teste de questao
 */
class GuiaTest extends PHPUnit_Framework_TestCase
{
    /**
     * 
     */
    protected function tearDown()
    {
        $guiaModel = new GuiaModel();
        $result = $guiaModel->pesquisarPorEditalModuloCategoria(1);

        $reflection = new ReflectionObject($guiaModel);
        $property = $reflection->getProperty('table');
        $property->setAccessible(true);
        $table = $property->getValue($guiaModel);
        $select = $table->select()->setIntegrityCheck(false)->from('tbGuia', 'idGuia')->where('nmGuia = ?', 'Guia teste');
        $result = $table->fetchAll($select)->toArray();
        foreach ($result as $guia) {
            $table->delete(array('idGuia = ?' => $guia['idGuia']));
        }
    }

    /**
     * 
     */
    public function testCadastrar()
    {
        $GuiaModel = new GuiaModel(
            null,
            1,
            'Guia teste',
            'Texto Guia teste'
        );
        $GuiaModel->cadastrar();
        $guia = $GuiaModel->toStdClass();
        $this->assertNotNull($guia->guia);
    }

    /**
     * @expectedException Exception 
     */
    public function testCadastrarValidar()
    {
        $GuiaModel = new GuiaModel();
        $GuiaModel->cadastrar();
    }

    /**
     * 
     */
    public function testPesquisarPorCategoria()
    {
        $this->testCadastrar();
        $GuiaModel = new GuiaModel();
        $result = $GuiaModel->pesquisarPorEditalModuloCategoria(1);
        $this->assertNotEmpty($result);
    }

    /**
     * 
     */
    public function testPesquisarPorModulo()
    {
        $this->testCadastrar();
        $GuiaModel = new GuiaModel();
        $result = $GuiaModel->pesquisarPorEditalModuloCategoria(null, 1007);
        $this->assertNotEmpty($result);
    }

    /**
     * 
     */
    public function testPesquisarPorEdital()
    {
        $this->testCadastrar();
        $GuiaModel = new GuiaModel();
        $result = $GuiaModel->pesquisarPorEditalModuloCategoria(null, null, 2068);
        $this->assertNotEmpty($result);
    }

    /**
     * 
     */
    public function testPesquisarPorEditalModuloCategoria()
    {
        $this->testCadastrar();
        $GuiaModel = new GuiaModel();
        $result = $GuiaModel->pesquisarPorEditalModuloCategoria(1, 1007, 2068);
        $this->assertNotEmpty($result);
    }
}