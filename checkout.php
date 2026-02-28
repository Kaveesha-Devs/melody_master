<?php
session_start();
include("config/db.php");

if(!isset($_SESSION['user_id'])){
    die("Please login first!");
}

if(!isset($_SESSION['cart'])){
    die("Cart is empty!");
}

$total = 0;

foreach($_SESSION['cart'] as $id => $qty){
    $sql = "SELECT * FROM products WHERE id=$id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    $subtotal = $row['price'] * $qty;
    $total += $subtotal;
}

// SHIPPING RULE
if($total > 100){
    $shipping = 0;
} else {
    $shipping = 10;
}

$final_total = $total + $shipping;

// SAVE ORDER
$user_id = $_SESSION['user_id'];

$conn->query("INSERT INTO orders (user_id,total_amount,shipping_cost) 
              VALUES ($user_id,$final_total,$shipping)");

$order_id = $conn->insert_id;

// SAVE ORDER ITEMS
foreach($_SESSION['cart'] as $id => $qty){

    $sql = "SELECT * FROM products WHERE id=$id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    $price = $row['price'];

    $conn->query("INSERT INTO order_items (order_id,product_id,quantity,price)
                  VALUES ($order_id,$id,$qty,$price)");

    // REDUCE STOCK
    $conn->query("UPDATE products 
                  SET stock = stock - $qty 
                  WHERE id=$id");
}

// CLEAR CART
unset($_SESSION['cart']);

echo "<h2>Order Placed Successfully!</h2>";
echo "Total: £$final_total<br>";
echo "Shipping: £$shipping<br>";
?>