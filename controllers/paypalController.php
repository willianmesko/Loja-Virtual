<?php
class paypalController extends controller {

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

		        $id_purchase = $purchases->createPurchase($uid, $total, 'paypal');

		        foreach($list as $item) {
		            $purchases->addItem($id_purchase, $item['id'], $item['qt'], $item['price']);
		        }

		        global $config;

		        // Começar a integração ao Paypal
		        $apiContext = new \PayPal\Rest\ApiContext(
		        	new \PayPal\Auth\OAuthTokenCredential(
		        		$config['paypal_clientid'],
		        		$config['paypal_secret']
		        	)
		        );

		        $payer = new \PayPal\Api\Payer();
		        $payer->setPaymentMethod('paypal');

		        $amount = new \PayPal\Api\Amount();
		        $amount->setCurrency('BRL')->setTotal($total);

		        $transaction = new \PayPal\Api\Transaction();
		        $transaction->setAmount($amount);
		        $transaction->setInvoiceNumber($id_purchase);

		        $redirectUrls = new \PayPal\Api\RedirectUrls();
		        $redirectUrls->setReturnUrl(BASE_URL.'paypal/obrigado');
		        $redirectUrls->setCancelUrl(BASE_URL.'paypal/cancelar');

		        $payment = new \PayPal\Api\Payment();
		        $payment->setIntent('sale');
		        $payment->setPayer($payer);
		        $payment->setTransactions(array($transaction));
		        $payment->setRedirectUrls($redirectUrls);

		        try {
		        	$payment->create($apiContext);

		        	header("Location: ".$payment->getApprovalLink());
		        	exit;

		        } catch(\PayPal\Exception\PayPalConnectionException $e) {
		        	echo $e->getData();
		        	exit;
		        }

	        }


        }

        $this->loadTemplate('cart_paypal', $dados);
    }

    public function obrigado() {
    	$purchases = new Purchases();

    	global $config;

    	$apiContext = new \PayPal\Rest\ApiContext(
        	new \PayPal\Auth\OAuthTokenCredential(
        		$config['paypal_clientid'],
        		$config['paypal_secret']
        	)
        );

        $paymentId = $_GET['paymentId'];
        $payment = \PayPal\Api\Payment::get($paymentId, $apiContext);

        $execution = new \PayPal\Api\PaymentExecution();
        $execution->setPayerId($_GET['PayerID']);

        try {

        	$result = $payment->execute($execution, $apiContext);

        	try {

        		$payment = \PayPal\Api\Payment::get($paymentId, $apiContext);

        		$status = $payment->getState();
        		$t = current($payment->getTransactions());
        		$t = $t->toArray();
        		$ref = $t['invoice_number'];

        		if($status == 'approved') {
        			$purchases->setPaid($ref);

        			unset($_SESSION['cart']);

			    	$store = new Store();
			    	$dados = $store->getTemplateData();

			    	$this->loadTemplate('paypal_obrigado', $dados);
        		} else {
        			$purchases->setCancelled($ref);

        			header("Location: ".BASE_URL."paypal/cancelar");
        			exit;
        		}

        	} catch(Exception $e) {
        		header("Location: ".BASE_URL."paypal/cancelar");
        		exit;
        	}

        } catch(Exception $e) {
        	header("Location: ".BASE_URL."paypal/cancelar");
        	exit;
        }


    }

    public function cancelar() {
    	unset($_SESSION['cart']);

    	$store = new Store();
    	$dados = $store->getTemplateData();

    	$this->loadTemplate('paypal_cancelar', $dados);

    }

    












}