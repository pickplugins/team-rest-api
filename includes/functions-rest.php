<?php
if (!defined('ABSPATH')) exit();



class TeamRestEndPoints
{
    function __construct()
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }


    public function register_routes()
    {



        register_rest_route(
            'team/v2',
            '/get_post_team_members',
            array(
                'methods'  => 'POST',
                'callback' => array($this, 'get_post_team_members'),
                'permission_callback' => '__return_true',
            )
        );
    }


    /**
     * Return Posts
     *
     * @since 1.0.0
     * @param WP_REST_Request $post_data Post data.
     */
    public function get_post_team_members($post_data)
    {



        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');


        $category      = isset($post_data['team_group']) ? sanitize_text_field($post_data['team_group']) : '';
        $keyword     = isset($post_data['keyword']) ? sanitize_text_field($post_data['keyword']) : '';
        $page     = isset($post_data['page']) ? sanitize_text_field($post_data['page']) : 1;
        $order     = isset($post_data['order']) ? sanitize_text_field($post_data['order']) : "DESC";
        $orderby     = isset($post_data['orderby']) ? sanitize_text_field($post_data['orderby']) : "date";
        $posts_per_page     = isset($post_data['per_page']) ? sanitize_text_field($post_data['per_page']) : 10;

        $response = [];
        $tax_query = [];
        $query_args = [];
        $query_args['post_type'] = 'team_member';
        $query_args['posts_per_page'] = $posts_per_page;
        $query_args['order']              = $order;
        $query_args['orderby']          = $orderby;

        if (!empty($keyword)) {
            $query_args['s'] = $keyword;
        }


        if (!empty($page)) {
            $query_args['paged'] = $page;
        }


        if (!empty($category)) {
            $tax_query[] = array(
                'taxonomy' => 'team_group',
                'field'    => 'term_id',
                'terms'    => $category,
            );

            $query_args['tax_query'] = $tax_query;
        }


        $posts = [];
        $wp_query = new WP_Query($query_args);

        $team_settings = get_option('team_settings');
        $custom_social_fields = isset($team_settings['custom_social_fields']) ? $team_settings['custom_social_fields'] : array();
        $social_fields_data = array();

        foreach ($custom_social_fields as $social_field) {

            $name = isset($social_field['name']) ? $social_field['name'] : '';
            $meta_key = isset($social_field['meta_key']) ? $social_field['meta_key'] : '';
            $icon = isset($social_field['icon']) ? $social_field['icon'] : '';
            $font_icon = isset($social_field['font_icon']) ? $social_field['font_icon'] : '';

            $visibility = isset($social_field['visibility']) ? $social_field['visibility'] : '';

            $social_fields_data[$meta_key] = array('icon' => $icon, 'font_icon' => $font_icon, 'name' => $name, 'visibility' => $visibility,);
        }



        if ($wp_query->have_posts()) :

            while ($wp_query->have_posts()) : $wp_query->the_post();
                $team_member_id = get_the_id();
                $post = get_post($team_member_id);
                $team_member_data = get_post_meta($team_member_id, 'team_member_data', true);
                $member_image_id = isset($team_member_data['member_image']) ? $team_member_data['member_image'] : '';

                $team_member_data = get_post_meta($team_member_id, 'team_member_data', true);
                $position = isset($team_member_data['custom_fields']['position']) ? $team_member_data['custom_fields']['position'] : '';

                $thumb_size =  'full';
                $member_image_arr = wp_get_attachment_image_src($member_image_id, $thumb_size);
                $member_image_url = isset($member_image_arr[0]) ? $member_image_arr[0] : '';

                $team_member_data = get_post_meta($team_member_id, 'team_member_data', true);
                $social_fields = isset($team_member_data['social_fields']) ? $team_member_data['social_fields'] : array();

                $post_id = $post->ID;
                $post->post_id = $post->ID;
                $post->post_title = $post->post_title;
                $post->post_content = $post->post_content;





                $post->thumb_url = $member_image_url;
                $post->position = $position;

                $team_member_data = get_post_meta($team_member_id, 'team_member_data', true);

                // add custom meta key
                $meta_key = "";
                $meta_key_value = isset($team_member_data['custom_fields'][$meta_key]) ? $team_member_data['custom_fields'][$meta_key] : '';

                $post->meta_key = $meta_key_value;

                $social_links = [];

                if (!empty($social_fields)) :
                    foreach ($social_fields as $fieldIndex => $field) :

                        $field_icon = isset($social_fields_data[$fieldIndex]['icon']) ? $social_fields_data[$fieldIndex]['icon'] : '';
                        $field_font_icon = isset($social_fields_data[$fieldIndex]['font_icon']) ? $social_fields_data[$fieldIndex]['font_icon'] : '';
                        //echo '<pre>'.var_export($field_font_icon, true).'</pre>';

                        $social_icon_type = "font_icon";

                        if (!empty($field)) :

                            $field_link = apply_filters('team_social_link', $field, $fieldIndex);
                            $social_links[$fieldIndex]["link"] = $field_link;
                            $social_links[$fieldIndex]["type"] = $social_icon_type;

?>

                                <?php
                                if ($social_icon_type == 'image_icon') :

                                    if (!empty($field_icon)) :
                                        $social_links[$fieldIndex]["src"] = $field_icon;

                                    endif;

                                elseif ($social_icon_type == 'font_icon') :

                                    if (!empty($field_font_icon)) :
                                        $social_links[$fieldIndex]["src"] = $field_font_icon;

                                    endif;

                                elseif ($social_icon_type == 'text_link') :
                                    $social_links[$fieldIndex]["src"] = $field_font_icon;
                                    $social_links[$fieldIndex]["label"] = $field;


                                endif;

                            endif;


                        endforeach;

                    endif;

                    $post->social_links = $social_links;

                    // $price = get_post_meta($post_id, 'price', true);
                    // $post->price = !empty($price) ? $price : 5;


                    $posts[]            = $post;



                //error_log(serialize($thumb_url));


                endwhile;
                wp_reset_query();
                wp_reset_postdata();



            endif;

            $response['posts'] = $posts;

            $terms = get_terms(
                array(
                    'taxonomy'   => 'layout_cat',
                    'hide_empty' => true,
                    'post_type'  => 'post_layout',
                )
            );

            $termsList = [];
            $termsList[] = ['label' => 'All', 'value' => ''];

            foreach ($terms as $term) {

                $termsList[] = ['label' => $term->name, 'value' => $term->term_id];
            }


            $response['terms'] = $termsList;



            die(wp_json_encode($response));
        }
    }

    $BlockPostGrid = new TeamRestEndPoints();
