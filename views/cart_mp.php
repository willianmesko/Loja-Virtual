<h1>Checkout Mercado Pago</h1>

<?php if(!empty($error)): ?>
<div class="warn">
	<?php echo $error; ?>
</div>
<?php endif; ?>

<h3>Dados Pessoais</h3>

<form method="POST">
	<strong>Nome:</strong><br/>
	<input type="text" name="name" value="Bonieky Lacerda Leal" /><br/><br/>

	<strong>CPF:</strong><br/>
	<input type="text" name="cpf" value="05347965401" /><br/><br/>

	<strong>Telefone:</strong><br/>
	<input type="text" name="telefone" value="8399999999" /><br/><br/>

	<strong>E-mail:</strong><br/>
	<input type="email" name="email" value="testemp@hotmail.com" /><br/><br/>

	<strong>Senha:</strong><br/>
	<input type="password" name="pass" value="123" /><br/><br/>

	<h3>Informações de Endereço</h3>

	<strong>CEP:</strong><br/>
	<input type="text" name="cep" value="58410340" /><br/><br/>

	<strong>Rua:</strong><br/>
	<input type="text" name="rua" value="Rua Vigário Calixto" /><br/><br/>

	<strong>Número:</strong><br/>
	<input type="text" name="numero" value="1400" /><br/><br/>

	<strong>Complemento:</strong><br/>
	<input type="text" name="complemento" /><br/><br/>

	<strong>Bairro:</strong><br/>
	<input type="text" name="bairro" value="Catolé" /><br/><br/>

	<strong>Cidade:</strong><br/>
	<input type="text" name="cidade" value="Campina Grande" /><br/><br/>

	<strong>Estado:</strong><br/>
	<input type="text" name="estado" value="PB" /><br/><br/>

	<input type="submit" value="Efetuar Compra" class="button efetuarCompra" />
</form>