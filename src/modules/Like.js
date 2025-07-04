import $ from 'jquery';

class Like {
    constructor() {
        this.events();
    }
    events() {
        $(".like-box").on("click", this.ourClickDispatcher.bind(this));
    }

    // Methods
    ourClickDispatcher(e) {
        // Guarantees that the parent div gets selected if you click child element like the heart
        var currentLikeBox = $(e.target).closest(".like-box");

        if (currentLikeBox.attr('data-exists') == 'yes') {
            this.deleteLike(currentLikeBox);
        } else {
            this.createLike(currentLikeBox);
        }
    }
    createLike(currentLikeBox) {
        $.ajax({
            //Sends along a [NONCE] value, so that wordpress can trust our request. So, proves we are who we say we are.
            beforeSend: (xhr) => {
                xhr.setRequestHeader('X-WP-Nonce', universityData.nonce);
            },
            url: universityData.root_url + '/wp-json/university/v1/manageLike',
            type: 'POST',
            'data': { 'professorId': currentLikeBox.data('professor') },
            success: (response) => {
                currentLikeBox.attr('data-exists','yes'); //updates empty heart into solid
                var likeCount = parseInt(currentLikeBox.find(".like-count").html(), 10);
                likeCount++;
                currentLikeBox.find(".like-count").html(likeCount);
                currentLikeBox.attr("data-like", response);
                console.log(response);
            },
            error: (response) => {
                console.log(response);
            },
        });
    }

    deleteLike(currentLikeBox) {
        $.ajax({
             beforeSend: (xhr) => {
                xhr.setRequestHeader('X-WP-Nonce', universityData.nonce);
            },
            url: universityData.root_url + '/wp-json/university/v1/manageLike',
            data: {'like': currentLikeBox.attr("data-like")},
            type: 'DELETE',
            success: (response) => {
                currentLikeBox.attr('data-exists','no'); 
                var likeCount = parseInt(currentLikeBox.find(".like-count").html(), 10);
                likeCount--;
                currentLikeBox.find(".like-count").html(likeCount);
                currentLikeBox.attr("data-like", '');
                console.log(response);
            },
            error: (response) => {
                console.log(response);
            },
        });
    }
}

export default Like;