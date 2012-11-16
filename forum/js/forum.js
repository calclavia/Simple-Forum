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

function resultBlock(data)
{
    $("#forum_notifications").empty();
    $("#forum_notifications").fadeIn('fast');

    var appendString = '<ul>';
	
    for (var i = 0; i < data.length; i++)
    {
        appendString += '<li>'+data[i]+'</li>';
    }
	
    appendString += '</ul>';
	
    $("#forum_notifications").append(appendString);
	
    $("#forum_notifications").fadeOut(10000);
}

var lastPostEditor;

$(document).ready(function() {
	
    $('.process_edit').click(function(){
        $.ajax({
            type: "POST",
            url: "forum/process.php",
            dataType: 'json',
            data: {
                ajax: $(this).data('type'), 
                e: $(this).attr('name'), 
                data: $(this).parent().find('.process_data').html()
            }
        }).done(function( msg ) {
            resultBlock(msg);
        });
    });
	
    $('.quick_edit').blur(function(){
        if($(this).html() != "")
        {
            $.ajax({
                type: "POST",
                url: "forum/process.php",
                dataType: 'json',
                data: {
                    ajax: $(this).data('type'), 
                    e: $(this).attr('name'), 
                    data: $(this).html()
                }
            }).done(function( msg ) {
                resultBlock(msg);
            });
        }
    });
	
    $('.post_edit').click(function(){
		
        if(lastPostEditor != null)
        {
            lastPostEditor.destroy();
        }
		
        if($(this).hasClass('editing'))
        {
            $.ajax({
                type: "POST",
                url: "forum/process.php",
                dataType: 'json',
                data: {
                    ajax: 'post_edit', 
                    e: $(this).data('forum-target'), 
                    data: $('#post_content_'+$(this).data('forum-target')).html()
                }
            }).done(function( msg ) {
                resultBlock(msg);
            });
			
            $(this).html('Edit');
            $(this).removeClass('editing');
        }
        else
        {
            lastPostEditor = CKEDITOR.replace('post_content_'+$(this).data('forum-target'), {
                height:'250', 
                width: '548'
            });
            $(this).html("Save");
            $(this).addClass('editing');
        }
    });
	
    $('.post_edit_active')
	
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