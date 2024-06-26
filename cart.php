<!-- https://codeshack.io/how-to-integrate-paypal-php-mysql/#gettingstarted -->
<?php
//If user clicked the add to cart button on the product page we can check for the form data
if (isset($_POST['product_id'], $_POST['quantity']) && is_numeric($_POST['product_id']) && is_numeric($_POST['quantity'])) {
    //Set the post variables so we easily identify them
    $product_id = (int) $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];
    //Prepare the SQL statement, basically checking if the product exists in the database
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$_POST['product_id']]);
    //Fetch the product from the database and return the result as an Array
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    //Check if the product exists (array is not empty)
    if ($product && $quantity > 0) {
        //Product exists in database, now create/update the session for the cart
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            if (array_key_exists($product_id, $_SESSION['cart'])) {
                //Product exists in cart so just update the quantity
                $_SESSION['cart'][$product_id] += $quantity;
            } else {
                //Product is not in cart so add it
                $_SESSION['cart'][$product_id] = $quantity;
            }
        } else {
            //There are no products in cart, this will first add the product to cart
            $_SESSION['cart'] = array($product_id => $quantity);
        }
    }
    //Prevent form resubmission
    header('location: index.php?page=cart');
    exit;
}

//Removoing the a product from cart
if (isset($_GET['remove']) && is_numeric($_GET['remove']) && isset($_SESSION['cart']) && isset($_SESSION['cart'][$_GET['remove']])) {
    //Remove the product from the shopping cart
    unset($_SESSION['cart'][$_GET['remove']]);
}

//Update product quantities in cart if the user click the "Update" button on the shopping cart
if (isset($_POST['update']) && isset($_SESSION['cart'])) {
    //Loop through the post data to update the quantites for every product in a cart
    foreach ($_POST as $k => $v) {
        if (strpos($k, 'quantity') !== false && is_numeric($v)) {
            $id = str_replace('quantity-', '', $k);
            $quantity = (int) $v;
            //Always do checks and validation
            if (is_numeric($id) && isset($_SESSION['cart'][$id]) && $quantity > 0) {
                //Update new quantity
                $_SESSION['cart'][$id] = $quantity;
            }
        }
    }
    //Prevent form resubmission
    header('location: index.php?page=cart');
    exit;
}

//Send the user to place order page if they click the place order button, also the cart should not be empty
if (isset($_POST['placeorder']) && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    header('Location: index.php?page=placeorder');
    exit;
}

//Check the seesion variable for products in cart
$products_in_cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$products = array();
$subtotal = 0.00;


//If there are products in cart
if ($products_in_cart) {
    //There are products in teh cart so we need to select those products from the database
    //Products in cart array to question mark string array
    $array_to_question_marks = implode(',', array_fill(0, count($products_in_cart), '?'));
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id IN (' . $array_to_question_marks . ')');
    //We only need the array keys, no the values, the keys are the id's of the products
    $stmt->execute(array_keys($products_in_cart));
    //Fetch the products from the database and return the result as an Array
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //Calculate the subtotal
    foreach ($products as $product) {
        $subtotal += (float) $product['price'] * (int) $products_in_cart[$product['id']];
    }
}
?>

<?php
//Initialize total quantity
$total_quantity = 0;

//Iterate through products in the cart
foreach ($products_in_cart as $quantity) {
    //Add the quantity of each product to the total quantity
    $total_quantity += $quantity;
}
?>
<?= template_header('Cart') ?>

<div class="cart content-wrapper">
    <h1>Shopping Cart</h1>
    <p>Total Quantity in Cart: <?= $total_quantity ?><!--Display total quantity--></p>
    <form action="index.php?page=cart" method="post">
        <table>
            <thead>
                <tr>
                    <td colspan="2">Product</td>
                    <td>Price</td>
                    <td>Quantity</td>
                    <td>Total</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)) : ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">You haven no products added in your Shopping Cart</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($products as $product) : ?>
                        <tr>
                            <td class="img">
                                <a href="index.php?page=product&id=<?= $product['id'] ?>">
                                    <img src="imgs/<?= $product['img'] ?>" width="50" height="50" alt="<?= $product['name'] ?>">
                                </a>
                            </td>
                            <td>
                                <a href="index.php?page=product&id=<?= $product['id'] ?>"><?= $product['name'] ?></a>
                                <br>
                                <a href="index.php?page=cart&remove=<?= $product['id'] ?>" class="remove">Remove</a>
                            </td>
                            <td class="price">&euro;<?= $product['price'] ?></td>
                            <td class="quantity">
                                <input type="number" name="quantity-<?= $product['id'] ?>" value="<?= $products_in_cart[$product['id']] ?>" min="1" max="<?= $product['quantity'] ?>" placeholder="Quantity" required>
                            </td>
                            <td class="price">&euro;<?= $product['price'] * $products_in_cart[$product['id']] ?></td>
                        </tr>
                    <?php endforeach; ?>

                <?php endif; ?>
            </tbody>
        </table>
        <div class="subtotal">
            <span class="text">Subtotal</span>
            <span class="price">&euro;<?= $subtotal ?></span>
        </div>
        <div class="buttons">
            <input type="submit" value="Update" name="update">
            <input type="submit" value="Place Order" name="placeorder">
        </div>
        <div class="paypal">
            <button type="submit" name="paypal"><img src="https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-100px.png" border="0" alt="PayPal Logo"></button>
        </div>
    </form>

</div>
<?= template_footer() ?>