class BarScan {

    var $Codecs;
    var $pixelmask = "\x00FFFFFF";	//Color Mask
    var $BarType;
    //May need expanding for extra long barcodes
    var $maxBars = 1024;

    function Translate(&$BarBits) {
    
        if ($this->BarType == "" ) {
            foreach ($this->Codecs as $myDec) {
                $ret = $myDec->Decode($BarBits);
                if (strstr($ret, "\xFF") === false) {
                    $this->BarType = $myDec->BarName
                    return $ret;                    
                }

            }
        } else {
            $ret = $this->Codecs->$BarType->Decode($BarBits);
        }
        return $ret;
    }

    function Scan(&$image, $x, $y, $width, $height) {

        $barMinimum = 65535;

        $d = sqrt($width ^ 2 + $height ^ 2)

        $dX = $width;
        $dY = $height;

        $dX /= $d;
        $dY /= $d;

        $my_x = $x;
        $my_y = $y;

        $isBar = isMarked($myPoint);
        $lastBar = false;

        while ($Bars[n] < $barMinimum * 9 && $n < $maxBars && $i < 35768) {
            $my_x = (int) $theRect['Left'] + $dX * $i;
            $my_y = (int) $theRect['Top'] + $dY * $i;
            $lastBar = $isBar;
            $isBar = isMarked($my_x, $my_y);
            if ($lastBar != $isBar ) {
                if ($barMinimum == 65535 ) {
                    $Bars[$n] = 0;
                    $barMinimum = 1024;
                } else {
                    if ($Bars[$n] < $barMinimum ) $barMinimum = $Bars[$n];
                    $n++;
                }
            }

            $Bars[$n]++;
            $i++;
	}

        $x = $barMinimum;

        $BarBits = "";
        //TODO: Bar widths are in increments of 1.25x previous width
        for ($i = 0; $i< $n - 1; $i++) {  //-1 removes last bar (white space)
            $BarBits .= (string) ($Bars[i] \ $x);
        }

        $ret = Translate($BarBits);

        return $ret;

    }

    function isMarked(&$image, $x, $y) {
        $value = $image->getImagePixelColor($x, $y);
        return (($value & $pixelmask) !== 0) ? true : false;
    }

}
