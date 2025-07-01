import axios from "axios";

class Like {
    constructor() {
        // Runs when a new 'like' obj is created
        if (document.querySelector(".like-box")) {
            //If 'like' -> set default WP nonce for authentication with REST API.
            axios.defaults.headers.common["X-WP-Nonce"] = universityData.nonce
            // Call to set up event listeners
            this.events();
        }
    }
    // Event Listener
    events() {
        document.querySelector(".like-box").addEventListener("click", e => this.ourClickDispatcher(e))
    }

    // Methods
    ourClickDispatcher(e) {
        let currentLikeBox = e.target;
        // Grab the nearest element with this class
        while (!currentLikeBox.classList.contains("like-box")) {
            currentLikeBox = currentLikeBox.parentElement;
        }
        // Delete if like exists, else create a like
        if (currentLikeBox.getAttribute("data-exists") == "yes") {
            this.deleteLike(currentLikeBox);
        } else {
            this.createLike(currentLikeBox);
        }
    }

    // The method to handle creating a like
    async createLike(currentLikeBox) {
        try {
            // Make POST req to create a like with professor ID
            const response = await axios.post(
                universityData.root_url + "/wp-json/university/v1/manageLike",
                { "professorId": currentLikeBox.getAttribute("data-professor") }
            )

            // If response is NOT the error message about login
            if (response.data != "Only logged in users can create a like.") {

            // Mark the box as "liked"
                currentLikeBox.setAttribute("data-exists", "yes");

            // Update the like count in the UI
                var likeCount = parseInt(currentLikeBox.querySelector(".like-count").innerHTML, 10);
                likeCount++;
                currentLikeBox.querySelector(".like-count").innerHTML = likeCount;

            // Save the ID of the new like so we can delete it later
                currentLikeBox.setAttribute("data-like", response.data);
            }

            // Log the server's response
            console.log(response.data);

        } catch (e) {
            console.log("sorry");
        }
    }

    // The method to handle deleting a like
    async deleteLike(currentLikeBox) {
        try {
        // Send a DELETE request to the API with the Like ID
            const response = await axios({
                url: universityData.root_url + "/wp-json/university/v1/manageLike",
                method: 'delete',
                data: { "like": currentLikeBox.getAttribute("data-like") },
            })

        // Mark the like as "not existing"
            currentLikeBox.setAttribute("data-exists", "no");
        
        // Update the like count in the UI
            var likeCount = parseInt(currentLikeBox.querySelector(".like-count").innerHTML, 10);
            likeCount--;
            currentLikeBox.querySelector(".like-count").innerHTML = likeCount;

        // Clear the like ID from the element
            currentLikeBox.setAttribute("data-like", "");
            
            console.log(response.data);
        } catch (e) {
            console.log(e);
        }
    }
}

export default Like;