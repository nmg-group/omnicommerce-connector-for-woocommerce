<?php
/**
 * Plugin Name: Omnicommerce connector for WooCommerce
 * Description: Facilita la consulta rápida de SKUs, stocks y precios desde Omnicommerce.
 * Version: 1.0
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
                    $productos[] = array(
                        'id' => $product->get_id(),
                        'tipo' => $product->get_type(),
                        'date_created' => $product->get_date_created()->format('Y-m-d H:i:s'),
                        'date_modified' => $product->get_date_modified()->format('Y-m-d H:i:s'),
                        'status' => $product->get_status(),
                        'parent_id' => $product->get_parent_id(),
                        'title' => $product->get_title(),
                        'sku' => $product->get_sku(),
                        'stock' => $product->get_stock_quantity(),
                        'precio' => $product->get_price(),
                        'precioRegular' => $product->get_regular_price(),
                        'precioSale' => $product->get_sale_price(),
                        );
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
        $omc_plugin_goto_link = "<a href='https://github.com/nmg-group/omnicommerce-connector-for-woocommerce'>" . __( 'Acerca del plugin' ) . '</a>';
        $omc_goto_link = "<a href='https://omnicommerce.app'>" . __( 'Ir a Omnicommerce' ) . '</a>';
        array_push($links,$omc_plugin_goto_link,$omc_goto_link);
        return $links;
}
