import $ from 'jquery';

class MyNotes {
    constructor() {
        this.events();
    }

    events() {
        $("#my-notes").on("click", ".delete-note",this.deleteNote); 
        $("#my-notes").on("click",".edit-note", this.editNote.bind(this));
        $("#my-notes").on("click",".update-note", this.updateNote.bind(this));
        $(".submit-note").on("click", this.createNote.bind(this));
    }

    // (custom) Methods will go here
    deleteNote(e) {
        var thisNote = $(e.target).parents("li"); // The data id number is in the -> <li data-id="<?php the_ID() ;?>">

        $.ajax({
            beforeSend: (xhr) => {
                xhr.setRequestHeader('X-WP-Nonce', universityData.nonce);
            },
            url: universityData.root_url + '/wp-json/wp/v2/note/' + thisNote.data('id'),
            type: 'DELETE',
            success: (response) => {
                thisNote.slideUp();
                console.log("Congrats");
                console.log(response);
                if(response.userNoteCount < 5){
                    $(".note-limit-message").removeClass("active");
                }
            },
            error: (error) => {
                console.log("Sorry Error");
                console.log(error);
            },
        });
    }
    updateNote(e) {
        var thisNote = $(e.target).parents("li"); // The data id number is in the -> <li data-id="<?php the_ID() ;?>">
        // Wordpress REST API needs these props
        var ourUpdatedPost = {
            'title': thisNote.find(".note-title-field").val(),
            'content': thisNote.find(".note-body-field").val(),
        }
        $.ajax({
            //Sends along a [NONCE] value, so that wordpress can trust our request. So, proves we are who we say we are.
            beforeSend: (xhr) => {
                xhr.setRequestHeader('X-WP-Nonce', universityData.nonce);
            },
            url: universityData.root_url + '/wp-json/wp/v2/note/' + thisNote.data('id'),
            type: 'POST',
            data: ourUpdatedPost,
            success: (response) => {
                this.makeNoteReadOnly(thisNote);
                console.log("Congrats");
                console.log(response);
            },
            error: (error) => {
                console.log("Sorry Error");
                console.log(error);
            },
        });
    }

    createNote(e) {
        var newPost = {
            'title': $(".new-note-title").val(),
            'content': $(".new-note-body").val(),
            'status': 'publish', // by default it is draft
        }
        $.ajax({
            beforeSend: (xhr) => {
                xhr.setRequestHeader('X-WP-Nonce', universityData.nonce);
            },
            url: universityData.root_url + '/wp-json/wp/v2/note/',
            type: 'POST',
            data: newPost,
            success: (response) => {
                $(".new-note-title, .new-note-body").val('');
                $(`
                <li data-id="${response.id}">
                    <input readonly class="note-title-field" type="text" value="${response.title.raw}" />

                        <span class="edit-note"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</span>
                        <span class="delete-note"><i class="fa fa-trash-o" aria-hidden="true"></i> Delete</span>

                        <textarea readonly class="note-body-field" name=""id="">${response.content.raw}</textarea>
                        
                    <span class="update-note btn btn--blue btn--small">
                    <i class="fa fa-arrow-right" aria-hidden="true"></i> Save</span>
                </li>
                    `).prependTo("#my-notes").hide().slideDown();

                console.log("Created a note successfully.");
                console.log(response);
            },
            error: (response) => {
                if(response.responseText == "You have reached your note limit."){
                    $(".note-limit-message").addClass("active");
                }
                console.log("Sorry Error");
                console.log(response);
            },
        });
    }
    editNote(e) {
        var thisNote = $(e.target).parents("li");

        if (thisNote.data("state") == "editable") {
            this.makeNoteReadOnly(thisNote);
        } else {
            this.makeNoteEditable(thisNote);
        }
    }
    makeNoteEditable(thisNote) {
        thisNote.find(".edit-note").html('<i class="fa fa-times" aria-hidden="true"></i> Cancel');
        thisNote.find(".note-title-field, .note-body-field").removeAttr("readonly").addClass("note-active-field");
        thisNote.find(".update-note").addClass("update-note--visible");
        thisNote.data("state", "editable");
    }
    makeNoteReadOnly(thisNote) {
        thisNote.find(".edit-note").html('<i class="fa fa-pencil" aria-hidden="true"></i> Edit');
        thisNote.find(".note-title-field, .note-body-field").attr("readonly", "readonly").removeClass("note-active-field");
        thisNote.find(".update-note").removeClass("update-note--visible");
        thisNote.data("state", "cancel");
    }
}



export default MyNotes;