<?php
/**
* Plugin Name: Hover Menu Content
* Plugin URI: http://hatrackmedia.com
* Description: A plugin that shows current product categories and tags in the Mega Menu
* Author: David Sato
* Author URI: http://hatrackmedia.com
* Version: 0.0.1
* License: GPLv2
*/

//Exit if accessed directly
if ( ! defined ( 'ABSPATH' ) ) {
	exit;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//  Start Class Code  //
class sklop_Hover_Menu_Widget extends WP_Widget {		
	//Start '__construct' code

	public function __construct() {
			$widget_options = array(
				'classname' 	=> 'hover_menu_content_widget',
				'description' 	=> 'A simple widget that puts cats and tags in hover menus'
			);
			parent::__construct( 'hover_menus_content' , 'Hover Menus Content' , '$widget_options' );
	}

	//End '__construct' code
		
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		
	function get_the_slug( $id=null ){
				if( empty($id) ):
					global $post;
					if( empty($post) )
						return ''; // No global $post var available.
					$id = $post->ID;
				endif;

				$slug = basename( get_permalink($id) );
				return $slug;
			}	
	//Start	'widget()' code

	public function widget( $args , $instance ) {
			
			
			
			$title = apply_filters( 'widget_title' , $instance[ 'title' ] );
			$categories = get_terms( array(
				'taxonomy' 		=> 'product_cat',
				'hide_empty' 	=> true,
				'order_by' 		=> 'name',
				'order' 		=> 'ASC'
			) );
						
			//echo '<pre>'; var_dump($categories); echo '</pre>';
						
			$cat_count = 0; //used to keep track of the total number of categories so that we can sort them into two lists.
			$cat_col_one = []; //used to divide the categories into two columns.
			$cat_col_two = []; //used to divide the categories into two columns.
						
			foreach( $categories as $category) {
				$cat_count ++;
				$category_link = sprintf(
					'<li class="list-unstyled"><a href="%1$s" alt="%2$s">%3$s</a></li>',
					esc_url( get_category_link( $category->term_id ) ), 									//%1$s
					esc_attr( sprintf( __('View all posts in %s' , 'textdomain' ) , $category->name ) ), 	//%2$s
					esc_html( $category->name ) 															//%3$s
				);
				if ($cat_count % 2 != 0) { //Read as: if the cat_count is NOT EVENLY divisible by 2 then do this or that
					$cat_col_one[] = $category_link;
				} 
				else {
					$cat_col_two[] = $category_link;
				}
			}
			
			echo $args[ 'before_widget' ] . $args[ 'before_title' ] . $title . $args[ 'after_title' ]; 
	?>
			
		<div class="container-fluid">	
			<div class="row vcenter">
			
				<div class="col-md-2">
					<div class="menu-categs-box">	
						<?php $wcatTerms = get_terms('product_cat', array(
																		'hide_empty' 	=> 0, 
																		'orderby' 		=>'ASC',  
																		'parent' 		=>0
																		) ); //, 'exclude' => '17,77'
							foreach($wcatTerms as $wcatTerm) : 
								$wthumbnail_id = get_woocommerce_term_meta( $wcatTerm->term_id, 'thumbnail_id', true );
								$wimage = wp_get_attachment_url( $wthumbnail_id );
							?>
							<ul>
								<li class="libreak"><?php if($wimage!=""):?><img src="<?php echo $wimage?>"><?php endif;?></li>
								<li>
									<a href="<?php echo get_term_link( $wcatTerm->slug, $wcatTerm->taxonomy ); ?>"><?php echo $wcatTerm->name; ?></a>
									<ul class="wsubcategs">
									<?php
									$wsubargs = array(
									   'hierarchical' => 1,
									   'show_option_none' => '',
									   'hide_empty' => 0,
									   'parent' => $wcatTerm->term_id,
									   'taxonomy' => 'product_cat'
									);
									$wsubcats = get_categories($wsubargs);
									foreach ($wsubcats as $wsc):
									?>
										<li><a href="<?php echo get_term_link( $wsc->slug, $wsc->taxonomy );?>"><?php echo $wsc->name;?></a></li>
									<?php
									endforeach;
									?>  
									</ul>
								</li>
							</ul>
						<?php 
							endforeach; 
						?>
					</div>
				</div>
				
				<div class="col-md-5">
					<ul>
						<?php
							$args5 = array( 'post_type' => 'product', 'posts_per_page' => 5, 'product_cat' => 'mode-femme', 'orderby' => 'rand' );
							$loop = new WP_Query( $args5 );
							while ( $loop->have_posts() ) : $loop->the_post(); global $product; ?>
							
									<li class="product">    

										<a href="<?php echo get_permalink( $loop->post->ID ) ?>" title="<?php echo esc_attr($loop->post->post_title ? $loop->post->post_title : $loop->post->ID); ?>">

											<h5><?php the_title(); ?></h5>

											<span class="price"><?php echo $product->get_price_html(); ?></span>                    

										</a>

										<?php woocommerce_template_loop_add_to_cart( $loop->post, $product ); ?>

									</li>

						<?php endwhile; ?>
						<?php wp_reset_query(); ?>
					</ul>
				</div>

				<div class="col-md-5"> 
					<ul>
						<?php/*
						foreach ($cat_col_two as $cat_two) {
							echo $cat_two;
						}*/ ?>
					</ul>
				</div>	
				
			</div>			
		</div><?php 
		
		echo $args [ 'after_widget' ];
	}		
			
	//End 'widget()' code

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////			

	//Start 'form()' code
	
	public function form( $instance ) {
		//Code below reads as: If the 'title' field of this $instance is NOT empty ( ! empty( $instance[ 'title' ]) ), THEN (?) set the 'title' of the $instance ELSE (:) set an empty string ( '' )		
		$title = ! empty($instance[ 'title' ] ) ? $instance[ 'title'] : ''; 
	?>
	<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label> 
		<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>">
	</p> 
	<?php	
	}	
	 
	//End 'form()' code

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//Start 'update()' code
	
	public function update( $new_instance , $old_instance ) {
		
		$instance = $old_instance;
		$instance [ 'title' ] = strip_tags( $new_instance[ 'title' ]);
		
		return $instance;
	}
	//End 'update()' code
	
} 
//End Class

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//Start function to register widget

function sklop_hover_menu_widget_register() { 
  register_widget( 'sklop_Hover_Menu_Widget' );
}
add_action( 'widgets_init' , 'sklop_hover_menu_widget_register' );	
?>