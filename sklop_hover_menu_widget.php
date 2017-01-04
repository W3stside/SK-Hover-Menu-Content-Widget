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
						
			$title = apply_filters( 'widget_title' , $instance[ 'title' ] ); 			//get the title input into the widget before outputting
			$sklop_title_sluggified = str_replace( ' ' , '-' , strtolower($title) ); 	//converts title into slug format
			
			$categories = get_terms( array(												//array for product cats
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
			
		<div class="container">	
			<div class="row vcenter">
				<div class="col-md-4">				<!--Photo DIV-->
					<?php
																		
					$sklop_catTerms = get_terms(array(
											'taxonomy' 		=> 'product_cat', 
											'slug' 			=> $sklop_title_sluggified,
											'hide_empty' 	=> 0, 
											'orderby' 		=> 'ASC',  
											'parent' 		=> 0
											) ); ?>
											
					<div class="img-cage vcenter">
						<?php
						foreach($sklop_catTerms as $sklop_catTerm) : 
								$sklop_thumbnail_id = get_woocommerce_term_meta( $sklop_catTerm->term_id, 'thumbnail_id', true );
								$sklop_image = wp_get_attachment_url( $sklop_thumbnail_id );
								if($sklop_image!="") : ?><img src="<?php echo $sklop_image?>" style="width: 250px"><?php endif;	
						endforeach;
					//End new loop 
					?>  
					</div>
				</div>
				
				<div class="col-md-4">	<!--First Column of Products-->
					<ul class="">
						<?php
							$args2 = array( 'post_type' => 'product', 'posts_per_page' => 5, 'product_cat' => $sklop_title_sluggified, 'orderby' => 'rand' );
							$loop = new WP_Query( $args2 );
							while ( $loop->have_posts() ) : $loop->the_post(); global $product; ?>
							
									<li class="left-align HM_li_content">    
										<a href="<?php echo get_permalink( $loop->post->ID ) ?>" title="<?php echo esc_attr($loop->post->post_title ? $loop->post->post_title : $loop->post->ID); ?>">
											<span><?php the_title(); ?></span>                    
										</a>
									</li>
									
						<?php endwhile; ?>
						<?php wp_reset_query(); ?>
					</ul>
				</div>

				<div class="col-md-4"> 
					
					<?php
						//Extracts ID from slug
							$sklop_id_extractor = get_term_by( 'slug' , $sklop_title_sluggified , 'product_cat');
							$sklop_id_from_slug = $sklop_id_extractor->term_id;
					
							$parent_cat_ID = $sklop_id_from_slug;
							$args3 = array(
							   'hierarchical' => 1,
							   'show_option_none' => '',
							   'hide_empty' => 0,
							   'parent' => $parent_cat_ID,
							   'taxonomy' => 'product_cat'
							);
						  $sklop_subcats = get_categories($args3);
					?>	  
					<ul class="">
					<?php
							  foreach ($sklop_subcats as $sklop_subcat) {
								$link = get_term_link( $sklop_subcat->slug, $sklop_subcat->taxonomy );
				echo '<li class="left-align HM_li_content"><a href="'. $link .'">'.$sklop_subcat->name.'</a></li>';
							  }
					?>		  
					</ul>
					<?php		
						
						/*$terms = get_terms( 'product_cat' );
						if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
							
							foreach ( $terms as $term ) {
								echo '<li>' . $term->name . '</li>';
							}
							
						}

						
						/*$categories = get_terms( array(
							'taxonomy' 		=> 'product_cat',
							'hide_empty' 	=> true,
							'order_by' 		=> 'name',
							'order' 		=> 'ASC'
						) );
						foreach ($categories as $c) {
							woocommerce_product_subcategories();
							}
						foreach ($cat_col_two as $cat_two) {
							echo $cat_two;
						} */?>
						
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