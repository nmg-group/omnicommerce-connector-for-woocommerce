<?php
/**
* Plugin Name: Omnicommerce connector for WooCommerce
* Description: Facilita la consulta rápida de SKUs, stocks y precios desde Omnicommerce.
* Version: 1.2
* Author: Omnicommerce - NMG Group, Inc.
* Author URI: https://omnicommerce.ar/
 */
function get_omc_skus( $request ) {

        // Check logged in user
        if (!is_user_logged_in()) {
        return new WP_REST_Response('Unauthorized', 401);
        }

        // Check required parameters
        if(isset($request->get_params()['type'])){
                $post_type = null;

                // Check type of post is valid
                switch($request->get_params()['type']){
                        case 'product':
                                $post_type = 'product';
                                break;
                        case 'product_variation':
                                $post_type = 'product_variation';
                                break;
                        default:
                                return array('status' => false, 'message' => 'type no es válido');
                                break;
                }

                $include_additional_attributes = isset($request->get_params()['include_additional_attributes']) ? true : false;

                if($include_additional_attributes){
                        $attributes = array();
                        $attribute_taxonomies = wc_get_attribute_taxonomies();
                        foreach ( $attribute_taxonomies as $tax ) {
                                $attribute = wc_sanitize_taxonomy_name( $tax->attribute_name );
                                $attributes[$attribute] = $tax->attribute_label;
                        }
                }


                // Initialize query config
                $products_query_config = array(
                        'post_type' => $post_type,
                        'orderby' => 'ID',
                        'posts_per_page' => (isset($request->get_params()['limit'])) ? $request->get_params()['limit'] : 100,
                        'ignore_sticky_posts' => 1
                    );

                // Add offset to query if provided
                if(isset($request->get_params()['offset'])){
                        $products_query_config['offset'] = $request->get_params()['offset'];
                }
                // Add paged to query if provided
                elseif(isset($request->get_params()['paged'])){
                        $products_query_config['paged'] = $request->get_params()['paged'];
                }
                $products_query = new WP_Query($products_query_config);
                $productos = [];

                // Iterate and populate results in a very efficient format to speed up sync tasks and improve customer experience.
                while ($products_query->have_posts() ) : $products_query->the_post();
                  global $product;
                      $productoActual = array(
                      'id' => $product->get_id(),
                      'tipo' => $product->get_type(),
                      'date_created' => $product->get_date_created()->format('Y-m-d H:i:s'),
                      'date_modified' => $product->get_date_modified()->format('Y-m-d H:i:s'),
                      'status' => $product->get_status(),
                      'parent_id' => $product->get_parent_id(),
                      'title' => $product->get_title(),
                      'sku' => $product->get_sku(),
                      'url' => get_permalink($product->get_id()),
                      'image' => wp_get_attachment_url($product->get_image_id()),
                      'stockStatus' => $product->get_stock_status(),
                      'stock' => $product->get_stock_quantity(),
                      'precio' => $product->get_price(),
                      'precioRegular' => $product->get_regular_price(),
                      'precioSale' => $product->get_sale_price(),
                      );

                        if($include_additional_attributes){
                                // Get description from variation (if it is and has any) or from the parent product
                                if($product->get_type() == 'variation' && $product->get_description() == '' && $product->get_parent_id() != 0){
                                        $productoActual['description'] = wc_get_product($product->get_parent_id())->get_description();
                                }
                                else{
                                        $productoActual['description'] = $product->get_description();
                                }

                                $productoActual['categories'] = array();
                                foreach (get_the_terms(($product->get_parent_id() != 0 ? $product->get_parent_id() : $product->get_id()), 'product_cat') as $term) {
                                        $productoActual['categories'][] = $term->name;
                                }
                                
                                $productoActual['image'] = wp_get_attachment_url($product->get_image_id());
                                $productoActual['attributes'] = [];

                                foreach ($attributes as $key => $value) {
                                        $attribute = $product->get_attribute($key);
                                        if($attribute){
                                                $productoActual['attributes'][$value] = $attribute;
                                        }
                                }
                        }
                      if(function_exists('slw_get_locations')){
                              $locations = array();
                              $terms = slw_get_locations();
                              foreach ($terms as $term) {
                                      $slw_default_location = get_term_meta($term->term_id, 'slw_default_location', true);
                                      $xyz = get_post_meta($product->get_id(), '_stock_at_' . $term->term_id, true);
                                      $locations[] = [
                                              'id' => $term->term_id,
                                              'slug' => $term->slug,
                                              'name' => $term->name,
                                              'quantity' => $xyz,
                                      ];
                              }
                              $productoActual['stocks'] = $locations;
                      }

                      $productos[] = $productoActual;
              endwhile;
                wp_reset_query();

                // Finally return results
                return array(
                        'query' => $products_query_config,
                        'totalResults' => $products_query->found_posts,
                        'data' => $productos,
                );
        }
        else{
                return array('status' => false, 'message' => 'type es un parametro requerido');
        }
}

// Add custom REST API Endpoint
add_action( 'rest_api_init', function () {
    register_rest_route( 'wc/v3', 'omc-skus', array(
        'methods' => 'GET',
        'callback' => 'get_omc_skus',
    ));
});

// Add information to contact developers and get help
add_filter( 'plugin_action_links_omnicommerce-connector-for-woocommerce/omnicommerce-connector-for-woocommerce.php', 'omc_goto_link' );
function omc_goto_link( $links ) {
        $omc_goto_link = "<a href='https://omnicommerce.app'>" . __( 'Ir a Omnicommerce' ) . '</a>';
        array_push($links,$omc_plugin_goto_link,$omc_goto_link);
        return $links;
}
