/* Copyright (c) 2007 Kelvin Luck (http://www.kelvinluck.com/)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 */
(function($){
	$.fn.extend({
		renderCalendar  :   function(s)
		{
			var dc = function(a)
			{
				return document.createElement(a);
			};
			s = $.extend(
				{
					month			: null,
					year			: null,
					renderCallback	: null,
					showHeader		: $.dpConst.SHOW_HEADER_SHORT,
					dpController	: null,
					hoverClass		: 'dp-hover'
				}
				, s
			);
			if (s.showHeader != $.dpConst.SHOW_HEADER_NONE) {
				var headRow = $(dc('tr'));
				for (var i=Date.firstDayOfWeek; i<Date.firstDayOfWeek+7; i++) {
					var weekday = i%7;
					var day = Date.dayNames[weekday];
					headRow.append(
						jQuery(dc('th')).attr({'scope':'col', 'abbr':day, 'title':day, 'class':(weekday == 0 || weekday == 6 ? 'weekend' : 'weekday')}).html(s.showHeader == $.dpConst.SHOW_HEADER_SHORT ? day.substr(0, 1) : day)
					);
				}
			};
			var calendarTable = $(dc('table'))
									.attr(
										{
											'cellspacing':2,
											'className':'jCalendar'
										}
									)
									.append(
										(s.showHeader != $.dpConst.SHOW_HEADER_NONE ?
											$(dc('thead'))
												.append(headRow)
											:
											dc('thead')
										)
									);
			var tbody = $(dc('tbody'));
			var today = (new Date()).zeroTime();
			var month = s.month == undefined ? today.getMonth() : s.month;
			var year = s.year || today.getFullYear();
			var currentDate = new Date(year, month, 1);
			var firstDayOffset = Date.firstDayOfWeek - currentDate.getDay() + 1;
			if (firstDayOffset > 1) firstDayOffset -= 7;
			currentDate.addDays(firstDayOffset-1);
			var doHover = function()
			{
				if (s.hoverClass) {
					$(this).addClass(s.hoverClass);
				}
			};
			var unHover = function()
			{
				if (s.hoverClass) {
					$(this).removeClass(s.hoverClass);
				}
			};
			var w = 0;
			while (w++<6) {
				var r = jQuery(dc('tr'));
				for (var i=0; i<7; i++) {
					var thisMonth = currentDate.getMonth() == month;
					var d = $(dc('td'))
								.text(currentDate.getDate() + '')
								.attr('className', (thisMonth ? 'current-month ' : 'other-month ') +
													(currentDate.isWeekend() ? 'weekend ' : 'weekday ') +
													(thisMonth && currentDate.getTime() == today.getTime() ? 'today ' : '')
								)
								.hover(doHover, unHover)
							;
					if (s.renderCallback) {
						s.renderCallback(d, currentDate, month, year);
					}
					r.append(d);
					currentDate.addDays(1);
				}
				tbody.append(r);
			}
			calendarTable.append(tbody);

			return this.each(
				function()
				{
					$(this).empty().append(calendarTable);
				}
			);
		},
		datePicker : function(s)
		{
			if (!$.event._dpCache) $.event._dpCache = [];
			s = $.extend(
				{
					month				: undefined,
					year				: undefined,
					startDate			: undefined,
					endDate				: undefined,
					renderCallback		: [],
					createButton		: true,
					showYearNavigation	: true,
					closeOnSelect		: true,
					displayClose		: false,
					selectMultiple		: false,
					clickInput			: false,
					verticalPosition	: $.dpConst.POS_TOP,
					horizontalPosition	: $.dpConst.POS_LEFT,
					verticalOffset		: 0,
					horizontalOffset	: 0,
					hoverClass			: 'dp-hover'
				}
				, s
			);
			return this.each(
				function()
				{
					var $this = $(this);
					if (!this._dpId) {
						this._dpId = $.event.guid++;
						$.event._dpCache[this._dpId] = new DatePicker(this);
					}
					var controller = $.event._dpCache[this._dpId];
					controller.init(s);
					if (s.createButton) {
						controller.button = $('<a href="#" class="dp-choose-date" title="' + $.dpText.TEXT_CHOOSE_DATE + '">&nbsp;&nbsp;&nbsp;&nbsp;</a>')
								.bind(
									'click',
									function()
									{
										$this.dpDisplay(this);
										this.blur();
										return false;
									}
								);
						$this.after(controller.button);
					}
					if ($this.is(':text')) {
						$this
							.bind(
								'dateSelected',
								function(e, selectedDate, $td)
								{
									this.value = selectedDate.asString();
								}
							).bind(
								'change',
								function()
								{
									var d = Date.fromString(this.value);
									if (d) {
										controller.setSelected(d, true, true);
									}
								}
							);
						if (s.clickInput) {
							$this.bind(
								'click',
								function()
								{
									$this.dpDisplay();
								}
							);
						}
					}
					$this.addClass('dp-applied');
				}
			)
		},
		dpSetDisabled : function(s)
		{
			return _w.call(this, 'setDisabled', s);
		},
		dpSetStartDate : function(d)
		{
			return _w.call(this, 'setStartDate', d);
		},
		dpSetEndDate : function(d)
		{
			return _w.call(this, 'setEndDate', d);
		},
		dpGetSelected : function()
		{
			var c = _getController(this[0]);
			if (c) {
				return c.getSelected();
			}
			return null;
		},
		dpSetSelected : function(d, v, m)
		{
			if (v == undefined) v=true;
			if (m == undefined) m=true;
			return _w.call(this, 'setSelected', d, v, m);
		},
		dpSetDisplayedMonth : function(m, y)
		{
			return _w.call(this, 'setDisplayedMonth', Number(m), Number(y));
		},
		dpDisplay : function(e)
		{
			return _w.call(this, 'display', e);
		},
		dpSetRenderCallback : function(a)
		{
			return _w.call(this, 'setRenderCallback', a);
		},
		dpSetPosition : function(v, h)
		{
			return _w.call(this, 'setPosition', v, h);
		},
		dpSetOffset : function(v, h)
		{
			return _w.call(this, 'setOffset', v, h);
		},
		_dpDestroy : function()
		{
		}
	});
	var _w = function(f, a1, a2, a3)
	{
		return this.each(
			function()
			{
				var c = _getController(this);
				if (c) {
					c[f](a1, a2, a3);
				}
			}
		);
	};
	function DatePicker(ele)
	{
		this.ele = ele;
		this.displayedMonth		=	null;
		this.displayedYear		=	null;
		this.startDate			=	null;
		this.endDate			=	null;
		this.showYearNavigation	=	null;
		this.closeOnSelect		=	null;
		this.displayClose		=	null;
		this.selectMultiple		=	null;
		this.verticalPosition	=	null;
		this.horizontalPosition	=	null;
		this.verticalOffset		=	null;
		this.horizontalOffset	=	null;
		this.button				=	null;
		this.renderCallback		=	[];
		this.selectedDates		=	{};
	};
	$.extend(
		DatePicker.prototype,
		{
			init : function(s)
			{
				this.setStartDate(s.startDate);
				this.setEndDate(s.endDate);
				this.setDisplayedMonth(Number(s.month), Number(s.year));
				this.setRenderCallback(s.renderCallback);
				this.showYearNavigation = s.showYearNavigation;
				this.closeOnSelect = s.closeOnSelect;
				this.displayClose = s.displayClose;
				this.selectMultiple = s.selectMultiple;
				this.verticalPosition = s.verticalPosition;
				this.horizontalPosition = s.horizontalPosition;
				this.hoverClass = s.hoverClass;
				this.setOffset(s.verticalOffset, s.horizontalOffset);
			},
			setStartDate : function(d)
			{
				if (d) {
					this.startDate = Date.fromString(d);
				}
				if (!this.startDate) {
					this.startDate = (new Date()).zeroTime();
				}
				this.setDisplayedMonth(this.displayedMonth, this.displayedYear);
			},
			setEndDate : function(d)
			{
				if (d) {
					this.endDate = Date.fromString(d);
				}
				if (!this.endDate) {
					this.endDate = (new Date('12/31/2999'));
				}
				if (this.endDate.getTime() < this.startDate.getTime()) {
					this.endDate = this.startDate;
				}
				this.setDisplayedMonth(this.displayedMonth, this.displayedYear);
			},
			setPosition : function(v, h)
			{
				this.verticalPosition = v;
				this.horizontalPosition = h;
			},
			setOffset : function(v, h)
			{
				this.verticalOffset = parseInt(v) || 0;
				this.horizontalOffset = parseInt(h) || 0;
			},
			setDisabled : function(s)
			{
				$e = $(this.ele);
				$e[s ? 'addClass' : 'removeClass']('dp-disabled');
				if (this.button) {
					$but = $(this.button);
					$but[s ? 'addClass' : 'removeClass']('dp-disabled');
					$but.attr('title', s ? '' : $.dpText.TEXT_CHOOSE_DATE);
				}
				if ($e.is(':text')) {
					$e.attr('disabled', s ? 'disabled' : '');
				}
			},
			setDisplayedMonth : function(m, y)
			{
				if (this.startDate == undefined || this.endDate == undefined) {
					return;
				}
				var s = new Date(this.startDate.getTime());
				s.setDate(1);
				var e = new Date(this.endDate.getTime());
				e.setDate(1);

				var t;

				if (isNaN(m) && isNaN(y)) {
					t = new Date().zeroTime();
					t.setDate(1);
				} else if (isNaN(m)) {
					t = new Date(y, this.displayedMonth, 1);
				} else if (isNaN(y)) {
					t = new Date(this.displayedYear, m, 1);
				} else {
					t = new Date(y, m, 1)
				}
				if (t.getTime() < s.getTime()) {
					t = s;
				} else if (t.getTime() > e.getTime()) {
					t = e;
				}
				this.displayedMonth = t.getMonth();
				this.displayedYear = t.getFullYear();
			},
			setSelected : function(d, v, moveToMonth)
			{
				if (this.selectMultiple == false) {
					this.selectedDates = {};
				}
				if (moveToMonth) {
					this.setDisplayedMonth(d.getMonth(), d.getFullYear());
				}
				this.selectedDates[d.getTime()] = v;
			},
			isSelected : function(t)
			{
				return this.selectedDates[t];
			},
			getSelected : function()
			{
				var r = [];
				for(t in this.selectedDates) {
					if (this.selectedDates[t] == true) {
						r.push(new Date(Number(t)));
					}
				}
				return r;
			},
			display : function(eleAlignTo)
			{
				if ($(this.ele).is('.dp-disabled')) return;
				eleAlignTo = eleAlignTo || this.ele;
				var c = this;
				var $ele = $(eleAlignTo);
				var eleOffset = $ele.offset();
				var _checkMouse = function(e)
				{
					var el = e.target;
					var cal = $('#dp-popup')[0];
					while (true){
						if (el == cal) {
							return true;
						} else if (el == document) {
							c._closeCalendar();
							return false;
						} else {
							el = $(el).parent()[0];
						}
					}
				};
				this._checkMouse = _checkMouse;
				this._closeCalendar(true)
				$('body')
					.append(
						$('<div></div>')
							.attr('id', 'dp-popup')
							.css(
								{
									'top'	:	eleOffset.top + c.verticalOffset,
									'left'	:	eleOffset.left + c.horizontalOffset
								}
							)
							.append(
								$('<h2></h2>'),
								$('<div id="dp-nav-prev"></div>')
									.append(
										$('<a id="dp-nav-prev-year" href="#" title="' + $.dpText.TEXT_PREV_YEAR + '">&lt;&lt;</a>')
											.bind(
												'click',
												function()
												{
													return c._displayNewMonth.call(c, this, 0, -1);
												}
											),
										$('<a id="dp-nav-prev-month" href="#" title="' + $.dpText.TEXT_PREV_MONTH + '">&lt;</a>')
											.bind(
												'click',
												function()
												{
													return c._displayNewMonth.call(c, this, -1, 0);
												}
											)
									),
								$('<div id="dp-nav-next"></div>')
									.append(
										$('<a id="dp-nav-next-year" href="#" title="' + $.dpText.TEXT_NEXT_YEAR + '">&gt;&gt;</a>')
											.bind(
												'click',
												function()
												{
													return c._displayNewMonth.call(c, this, 0, 1);
												}
											),
										$('<a id="dp-nav-next-month" href="#" title="' + $.dpText.TEXT_NEXT_MONTH + '">&gt;</a>')
											.bind(
												'click',
												function()
												{
													return c._displayNewMonth.call(c, this, 1, 0);
												}
											)
									),
								$('<div></div>')
									.attr('id', 'dp-calendar')
							)
							.bgIframe()
						);
				var $pop = $('#dp-popup');
				if (this.showYearNavigation == false) {
					$('#dp-nav-prev-year, #dp-nav-next-year').css('display', 'none');
				}
				if (this.displayClose) {
					$pop.append(
						$('<a href="#" id="dp-close">' + $.dpText.TEXT_CLOSE + '</a>')
							.bind(
								'click',
								function()
								{
									c._closeCalendar();
									return false;
								}
							)
					);
				}
				c._renderCalendar();
				if (this.verticalPosition == $.dpConst.POS_BOTTOM) {
					$pop.css('top', eleOffset.top + $ele.height() - $pop.height() + c.verticalOffset);
				}
				if (this.horizontalPosition == $.dpConst.POS_RIGHT) {
					$pop.css('left', eleOffset.left + $ele.width() - $pop.width() + c.horizontalOffset);
				}
				$(this.ele).trigger('dpDisplayed', $pop);
				$(document).bind('mousedown', this._checkMouse);
			},
			setRenderCallback : function(a)
			{
				if (a && typeof(a) == 'function') {
					a = [a];
				}
				this.renderCallback = this.renderCallback.concat(a);
			},
			cellRender : function ($td, thisDate, month, year) {
				var c = this.dpController;
				var d = new Date(thisDate.getTime());
				$td.bind(
					'click',
					function()
					{
						var $this = $(this);
						if (!$this.is('.disabled')) {
							c.setSelected(d, !$this.is('.selected') || !c.selectMultiple);
							var s = c.isSelected(d.getTime());
							$(c.ele).trigger('dateSelected', [d, $td, s]);
							if (c.closeOnSelect) {
								c._closeCalendar();
							} else {
								$this[s ? 'addClass' : 'removeClass']('selected');
							}
						}
					}
				);
				if (c.isSelected(d.getTime())) {
					$td.addClass('selected');
				}
				for (var i=0; i<c.renderCallback.length; i++) {
					c.renderCallback[i].apply(this, arguments);
				}
			},
			_displayNewMonth : function(ele, m, y)
			{
				if (!$(ele).is('.disabled')) {
					this.setDisplayedMonth(this.displayedMonth + m, this.displayedYear + y);
					this._clearCalendar();
					this._renderCalendar();
					$(this.ele).trigger('dpMonthChanged', [this.displayedMonth, this.displayedYear]);
				}
				ele.blur();
				return false;
			},
			_renderCalendar : function()
			{
				$('#dp-popup h2').html(Date.monthNames[this.displayedMonth] + ' ' + this.displayedYear);
				$('#dp-calendar').renderCalendar(
					{
						month			: this.displayedMonth,
						year			: this.displayedYear,
						renderCallback	: this.cellRender,
						dpController	: this,
						hoverClass		: this.hoverClass
					}
				);
				if (this.displayedYear == this.startDate.getFullYear() && this.displayedMonth == this.startDate.getMonth()) {
					$('#dp-nav-prev-year').addClass('disabled');
					$('#dp-nav-prev-month').addClass('disabled');
					$('#dp-calendar td.other-month').each(
						function()
						{
							var $this = $(this);
							if (Number($this.text()) > 20) {
								$this.addClass('disabled');
							}
						}
					);
					var d = this.startDate.getDate();
					$('#dp-calendar td.current-month').each(
						function()
						{
							var $this = $(this);
							if (Number($this.text()) < d) {
								$this.addClass('disabled');
							}
						}
					);
				} else {
					$('#dp-nav-prev-year').removeClass('disabled');
					$('#dp-nav-prev-month').removeClass('disabled');
					var d = this.startDate.getDate();
					if (d > 20) {
						// check if the startDate is last month as we might need to add some disabled classes...
						var sd = new Date(this.startDate.getTime());
						sd.addMonths(1);
						if (this.displayedYear == sd.getFullYear() && this.displayedMonth == sd.getMonth()) {
							$('#dp-calendar td.other-month').each(
								function()
								{
									var $this = $(this);
									if (Number($this.text()) < d) {
										$this.addClass('disabled');
									}
								}
							);
						}
					}
				}
				if (this.displayedYear == this.endDate.getFullYear() && this.displayedMonth == this.endDate.getMonth()) {
					$('#dp-nav-next-year').addClass('disabled');
					$('#dp-nav-next-month').addClass('disabled');
					$('#dp-calendar td.other-month').each(
						function()
						{
							var $this = $(this);
							if (Number($this.text()) < 14) {
								$this.addClass('disabled');
							}
						}
					);
					var d = this.endDate.getDate();
					$('#dp-calendar td.current-month').each(
						function()
						{
							var $this = $(this);
							if (Number($this.text()) > d) {
								$this.addClass('disabled');
							}
						}
					);
				} else {
					$('#dp-nav-next-year').removeClass('disabled');
					$('#dp-nav-next-month').removeClass('disabled');
					var d = this.endDate.getDate();
					if (d < 13) {
						// check if the endDate is next month as we might need to add some disabled classes...
						var ed = new Date(this.endDate.getTime());
						ed.addMonths(-1);
						if (this.displayedYear == ed.getFullYear() && this.displayedMonth == ed.getMonth()) {
							$('#dp-calendar td.other-month').each(
								function()
								{
									var $this = $(this);
									if (Number($this.text()) > d) {
										$this.addClass('disabled');
									}
								}
							);
						}
					}
				}
			},
			_closeCalendar : function(programatic)
			{
				$(document).unbind('mousedown', this._checkMouse);
				this._clearCalendar();
				$('#dp-popup a').unbind();
				$('#dp-popup').empty().remove();
				if (!programatic) {
					$(this.ele).trigger('dpClosed', [this.getSelected()]);
				}
			},
			_clearCalendar : function()
			{
				$('#dp-calendar td').unbind();
				$('#dp-calendar').empty();
			}
		}
	);
	$.dpConst = {
		SHOW_HEADER_NONE	:	0,
		SHOW_HEADER_SHORT	:	1,
		SHOW_HEADER_LONG	:	2,
		POS_TOP				:	0,
		POS_BOTTOM			:	1,
		POS_LEFT			:	0,
		POS_RIGHT			:	1
	};
	$.dpText = {
		TEXT_PREV_YEAR		:	'&modules.form.frontoffice.datepicker.Previous-year;',  // Previous year
		TEXT_PREV_MONTH		:	'&modules.form.frontoffice.datepicker.Previous-month;', // Previous month
		TEXT_NEXT_YEAR		:	'&modules.form.frontoffice.datepicker.Next-year;',      // Next year
		TEXT_NEXT_MONTH		:	'&modules.form.frontoffice.datepicker.Next-month;',     // Next month
		TEXT_CLOSE			:	'&modules.form.frontoffice.datepicker.Close;',          // Close
		TEXT_CHOOSE_DATE	:	'&modules.form.frontoffice.datepicker.Choose-date;'     // Choose date
	};
	$.dpVersion = '$Id: jquery.datePicker.js 1933 2007-05-20 19:10:39Z kelvin.luck $';
	function _getController(ele)
	{
		if (ele._dpId) return $.event._dpCache[ele._dpId];
		return false;
	};
	if ($.fn.bgIframe == undefined) {
		$.fn.bgIframe = function() {return this; };
	};
	$(window)
		.bind('unload', function() {
			var els = $.event._dpCache || [];
			for (var i in els) {
				$(els[i].ele)._dpDestroy();
			}
		});
})(jQuery);