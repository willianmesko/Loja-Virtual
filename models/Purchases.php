<?php
class Purchases extends model {

	public function createPurchase($uid, $total, $payment_type) {

		$sql = "INSERT INTO purchases (id_user, total_amount, payment_type, payment_status) VALUES (:uid, :total, :type, 1)";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(":uid", $uid);
		$sql->bindValue(":total", $total);
		$sql->bindValue(":type", $payment_type);
		$sql->execute();

		return $this->db->lastInsertId();

	}

	public function addItem($id, $id_product, $qt, $price) {

		$sql = "INSERT INTO purchases_products (id_purchase, id_product, quantity, product_price) VALUES (:id, :idp, :qt, :price)";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(":id", $id);
		$sql->bindValue(":idp", $id_product);
		$sql->bindValue(":qt", $qt);
		$sql->bindValue(":price", $price);
		$sql->execute();

	}

	public function setPaid($id) {

		$sql = "UPDATE purchases SET payment_status = :status WHERE id = :id";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(":status", '2');
		$sql->bindValue(":id", $id);
		$sql->execute();

	}

	public function setCancelled($id) {

		$sql = "UPDATE purchases SET payment_status = :status WHERE id = :id";
		$sql = $this->db->prepare($sql);
		$sql->bindValue(":status", '3');
		$sql->bindValue(":id", $id);
		$sql->execute();

	}













}