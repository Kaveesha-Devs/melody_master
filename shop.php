<?php
session_start();
include("config/db.php");

$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<h2>Products</h2>

<?php
while($row = $result->fetch_assoc()){
?>
    <div>
        <h3><?php echo $row['name']; ?></h3>
        <p>Price: £<?php echo $row['price']; ?></p>
        <p>Stock: <?php echo $row['stock']; ?></p>

        <a href="cart.php?id=<?php echo $row['id']; ?>">
            Add to Cart
        </a>
    </div>
    <hr>
<?php
}
?>