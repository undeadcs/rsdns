//
$( document ).ready( function( ) {
	//
	$( "#date1" ).date_input( prepareDateInput( ) );
	$( "#date2" ).date_input( prepareDateInput( ) );
	//
} );

function prepareDateInput( ) {
	DateInput.prototype.stringToDate = function( string ) {
		var matches;
		//if (matches = string.match(/^(\d{1,2})-([^\s]+)-(\d{4,4})$/)) return new Date(matches[3], this.shortMonthNum(matches[2]), matches[1]);
		if (matches = string.match(/^(\d{4,4})-([^\s]+)-(\d{1,2})$/)) return new Date(matches[1], this.shortMonthNum(matches[2]), matches[3]);
		else return null;
	}
	  
	DateInput.prototype.dateToString = function(date) {
		var d = date.getDate()+'';
		if (d.length == 1) d = '0'+d;
		return date.getFullYear() + "-" + this.short_month_names[date.getMonth()] + "-" + d ;
	}
	
	var opts = { short_month_names: ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"] };
	opts.month_names = ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"];
	opts.short_day_names = ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"];
	return opts;
}
