$(document).ready(function(){
	const path = window.location.pathname;
	const isCreate = path.indexOf('/web/create') !== -1;
	const isEdit = /\/web\/\d+\/edit/.test(path);

	if (!isCreate && !isEdit) {
		return;
	}

	const $detalle = $('#detalle');

	if ($detalle.length === 0 || !$detalle.is('textarea')) {
		return;
	}

	if (typeof CKEDITOR === 'undefined') {
		return;
	}

	try {
		CKEDITOR.replace('detalle', {
		    height: 200,
		      // By default, some basic text styles buttons are removed in the Standard preset.
		      // The code below resets the default config.removeButtons setting.
		      removeButtons: ''
		    });
	} catch (error) {
		console.warn('CKEditor skipped on this page:', error);
	}
});
