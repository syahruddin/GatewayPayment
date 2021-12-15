<?php
  function formToIsoMessage(string $formMessage, string $STAN,string $bank_Config)
  {


    realtimeDebug("creating message...");
    $bank_Config = json_decode($bank_Config);
    $RRN = createRRN($STAN);
    realtimeDebug("RRN:$RRN");
    $data = json_decode($formMessage);
    $issuer = $data->issuer;
    realtimeDebug("Issuer:$issuer");
    $currentBank = $bank_Config->Bank->$issuer;


    $message = new RoyISO8583();
    $MTI = $currentBank->MTI;
    realtimeDebug("MTI:$MTI");
    $message->setType($MTI);


    foreach ($currentBank->Field as $field => $value)
    {
      realtimeDebug("creating field $field...");
      if($value != null)
      {
        $message->addData("$field","$value");
      }
      else
      {
        switch ($field)
        {
          case 2:
            $message->addData("$field","$data->cnumber"); //primary account number
            break;
          case 4:
            $message->addData("$field","000000275000"); //amount transaction 12 digit
            break;

          case 7:
            $UTCdatetime = date_format(date_create(null,timezone_open("UTC")),"mdHis");
            $message->addData("$field","$UTCdatetime"); //transmission date and time format MMDDhhmmss UTC
            break;

          case 11:
            $message->addData("$field","$STAN");//system trace audit number
            break;

          case 12:
            $localtime = date("His");
            $message->addData("$field","$localtime");//time, local transaction
            break;

          case 13:
            $localdate = date("md");
            $message->addData("$field","$localdate");//date, local transaction
            break;

          case 14:
            $message->addData("$field",date("ym",strtotime($data->exp)));//date, exp
            break;

          case 37:
            $message->addData("$field","$RRN"); //RRN
            break;

          case 45:
            $message->addData("$field",createTrackOne($formMessage)); //Track1
            break;

          default:
            break;
        }
      }
    }

    realtimeDebug("Creating MAC...");
    createMAC($message);
    realtimeDebug("Packing Message...");
    return $message->getISO();

  }

  function createSTAN(mysqli $con)
  {
    if($result = $con->query("SELECT SystemTraceAuditNumber FROM log_pembayaran WHERE tanggal_pembayaran = CURRENT_DATE ORDER BY SystemTraceAuditNumber DESC;"))
    {
      if($result->num_rows > 0)
      {
        $firstRow = $result->fetch_assoc();
        return sprintf("%06d", $firstRow['SystemTraceAuditNumber'] + 1);
      }
      else
      {
        return "000001";
      }
    }
    return "-1";
  }

  function createRRN(string $STAN)
  {
    $date = date("ymd");
    return ($date . $STAN);
  }
  function searchByRRN(string $RRN, mysqli $con)
  {
    if($result = $con->query("SELECT MESSAGE FROM log_pembayaran WHERE RetrivalReferenceNumber = $RRN;"))
    {
      $message = $result->fetch_assoc();
    }
    return $message;
  }
  function createMAC(RoyISO8583 $message)
  {
//    $stringtemp = "";
//    foreach($message as $key=>$val)
//    {
//      if($key != 64)
//      {
//        $stringtemp .= $val;
//      }
//    }
    $stringtemp = $message->getBody();
    $hash = hash("sha256",$stringtemp);
    realtimeDebug("MAC: $hash");
    $message->addData("64",$hash);
  }
  function createTrackOne(string $Data)
  {
    $Data = json_decode($Data);

    $track_data = "B"; //B
    $track_data .= $Data->cnumber; //card number
    $track_data .= "^"; //^
    $track_data .= str_pad($data->owner,26," ",STR_PAD_RIGHT);
    $track_data .= date("my",strtotime($firstData->exp)); //Expired date MMYY
    $track_data .= "220"; //Service Code
    $track_data .= "0000"; // 0000
    $track_data .= $Data->CVV; //CVV
    $track_data .= "000"; //trailing 000

    return $track_data;
  }

  function realtimeDebug(string $log)
  {
    echo "$log\n";
    flush();
  }

?>
