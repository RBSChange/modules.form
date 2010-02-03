/* Copyright (c) 2006 Jörn Zaefferer and Brandon Aaron (brandon.aaron@gmail.com || http://brandonaaron.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
Date.dayNames = [
	'&modules.uixul.bo.datePicker.Calendar.Sunday;',
	'&modules.uixul.bo.datePicker.Calendar.Monday;',
	'&modules.uixul.bo.datePicker.Calendar.Tuesday;',
	'&modules.uixul.bo.datePicker.Calendar.Wednesday;',
	'&modules.uixul.bo.datePicker.Calendar.Thursday;',
	'&modules.uixul.bo.datePicker.Calendar.Friday;',
	'&modules.uixul.bo.datePicker.Calendar.Saturday;'
	];
Date.abbrDayNames = [
	'&modules.uixul.bo.datePicker.Calendar.Abbreviation.Sunday;',
	'&modules.uixul.bo.datePicker.Calendar.Abbreviation.Monday;',
	'&modules.uixul.bo.datePicker.Calendar.Abbreviation.Tuesday;',
	'&modules.uixul.bo.datePicker.Calendar.Abbreviation.Wednesday;',
	'&modules.uixul.bo.datePicker.Calendar.Abbreviation.Thursday;',
	'&modules.uixul.bo.datePicker.Calendar.Abbreviation.Friday;',
	'&modules.uixul.bo.datePicker.Calendar.Abbreviation.Saturday;'
	];
Date.monthNames = [
	'&modules.uixul.bo.datePicker.Calendar.January;',
	'&modules.uixul.bo.datePicker.Calendar.February;',
	'&modules.uixul.bo.datePicker.Calendar.March;',
	'&modules.uixul.bo.datePicker.Calendar.April;',
	'&modules.uixul.bo.datePicker.Calendar.May;',
	'&modules.uixul.bo.datePicker.Calendar.June;',
	'&modules.uixul.bo.datePicker.Calendar.July;',
	'&modules.uixul.bo.datePicker.Calendar.August;',
	'&modules.uixul.bo.datePicker.Calendar.September;',
	'&modules.uixul.bo.datePicker.Calendar.October;',
	'&modules.uixul.bo.datePicker.Calendar.November;',
	'&modules.uixul.bo.datePicker.Calendar.December;'
	];
Date.abbrMonthNames = [
	'&modules.uixul.bo.datePicker.Calendar.Abbreviation.January;',
	'&modules.uixul.bo.datePicker.Calendar.Abbreviation.February;',
	'&modules.uixul.bo.datePicker.Calendar.Abbreviation.March;',
	'&modules.uixul.bo.datePicker.Calendar.Abbreviation.April;',
	'&modules.uixul.bo.datePicker.Calendar.Abbreviation.May;',
	'&modules.uixul.bo.datePicker.Calendar.Abbreviation.June;',
	'&modules.uixul.bo.datePicker.Calendar.Abbreviation.July;',
	'&modules.uixul.bo.datePicker.Calendar.Abbreviation.August;',
	'&modules.uixul.bo.datePicker.Calendar.Abbreviation.September;',
	'&modules.uixul.bo.datePicker.Calendar.Abbreviation.October;',
	'&modules.uixul.bo.datePicker.Calendar.Abbreviation.November;',
	'&modules.uixul.bo.datePicker.Calendar.Abbreviation.December;'
	];
Date.firstDayOfWeek = 1;
Date.format = '&framework.date.date.default-date-format-for-jquery-datepicker;';
(function() {
	function add(name, method) {
		if( !Date.prototype[name] ) {
			Date.prototype[name] = method;
		}
	};
	add("isLeapYear", function() {
		var y = this.getFullYear();
		return (y%4==0 && y%100!=0) || y%400==0;
	});
	add("isWeekend", function() {
		return this.getDay()==0 || this.getDay()==6;
	});
	add("isWeekDay", function() {
		return !this.isWeekend();
	});
	add("getDaysInMonth", function() {
		return [31,(this.isLeapYear() ? 29:28),31,30,31,30,31,31,30,31,30,31][this.getMonth()];
	});
	add("getDayName", function(abbreviated) {
		return abbreviated ? Date.abbrDayNames[this.getDay()] : Date.dayNames[this.getDay()];
	});
	add("getMonthName", function(abbreviated) {
		return abbreviated ? Date.abbrMonthNames[this.getMonth()] : Date.monthNames[this.getMonth()];
	});
	add("getDayOfYear", function() {
		var tmpdtm = new Date("1/1/" + this.getFullYear());
		return Math.floor((this.getTime() - tmpdtm.getTime()) / 86400000);
	});
	add("getWeekOfYear", function() {
		return Math.ceil(this.getDayOfYear() / 7);
	});
	add("setDayOfYear", function(day) {
		this.setMonth(0);
		this.setDate(day);
		return this;
	});
	add("addYears", function(num) {
		this.setFullYear(this.getFullYear() + num);
		return this;
	});
	add("addMonths", function(num) {
		var tmpdtm = this.getDate();

		this.setMonth(this.getMonth() + num);

		if (tmpdtm > this.getDate())
			this.addDays(-this.getDate());

		return this;
	});
	add("addDays", function(num) {
		this.setDate(this.getDate() + num);
		return this;
	});
	add("addHours", function(num) {
		this.setHours(this.getHours() + num);
		return this;
	});
	add("addMinutes", function(num) {
		this.setMinutes(this.getMinutes() + num);
		return this;
	});
	add("addSeconds", function(num) {
		this.setSeconds(this.getSeconds() + num);
		return this;
	});
	add("zeroTime", function() {
		this.setMilliseconds(0);
		this.setSeconds(0);
		this.setMinutes(0);
		this.setHours(0);
		return this;
	});
	add("asString", function() {
		var r = Date.format;
		return r
			.split('yyyy').join(this.getFullYear())
			.split('Y').join(this.getFullYear())
			.split('yy').join(this.getYear())
			.split('y').join(this.getYear())
			.split('M').join(this.getMonthName(true))
			.split('mm').join(_zeroPad(this.getMonth()+1))
			.split('m').join(_zeroPad(this.getMonth()+1))
			.split('dd').join(_zeroPad(this.getDate()))
			.split('d').join(_zeroPad(this.getDate()));
	});
	Date.fromString = function(s)
	{
		var f = Date.format;
		var d = new Date('01/01/1977');
		var iY = f.indexOf('yyyy');
		if (iY > -1) {
			d.setFullYear(Number(s.substr(iY, 4)));
		} else {
			d.setYear(Number(s.substr(f.indexOf('yy'), 2)));
		}
		var iM = f.indexOf('M');
		if (iM > -1) {
			var mStr = s.substr(iM, 3);
			for (var i=0; i<Date.abbrMonthNames.length; i++) {
				if (Date.abbrMonthNames[i] == mStr) break;
			}
			d.setMonth(i);
		} else {
			d.setMonth(Number(s.substr(f.indexOf('mm'), 2)) - 1);
		}
		d.setDate(Number(s.substr(f.indexOf('dd'), 2)));
		if (isNaN(d.getTime())) return false;
		return d;
	};
	var _zeroPad = function(num) {
		var s = '0'+num;
		return s.substring(s.length-2)
	};
})();
