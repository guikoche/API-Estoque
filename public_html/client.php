<?php
//Requisição POST para realizar a atualização dos novos valores de estoque 
//Passando os parametros CodRefProduto e QuantItens que esta sendo efetuado a compra 
    $url = 'http://localhost/api/public_html/api/produtos';

    $class = '/produtos';
    $param = '';

    $response = file_get_contents($url.$class.$param);
