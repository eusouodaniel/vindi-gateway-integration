<?php
class Vindi{

    /**
     * Tempo máximo de execução
     *
     * @param int
    */
    private $apiTimeout = 60;

    /**
     * Chave da API
     *
     * @param string
    */
    private $apiKey;

    /**
     * Início do curl manual
     *
     * @param boolean
    */
    private $curlInitManually = false;

    /**
     * Fechamento do curl manual
     *
     * @param resource
    */
    private $curlCloseManually = false;

    /**
     * Chamada na API via curl
     *
     * @param resource
    */
    private $ch;

    /**
     * Resposta da API
     *
     * @param array
    */
    private $response;

    /**
     * Corpo da resposta da API
     *
     * @param resource
    */
    private $body;

    public function __construct($api_key){
    	$this->apiKey = $api_key;
    }

    /**
     * Abrindo conexão
     *
     * @return void
    */
    public function curlInit(){
    	$this->ch = curl_init();
    	curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    	curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->apiTimeout);
    	curl_setopt($this->ch, CURLOPT_USERPWD, $this->apiKey . ':');
    	curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($this->ch, CURLOPT_HEADER, 1);
    }

    /**
     * Fechando conexão
     *
     * @return void
    */
    public function curlClose(){
    	curl_close($this->ch);
    }

    /**
     * Executando chamada
     *
     * @return void
    */
    private function execCall($endPoint, $params = null){
    	try{
            if($this->curlInitManually === false){
            	$this->curlInit();
            }

            curl_setopt($this->ch, CURLOPT_URL, $endPoint);

            if($params){
            	curl_setopt($this->ch, CURLOPT_POST, 1);
            	curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($params));
            }

            $response   = curl_exec($this->ch);
            $error      = curl_error($this->ch);
            $headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);

            $result = array(
            	'header'     => substr($response, 0, $headerSize),
            	'body'       => substr($response, $headerSize),
            	'curl_error' => $error,
            	'http_code'  => curl_getinfo($this->ch, CURLINFO_HTTP_CODE),
            	'last_url'   => curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL)
            );

            if(!empty($result['body'])){
            	$this->body = json_decode($result['body']);
            }

            if($this->curlCloseManually === false){
            	$this->curlClose();
            }

            return $this->response = $result;

        }catch(exception $e){

        }
    }

    /**
     * Busca o Curl do response
     *
     * @return array | null
    */
    public function getResponse(){
    	return $this->response;
    }

    /**
     * Busca o body do response
     *
     * @return resource | null
    */
    public function getBody(){
    	return $this->body;
    }

    /**
     * Busca na API todos os metódos de pagamento
     *
     * @return object
    */
    public function getPaymentMethods(){
    	$endPoint = 'https://app.vindi.com.br:443/api/v1/payment_methods';

    	return $this->execCall($endPoint);
    }

    /**
     * Criar um novo cliente
    */
    public function createCustomer($name, $email, $code){
    	$endPoint = 'https://app.vindi.com.br:443/api/v1/customers';

    	$params = array(
    		'name'  => $name,
    		'email' => $email,
    		'code'  => $code,
    	);

    	$response = $this->execCall($endPoint, $params);

    	if($response){
    		return $this->getBody()->customer->id;
    	}

    	return false;
    }

    /**
     * Criar um novo produto
     *
	*/
	public function createProduct($name, $status, $code){
		$endPoint = 'https://app.vindi.com.br:443/api/v1/products';

		$params = array(
			"name" => "",
			"code" => "",
			"unit" => "",
			"status" => "",
			"pricing_schema" => array
			(
				"price" => 0,
				"minimum_price" => 0,
				"schema_type" => "",
				"pricing_ranges" => array
				(
					array
					(
						"start_quantity" => 0,
						"end_quantity" => 0,
						"price" => 0,
						"overage_price" => 0
					)
				)
			),
			//"metadata": {}
		);

		$response = $this->execCall($endPoint, $params);

		if($response){
			return $this->getBody()->customer->id;
		}

		return false;
	}

    /**
     * Criar um perfil de pagamento
    */
    public function createPaymentProfile($holder_name, $card_expiration, $card_number, $card_cvv, $customer_id){
    	$endPoint = 'https://app.vindi.com.br:443/api/v1/payment_profiles';

    	$params = array(
    		'holder_name'     => $holder_name,
    		'card_expiration' => $card_expiration,
    		'card_number'     => $card_number,
    		'card_cvv'        => $card_cvv,
    		'customer_id'     => $customer_id,
    	);

    	return $this->execCall($endPoint, $params);
    }

    /**
     * Criar compra
     * @return Objeto da API
    */
    public function createBill($customer_id, $payment_method_code, $amount, $product_id){
    	$endPoint = 'https://app.vindi.com.br:443/api/v1/bills';

    	$params = array(
    		'customer_id'         => $customer_id,
    		'payment_method_code' => $payment_method_code,
    		'bill_items'          => array(
    			array(
    				'product_id'  => $product_id,
    				'amount'      => $amount
    			)
    		)
    	);

    	return $this->execCall($endPoint, $params);
    }

    /**
     * Buscar determinada compra
    */
    public function getBill($id){
    	$endPoint = 'https://app.vindi.com.br:443/api/v1/bills/'.$id;

    	return $this->execCall($endPoint);
    }
}