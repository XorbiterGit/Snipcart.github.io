<?php
//Check to make sure the id parameter is specified in the URL
if (isset($_GET['id'])) {
    //Preapre statement and execute, prevents SQL injection
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$_GET['id']]);
    //Fetch the product from the adatbase and return the result as an array
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    //Check if the product exists (array is not empy)
    if (!$product) {
        //Simple error to display if the id for the product doesn't exists (array is empty)
        exit('Product does not exists!');
    }
} else {

    exit('Product does not exists!');
}

?>
<?= template_header('Product') ?>

<div class="product content-wrapper">
    <img src="imgs/<?= $product['img'] ?>" width="500" height="500" alt="<?= $product['name'] ?>">
    <div>
        <h1 class="name"><?= $product['name'] ?></h1>
        <span class="price">
            &euro;<?= $product['price'] ?>
            <?php if ($product['rrp'] > 0) : ?>
                <span class="rrp">&euro;<?= $product['rrp'] ?></span>
            <?php endif; ?>
        </span>
        <form action="index.php?page=cart" method="post">
            <input type="number" name="quantity" value="1" min="1" max="<?= $product['quantity'] ?>" placeholder="Quantity" required>
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <input type="submit" value="Add To Cart">
        </form>
        <div class="description">
            <?= $product['desc'] ?>
        </div>
    </div>

</div>

<?= template_footer() ?>