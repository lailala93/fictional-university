<?php

add_action('rest_api_init', 'universityLikeRoutes');
function universityLikeRoutes()
{
    register_rest_route('university/v1', 'manageLike', array(
        'methods' => 'POST',
        'callback' => 'createLike',
    ));

    register_rest_route('university/v1', 'manageLike', array(
        'methods' => 'DELETE',
        'callback' => 'deleteLike',
    ));
}


function createLike($data)
{
    if (is_user_logged_in()) {
        //  -> From JS File obj: createLike(), prop: 'data':{ 'professorId':789}
        $professor = sanitize_text_field($data['professorId']);

        $existQuery = new WP_Query(array(
            'author' => get_current_user_id(),
            'post_type' => 'like',
            'meta_query' => array(
                array(
                    'key' => 'liked_professor_id',
                    'compare' => '=',
                    'value' => $professor,
                )
            )
        ));

        if ($existQuery->found_posts == 0 AND get_post_type($professor) == 'professor') {
            return wp_insert_post(array(
                'post_type' => 'like',
                'post_status' => 'publish',
                'post_title' => '2nd PHP Post Test',
                'meta_input' => array(
                    'liked_professor_id' => $professor,
                )
            ));
        } else {
            die("You've already liked a professor.");
        }

    } else {
        die("Only logged in users can create a like.");
    }
}

function deleteLike($data)
{
    $likeID = sanitize_text_field($data['like']); 
    if(get_current_user_id() == get_post_field('post_author',$likeID) AND get_post_type($likeID) == 'like'){
        wp_delete_post($likeID, true);
        return 'Congrats, like deleted.';
    } else {
        die("You don't have permission to delete that.");
    }
}