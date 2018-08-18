<?php
class mpController extends controller {

	private $user;

    public function __construct() {
        parent::__construct();
    }

    public function index() {
    	$store = new Store();
    	$users = new Users();
    	$cart = new Cart();
    	$purchases = new Purchases();

        $dados = $store->getTemplateData();
        $dados['error'] = '';

        if(!empty($_POST['name'])) {

        	$name = addslashes($_POST['name']);
	        $cpf = addslashes($_POST['cpf']);
	        $telefone = addslashes($_POST['telefone']);
	        $email = addslashes($_POST['email']);
	        $pass = addslashes($_POST['pass']);
	        $cep = addslashes($_POST['cep']);
	        $rua = addslashes($_POST['rua']);
	        $numero = addslashes($_POST['numero']);
	        $complemento = addslashes($_POST['complemento']);
	        $bairro = addslashes($_POST['bairro']);
	        $cidade = addslashes($_POST['cidade']);
	        $estado = addslashes($_POST['estado']);

	        if($users->emailExists($email)) {
	            $uid = $users->validate($email, $pass);

	            if(empty($uid)) {
	            	$dados['error'] = 'E-mail e/ou senha não conferem.';
	            }
	        } else {
	            $uid = $users->createUser($email, $pass);
	        }

	        if(!empty($uid)) {

	        	$list = $cart->getList();
	        	$frete = 0;
	        	$total = 0;

	        	foreach($list as $item) {
		            $total += (floatval($item['price']) * intval($item['qt']));
		        }

	        	if(!empty($_SESSION['shipping'])) {
		            $shipping = $_SESSION['shipping'];

		            if(isset($shipping['price'])) {
		                $frete = floatval(str_replace(',', '.', $shipping['price']));
		            } else {
		                $frete = 0;
		            }

		            $total += $frete;
		        }

		        $id_purchase = $purchases->createPurchase($uid, $total, 'mp');

		        foreach($list as $item) {
		            $purchases->addItem($id_purchase, $item['id'], $item['qt'], $item['price']);
		        }

		        global $config;

		        $mp = new MP($config['mp_appid'], $config['mp_key']);

		        $data = array(
		        	'items' => array(),
		        	'shipments' => array(
		        		'mode' => 'custom',
		        		'cost' => $frete,
		        		'receiver_address' => array(
		        			'zip_code' => $cep
		        		)
		        	),
		        	'back_urls' => array(
		        		'success' => BASE_URL.'mp/obrigadoaprovado',
		        		'pending' => BASE_URL.'mp/obrigadoanalise',
		        		'failure' => BASE_URL.'mp/obrigadocancelado'
		        	),
		        	'notification_url' => BASE_URL.'mp/notificacao',
		        	'auto_return' => 'all',
		        	'external_reference' => $id_purchase
		        );

		        foreach($list as $item) {
		        	$data['items'][] = array(
		        		'title' => $item['name'],
		        		'quantity' => $item['qt'],
		        		'currency_id' => 'BRL',
		        		'unit_price' => floatval($item['price'])
		        	);
		        }

		        $link = $mp->create_preference($data);

		        if($link['status'] == '201') {
		        	$link = $link['response']['init_point'];
		        	header("Location: ".$link);
		        	exit;
		        } else {
		        	$dados['error'] = 'Tente novamente mais tarde';
		        }


	        }


        }

        $this->loadTemplate('cart_mp', $dados);
    }

    public function notificacao() {
    	$purchases = new Purchases();

    	global $config;
		$mp = new MP($config['mp_appid'], $config['mp_key']);
		$mp->sandbox_mode(false);

		$info = $mp->get_payment_info($_GET['id']);

		if($info['status'] == '200') {

			$array = $info['response'];
			$ref = $array['collection']['external_reference'];
			$status = $array['collection']['status'];
			/*
			pending = Em análise
			approved = Aprovado
			in_procress = Em revisão
			in_mediation = Em processo de disputa
			rejected = Foi rejeitado
			cancelled = Foi cancelado
			refunded = Reembolsado
			charged_back = Chargeback
			*/

			if($status == 'approved') {
				$purchases->setPaid($ref);
			}
			elseif($status == 'cancelled') {
				$purchases->setCancelled($ref);
			}


		}

    }












}