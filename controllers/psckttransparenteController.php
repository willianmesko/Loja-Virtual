<?php
class psckttransparenteController extends controller {

	private $user;

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $store = new Store();
        $products = new Products();
        $cart = new Cart();
        $dados = $store->getTemplateData();

        $list = $cart->getList();
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

        $dados['total'] = number_format($total, 2);

        try {
            $sessionCode = \PagSeguro\Services\Session::create(
                \PagSeguro\Configuration\Configure::getAccountCredentials()
            );

            $dados['sessionCode'] = $sessionCode->getResult();
        } catch(Exception $e) {
            echo "ERRO: ".$e->getMessage();
            exit;
        }



        $this->loadTemplate('cart_psckttransparente', $dados);
    }

    public function checkout() {
        $users = new Users();
        $cart = new Cart();
        $purchases = new Purchases();

        $id = addslashes($_POST['id']);
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
        $cartao_titular = addslashes($_POST['cartao_titular']);
        $cartao_cpf = addslashes($_POST['cartao_cpf']);
        $cartao_numero = addslashes($_POST['cartao_numero']);
        $cvv = addslashes($_POST['cvv']);
        $v_mes = addslashes($_POST['v_mes']);
        $v_ano = addslashes($_POST['v_ano']);
        $cartao_token = addslashes($_POST['cartao_token']);
        $parc = explode(';', $_POST['parc']);

        if($users->emailExists($email)) {
            $uid = $users->validate($email, $pass);

            if(empty($uid)) {
                $array = array('error'=>true, 'msg'=>'E-mail e/ou senha não conferem.');
                echo json_encode($array);
                exit;
            }
        } else {
            $uid = $users->createUser($email, $pass);
        }

        $list = $cart->getList();
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

        $id_purchase = $purchases->createPurchase($uid, $total, 'psckttransparente');

        foreach($list as $item) {
            $purchases->addItem($id_purchase, $item['id'], $item['qt'], $item['price']);
        }

        global $config;

        $creditCard = new \PagSeguro\Domains\Requests\DirectPayment\CreditCard();
        $creditCard->setReceiverEmail($config['pagseguro_seller']);
        $creditCard->setReference($id_purchase);
        $creditCard->setCurrency("BRL");

        foreach($list as $item) {
            $creditCard->addItems()->withParameters(
                $item['id'],
                $item['name'],
                intval($item['qt']),
                floatval($item['price'])
            );
        }

        $creditCard->setSender()->setName($name);
        $creditCard->setSender()->setEmail($email);
        $creditCard->setSender()->setDocument()->withParameters('CPF', $cpf);
        
        $ddd = substr($telefone, 0, 2);
        $telefone = substr($telefone, 2);

        $creditCard->setSender()->setPhone()->withParameters(
            $ddd,
            $telefone
        );

        $creditCard->setSender()->setHash($id);

        $ip = $_SERVER['REMOTE_ADDR'];
        if(strlen($ip) < 9) {
            $ip = '127.0.0.1';
        }
        $creditCard->setSender()->setIp($ip);

        $creditCard->setShipping()->setAddress()->withParameters(
            $rua,
            $numero,
            $bairro,
            $cep,
            $cidade,
            $estado,
            'BRA',
            $complemento
        );

        $creditCard->setShipping()->setCost()->withParameters($frete);

        $creditCard->setBilling()->setAddress()->withParameters(
            $rua,
            $numero,
            $bairro,
            $cep,
            $cidade,
            $estado,
            'BRA',
            $complemento
        );

        $creditCard->setToken($cartao_token);
        $creditCard->setInstallment()->withParameters($parc[0], $parc[1]);
        $creditCard->setHolder()->setName($cartao_titular);
        $creditCard->setHolder()->setDocument()->withParameters('CPF', $cartao_cpf);

        $creditCard->setMode('DEFAULT');

        $creditCard->setNotificationUrl(BASE_URL."psckttransparente/notification");

        try {
            $result = $creditCard->register(
                \PagSeguro\Configuration\Configure::getAccountCredentials()
            );

            echo json_encode($result);
            exit;
        } catch(Exception $e) {
            echo json_encode(array('error'=>true, 'msg'=>$e->getMessage()));
            exit;
        }


    }

    public function obrigado() {
        unset($_SESSION['cart']);

        $store = new Store();
        $dados = $store->getTemplateData();

        $this->loadTemplate("psckttransparente_obrigado", $dados);
    }
    
    public function notification() {
        $purchases = new Purchases();

        try {

            if(\PagSeguro\Helpers\Xhr::hasPost()) {
                $r = \PagSeguro\Services\Transactions\Notification::check(
                    \PagSeguro\Configuration\Configure::getAccountCredentials()
                );

                $ref = $r->getReference();
                $status = $r->getStatus();
                /*
                1 = Aguardando Pagamento
                2 = Em análise
                3 = Paga
                4 = Disponível
                5 = Em disputa
                6 = Devolvida
                7 = Cancelada
                8 = Debitado
                9 = Retenção Temporária = Chargeback
                */

                if($status == 3) {
                    $purchases->setPaid($ref);
                }
                elseif($status == 7) {
                    $purchases->setCancelled($ref);
                }

            }

        } catch(Exception $e) {

        }

    }





















}