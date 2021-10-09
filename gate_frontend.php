<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Payment Gateway</title>
  </head>
  <body>
    <?php //if($error != 0)
    {

    }?>
    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method = "POST" >
      <label for="issuer">Issuer</label>
      <select name="issuer" id="issuer">
        <option value="Visa">Visa</option><br>
      </select><br>

      <label for="owner">Pembayar</label><br>
      <input type="text" id="username" name="username" required>
      <br><br>
      <label for="owner">CARD NUMBER</label><br>
      <input type="number" id="cnumber" name="cnumber" required><br>
      <label for="exp">EXPIRATION DATE</label><br>
      <input type="month" id="exp" name="exp" required><br><br>
      <label for="cvc">CV CODE</label><br>
      <input type="number" id="cvc" name="cvc" required><br><br>
      <label for="owner">CARD OWNER</label><br>
      <input type="text" id="owner" name="owner" required><br><br>
      <input type="submit" value="Submit">
    </form>
  </body>
</html>
