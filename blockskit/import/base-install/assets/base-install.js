jQuery(document).ready(function ($) {
	$(".ai-demo-import").click(function (e) {

		var $item = $(this).closest('.ai-item');
		var slug = $item.data('slug').replace('-pro', '');
		var name = $item.attr('aria-label') || slug; // Fallback to slug if no label

		name = name.replace(/Pro/g, '').trim();

		if (name.indexOf('BlocksKit') === -1) {
			name = 'BlocksKit ' + name;
		}



		// Check if theme is not active
		if (direct_install.active_theme_slug !== slug) {
			var is_installed = direct_install.installed_themes && direct_install.installed_themes.indexOf(slug) !== -1;

			if (is_installed || (!is_installed && direct_install.installed_themes)) {
				e.preventDefault();

				var template = direct_install.popup_template;
				var btn_text = is_installed ? 'Activate ' + name : 'Install and Activate ' + name;

				// Replace name, slug and button text
				var html = template.replace(/{{name}}/g, name)
					.replace(/{{slug}}/g, slug)
					.replace(/Install and Activate {{name}}/g, btn_text);

				html = template.replace(/{{name}}/g, name).replace(/{{slug}}/g, slug);

				if (is_installed) {
					html = html.replace(/Install and Activate/g, 'Activate');
				}

				$('body').append(html);
			}
		}

	});
	$(document.body).on('click', '.close-base-notice', function () {
		$(".base-install-notice-outer").remove();
	});
	//install base theme
	$(document.body).on('click', '.install-base-theme', function () {
		$(this).addClass('updating-message');
		var slug = $(this).data('slug').replace('-pro', '');

		$.ajax({
			type: "POST",
			url: direct_install.ajax_url,
			data: {
				action: 'install_base_theme',
				security: direct_install.nonce,
				slug: slug
			},
			success: function () {
				$(this).removeClass('updating-message');
				$('.base-install-prompt').remove();
				$('.base-install-success').show();

				// Add the new slug to installed list locally so subsequent clicks don't trigger popup
				if (direct_install.installed_themes) {
					direct_install.installed_themes.push(slug);
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {
				console.log(thrownError);
			}
		});
	});
});