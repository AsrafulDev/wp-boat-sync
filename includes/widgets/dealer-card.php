<?php
/**
 * WPBS Elementor Widget - Dealer Card
 *
 * @package WP_Boat_Sync
 */

if (!defined('ABSPATH')) {
	exit;
}

class WPBS_Elementor_Dealer_Card_Widget extends \Elementor\Widget_Base
{
	public function get_name()
	{
		return 'wpbs_dealer_card';
	}

	public function get_title()
	{
		return esc_html__('Dealer Card', 'wp-boat-sync');
	}

	public function get_icon()
	{
		return 'eicon-person';
	}

	public function get_categories()
	{
		return ['wpbs-boat'];
	}

	public function get_style_depends()
	{
		return ['wpbs-frontend'];
	}

	protected function register_controls()
	{
		// Content Section
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__('Dealer Card Settings', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'boat_id',
			[
				'label' => esc_html__('Boat ID', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'description' => esc_html__('Leave empty to use current boat', 'wp-boat-sync'),
			]
		);

		$this->add_control(
			'post_id',
			[
				'label' => esc_html__('Post ID', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::NUMBER,
			]
		);

		$this->end_controls_section();

		// Card Container Style
		$this->start_controls_section(
			'container_style',
			[
				'label' => esc_html__('Container', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'container_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-dealer-card' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'container_border',
				'selector' => '{{WRAPPER}} .wpbs-dealer-card',
			]
		);

		$this->add_responsive_control(
			'container_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-dealer-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'container_shadow',
				'selector' => '{{WRAPPER}} .wpbs-dealer-card',
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-dealer-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Dealer Name Style
		$this->start_controls_section(
			'name_style',
			[
				'label' => esc_html__('Dealer Name', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
        $this->add_control(
            'name_color',
            [
                'label' => esc_html__('Color', 'wp-boat-sync'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpbs-dealer-card__header h3' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'name_font_size',
            [
                'label' => esc_html__('Font Size', 'wp-boat-sync'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .wpbs-dealer-card__header h3' => 'font-size: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->add_control(
            'name_font_weight',
            [
                'label' => esc_html__('Font Weight', 'wp-boat-sync'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '' => esc_html__('Default', 'wp-boat-sync'),
                    '100' => '100',
                    '200' => '200',
                    '300' => '300',
                    '400' => '400',
                    '500' => '500',
                    '600' => '600',
                    '700' => '700',
                    '800' => '800',
                    '900' => '900',
                ],
                'selectors' => [
                    '{{WRAPPER}} .wpbs-dealer-card__header h3' => 'font-weight: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'content_color',
            [
                'label' => esc_html__('Content Color', 'wp-boat-sync'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpbs-dealer-card__body, {{WRAPPER}} .wpbs-dealer-card__body a' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'content_link_color',
            [
                'label' => esc_html__('Content Link Color', 'wp-boat-sync'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpbs-dealer-card__body a' => 'color: {{VALUE}} !important;',
                ],
            ]
         );
        $this->add_control(
            'content_font_size',
            [
                'label' => esc_html__('Content Font Size', 'wp-boat-sync'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .wpbs-dealer-card__body, {{WRAPPER}} .wpbs-dealer-card__body a' => 'font-size: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );  

        $this->start_controls_tabs('button_tabs');

        $this->start_controls_tab(
            'button_style_normal',
            [
                'label' => esc_html__('Normal', 'wp-boat-sync'),
            ]
        );
        $this->add_control(
            'button_color',
            [
                'label' => esc_html__('Button Color', 'wp-boat-sync'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpbs-dealer-card__button' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'button_text_color',
            [
                'label' => esc_html__('Button Text Color', 'wp-boat-sync'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpbs-dealer-card__button' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'button_border_radius',
            [
                'label' => esc_html__('Button Border Radius', 'wp-boat-sync'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .wpbs-dealer-card__button' => 'border-radius: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->add_control(
            'button_padding',
            [
                'label' => esc_html__('Button Padding', 'wp-boat-sync'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wpbs-dealer-card__button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->end_controls_tab();
        $this->start_controls_tab(
            'button_style_hover',
            [
                'label' => esc_html__('Hover', 'wp-boat-sync'),
            ]
        );
        $this->add_control(
            'button_hover_background_color',
            [
                'label' => esc_html__('Button Hover Background Color', 'wp-boat-sync'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpbs-dealer-card__button:hover' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'button_hover_background_color',
            [
                'label' => esc_html__('Button Hover Background Color', 'wp-boat-sync'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpbs-dealer-card__button:hover' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'button_hover_border_radius',
            [
                'label' => esc_html__('Button Hover Border Radius', 'wp-boat-sync'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .wpbs-dealer-card__button:hover' => 'border-radius: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->add_control(
            'button_hover_padding',
            [
                'label' => esc_html__('Button Hover Padding', 'wp-boat-sync'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wpbs-dealer-card__button:hover' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		
		// Get boat ID
		$boat_id = $settings['boat_id'];
		$post_id = $settings['post_id'];
		
		if ($post_id) {
			$final_id = $post_id;
		} elseif ($boat_id && is_numeric($boat_id)) {
			$final_id = $boat_id;
		} elseif ($boat_id) {
			$args = array(
				'post_type' => 'boats',
				'meta_query' => array(
					array(
						'key' => 'wpbs_boat_id',
						'value' => $boat_id,
						'compare' => '='
					)
				),
				'posts_per_page' => 1,
				'fields' => 'ids'
			);
			$query = new \WP_Query($args);
			$final_id = $query->posts ? $query->posts[0] : 0;
		} else {
			$final_id = get_the_ID();
		}
		
		if (!$final_id) {
			return;
		}
		
		$dealer = get_post_meta($final_id, 'wpbs_dealer', true);
		$office_phone = get_post_meta($final_id, 'wpbs_office_phone', true);
		$office_email = get_post_meta($final_id, 'wpbs_office_email', true);
		$location = get_post_meta($final_id, 'wpbs_location', true);
		
		?>
		<div class="wpbs-dealer-card">
			<div class="wpbs-dealer-card__header">
				<h3><?php esc_html_e('Dealer Information', 'wp-boat-sync'); ?></h3>
			</div>
			<div class="wpbs-dealer-card__body">
				<?php if ($dealer): ?>
					<div class="wpbs-dealer-card__row"><strong>Dealer:</strong> <?php echo esc_html($dealer); ?></div>
				<?php endif; ?>
				<?php if ($office_phone): ?>
					<div class="wpbs-dealer-card__row"><strong>Phone:</strong> <a href="tel:<?php echo esc_attr($office_phone); ?>"><?php echo esc_html($office_phone); ?></a></div>
				<?php endif; ?>
				<?php if ($office_email): ?>
					<div class="wpbs-dealer-card__row"><strong>Email:</strong> <a href="mailto:<?php echo esc_attr($office_email); ?>"><?php echo esc_html($office_email); ?></a></div>
				<?php endif; ?>
				<?php if ($location): ?>
					<div class="wpbs-dealer-card__row"><strong>Location:</strong> <?php echo esc_html($location); ?></div>
				<?php endif; ?>
			</div>
			<div class="wpbs-dealer-card__actions">
				<a href="<?php echo esc_url(get_post_type_archive_link('boats')); ?>" class="wpbs-btn wpbs-btn--outline">View All Boats</a>
			</div>
		</div>
		<?php
	}
}
