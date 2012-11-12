function lightBox(targetID)
{
		document.getElementById(targetID).style.display='block';
		document.getElementById('fade').style.display='block';
}

function closeLightBox()
{				
		if (document.getElementsByClassName)
		{
				var elements = document.getElementsByClassName('white_content');

				for (var i = 0; i < elements.length; i++)
				{
						elements[i].style.display='none';
				}

				document.getElementById('fade').style.display='none';
		}
		else
		{
				alert ('Your browser does not support the getElementsByClassName method. Please update your browser!');
		}
}

function allowDrop(ev)
{
		ev.preventDefault();
}

function drag(ev, id)
{
		ev.dataTransfer.setData('id', id);
}

function drop(ev, targetID)
{
		ev.preventDefault();

		if(targetID != ev.dataTransfer.getData('id'))
		{
			window.location = 'forum.php?p='+ev.dataTransfer.getData('id')+'&o='+targetID;
		}
}

function move(ev, targetID)
{
		ev.preventDefault();

		if(targetID != ev.dataTransfer.getData('id'))
		{
			window.location = 'forum.php?p='+ev.dataTransfer.getData('id')+'&m='+targetID;
		}
}

$(document).ready(function() {
	
	$('.draggable').hover(function(){
		$(this).find('.dragText').stop(true, true).fadeIn('slow');
	},
	function(){
		$(this).find('.dragText').stop(true, true).fadeOut('slow');
	});

	$('.inlineEdit').bind('focus, mouseover', function()
	{
		$(this).parent().find('.inline_form').fadeIn('slow');
	});

	$('.inlineEdit').blur(function()
	{
		$(this).parent().find('.inline_form').fadeOut('slow');
	});
});