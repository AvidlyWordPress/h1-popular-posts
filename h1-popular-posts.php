<?php
/**
 * Plugin Name: H1 Popular Posts
 * Plugin URI:
 * Description: Super simple popular posts with WPML/Polylang support.
 * Version: 0.1
 * Author: Tomi Mäenpää / H1
 * Author URI: https://h1.fi
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 * Text Domain: h1-popular-posts
 */

/*  Copyright 2015  Tomi Mäenpää / H1  (email : tomi@h1.fi)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'h1pp_update_view_count' ) ) {
	/**
	 * Update post view count.
	 *
	 * @since 0.1
	 */
	function h1pp_update_view_count() {

		global $post;

		$post_id    = $post->ID;
		$meta_key   = 'h1pp_view_count';
		$meta_value = intval( get_post_meta( $post_id, $meta_key, true ) );
		$meta_value++;

		update_post_meta( $post_id, $meta_key, $meta_value );
	}
}

add_action( 'wp_head', 'h1pp_track_view_count' );
if ( ! function_exists( 'h1pp_track_view_count' ) ) {
	/**
	 * Track post view count.
	 *
	 * @since 0.1
	 */
	function h1pp_track_view_count() {

		$post_types = array( 'post' );

		if ( is_singular( $post_types ) ) {
			h1pp_update_view_count();
		}
	}
}

add_action( 'widgets_init', function() {
	register_widget( 'H1_Popular_Posts' );
});
if ( ! class_exists( 'H1_Popular_Posts' ) ) {
	/**
	 * Popular Posts Widget
	 *
	 * @since 0.1
	 */
	class H1_Popular_Posts extends WP_Widget {

		/**
		 * Register widget with WordPress.
		 */
		public function __construct() {
			parent::__construct(
					'h1_popular_posts',
					__( 'Popular Posts', 'h1-popular-posts' ),
					array( 'description' => __( 'List of the most popular posts.', 'h1-popular-posts' ), )
				);
		}

		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget( $args, $instance ) {

			extract( $args );

			$title   = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Popular Posts', 'h1-popular-posts' );
			$number  = intval( $instance['number'] );

			echo $before_widget;

				echo $before_title . $title . $after_title;

				$popular_posts = new WP_Query( array(
					'post_type'           => 'post',
					'posts_per_page'      => $number,
					'ignore_sticky_posts' => true,
					'order'               => 'DESC',
					'orderby'             => 'meta_value_num',
					'meta_key'            => 'h1pp_view_count',
				) );

				if ( $popular_posts->have_posts() ) {
					echo "<ul>";
					while ( $popular_posts->have_posts() ) {
						$popular_posts->the_post();

						echo "<li>";
						echo "<a href='" . get_the_permalink() . "'>" . get_the_title() . "</a>";
						echo "</li>";

					}
					echo "</ul>";
				} else {
					echo __( 'No popular posts found.', 'h1-popular-posts' );
				}

				wp_reset_postdata();

			echo $after_widget;

		}

		/**
		 * Back-end widget form.
		 *
		 * @see WP_Widget::form()
		 *
		 * @param array $instance Previously saved values from database.
		 */
		public function form( $instance ) {

			$title   = ! empty( $instance['title'] ) ? $instance['title'] : '';
			$number  = ! empty( $instance['number'] ) ? $instance['number'] : 5;

			?>

			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
				<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" />
			</p>

			<?php
		}

		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @see WP_Widget::update()
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		public function update( $new_instance, $old_instance ) {

			$instance            = $old_instance;
			$instance['title']   = strip_tags( $new_instance['title'] );
			$instance['number']  = strip_tags( $new_instance['number'] );

			return $instance;
		}
	}
}