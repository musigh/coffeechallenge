<?php

namespace Coffee;

/**
 * Accepted response to the coffee challenge.
 */
class OttoCoffee extends VendingMachine{

	public function enoughCoffee($coffeeID, $coffeeRequired)
	{
		$amountLeft = $this->db->query("SELECT amount_left FROM coffees WHERE id = " . $coffeeID)->fetch(\PDO::FETCH_NUM)[0];
		
		return $amountLeft >= VendingMachine::COFFEE_PER_OUNCE * $coffeeRequired;
	}

	public function order($coffeeID, $cupID, $loyalty)
	{
		
		$cupSize = $this->db->query("SELECT size FROM cups WHERE id = " . $cupID)->fetch()[0];
		
		if(!$this->enoughCoffee($coffeeID, $cupSize)){
			return FALSE;
		}
		
		$this->db->query("INSERT INTO orders(coffee_id, cup_id, loyalty_number) VALUES({$coffeeID}, {$cupID}, {$loyalty})");
		
		return TRUE;
	}
	
	public function amountOwed($orderID)
	{
		$order = $this->db->query("SELECT o.*, (SELECT COUNT(*) FROM orders WHERE coffee_id = o.coffee_id AND loyalty_number = o.loyalty_number) AS orders FROM orders AS o WHERE id = " . $orderID)->fetch();
		
		if($order->orders == 5) return 0;
		
		$cost = $this->db->query("SELECT cost FROM cups WHERE id = " . $order['cup_id'])->fetch()[0];
		
		return $cost;
	}
	
	public function pay($orderID, $amount)
	{
		$this->db->query("UPDATE orders SET paid = " . date() . " WHERE id = " . $orderID);
	}
	
	public function brew($orderID)
	{
		$order = $this->db->query("SELECT * FROM orders WHERE id = " . $orderID)->fetch();
		
		$cupSize = $this->db->query("SELECT size FROM cups WHERE id = " . $order['cup_id'])->fetch()['size'];
		$this->db->query("UPDATE coffee SET amount_left = {$cupSize} WHERE id = " . $order['coffee_id']);	
	}
}
