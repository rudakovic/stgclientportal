<?php
namespace ValetTasks\Services;

class Custom_Project_Status {

	private $cpt = 'valet_project';
	private $statuses = [
		'scoping'   => ['label' => 'Scoping', 'desc' => 'Project is being defined.'],
		'proposed'  => ['label' => 'Proposed', 'desc' => 'Awaiting client approval.'],
		'on_hold'   => ['label' => 'On Hold', 'desc' => 'Work is temporarily paused.'],
		'declined'  => ['label' => 'Declined', 'desc' => 'Project will not proceed.'],
		'approved'  => ['label' => 'Approved/Active', 'desc' => 'Work is currently in progress.'],
		'completed' => ['label' => 'Completed', 'desc' => 'Project is finished.'],
	];

	public function __construct() {
		add_action( 'init', [ $this, 'register_custom_statuses' ] );
		add_action( 'admin_footer-post.php', [ $this, 'inject_gutenberg_script' ] );
		add_action( 'admin_footer-edit.php', [ $this, 'add_to_quick_edit' ] );
		add_filter( 'display_post_states', [ $this, 'show_status_in_list' ], 10, 2 );
	}

	public function register_custom_statuses() {
		foreach ( $this->statuses as $key => $data ) {
			register_post_status( $key, [
				'label'       => $data['label'],
				'public'      => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop( $data['label'] . " <span class='count'>(%s)</span>", $data['label'] . " <span class='count'>(%s)</span>" ),
			] );
		}
	}

	public function inject_gutenberg_script() {
		global $post;
		if ( ! $post || $post->post_type !== $this->cpt ) return;

		$current_status = $post->post_status;
		$statuses_json  = json_encode($this->statuses);
		?>
		<script>
            (function($) {
                const statuses = <?php echo $statuses_json; ?>;
                const currentStatus = '<?php echo $current_status; ?>';

                // Function to inject radio buttons into the popover
                const injectRadioOptions = (wrapper) => {
                    const $group = $(wrapper).find('.components-radio-control__group-wrapper');
                    if (!$group.length || $group.find('.custom-status-option').length > 0) return;

                    $.each(statuses, function(key, data) {
                        const isChecked = currentStatus === key ? 'checked' : '';
                        const radioHtml = `
                        <div class="components-radio-control__option custom-status-option">
                            <input id="custom-status-${key}" class="components-radio-control__input" type="radio" name="inspector-radio-control-custom" value="${key}" ${isChecked}>
                            <label class="components-radio-control__label" for="custom-status-${key}">${data.label}</label>
                            <p class="components-radio-control__option-description">${data.desc}</p>
                        </div>`;

                        const $option = $(radioHtml);

                        // Update WordPress Internal State when clicked
                        $option.find('input').on('change', function() {
                            const val = $(this).val();
                            wp.data.dispatch('core/editor').editPost({ status: val });
                            // Close the popover to mimic native behavior
                            $('.components-button[aria-label="Close"]').click();
                        });

                        $group.append($option);
                    });
                };

                // Watch for the Popover appearing in the DOM
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        mutation.addedNodes.forEach((node) => {
                            if ($(node).hasClass('components-popover') || $(node).find('.editor-change-status__options').length) {
                                injectRadioOptions(node);
                            }
                        });
                    });
                });

                observer.observe(document.body, { childList: true, subtree: true });

                // Also update the button label in the sidebar so it's not empty
                setInterval(() => {
                    const $btn = $('.editor-post-status__toggle');
                    if ($btn.length && $btn.text().trim() === "" && statuses[currentStatus]) {
                        $btn.text(statuses[currentStatus].label);
                    }
                }, 1000);

            })(jQuery);
		</script>
		<?php
	}

	public function add_to_quick_edit() {
		global $post_type;
		if ( $post_type !== $this->cpt ) return;
		?>
		<script>
            jQuery(document).ready(function($) {
                const statuses = <?php echo json_encode($this->statuses); ?>;
                const $select = $('select[name="_status"]');
                $.each(statuses, function(key, data) {
                    $select.append(`<option value="${key}">${data.label}</option>`);
                });
            });
		</script>
		<?php
	}

	public function show_status_in_list( $states, $post ) {
		if ( $post->post_type === $this->cpt && isset($this->statuses[$post->post_status]) ) {
			$states[] = $this->statuses[$post->post_status]['label'];
		}
		return $states;
	}
}

new Custom_Project_Status();
