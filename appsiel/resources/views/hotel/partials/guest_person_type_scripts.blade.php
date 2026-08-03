<?php
    $hotelGuestNaturalFieldIds = array();

    if (class_exists('App\\Hotel\\HotelGuest')) {
        $hotelGuestNaturalFieldIds = App\Hotel\HotelGuest::hotelFieldIds();
    }
?>

<script type="text/javascript">
    (function ($) {
        if (window.hotelGuestPersonTypeScriptLoaded) {
            return;
        }

        window.hotelGuestPersonTypeScriptLoaded = true;

        var hotelGuestNaturalFieldIds = {!! json_encode(array_values($hotelGuestNaturalFieldIds)) !!};

        function getFieldNames() {
            var names = [];

            $.each(hotelGuestNaturalFieldIds, function (index, fieldId) {
                fieldId = parseInt(fieldId, 10);
                if (fieldId > 0) {
                    names.push('core_campo_id-' + fieldId);
                }
            });

            return names;
        }

        function getFieldContainer($field) {
            var $container = $field.closest('.form-group');

            if ($container.length > 0) {
                return $container;
            }

            $container = $field.closest('.row');
            if ($container.length > 0) {
                return $container;
            }

            return $field.parent();
        }

        function getSelectedText($field) {
            if ($field.is('select')) {
                return $.trim($field.find('option:selected').text());
            }

            return $.trim($field.val());
        }

        function isPersonaJuridica($field) {
            var text = (getSelectedText($field) + ' ' + $.trim($field.val())).toLowerCase();
            text = text.replace(/í/g, 'i');

            return text.indexOf('persona juridica') !== -1;
        }

        function setHiddenNaturalFields($scope, hidden) {
            var names = getFieldNames();
            var found = false;

            $.each(names, function (index, name) {
                var $field = $scope.find('[name="' + name + '"], [name="' + name + '[]"]');

                if ($field.length === 0) {
                    return true;
                }

                found = true;

                $field.each(function () {
                    var $currentField = $(this);
                    var $container = getFieldContainer($currentField);

                    if (hidden) {
                        if ($currentField.prop('required')) {
                            $currentField.attr('data-hotel-required-hidden', '1');
                        }
                        $currentField.prop('required', false);
                        $container.hide();
                    } else {
                        if ($currentField.attr('data-hotel-required-hidden') == '1') {
                            $currentField.prop('required', true);
                            $currentField.removeAttr('data-hotel-required-hidden');
                        }
                        $container.show();
                    }
                });
            });

            return found;
        }

        function refreshHotelGuestNaturalFields($scope) {
            var $root = $scope || $(document);
            var $forms = $root.is('form') ? $root : $root.find('form');

            if ($forms.length === 0) {
                $forms = $(document);
            }

            $forms.each(function () {
                var $form = $(this);

                if (!setHiddenNaturalFields($form, false)) {
                    return true;
                }

                var $tipo = $form.find('[name="tipo"]').first();
                if ($tipo.length === 0) {
                    return true;
                }

                setHiddenNaturalFields($form, isPersonaJuridica($tipo));
            });
        }

        $(document).off('change.hotelGuestPersonType', '[name="tipo"]');
        $(document).on('change.hotelGuestPersonType', '[name="tipo"]', function () {
            refreshHotelGuestNaturalFields($(this).closest('form'));
        });

        $(document).ready(function () {
            refreshHotelGuestNaturalFields($(document));
        });

        $(document).off('shown.bs.modal.hotelGuestPersonType', '#hotelClienteAutocompleteModal, #hotelGuestCreateModal');
        $(document).on('shown.bs.modal.hotelGuestPersonType', '#hotelClienteAutocompleteModal, #hotelGuestCreateModal', function () {
            refreshHotelGuestNaturalFields($(this));
        });
    })(jQuery);
</script>
