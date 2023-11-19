<?php

namespace App\Models;

class Produtos
{
    private $host = ""; // Endereço de hospedagem do banco de dados
    private $user = ""; // Nome do usuário do banco de dados
    private $password = ""; // Senha do banco de dados
    private $database = ""; // Nome do banco de dados
    private $SQL;

    public function connect()
    {
        $connPdo = new \PDO('mysql:host=' . $this->host . ';dbname=' . $this->database, $this->user, $this->password);
        $connPdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $connPdo;
    }

    public function select(int $id)
    {
        $connPdo = $this->connect();

        $sql = 'SELECT 
                p.Ativo,
                p.CodRefProduto,
                p.NomeProduto,
                p.PublicProduto,
                p.Usuario_idUser,
                i.ItensAtivo,
                i.ItensPublic,
                i.QuantItens,
                i.QuantItensVend
            FROM 
                produtos p
            INNER JOIN 
                itens i ON p.CodRefProduto = i.Produto_CodRefProduto 
            WHERE CodRefProduto = :CodRefProduto';
        $stmt = $connPdo->prepare($sql);
        $stmt->bindValue(':CodRefProduto', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } else {
            throw new \Exception("Nenhum produto encontrado!");
        }
    }

    public function selectAll()
    {
        $connPdo = $this->connect();

        $sql = 'SELECT 
                p.Ativo,
                p.CodRefProduto,
                p.NomeProduto,
                p.PublicProduto,
                p.Usuario_idUser,
                i.ItensAtivo,
                i.ItensPublic,
                i.QuantItens,
                i.QuantItensVend
            FROM 
                produtos p
            INNER JOIN 
                itens i ON p.CodRefProduto = i.Produto_CodRefProduto;';
        $stmt = $connPdo->prepare($sql);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            throw new \Exception("Nenhum produto encontrado!");
        }
    }

    public function insert($data)
    {
        $connPdo = $this->connect();

        try {
            $connPdo->beginTransaction();

            // Verifica se existe o CodRefProduto na tabela 'produtos'
            $sqlCheckProduto = 'SELECT CodRefProduto, QuantItens FROM produtos p INNER JOIN itens i ON p.CodRefProduto = i.Produto_CodRefProduto WHERE CodRefProduto = :codRefProduto';
            $stmtCheckProduto = $connPdo->prepare($sqlCheckProduto);
            $stmtCheckProduto->bindValue(':codRefProduto', $data['CodRefProduto']);
            $stmtCheckProduto->execute();

            $produtoData = $stmtCheckProduto->fetch();

            if ($produtoData) {
                $estoqueDisponivel = $produtoData['QuantItens'];

                // Verifica se a quantidade desejada é maior do que a disponível em estoque
                if ($data['QuantItens'] > $estoqueDisponivel) {
                    throw new \Exception('Quantidade desejada não possui estoque. Quantidade atual em estoque: ' . $estoqueDisponivel);
                }

                // Calcula a diferença entre a quantidade disponível e a quantidade desejada
                $novaQuantidade = $estoqueDisponivel - $data['QuantItens'];

                // Atualiza a QuantItens na tabela 'itens' usando o CodRefProduto
                $sqlUpdateItens = 'UPDATE itens SET QuantItens = :quantItens WHERE Produto_CodRefProduto = :produtoId';
                $stmtUpdateItens = $connPdo->prepare($sqlUpdateItens);
                $stmtUpdateItens->bindValue(':quantItens', $novaQuantidade);
                $stmtUpdateItens->bindValue(':produtoId', $produtoData['CodRefProduto']);
                $stmtUpdateItens->execute();

                $connPdo->commit();
                return 'Quantidade de itens atualizada com sucesso!';
            } else {
                throw new \Exception('Produto não encontrado!');
            }
        } catch (\Exception $e) {
            $connPdo->rollBack();
            throw new \Exception('Falha ao atualizar quantidade de itens: ' . $e->getMessage());
        }
    }
}


