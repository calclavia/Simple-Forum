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

    var appendString = '<ul>';
	
    for (var i = 0; i < data.length; i++)
    {
        appendString += '<li>'+data[i]+'</li>';
    }
	
    appendString += '</ul>';
	
    $("#forum_notifications").append(appendString);
    $("#forum_notifications").slideDown('slow').delay(5000).slideUp('slow');
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
	
	$('.new_board_button').click(function(){
		$('#title_'+$(this).data('forum-target')).attr('contenteditable', 'true');
		$('#content_'+$(this).data('forum-target')).attr('contenteditable', 'true');
		$('#newBoard_'+$(this).data('forum-target')).stop(true, true).slideToggle();
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

    $('.thread_edit').click(function(){
	    if($(this).hasClass('editing'))
	    {
	        $.ajax({
	            type: "POST",
	            url: "forum/process.php",
	            dataType: 'json',
	            data: {
	                ajax: 3, 
	                element: $(this).data('forum-target'), 
	                data1: $('#thread_title_'+$(this).data('forum-target')).html(),
	                data2: $('#sticky_'+$(this).data('forum-target')).is(':checked'),
	                data3: $('#lock_'+$(this).data('forum-target')).is(':checked')
	            }
	        }).done(function( msg ) {
	            resultBlock(msg);
	        });
			
	        $('#thread_title_'+$(this).data('forum-target')).attr('contenteditable', 'false');
	        $('#sticky_'+$(this).data('forum-target')).parent().slideUp();
	        $('#lock_'+$(this).data('forum-target')).parent().slideUp();
	        $(this).html('Edit');
	        $(this).removeClass('editing');
	    }
	    else
	    {
	        $('#thread_title_'+$(this).data('forum-target')).attr('contenteditable', 'true');
	        $('#sticky_'+$(this).data('forum-target')).parent().slideDown();
	        $('#lock_'+$(this).data('forum-target')).parent().slideDown();
	        $('#thread_title_'+$(this).data('forum-target')).focus();
	        $(this).html("Save");
	        $(this).addClass('editing');
	    }
	});
    
    $('.category_edit').click(function(){
	    if($(this).hasClass('editing'))
	    {
	        $.ajax({
	            type: "POST",
	            url: "forum/process.php",
	            dataType: 'json',
	            data: {
	                ajax: 4, 
	                element: $(this).data('forum-target'), 
	                data1: $('#category_title_'+$(this).data('forum-target')).html()
	            }
	        }).done(function( msg ) {
	            resultBlock(msg);
	        });
			
	        $('#category_title_'+$(this).data('forum-target')).attr('contenteditable', 'false');
	        $(this).html('Edit');
	        $(this).removeClass('editing');
	    }
	    else
	    {
	        $('#category_title_'+$(this).data('forum-target')).attr('contenteditable', 'true');
	        $('#category_title_'+$(this).data('forum-target')).focus();
	        $(this).html("Save");
	        $(this).addClass('editing');
	    }
	});
    
    $('.board_edit').click(function(){
	    if($(this).hasClass('editing'))
	    {
	        $.ajax({
	            type: "POST",
	            url: "forum/process.php",
	            dataType: 'json',
	            data: {
	                ajax: 5, 
	                element: $(this).data('forum-target'), 
	                data1: $('#board_title_'+$(this).data('forum-target')).html(),
	                data2: $('#board_description_'+$(this).data('forum-target')).html()
	            }
	        }).done(function( msg ) {
	            resultBlock(msg);
	        });
			
	        $('#board_title_'+$(this).data('forum-target')).attr('contenteditable', 'false');
	        $('#board_description_'+$(this).data('forum-target')).attr('contenteditable', 'false');
	        $(this).html('Edit');
	        $(this).removeClass('editing');
	    }
	    else
	    {
	        $('#board_title_'+$(this).data('forum-target')).attr('contenteditable', 'true');
	        $('#board_description_'+$(this).data('forum-target')).attr('contenteditable', 'true');
	        $('#board_title_'+$(this).data('forum-target')).focus();
	        $(this).html("Save");
	        $(this).addClass('editing');
	    }
	});
    
    $('.board_box').hover(function() {
    	$(this).find('.sub_boards').stop(true, true).slideDown('slow');
    }, function() {
    	$(this).find('.sub_boards').stop(true, true).slideUp('slow');
    });
    
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
    
    $('.editors').each(function(){
    	CKEDITOR.replace($(this).attr('id'), {
            height:'250', 
            width: '548'
        });
    });
});