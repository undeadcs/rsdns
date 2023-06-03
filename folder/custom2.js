//
function Ticks5Mins24Hours( axis ) {
	var r = [];
	var i = 0;
	var j = 0;
	var szHour = "";
	var szMinute = "";
	var szText = "";
	var iIndex = 0;
	for( i = 0; i < 24; ++i ) {
		szHour = ( i < 10 ? "0" + i : "" + i );
		j = 0;
		for( ; j < 60; j += 5 ) {
			szMinute = ( j < 10 ? "0" + j : "" + j );
			iIndex = 0;
			if ( i == 0 ) {
				iIndex = j;
			} else if ( i < 10 ) {
				iIndex = parseInt( "" + i + szMinute );
			} else {
				iIndex = parseInt( "" + szHour + szMinute );
			}
			if ( j == 0 ) {
				r.push( [ iIndex, szHour ] );
			} else {
				r.push( [ iIndex, "" ] );
			}
		}
	}
	return r;
}

$( document ).ready( function( ) {
	var options = {
		legend: { show: true },
		colors: ["#edc240", "#afd8f8", "#cb4b4b", "#4da74d", "#9440ed"],
		lines: { show: true, lineWidth: 1 },
		points: { show: false },
		xaxis: { ticks: Ticks5Mins24Hours },
		yaxis: { min: 0, max: g_QueriesTotal },
		lines: { lineWidth: 1 },
		grid: {
				//color: "#f00",
				tickColor: "#fff",
				borderWidth: 0
		},
		selection: { mode: "x" },
		shadowSize: 0
	};
	var plot = $.plot( $( "#flot" ), [ g_FlotData ], options );
	var plot_min = $.plot($("#flot_min"), [ g_FlotData ], options );
	
	$( "#flot" ).bind( "plotselected", function( event, ranges ) {
		plot = $.plot($("#flot"), [g_FlotData],
			$.extend(true, {}, options, {
			xaxis: { min: ranges.xaxis.from, max: ranges.xaxis.to }
			})
		);
		plot_min.setSelection(ranges, true);
	} );
	
	$("#flot_min").bind("plotselected", function (event, ranges) {
        plot.setSelection(ranges);
    });
	
} );

