<?php

    namespace App\Services;

    use App\Models\Produtos;

    class ProdutosService
    {
        public function get($id = null)
        {
            $produtos = new Produtos();

            if ($id !== null) {
                return $produtos->select($id);
            } else {
                return $produtos->selectAll();
            }
        }

        public function post()
        {
            $data = $_POST;

            $produtos = new Produtos();
            return $produtos->insert($data);
        }
}
