<form action="<?php echo $_SERVER['PHP_SELF'];?>" method = "POST" >
  <label for="PVV">PVV</label><br>
  <input type="number" id="PVV" name="PVV" required>
  <input type="hidden" id="RRN" name="RRN" value="<?php echo $RRN;?>">
  <input type="submit" value="Submit">
</form>
