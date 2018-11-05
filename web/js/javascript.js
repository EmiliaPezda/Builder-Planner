$(document).ready(function() {

    var wrapper         = $(".input_fields_wrap"); //Fields wrapper
    var add_button      = $("#add_input"); //Add button ID

    var materialAdd = $("#form_materials");

    var x = 1; //initlal text box count
    add_button.click(function(e){ //on add input button click
        e.preventDefault();
        x++; //text box increment
        $(wrapper).append('<input type="text" name="form[materials]" required="required" class="form-control"><a href="#" class="remove_field"><span class="glyphicon glyphicon-remove"></span></a>'); //add input box
    });

    wrapper.on("click",".remove_field", function(e){ //user click on remove text
        e.preventDefault(); $(this).parent('div').remove();
        x--;
    });

    var tasks = $("div.task"); //all div with tasks
    var editButtons = $("button.edit"); //edit buttons

    var commentButtons = $("span.comments"); //comment buttons

    commentButtons.one('click', function(){
        var commentedTaskId=$(this).parent().parent().parent().attr('id');
        $(this).parent().append('<br><form id="addComment" method="POST"><input type="text" id="comment" name="comment"/><input type="submit" style="display: none" /></form>');
    });


    $('#comment').keydown(function(event) {
        if (event.keyCode == 13) {
            this.form.submit();
            return false;
        }
    });


    var modal = $('#myModal');

    editButtons.on('click', function(){
        var editedTask = $(this).parent().parent();
        $('#form_save').html('Edit task');
    });

    $('#buttonAddTask').on('click', function(){
        $('#form_save').html('Add task');
    });

    var doneButtons = $("button.glyphicon-ok"); //mark as done buttons

    doneButtons.on('click', function(){
        var clickedTask = $(this).parent().parent();
        var IdOfTask = clickedTask.attr('id');


    var project_name = $('#projectName').html();

        $('a.btn-success').click(function(){

                $.ajax({
                    url: "./"+project_name,
                    type: "GET",
                    data: {"taskId": IdOfTask},
                    success: function (response) {
                        alert('task marked as done');
                        //window.location.reload();
                    }
                });
        });
   
    });

});
