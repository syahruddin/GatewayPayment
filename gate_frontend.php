<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Payment Gateway</title>
  </head>
  <body>
    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method = "POST" >
      <label for="issuer">Issuer</label>
      <select name="issuer" id="issuer">
        <option value="Visa">Visa</option><br>
      </select><br>

      <label for="owner">Pembayar</label><br>
      <input type="text" id="username" name="username" required>
      <br><br>
      <label for="owner">CARD NUMBER</label><br>
      <input oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" type="number" id="cnumber" name="cnumber" maxlength="19" required><br>
      <label for="exp">EXPIRATION DATE</label><br>
      <input type="month" id="exp" name="exp" required><br><br>
      <label for="cvc">CV CODE</label><br>
      <input oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" type="number" id="cvc" name="cvc" maxlength="3" required><br><br>
      <label for="owner">CARD OWNER</label><br>
      <input type="text" id="owner" name="owner" required><br><br>
      <input type="submit" value="Submit">
    </form>
  </body>
</html>
