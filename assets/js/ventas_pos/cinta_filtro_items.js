var POS_ITEM_SEARCH_MIN_LENGTH = 2;
var POS_ITEM_SEARCH_MAX_RESULTS = 80;
var POS_ITEM_SEARCH_DEBOUNCE_MS = 180;

var posItemSearchTimer = null;
var posItemSearchSequence = 0;
var posItemSearchIndex = [];
var posItemSearchProductsReference = null;
var posItemPriceMap = {};
var posItemPricesReference = null;

function normalizePosItemSearchText(value)
{
    var text = (value == null ? '' : value.toString()).toLowerCase();

    if (typeof text.normalize === 'function') {
        text = text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    return text.replace(/\s+/g, ' ').trim();
}

function escapePosItemHtml(value)
{
    return $('<div>').text(value == null ? '' : value.toString()).html();
}

function buildPosItemSearchIndex()
{
    if (!Array.isArray(productos)) {
        posItemSearchIndex = [];
        posItemSearchProductsReference = productos;
        return;
    }

    if (posItemSearchProductsReference === productos) {
        return;
    }

    posItemSearchIndex = [];
    productos.forEach(function(item) {
        if (!item.mostrar_grupo_en_pagina_web || item.estado !== 'Activo') {
            return;
        }

        var reference = item.referencia || '';
        var description = item.descripcion || '';
        posItemSearchIndex.push({
            item: item,
            label: (reference + ' ' + description + ' (' + item.id + ')').trim(),
            searchableText: normalizePosItemSearchText(reference + ' ' + description + ' ' + item.id)
        });
    });

    posItemSearchProductsReference = productos;
}

function buildPosItemPriceMap()
{
    if (posItemPricesReference === precios) {
        return;
    }

    posItemPriceMap = {};
    if (Array.isArray(precios)) {
        precios.forEach(function(price) {
            posItemPriceMap[parseInt(price.producto_codigo, 10)] = parseFloat(price.precio) || 0;
        });
    }

    posItemPricesReference = precios;
}

function getPosItemSearchPrice(itemId)
{
    buildPosItemPriceMap();
    return posItemPriceMap[parseInt(itemId, 10)] || 0;
}

function draw_items(indexedItems, hasMoreResults)
{
    var html = [];

    indexedItems.forEach(function(indexedItem) {
        var item = indexedItem.item;
        var price = Math.round(getPosItemSearchPrice(item.id));

        html.push(
            '<button type="button" onclick="mandar_codigo4(' + parseInt(item.id, 10) + ')"' +
            ' class="icono_item" data-label_item="' + escapePosItemHtml(indexedItem.searchableText) + '">' +
            escapePosItemHtml(indexedItem.label) +
            '<b> $' + new Intl.NumberFormat('de-DE').format(price) + '</b></button>'
        );
    });

    $('#pos_item_search_results').html(html.join(''));

    if (indexedItems.length === 0) {
        setPosItemSearchMessage('No se encontraron productos.', false, true);
    } else if (hasMoreResults) {
        setPosItemSearchMessage('Mostrando los primeros ' + POS_ITEM_SEARCH_MAX_RESULTS + ' resultados. Escriba más para precisar la búsqueda.', false, true);
    } else {
        setPosItemSearchMessage(indexedItems.length + (indexedItems.length === 1 ? ' producto encontrado.' : ' productos encontrados.'), false, true);
    }
}

function filterItems(query)
{
    buildPosItemSearchIndex();

    var normalizedQuery = normalizePosItemSearchText(query);
    var terms = normalizedQuery.split(' ').filter(function(term) {
        return term !== '';
    });
    var matches = [];
    var hasMoreResults = false;

    for (var index = 0; index < posItemSearchIndex.length; index++) {
        var indexedItem = posItemSearchIndex[index];
        var matchesEveryTerm = true;

        for (var termIndex = 0; termIndex < terms.length; termIndex++) {
            if (indexedItem.searchableText.indexOf(terms[termIndex]) === -1) {
                matchesEveryTerm = false;
                break;
            }
        }

        if (!matchesEveryTerm) {
            continue;
        }

        if (matches.length >= POS_ITEM_SEARCH_MAX_RESULTS) {
            hasMoreResults = true;
            break;
        }

        matches.push(indexedItem);
    }

    draw_items(matches, hasMoreResults);
    return false;
}

function setPosItemSearchMessage(message, searching, visible)
{
    $('#pos_item_search_message').text(message || '');
    $('#pos_item_search_spinner').toggle(!!searching);
    $('#pos_item_search_status').toggle(visible !== false);
}

function clearPosItemSearch()
{
    posItemSearchSequence++;
    if (posItemSearchTimer !== null) {
        clearTimeout(posItemSearchTimer);
        posItemSearchTimer = null;
    }

    $('#pos_item_search_results').empty();
    $('#pos_item_search_status').hide();
}

function schedulePosItemSearch(query)
{
    var sequence = ++posItemSearchSequence;
    if (posItemSearchTimer !== null) {
        clearTimeout(posItemSearchTimer);
    }

    setPosItemSearchMessage('Buscando productos...', true, true);
    posItemSearchTimer = setTimeout(function() {
        if (sequence !== posItemSearchSequence) {
            return;
        }

        // Permite que el navegador pinte el spinner antes del trabajo sincrono.
        setTimeout(function() {
            if (sequence !== posItemSearchSequence) {
                return;
            }

            filterItems(query);
            posItemSearchTimer = null;
        }, 0);
    }, POS_ITEM_SEARCH_DEBOUNCE_MS);
}

$(document).ready(function () {
    $('#textinput_filter_item').on('keyup', function (event) {
        $("[data-toggle='tooltip']").tooltip('hide');
        $('#popup_alerta').hide();

        var keyCode = event.which || event.keyCode;

        switch (keyCode) {
            case 27: // ESC
                clearPosItemSearch();
                $('#efectivo_recibido').focus().select();
                return false;

            case 13: // Enter
                if ($(this).val() !== '') {
                    $('#quantity').select();
                }
                return false;

            case 113: // F2
                clearPosItemSearch();
                $('#inv_producto_id').focus();
                return false;
        }

        var query = $(this).val();
        if (normalizePosItemSearchText(query).length < POS_ITEM_SEARCH_MIN_LENGTH) {
            clearPosItemSearch();
            return false;
        }

        schedulePosItemSearch(query);
    });

    $(document).on('focus', '#textinput_filter_item', function () {
        $(this).select();
    });

    $(document).on('keyup', '#quantity', function (event) {
        var keyCode = event.which || event.keyCode;
        if (keyCode === 13) {
            $(this).next().focus();
        }
        if (keyCode === 113) {
            $('#textinput_filter_item').select();
        }
    });

    $(document).on('focus', '#quantity', function () {
        $(this).select();
    });

    $(document).on('keyup', '.icono_item', function (event) {
        var keyCode = event.which || event.keyCode;
        if (keyCode === 113) {
            $('#textinput_filter_item').select();
        }
    });

    $(document).on('focus', '.icono_item', function () {
        $(this).css({ background: '#574696', color: 'white' });
    });

    $(document).on('blur', '.icono_item', function () {
        $(this).css({ background: '#ddd', color: 'black' });
    });
});
