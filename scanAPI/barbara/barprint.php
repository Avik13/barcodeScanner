var $Class BarPrint
    var $Codecs;

    var $BarType ="";
    var $BarText As String
    var $ShowText As Boolean
    var $Extents As System.Drawing.Rectangle         //Defines extent and angle
    var $Angle As Integer
    var $BarWidth As Integer

    var $Target As System.Drawing.Bitmap

    function  RenderBarcode(ByVal text As String)
        var $myCode As BarCodec
        var $theBars As String

        myCode = Codecs(BarType)

        theBars = myCode.Encode(text)

        If theBars.IndexOf(Convert.ToChar(255)) = -1 Then
            DrawBars(theBars)
        }

    }

    function  DrawBars(ByVal Bars As String)
        var $i As Integer

        var $myPoints(3) As System.Drawing.Point
        var $theBar As Double
        var $barDX As Double   //Angle X
        var $barDY As Double   //Angle Y


        barDX = Math.Cos(Angle / (Math.PI / 180)) * BarWidth
        barDY = Math.Sin(Angle / (Math.PI / 180)) * BarWidth

        myPoints(0).X = Extents.Left
        myPoints(0).Y = Extents.Top

        //1 bar = 1.125 pix
        //2 bar = 2.25  pix
        //3 bar = 3.5   pix

        for ($i = 0; $i< strlen($Bars); $i++) {
            $theBar = (float) (int) (Bars.Substring(i, 1)) + (theBar / 8))

            If i Mod 2 = 1 Then //Marked Bar
                myPoints(3).X = Convert.ToInt32(myPoints(0).X + Extents.Height * barDY)
                myPoints(3).Y = Convert.ToInt32(myPoints(0).Y + Extents.Height * barDX)

                myPoints(1).X = Convert.ToInt32(myPoints(0).X + (theBar * barDX))
                myPoints(1).Y = Convert.ToInt32(myPoints(0).Y + (theBar * barDY))

                myPoints(2).X = Convert.ToInt32(myPoints(3).X + (theBar * barDX))
                myPoints(2).Y = Convert.ToInt32(myPoints(3).Y + (theBar * barDY))

                System.Drawing.Graphics.FromImage(Target).FillPolygon(Drawing.Brushes.Black, myPoints)
            }

            myPoints(0).X = Convert.ToInt32(myPoints(0).X + (barDX * theBar))
            myPoints(0).Y = Convert.ToInt32(myPoints(0).Y + (barDY * theBar))
        }
    }

}
