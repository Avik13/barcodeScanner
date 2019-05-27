<?php
/*
    PHP BarCodec class    
    Decodes & Encodes barcodes for a single type
    Stanley Sufficool 10-06-2007
    
*/
class barcode {
	var $Codecs;
	var $CodeName = "";

	function load($filename) {

		$s = file_get_contents($filename);
		$this->Codecs = json_decode($s) or
			die("Invalid JSON encoded barcode file");
			
		//Add reverse coding(Value to Barcode for BarPrint)
		//Each Barcode Type
		foreach ($this->Codecs as $key => $value) {
			//Each Barcode/Value Pair
			if(isset($this->Codec->$key->CodeLeft)) {
				foreach ($this->Codec->$key->CodeLeft as $key => $value)
					$this->Codec->$key->EncodeLeft->$value = $key;
			}

			if(isset($this->Codec->$key->CodeRight)) {
				foreach ($this->Codec->$key->CodeRight as $key => $value)
					$this->Codec->$key->EncodeRight->$value = $key;
			}
		}
	}

	function clipBars(&$value, $BarWidths) {
		$ret = "";

		for($i = 0; $i < strlen($value); $i++) {
			if ($value[$i] > (string) $BarWidths) {
				$ret .= (string) $BarWidths;
			} else {
				$ret .= $value[$i];
			}
		}

		return $ret;
	}
	
	function whichBarcode(&$bits) {
		//Returns which barcode matches in dictionary
		//TODO: Add switch bar to detection
		foreach($this->Codecs as $key => $code) {
			$mybits = $this->clipBars($bits, $code->BarWidths);
			if (substr($mybits, 0, strlen($code->StartBar)) == $code->StartBar && 
			        substr($mybits, -strlen($code->StopBar), strlen($code->StopBar)) == $code->StopBar)
			        return $key;
		}
		return false;
	}

    function Decode($bits) {
        $ret = "";
        $i = 0;
        
        if ($bits == "" ) 
        	return false;

        if (! $CodeName) $CodeName = $this->whichBarcode($bits);
        if ($CodeName === false) return false;
        
        $CodeType =& $this->Codecs->$CodeName;

        if (strlen($bits) <= strlen($CodeType->StopBar . $CodeType->SwitchBar . $CodeType->StopBar) + $CodeType->CodeWidth) {
            return false;
        }

        $bits = $this->clipBars($bits, $CodeType->BarWidths);


        //Remove start/stop/switch bar
	$bits = substr($bits, strlen($CodeType->StartBar), strlen($bits));
	$bits = substr($bits, 0, strlen($bits) - strlen($CodeType->StopBar));

        if (isset($CodeType->SwitchBar)) {
            $i = (int) (strlen($bits) - strlen($CodeType->SwitchBar)) / 2;
            $bits = substr($bits, 0, $i) . substr($bits, $i + strlen($CodeType->SwitchBar));
        }

        if ($bits == "" || $CodeType->CodeWidth < 1) {
		return false;
        }
        
        if (strlen($bits) % $CodeType->CodeWidth != 0) {	
        	return false;
        }

	$BarSide = "CodeLeft";

        for ($i = 0; $i < strlen($bits); $i += $CodeType->CodeWidth) {
        	$barcode = substr($bits, $i, $CodeType->CodeWidth);
		if (isset($CodeType->SwitchBar) && ($i / 2 == (int) $i / 2))
			$BarSide = "CodeRight";
		if (isset($CodeType->$BarSide->$barcode)) {
			$ret .= $CodeType->$BarSide->$barcode;
		} else {
			return false;
		}
        }

        return $ret;
    }

    function Encode($chars) {
        $ret = "";
        $i = 0;
        $barSide = "";
        $myChar = "";

        if (! $CodeType) return false;

        if ($Chars == "" || $CodeType->CharWidth < 1) 
            return false;

        //Add start bar
        $ret = $CodeType->StartBar;

        $barSide = "EncodeLeft";

        for($i = 0; $i < strlen($Chars); $i += $CodeType->CharWidth) {
            $myChar = substr($Chars, $i, $CodeType->CharWidth);

            if (isset($CodeType->$barSide->$myChar)) {
                $ret .= $CodeType->$barSide->$myChar;
            } else {
                return false;
            }

            if ($CodeType->SwitchBar != "") {
                if ($i / 2 == (int) $i / 2 ) {
                    $ret .= $this->SwitchBar;
                    $barSide="EncodeRight";
                }
            }
        }

        if ($CodeType->StopBar != "") $ret .= $StopBar;

        return $ret;
    }

} //End Class


class BarScan {
    var $Codecs;
    var $pixelmask = "\x00FFFFFF";	//Color Mask
    var $BarType;
    //May need expanding for extra long barcodes
    var $maxBars = 1024;

    function Scan(&$image, $x, $y, $width, $height) {
    	//Collect the bars from an image (ImageMagick class)
    	
        $barMinimum = 65535;

        $d = sqrt( ($width * $width) + ($height * $height));

        $dx = $width / $d;
        $dy = $height / $d;
        
	$my_x = $x;
	$my_y = $y;

        $isBar = $this->isMarked($image, $x, $y);

	$n =0;
	$Bars[$n] = 0;
        while ( ($Bars[$n] < $barMinimum * 9) && ($n < $this->maxBars) && ($i < 35768) ) {
            $my_x = floor($x + ($dx * $i));
            $my_y = floor($y + ($dy * $i));
            $lastBar = $isBar;
            $isBar = $this->isMarked($image, $my_x, $my_y);
            //Autodetect minimum bar width
            if ($lastBar !== $isBar ) {
                if ($barMinimum == 65535 ) {
                    $Bars[$n] = 0;
                    $barMinimum = 1024;
                } else {
                    if ($Bars[$n] < $barMinimum ) $barMinimum = $Bars[$n];
                    $n++;
                }
            }
		if ($my_x < 0 || $my_y < 0 || $my_x > $image->getImageWidth() 
  			|| $my_y > $image->getImageHeight() ) 
  			$i = 65535;
			
		$Bars[$n]++;
		$i++;
	}

        $x = $barMinimum;
        $barbits = "";
        //TODO: Bar widths are in increments of 1.25x previous width
        for ($i = 0; $i < $n; $i++) {  //-1 removes last bar (white space)
            $barbits .= (string) floor($Bars[$i] / $x);
        }
        
        echo "<br />Bars: $barbits <br />Minbar=$x<br />";

        $ret = $this->Codecs->Decode($barbits);

        return $ret;

    }

    function isMarked(&$image, $x, $y) {
        $value = $image->getImagePixelColor($x, $y);
        $color = new ImagickPixel('BLACK') or die("Unable to init color");
        echo $value->isSimilar($color, .5) ? "1" : "0";
	return ($value->isSimilar($color, .5) == 1) ? true : false;
    }

}
