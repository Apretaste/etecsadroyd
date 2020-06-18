<?php

use Framework\Core;
use Apretaste\Request;
use Apretaste\Response;
use Framework\Database;

class Service
{

	/**
	 * Main function
	 *
	 * @param Request $request
	 * @param Response $response
	 *
	 * @throws \Framework\Alert
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
		$search = $request->input->data->search ?? false;
		$address = $request->input->data->address ?? false;

		if (!$search && !$address) {
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
			} else if ($search[0] == '+') {
				if (substr($search, 0, 3) == '+53' && strlen($search) >= 6 && strlen($search) <= 14) {
					$search = str_replace('+53', '', $search);
					$invalidNumber = intval($search) == 0;
					$column = 'phone';
				} else $invalidNumber = true;
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
		} else $search = $address;

		$type = $request->input->data->type ?? 'ALL';
		$province = $request->input->data->province ?? 'ALL';

		if ($type != 'ALL') $extraWhere .= " AND type='$type'";
		if ($province != 'ALL') $extraWhere .= " AND province='$province'";

		$search = strtoupper($search);
		$searchQuery = $searchQuery = "MATCH(`$column`) AGAINST('+$search' IN BOOLEAN MODE)";
		if ($column == 'phone' && $search) {
			$search = implode(' +', explode(' ', $search));
			$searchQuery = "RIGHT(CONCAT(IF(type = 'FIX', code, '53'), phone), LENGTH($search)) = $search";
		} else if (!empty($address)) {
			$address = implode(' ,', explode(' ', $address));
			$searchQuery = " MATCH(`address`) AGAINST('$address') AND personal=0";
		}

		$results = Database::query("SELECT `name`, phone, `type`, personal, province, address FROM _directory WHERE $searchQuery $extraWhere LIMIT 10");

		if (empty($results)) {
			$content = [
				'header' => '¡Sin resultados!',
				'icon' => 'sentiment_very_dissatisfied',
				'text' => "No hemos encontrado nada en el directorio para $search, por favor intente con otra búsqueda.",
				'button' => ['href' => 'ETECSADROYD', 'caption' => 'Volver']];

			$response->setTemplate('message.ejs', $content);
			return;
		}

		foreach ($results as &$result) {
			$result->name = ucwords(mb_strtolower($result->name));
			if ($result->personal) unset($result->address);
			else $result->address = ucwords(mb_strtolower($result->address));
		}

		$search = ucwords(mb_strtolower($search));

		// create response
		$response->setCache('month');
		$response->setTemplate('search.ejs', ['search' => $search, 'results' => $results]);
	}
}
