<?php

use Apretaste\Request;
use Apretaste\Response;
use Framework\Database;
use Apretaste\Challenges;

class Service
{
	/**
	 * Main function
	 *
	 * @param Request $request
	 * @param Response $response
	 * @author salvipascual
	 */
	public function _main(Request $request, Response $response)
	{
		// create response
		$response->setCache('year');
		$response->setTemplate('main.ejs', []);
	}

	public function _buscar(Request $request, Response $response)
	{
		$search = $request->input->data->search ?? '';
		$address = $request->input->data->address ?? '';
		$type = $request->input->data->type ?? 'ALL';
		$province = $request->input->data->province ?? 'ALL';

		// clear input texts
		$cleaner = function ($str) {
			return preg_replace('/[^[:alnum:][:space:]]/u', '', $str);
		};

		$search = $cleaner($search);
		$address = $cleaner($address);

		if (empty($search.$address)) {
			$content = [
				'header' => 'Busqueda vacía',
				'icon' => 'warning',
				'text' => 'Su búsqueda esta vacía',
				'button' => ['href' => 'ETECSADROYD', 'caption' => 'Volver']];

			$response->setTemplate('message.ejs', $content);
			return;
		}

		$column = "name";
		$invalidNumber = false;
		$extraWhere = '';

		if ($search) {
			// Min 3 Max 11

			if (intval($search[0]) != 0) {
				$invalidNumber = strlen($search) > 11 || strlen($search) < 3 || intval($search) == 0;
				$column = 'phone';
			} elseif ($search[0] == '+') {
				if (substr($search, 0, 3) == '+53' && strlen($search) >= 6 && strlen($search) <= 14) {
					$search = str_replace('+53', '', $search);
					$invalidNumber = intval($search) == 0;
					$column = 'phone';
				} else {
					$invalidNumber = true;
				}
			}

			if ($invalidNumber) {
				$content = [
					'header' => 'Número inválido',
					'icon' => 'warning',
					'text' => "El número {$request->input->data->search} no es un numero valido en Cuba.",
					'button' => ['href' => 'ETECSADROYD', 'caption' => 'Volver']];

				$response->setTemplate('message.ejs', $content);
				return;
			}
		}

		$extraWhere .= $type != 'ALL' ? " AND type = '$type'": "";
		$extraWhere .= $province != 'ALL' ? " AND province = '$province'": "";

		$searchQuery = '';
		$addressQuery = '';
		if ($search) {
			$search = preg_replace('!\s+!', ' ', strtoupper($search));
			$search = trim(str_replace('+','', $search);
			$escapedSearch = Database::escape($search);

			if ($column == 'phone') {
				$escapedSearch = str_replace(' ', '', $escapedSearch);
				$searchQuery = "RIGHT(CONCAT(IF(type = 'FIX', code, '53'), phone), LENGTH('$escapedSearch')) = '$escapedSearch'";
			} elseif ($column == 'name') {
				$escapedSearch = implode(' +', explode(' ', $escapedSearch));
				$searchQuery = "MATCH(`$column`) AGAINST('+$escapedSearch' IN BOOLEAN MODE)";
			}
		}


		if (!empty($address)) {
			$address = preg_replace('!\s+!', ' ', $address);
			$escapedAddress = Database::escape($address);
			$escapedAddress = implode(' ,', explode(' ', $escapedAddress));
			$addressQuery = " MATCH(`address`) AGAINST('$escapedAddress') AND personal=0";
			if ($search) {
				$addressQuery = 'AND' . $addressQuery;
			}
		}

		$results = Database::query("SELECT `name`, phone, `type`, personal, province, address FROM _directory WHERE $searchQuery $addressQuery $extraWhere LIMIT 10");

		$search = str_replace('+', '', $search);
		$address = str_replace(',', '', $address);

		if (empty($results)) {
			$content = [
				'header' => '¡Sin resultados!',
				'icon' => 'sentiment_very_dissatisfied',
				'text' => "No hemos encontrado nada en el directorio para su busqueda, por favor intente con otra búsqueda.",
				'button' => ['href' => 'ETECSADROYD', 'caption' => 'Volver']];

			$response->setTemplate('message.ejs', $content);
			return;
		}

		foreach ($results as &$result) {
			$result->name = ucwords(mb_strtolower($result->name));
			if ($result->personal) {
				unset($result->address);
			} else {
				$result->address = ucwords(mb_strtolower($result->address));
			}
		}

		$search = ucwords(mb_strtolower($search));

		str_replace('+', '', $search);

		$content = [
			'search' => $search,
			'province' => $province,
			'type' => $type,
			'address' => $address,
			'results' => $results
		];

		Challenges::complete('search-etecsa', $request->person->id);

		// create response
		$response->setCache('month');
		$response->setTemplate('search.ejs', $content);
	}
}
