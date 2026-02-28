<?php
session_start();
include("config/db.php");

// Add product to cart
if(isset($_GET['id'])){

    $product_id = $_GET['id'];

    if(isset($_SESSION['cart'][$product_id])){
        $_SESSION['cart'][$product_id]++;
    } else {
        $_SESSION['cart'][$product_id] = 1;
    }

    echo "Product Added to Cart! <br><br>";
}

// Show Cart
if(isset($_SESSION['cart'])){

    echo "<h2>Your Cart</h2>";

    $total = 0;

    foreach($_SESSION['cart'] as $id => $qty){

        $sql = "SELECT * FROM products WHERE id=$id";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

        $subtotal = $row['price'] * $qty;
        $total += $subtotal;

        echo "<p>";
        echo $row['name'] . " | Qty: " . $qty;
        echo " | Subtotal: £" . $subtotal;
        echo "</p>";
    }

    echo "<h3>Total: £$total</h3>";
    echo "<br><a href='checkout.php'>Proceed to Checkout</a>";

} else {
    echo "Cart is empty!";
}
?>