<?php
		namespace App\Controllers;
		use App\Controllers\BaseController;
		class Contents extends BaseController
		{
			public function index()
			{
				$data = array_merge($this->data, [
					'title'         => 'Contents'
				]);
				return view('contents', $data);
			}
		}
		