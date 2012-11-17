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
	
    $('.new_board').click(function(){
        $.ajax({
            type: "POST",
            url: "forum/process.php",
            dataType: 'json',
            data: {
                ajax: 1, 
                element: $(this).data('element'), 
                data1: $('#title_'+$(this).data('element')).html(),
                data2: $('#content_'+$(this).data('element')).html(),
                data3: $('#moderators_'+$(this).data('element')).val()
            }
        }).done(function( msg ) {
            resultBlock(msg);
            document.location.reload(true);
        });
    });
    
    $('.new_subboard').click(function(){
        $.ajax({
            type: "POST",
            url: "forum/process.php",
            dataType: 'json',
            data: {
                ajax: 2, 
                element: $(this).data('element'), 
                data1: $('#title_'+$(this).data('element')).html(),
                data2: $('#content_'+$(this).data('element')).html(),
                data3: $('#moderators_'+$(this).data('element')).val()
            }
        }).done(function( msg ) {
            resultBlock(msg);
            document.location.reload(true);
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
    
    tinyMCE.init({
    	theme : "advanced",
        mode : "textareas",
        /*theme_advanced_styles : "Code=codeStyle;Quote=quoteStyle",
        plugins : "bbcode",
        theme_advanced_buttons1 : "bold,italic,underline,undo,redo,link,unlink,image,forecolor,styleselect,removeformat,cleanup,code",
        theme_advanced_buttons2 : "",
        theme_advanced_buttons3 : "",
        theme_advanced_toolbar_location : "bottom",
        theme_advanced_toolbar_align : "center",
        content_css : "css/bbcode.css",
        add_unload_trigger : false,
        remove_linebreaks : false,
        inline_styles : false,
        convert_fonts_to_spans : false,*/
        entity_encoding : "raw"
    });
});