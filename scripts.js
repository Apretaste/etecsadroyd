var provinces = {
	'PINAR_DEL_RIO': 'Pinar del Río',
	'LA_HABANA': 'La Habana',
	'ARTEMISA': 'Artemisa',
	'MAYABEQUE': 'Mayabeque',
	'MATANZAS': 'Matanzas',
	'VILLA_CLARA': 'Villa Clara',
	'CIENFUEGOS': 'Cienfuegos',
	'SANCTI_SPIRITUS': 'Sancti Spiritus',
	'CIEGO_DE_AVILA': 'Ciego de Ávila',
	'CAMAGUEY': 'Camagüey',
	'LAS_TUNAS': 'Las Tunas',
	'HOLGUIN': 'Holguín',
	'GRANMA': 'Granma',
	'SANTIAGO_DE_CUBA': 'Santiago de Cuba',
	'GUANTANAMO': 'Guantánamo',
	'ISLA_DE_LA_JUVENTUD': 'Isla de la Juventud'
};

var types = {'FIX': 'Fijo', 'MOBILE': 'Móvil'};

$(function () {
	$('select').formSelect();
	M.updateTextFields();
})

function search() {
	var search = $('#search').val().trim();
	var province = $('#province').val();
	var type = $('#type').val();
	var address = $('#address').val().trim();

	if (search.length >= 3 || address !== '') {
		if (search.length > 40 || address.length > 40) {
			showToast('Maximo 40 caracteres');
			return;
		}

		if (search !== '') {
			if (!isNaN(search) && (search.length > 11 || search.length < 3)) {
				showToast('Número inválido');
				return;
			} else if (search[0] === '+' && (search.substr(0, 3) !== '+53' || search.length > 14 || search.length < 6 || isNaN(search.substr(3)))) {
				showToast('Número inválido');
				return;
			}
		}

		apretaste.send({
			'command': 'ETECSADROYD BUSCAR',
			'data': {
				'search': search,
				'province': province,
				'type': type,
				'address': address
			}
		});
	} else {
		showToast('Mínimo 3 caracteres');
	}
}

function showToast(text) {
	M.toast({
		html: text
	});
}


// POLLYFILL

function _typeof(obj) {
	if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
		_typeof = function _typeof(obj) {
			return typeof obj;
		};
	} else {
		_typeof = function _typeof(obj) {
			return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
		};
	}
	return _typeof(obj);
}

if (!Object.keys) {
	Object.keys = function () {
		'use strict';

		var hasOwnProperty = Object.prototype.hasOwnProperty,
			hasDontEnumBug = !{
				toString: null
			}.propertyIsEnumerable('toString'),
			dontEnums = ['toString', 'toLocaleString', 'valueOf', 'hasOwnProperty', 'isPrototypeOf', 'propertyIsEnumerable', 'constructor'],
			dontEnumsLength = dontEnums.length;

		return function (obj) {
			if (_typeof(obj) !== 'object' && (typeof obj !== 'function' || obj === null)) {
				throw new TypeError('Object.keys called on non-object');
			}

			var result = [],
				prop,
				i;

			for (prop in obj) {
				if (hasOwnProperty.call(obj, prop)) {
					result.push(prop);
				}
			}

			if (hasDontEnumBug) {
				for (i = 0; i < dontEnumsLength; i++) {
					if (hasOwnProperty.call(obj, dontEnums[i])) {
						result.push(dontEnums[i]);
					}
				}
			}

			return result;
		};
	}();
}