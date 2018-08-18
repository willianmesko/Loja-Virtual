<h1>Carrinho de Compras</h1>

<table border="0" width="100%">
	<tr>
		<th width="100">Imagem</th>
		<th>Nome</th>
		<th width="50">Qtd.</th>
		<th width="120">Preço</th>
		<th width="20"></th>
	</tr>
	<?php
	$subtotal = 0;
	?>
	<?php foreach($list as $item): ?>
	<?php
	$subtotal += (floatval($item['price']) * intval($item['qt']));
	?>
	<tr>
		<td><img src="<?php echo BASE_URL; ?>media/products/<?php echo $item['image']; ?>" width="80" /></td>
		<td><?php echo $item['name']; ?></td>
		<td><?php echo $item['qt']; ?></td>
		<td>R$ <?php echo number_format($item['price'], 2, ',', '.'); ?></td>
		<td><a href="<?php echo BASE_URL; ?>cart/del/<?php echo $item['id']; ?>"><img src="<?php echo BASE_URL; ?>assets/images/delete.png" width="15" /></a></td>
	</tr>
	<?php endforeach; ?>
	<tr>
		<td colspan="3" align="right">Sub-total:</td>
		<td><strong>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></strong></td>
	</tr>
	<tr>
		<td colspan="3" align="right">Frete:</td>
		<td>
			<?php if(isset($shipping['price'])): ?>
				<strong>R$ <?php echo $shipping['price']; ?></strong> (<?php echo $shipping['date']; ?> dia<?php echo ($shipping['date']=='1')?'':'s'; ?>)
			<?php else: ?>
				Qual seu CEP?<br/>
				<form method="POST">
					<input type="number" name="cep" /><br/>
					<input type="submit" value="Calcular" />
				</form>
			<?php endif; ?>	
		</td>
	</tr>
	<tr>
		<td colspan="3" align="right">Total:</td>
		<td><strong>R$ <?php
		if(isset($shipping['price'])) {
			$frete = floatval(str_replace(',', '.', $shipping['price']));
		} else {
			$frete = 0;
		}		
		$total = $subtotal + $frete;
		echo number_format($total, 2, ',', '.');
		?></strong></td>
	</tr>
</table>

<hr/>

<?php if($frete > 0): ?>

<form method="POST" action="<?php echo BASE_URL; ?>cart/payment_redirect" style="float:right">
	<select name="payment_type">
		<option value="checkout_transparente">Pagseguro Checkout Transparente</option>
		<option value="mp">Mercado Pago</option>
		<option value="paypal">PayPal</option>
	</select>

	<input type="submit" value="Finalizar Compra" class="button" />
</form>

<?php endif; ?>













