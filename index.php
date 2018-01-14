<?php
require_once('Vindi.php');

$vindClass = new Vindi('coloque_a_chave_da_api_aqui');

$idCustomer = $vindClass->createCustomer('Nome do cliente', 'emaildocliente@email.com', 123);

if($idCustomer){
	$productId = $vindClass->createProduct('Nome do produto', 'status do produto', 123);
	if($productId){
		//bank_slip = Boleto bancário
		$vindClass->createBill($idCustomer, 'bank_slip', 1, $productId);
	}
}