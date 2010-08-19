var conditionalForm =
{
	// hide zone
	clearZone : function(zone)
	{		
		// hide selected zone
		jQuery('li#' + zone).each(function()
		{
			// hide sub zone that should be hidden
			jQuery(this).find('li.toHide').each(function()
			{
				jQuery(this).hide('normal');
			});
			jQuery(this).hide('normal');
		});
		// reset text inputs and selects, except radio and checkbox buttons
		jQuery('li#' + zone + ' :input').each(function()
		{
			jQuery(this).not(':radio, :checkbox').val('');
			jQuery(this).removeAttr('checked').removeAttr('selected');
		});
	},
	// manage fields controled by boolean checkbox inputs
	handleCheckboxBoolean : function(zone, questionid, activationvalue)
	{
		jQuery('li#' + zone).css('display','none');
	
		jQuery('li#field' + questionid + ' :checkbox').bind('click change',function ()
		{
			var checked = jQuery(this).is(':checked');
			if(checked)
			{
				var value = jQuery(this).val();
				if(value == activationvalue)
				{
					jQuery('li#' + zone).show('normal');
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
		jQuery('li#' + zone).css('display','none');
	
		jQuery('label#' + fieldid + ' :checkbox').bind('click change',function ()
		{		
			var checked = jQuery(this).is(':checked');	
			if(checked)
			{
				jQuery('li#' + zone).show('normal');
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
		jQuery('li#' + zone).css('display','none');
	
		jQuery('select#' + fieldid).change(function () 
		{
			jQuery('select#' + fieldid + ' option:selected').each(function () 
			{
				var value = jQuery(this).val();				
				if (value == activationvalue)
				{
					jQuery('li#' + zone).show('normal');
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
		jQuery('li#' + zone).css('display','none');

		jQuery('li#field' + questionid + ' :radio').bind('click change',function ()
		{
			var checked = jQuery(this).is(':checked');
			
			if(checked)			
			{
				var value = jQuery(this).val();
				if(value == activationvalue)
				{
					jQuery('li#' + zone).show('normal');
				}
				else
				{					
					conditionalForm.clearZone(zone);
				}
			}
		}).change();
	}
};

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
};