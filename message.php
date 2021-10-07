<?php
  function formToIsoMessage(string $formMessage, string $STAN,string $bank_Config)
  {
    $bank_Config = json_decode($bank_Config);
    $RRN = createRRN($STAN);
    $data = json_decode($formMessage);
    $currentBank = $bank_Config->Bank->($data->bank)

    $message = new Message(new Protocol(),['lengthPrefix' => 0]);
    $message->setMTI($currentBank->MTI);

    foreach ($currentBank->Field as $field => $value)
    {
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
            $UTCdatetime = date_format(date_create(null,timezone_open("UTC")),"mdGis");
            $message->setField(7, "$UTCdatetime"); //transmission date and time format MMDDhhmmss UTC
            break;

          case 11:
            $message->setField(11, "$STAN");//system trace audit number
            break;

          case 12:
            $localtime = date("Gis");
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

          default:
            break;
        }
      }
    }
    createMAC($message);
    $message->pact();
    return $message;
  }

  function createSTAN()
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
    $date = date(ymd);
    return ($date . $STAN);
  }
  function searchByRRN(string $RRN)
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
    $message->setField(64, $hash);
  }
  function addTrackTwo(string $isoMessage, string $firstData, string $secondData)
  {
    $firstData = json_decode($firstData);
    $secondData = json_decode($secondData);

    $message->unpack("$isoMessage");
    $track_data = $firstData->cnumber; //card number
    $track_data .= "="; //=
    $track_data .= date("my",strtotime($firstData->exp)); //Expired date MMYY
    $track_data .= "220"; //Service Code
    $track_data .= $secondData->PVV; //PVV
    $track_data .= $firstData->CVV; //CVV
    $track_data .= "0"; //trailing 0
    $message->setField(35, "$track_data");
    createMAC($message);
    return $message->pack();
  }


?>
