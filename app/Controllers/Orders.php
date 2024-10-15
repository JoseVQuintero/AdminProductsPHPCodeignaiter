<?php
		namespace App\Controllers;
		use App\Controllers\BaseController;
		class Orders extends BaseController
		{
			public function index()
			{
				$data = array_merge($this->data, [
					'title'         => 'Orders'
				]);
				return view('orders', $data);
			}
		}
		