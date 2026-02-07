<?php
/**
 * WPBS Elementor Widget - Loan Calculator
 *
 * @package WP_Boat_Sync
 */

if (!defined('ABSPATH')) {
	exit;
}

class WPBS_Elementor_Loan_Calculator_Widget extends \Elementor\Widget_Base
{
	public function get_name()
	{
		return 'wpbs_loan_calculator';
	}

	public function get_title()
	{
		return esc_html__('Loan Calculator', 'wp-boat-sync');
	}

	public function get_icon()
	{
		return 'eicon-calculator';
	}

	public function get_categories()
	{
		return ['wpbs-boat'];
	}

	public function get_style_depends()
	{
		return ['wpbs-frontend'];
	}

	public function get_script_depends()
	{
		return ['wpbs-gallery'];
	}

	protected function register_controls()
	{
		// Content Section
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__('Calculator Settings', 'wp-boat-sync'),
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

		// Container Style
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
					'{{WRAPPER}} .wpbs-loan-calculator' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'container_border',
				'selector' => '{{WRAPPER}} .wpbs-loan-calculator',
			]
		);

		$this->add_responsive_control(
			'container_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'container_shadow',
				'selector' => '{{WRAPPER}} .wpbs-loan-calculator',
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Title Style
		$this->start_controls_section(
			'title_style',
			[
				'label' => esc_html__('Title', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator__title' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .wpbs-loan-calculator__title',
			]
		);

		$this->add_responsive_control(
			'title_margin',
			[
				'label' => esc_html__('Margin', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator__title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Label Style
		$this->start_controls_section(
			'label_style',
			[
				'label' => esc_html__('Labels', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'label_color',
			[
				'label' => esc_html__('Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator__label' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'label_typography',
				'selector' => '{{WRAPPER}} .wpbs-loan-calculator__label',
			]
		);

		$this->add_responsive_control(
			'label_margin',
			[
				'label' => esc_html__('Margin', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator__label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Input Style
		$this->start_controls_section(
			'input_style',
			[
				'label' => esc_html__('Inputs', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'input_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator__input' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'input_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator__input' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'input_border',
				'selector' => '{{WRAPPER}} .wpbs-loan-calculator__input',
			]
		);

		$this->add_responsive_control(
			'input_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator__input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'input_typography',
				'selector' => '{{WRAPPER}} .wpbs-loan-calculator__input',
			]
		);

		$this->add_responsive_control(
			'input_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator__input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Button Style
		$this->start_controls_section(
			'button_style',
			[
				'label' => esc_html__('Calculate Button', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs('button_tabs');

		$this->start_controls_tab('button_normal', ['label' => esc_html__('Normal', 'wp-boat-sync')]);

		$this->add_control(
			'button_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator__button' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'button_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator__button' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab('button_hover', ['label' => esc_html__('Hover', 'wp-boat-sync')]);

		$this->add_control(
			'button_hover_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator__button:hover' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'button_hover_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator__button:hover' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .wpbs-loan-calculator__button',
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'button_border',
				'selector' => '{{WRAPPER}} .wpbs-loan-calculator__button',
			]
		);

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator__button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator__button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Result Style
		$this->start_controls_section(
			'result_style',
			[
				'label' => esc_html__('Result Display', 'wp-boat-sync'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'result_background',
			[
				'label' => esc_html__('Background', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator__result' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'result_color',
			[
				'label' => esc_html__('Text Color', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator__result' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'result_typography',
				'selector' => '{{WRAPPER}} .wpbs-loan-calculator__result',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'result_border',
				'selector' => '{{WRAPPER}} .wpbs-loan-calculator__result',
			]
		);

		$this->add_responsive_control(
			'result_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator__result' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'result_padding',
			[
				'label' => esc_html__('Padding', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator__result' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'result_margin',
			[
				'label' => esc_html__('Margin', 'wp-boat-sync'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .wpbs-loan-calculator__result' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		
		// Get boat ID
		$boat_id = $settings['boat_id'];
		$post_id = $settings['post_id'];
		$purchase_price = 0;
		
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
			wp_reset_postdata();
		} else {
			$final_id = get_the_ID();
		}
		
		if ($final_id) {
			$price_raw = get_post_meta($final_id, 'wpbs_price', true);
			$purchase_price = floatval($price_raw);
		}
		
		// Default values
		$default_down_percent = 20;
		$default_down = $purchase_price * ($default_down_percent / 100);
		$default_term = 15;
		$default_rate = 7.5;
		$loan_amount = max(0, $purchase_price - $default_down);
		$n = $default_term * 12;
		$r = ($default_rate / 100) / 12;
		
		// Calculate monthly payment
		$monthly_payment = 0;
		if ($loan_amount > 0) {
			if ($r == 0) {
				$monthly_payment = $loan_amount / $n;
			} else {
				$monthly_payment = $loan_amount * ($r * pow(1 + $r, $n)) / (pow(1 + $r, $n) - 1);
			}
		}
		
		$calc_id = 'wpbs-calc-' . wp_rand(1000, 9999);
		
		?>
		<div class="wpbs-loan-calculator" id="<?php echo esc_attr($calc_id); ?>">
			<div class="wpbs-loan-calculator__header">
				<h3 class="wpbs-loan-calculator__title">Loan Payment Calculator</h3>
			</div>
			<div class="wpbs-loan-calculator__body">
				<!-- Purchase Price -->
				<div class="wpbs-loan-calculator__field">
					<label for="<?php echo esc_attr($calc_id); ?>-price">Purchase Price</label>
					<div class="wpbs-loan-calculator__input-wrap">
						<span class="wpbs-loan-calculator__prefix">$</span>
						<input type="text" id="<?php echo esc_attr($calc_id); ?>-price" class="wpbs-loan-calculator__input" data-field="price" value="<?php echo esc_attr(number_format($purchase_price, 0, '.', ',')); ?>">
					</div>
				</div>
				
				<!-- Down Payment -->
				<div class="wpbs-loan-calculator__field">
					<label for="<?php echo esc_attr($calc_id); ?>-down">Down Payment</label>
					<div class="wpbs-loan-calculator__input-wrap">
						<span class="wpbs-loan-calculator__prefix">$</span>
						<input type="text" id="<?php echo esc_attr($calc_id); ?>-down" class="wpbs-loan-calculator__input" data-field="down" value="<?php echo esc_attr(number_format($default_down, 0, '.', ',')); ?>">
					</div>
					<div class="wpbs-loan-calculator__percent-info">(<span data-down-percent"><?php echo $purchase_price > 0 ? number_format(($default_down / $purchase_price) * 100, 1) : '0'; ?></span>% of price)</div>
				</div>
				
				<!-- Loan Term -->
				<div class="wpbs-loan-calculator__field">
					<label for="<?php echo esc_attr($calc_id); ?>-term">Loan Term</label>
					<div class="wpbs-loan-calculator__select-wrap">
						<select id="<?php echo esc_attr($calc_id); ?>-term" class="wpbs-loan-calculator__select" data-field="term">
							<?php foreach (array(0.5,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15) as $term): ?>
								<option value="<?php echo $term; ?>"<?php echo $term == $default_term ? ' selected' : ''; ?>><?php echo $term; ?> Years</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				
				<!-- Interest Rate -->
				<div class="wpbs-loan-calculator__field">
					<label for="<?php echo esc_attr($calc_id); ?>-rate">Annual Interest Rate</label>
					<div class="wpbs-loan-calculator__input-wrap">
						<input type="text" id="<?php echo esc_attr($calc_id); ?>-rate" class="wpbs-loan-calculator__input wpbs-loan-calculator__input--rate" data-field="rate" value="<?php echo esc_attr(number_format($default_rate, 2)); ?>">
						<span class="wpbs-loan-calculator__suffix">%</span>
					</div>
				</div>
				
				<!-- Results -->
				<div class="wpbs-loan-calculator__results">
					<div class="wpbs-loan-calculator__result-row">
						<span class="wpbs-loan-calculator__result-label">Loan Amount</span>
						<span class="wpbs-loan-calculator__result-value" data-result="loan-amount">$<?php echo number_format($loan_amount, 0); ?></span>
					</div>
					<div class="wpbs-loan-calculator__result-row wpbs-loan-calculator__result-row--highlight">
						<span class="wpbs-loan-calculator__result-label">Est. Monthly Payment</span>
						<span class="wpbs-loan-calculator__result-value wpbs-loan-calculator__result-value--large" data-result="monthly-payment">$<?php echo number_format($monthly_payment, 2); ?></span>
					</div>
					<div class="wpbs-loan-calculator__result-row">
						<span class="wpbs-loan-calculator__result-label">Total of Payments</span>
						<span class="wpbs-loan-calculator__result-value" data-result="total-payments">$<?php echo number_format($monthly_payment * $default_term * 12, 0); ?></span>
					</div>
					<div class="wpbs-loan-calculator__result-row">
						<span class="wpbs-loan-calculator__result-label">Total Interest</span>
						<span class="wpbs-loan-calculator__result-value" data-result="total-interest">$<?php echo number_format(($monthly_payment * $default_term * 12) - $loan_amount, 0); ?></span>
					</div>
				</div>
				
				<p class="wpbs-loan-calculator__disclaimer">*This calculator provides estimates for informational purposes only. Actual loan terms, rates, and payments may vary based on credit qualifications and lender requirements.</p>
			</div>
		</div>
		
		<script>
		(function() {
			const calc = document.getElementById("<?php echo esc_js($calc_id); ?>");
			if (!calc) return;

			const priceInput = calc.querySelector("[data-field=price]");
			const downInput = calc.querySelector("[data-field=down]");
			const termSelect = calc.querySelector("[data-field=term]");
			const rateInput = calc.querySelector("[data-field=rate]");
			const downPercent = calc.querySelector("[data-down-percent]");
			const loanAmountEl = calc.querySelector("[data-result=loan-amount]");
			const monthlyPaymentEl = calc.querySelector("[data-result=monthly-payment]");
			const totalPaymentsEl = calc.querySelector("[data-result=total-payments]");
			const totalInterestEl = calc.querySelector("[data-result=total-interest]");

			function parseNumber(str) {
				return parseFloat(str.replace(/[^0-9.]/g, "")) || 0;
			}

			function formatNumber(num, decimals = 0) {
				return num.toLocaleString("en-US", { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
			}

			function calculatePayment() {
				const price = parseNumber(priceInput.value);
				const down = parseNumber(downInput.value);
				const term = parseInt(termSelect.value) || 15;
				const rate = parseFloat(rateInput.value.replace(/[^0-9.]/g, "")) || 0;

				const loanAmount = Math.max(0, price - down);
				const n = term * 12;
				const r = (rate / 100) / 12;

				let monthlyPayment = 0;
				if (loanAmount > 0) {
					if (r === 0) {
						monthlyPayment = loanAmount / n;
					} else {
						monthlyPayment = loanAmount * (r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
					}
				}

				const totalPayments = monthlyPayment * n;
				const totalInterest = totalPayments - loanAmount;

				if (downPercent && price > 0) {
					downPercent.textContent = ((down / price) * 100).toFixed(1);
				}

				loanAmountEl.textContent = "$" + formatNumber(loanAmount);
				monthlyPaymentEl.textContent = "$" + formatNumber(monthlyPayment, 2);
				totalPaymentsEl.textContent = "$" + formatNumber(totalPayments);
				totalInterestEl.textContent = "$" + formatNumber(Math.max(0, totalInterest));
			}

			function formatInput(input, decimals = 0) {
				const val = parseNumber(input.value);
				input.value = formatNumber(val, decimals);
			}

			[priceInput, downInput].forEach(input => {
				input.addEventListener("input", calculatePayment);
				input.addEventListener("blur", function() {
					formatInput(this);
					calculatePayment();
				});
			});

			rateInput.addEventListener("input", calculatePayment);
			rateInput.addEventListener("blur", function() {
				formatInput(this, 2);
				calculatePayment();
			});

			termSelect.addEventListener("change", calculatePayment);
		})();
		</script>
		<?php
	}
}
