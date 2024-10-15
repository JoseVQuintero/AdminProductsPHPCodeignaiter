<?php
		namespace App\Controllers;
		use App\Controllers\BaseController;
		class Manufacturer extends BaseController
		{
			public function index()
			{
				$data = array_merge($this->data, [
					'title'         => 'Manufacturer'
				]);
				return view('manufacturer', $data);
			}
		}
		