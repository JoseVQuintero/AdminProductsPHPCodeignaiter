<?php
		namespace App\Controllers;
		use App\Controllers\BaseController;
		class Categories extends BaseController
		{
			public function index()
			{
				$data = array_merge($this->data, [
					'title'         => 'Categories'
				]);
				return view('categories', $data);
			}
		}
		