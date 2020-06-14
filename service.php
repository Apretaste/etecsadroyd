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
				'text' => 'Su busqueda esta vacía',
				'button' => ['href' => 'ETECSADROYD', 'caption' => 'Volver']];

			$response->setTemplate('message.ejs', $content);
			return;
		}

		$search = strtoupper($search);

		$results = Database::query("SELECT `name`, phone, 'La Habana' AS location FROM _directory WHERE `name` LIKE '%$search%' LIMIT 10");

		if (empty($results)) {
			$content = [
				'header' => '¡Sin resultados!',
				'icon' => 'sentiment_very_dissatisfied',
				'text' => "No hemos encontrado nada en el directorio para $search, por favor intente con otra busqueda.",
				'button' => ['href' => 'ETECSADROYD', 'caption' => 'Volver']];

			$response->setTemplate('message.ejs', $content);
			return;
		}

		foreach ($results as &$result){
			$result->name = ucwords(mb_strtolower($result->name));
		}

		$search = ucwords(mb_strtolower($search));

		// create response
		$response->setCache('month');
		$response->setTemplate('search.ejs', ['search' => $search, 'results' => $results]);
	}
}
