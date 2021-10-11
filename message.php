<?php
  require 'vendor/autoload.php';
  use ISO8583\Protocol;
  use ISO8583\Message;

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

    $message = new Message(new Protocol(),['lengthPrefix' => 0]);
    $MTI = $currentBank->MTI;
    realtimeDebug("MTI:$MTI");
    $message->setMTI("$MTI");

    foreach ($currentBank->Field as $field => $value)
    {
      realtimeDebug("creating field $field...");
      if($value != null)
      {
        $message->setField($field, "$value");
      }
      else
      {
        switch ($field)
        {
          case 2:
            $message->setField(2, $data->cnumber ); //primary account number
            break;
          case 4:
            $message->setField(4, "000000275000"); //amount transaction 12 digit
            break;

          case 7:
            $UTCdatetime = date_format(date_create(null,timezone_open("UTC")),"mdHis");
            $message->setField(7, "$UTCdatetime"); //transmission date and time format MMDDhhmmss UTC
            break;

          case 11:
            $message->setField(11, "$STAN");//system trace audit number
            break;

          case 12:
            $localtime = date("His");
            $message->setField(12, "$localtime");//time, local transaction
            break;

          case 13:
            $localdate = date("md");
            $message->setField(13, "$localdate");//date, local transaction
            break;

          case 14:
            $message->setField(14, date("ym",strtotime($data->exp)));//date, exp
            break;

          case 37:
            $message->setField(37, "$RRN");
            break;

          case 49:
            $message->setField(49, createTrackOne($formMessage));
            break;

          default:
            break;
        }
      }
    }
  //  realtimeDebug("Creating MAC...");
  //  createMAC($message);
    realtimeDebug("Packing Message...");
    return $message->pack();

  }

  function createSTAN(mysqli $con)
  {
    if($result = $con->query("SELECT SYSTEMTRACEAUDITNUMBER FROM log_pembayaran WHERE tanggal_pembayaran = current_timestamp ORDER BY SYSTEMTRACEAUDITNUMBER DESC;"))
    {
      if($result->num_rows > 0)
      {
        $firstRow = $result->fetch_assoc();
        return sprinf("%06d", $firstRow['SYSTEMTRACEAUDITNUMBER'] + 1);
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
  function createMAC(Message $message)
  {
    $stringtemp = "";
    foreach($message->getFields() as $key=>$val)
    {
      if($key != 64)
      {
        $stringtemp .= $val;
      }
    }
    $hash = hash("sha256",$stringtemp);
    realtimeDebug("MAC: $hash");
    $message->setField(64, $hash);
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
