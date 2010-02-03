var conditionalForm =
{
	// hide zone
	clearZone : function(zone)
	{		
		// hide selected zone
		$('li#' + zone).each(function()
		{
			// hide sub zone that should be hidden
			$(this).find('li.toHide').each(function()
			{
				$(this).hide('normal');
			});
			$(this).hide('normal');
		});
		// reset text inputs and selects, except radio and checkbox buttons
		$('li#' + zone + ' :input').each(function()
		{
			$(this).not(':radio, :checkbox').val('');
			$(this).removeAttr('checked').removeAttr('selected');
		});
	},
	// manage fields controled by boolean checkbox inputs
	handleCheckboxBoolean : function(zone, questionid, activationvalue)
	{
		$('li#' + zone).css('display','none');
	
		$('li#field' + questionid + ' :checkbox').bind('click change',function ()
		{
			var checked = $(this).is(':checked');
			if(checked)
			{
				var value = $(this).val();
				if(value == activationvalue)
				{
					$('li#' + zone).show('normal');
				}
				else
				{
					conditionalForm.clearZone(zone);
				}
			}
			else
			{
				conditionalForm.clearZone(zone);
			}
		}).change();
	},
	// manage fields controled by checkbox inputs
	handleCheckbox : function(zone, fieldid, activationvalue)
	{
		$('li#' + zone).css('display','none');
	
		$('label#' + fieldid + ' :checkbox').bind('click change',function ()
		{		
			var checked = $(this).is(':checked');	
			if(checked)
			{
				$('li#' + zone).show('normal');
			}
			else
			{
				conditionalForm.clearZone(zone);
			}
		}).change();
	},
	// manage fields controled by list
	handleList : function(zone, fieldid, activationvalue)
	{
		$('li#' + zone).css('display','none');
	
		$('select#' + fieldid).change(function () 
		{
			$('select#' + fieldid + ' option:selected').each(function () 
			{
				var value = $(this).val();				
				if (value == activationvalue)
				{
					$('li#' + zone).show('normal');
				}
				else
				{
					conditionalForm.clearZone(zone);
				}                
	        });
		}).change();
	},
	// manage fields controled by radio inputs
	handleRadio : function(zone, questionid, activationvalue)
	{
		$('li#' + zone).css('display','none');

		$('li#field' + questionid + ' :radio').bind('click change',function ()
		{
			var checked = $(this).is(':checked');
			
			if(checked)			
			{
				var value = $(this).val();
				if(value == activationvalue)
				{
					$('li#' + zone).show('normal');
				}
				else
				{					
					conditionalForm.clearZone(zone);
				}
			}
		}).change();
	}
}

var CAPTCHA =
{
	imageURL : '',
	reload : function(input)
	{
		input.setAttribute('src', this.buildCaptchaImageURL())
	},
	buildCaptchaImageURL : function()
	{
		return this.imageURL.replace(new RegExp('amp;', 'g'), '') + '&rnd=' + Math.random();
	}
}