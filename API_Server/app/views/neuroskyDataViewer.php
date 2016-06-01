<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>

    <script src="http://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script src="http://laravel.my.n-pure.net:3636/socket.io/socket.io.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/dygraph/1.1.1/dygraph-combined.js"></script>
    

    <script type="text/javascript">
        var fillArray = function(len, fill) {
            var arr = new Array();

            for(var i = 0; i < len; i++) {
                arr.push( fill );
            }

            return arr;
        };

        var slabel = fillArray(512, 0);
        var sdata = fillArray(512, 0.0);




        $(document).ready( function() {
            
            // 차트
            var g = new Dygraph(

                // containing div
                document.getElementById("graphdiv"),

                // CSV or path to a CSV file.
                "Seq,Raw\n" +
                "0,75\n" 
                ,
                {
                    valueRange: [-110.0,110.0]
                }

            );



            var socket = io('http://laravel.my.n-pure.net:3636');
            
            socket.on('connect', function() {
                console.log(' client arrived ');
                socket.on('nData', function(data) {
                    //console.log(' DATA ARRIVED :: ', data);

                    var pData = data.data;
                    pData = pData.data;
                   
                    var s = "";
                    for(var len = 0; len < pData.length; len++) {
                        s += len + "," + pData[len] + "\n";
                    }
                    
                    g.updateOptions({ file: s} );

                    $('#graph_result').html(" Data 갯수 : " + pData.length + " / " + " Time :: " + (new Date()).toString() );

                });
            });
            

        });


        


    </script>

    <style type="text/css">
        html,body {
            width: 100%;
            height: 100%;
        }
    </style>

</head>
<body>

Neurosky.. EEG <br />
<div id="graphdiv" style="width:90%; height: 80%;"></div>
<br />
<div id="graph_result"></div>

</body>
</html>