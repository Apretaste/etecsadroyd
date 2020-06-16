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
		if (!$search) {
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

		if (intval($search[0]) != 0) {
			if (strlen($search) != 8 || intval($search) == 0) {
				$invalidNumber = true;
			}

			$column = 'phone';
		} else if ($search[0] == '+') {
			if (substr($search, 0, 3) == '+53' && strlen($search) == 11) {
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

		$search = strtoupper($search);

		$results = Database::query("SELECT `name`, phone, 'La Habana' AS location FROM _directory WHERE `$column` LIKE '%$search%' LIMIT 10");

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
		}

		$search = ucwords(mb_strtolower($search));

		// create response
		$response->setCache('month');
		$response->setTemplate('search.ejs', ['search' => $search, 'results' => $results]);
	}
}
